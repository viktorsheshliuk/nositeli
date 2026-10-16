<?php
// Подключаем автозагрузчик Composer (абсолютный путь, чтобы работало и в админке)
require_once dirname(DIR_SYSTEM) . '/vendor/autoload.php';

use GuzzleHttp\Client;

class UkrposhtaStatusTracking
{
    // Резервный токен, если настройки ещё не заданы в админке
    const DEFAULT_BEARER = '072bd668-2118-3173-a665-3defd27eb338';

    private $bearerToken;
    private $baseUrl;
    private $httpClient;

    /**
     * @param Registry $registry
     * @param bool $sandbox
     */
    public function __construct($registry, bool $sandbox = false)
    {
        $config = $registry->get('config');

        // Токен берём из настроек OpenCart
        $this->bearerToken = $config ? $config->get('module_ukrposhta_tracking_bearer') : '';

        if (!$this->bearerToken) {
            $this->bearerToken = self::DEFAULT_BEARER;
        }

        $this->baseUrl = $sandbox
            ? 'https://dev.ukrposhta.ua/status-tracking/0.0.1'
            : 'https://www.ukrposhta.ua/status-tracking/0.0.1';

        $this->httpClient = new Client([
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ],
        ]);
    }

    public function getLastStatus(string $barcode, ?string $lang = null): array
    {
        $query = ['barcode' => $barcode];
        if ($lang) {
            $query['lang'] = $lang;
        }

        $response = $this->httpClient->get($this->baseUrl . '/statuses/last', [
            'query' => $query,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getAllStatuses(string $barcode, ?string $lang = null): array
    {
        $query = ['barcode' => $barcode];
        if ($lang) {
            $query['lang'] = $lang;
        }

        $response = $this->httpClient->get($this->baseUrl . '/statuses', [
            'query' => $query,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Синхронизация статусов заказов со статусами Укрпошты
     *
     * @param array    $orders   [['order_id' => int, 'order_status_id' => int, 'ttn' => string], ...]
     * @param array    $status_map  карта соответствия: [event Укрпошты => order_status_id магазина]
     * @param callable $status_change_callback  function($order_id, $order_status_id)
     *
     * @return array ['processed' => int, 'updated' => int, 'skipped' => int, 'errors' => array]
     */
    public function syncOrderStatuses($orders, $status_map, $status_change_callback) {
        $result = array(
            'processed' => 0,
            'updated'   => 0,
            'skipped'   => 0,
            'errors'    => array()
        );

        if (!$orders) {
            $result['errors'][] = 'Нет заказов для отслеживания';

            return $result;
        }

        if (!$status_map || !is_array($status_map)) {
            $result['errors'][] = 'Не настроено соответствие статусов';

            return $result;
        }

        $result['processed'] = count($orders);

        foreach ($orders as $order) {
            if (empty($order['ttn'])) {
                $result['skipped']++;

                continue;
            }

            try {
                $status = $this->getLastStatus($order['ttn']);
            } catch (Exception $e) {
                $result['errors'][] = 'ТТН ' . $order['ttn'] . ': ' . $e->getMessage();
                $result['skipped']++;

                continue;
            }

            if (!is_array($status) || empty($status['event'])) {
                $result['skipped']++;

                continue;
            }

            $event = (int)$status['event'];

            // Особый случай: возврат отправителю (event=41000 + reason=10 => 41010)
            $event_reason = isset($status['eventReason_id']) ? $status['eventReason_id'] : (isset($status['eventReasonId']) ? $status['eventReasonId'] : 0);

            if ($event == 41000 && (int)$event_reason == 10) {
                $event = 41010;
            }

            if (!isset($status_map[$event])) {
                $result['skipped']++;

                continue;
            }

            $order_status_id = (int)$status_map[$event];

            // Статус уже такой же - ничего не меняем
            if ((int)$order['order_status_id'] == $order_status_id) {
                $result['skipped']++;

                continue;
            }

            call_user_func($status_change_callback, $order['order_id'], $order_status_id);

            $result['updated']++;
        }

        return $result;
    }

}