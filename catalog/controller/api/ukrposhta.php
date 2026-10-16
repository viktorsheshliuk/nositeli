<?php
/**
 * Cron-эндпоинт для автообновления статусов заказов Укрпошты.
 * Пример вызова: index.php?route=api/ukrposhta/tracking&api_key=ВАШ_КЛЮЧ
 */
class ControllerApiUkrposhta extends Controller {
    const DEFAULT_CRON_KEY = '77777';

    private function getCronKey() {
        $cron_key = $this->config->get('module_ukrposhta_tracking_cron_key');

        if (!$cron_key) {
            $cron_key = self::DEFAULT_CRON_KEY;
        }

        return (string)$cron_key;
    }

    private function validateApiKey() {
        $api_key = isset($this->request->get['api_key']) ? (string)$this->request->get['api_key'] : '';

        if (!hash_equals($this->getCronKey(), $api_key)) {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode(array('error' => 'Invalid API key')));

            return false;
        }

        return true;
    }

    public function tracking() {
        if (!$this->validateApiKey()) {
            return;
        }

        $this->load->model('extension/module/ukrposhta_tracking');
        $this->load->library('ukrposhtastatustracking');

        $json = array(
            'processed' => 0,
            'updated'   => 0,
            'skipped'   => 0,
            'errors'    => array()
        );

        try {
            if (!$this->config->get('module_ukrposhta_tracking_tracking_enabled')) {
                throw new Exception('Tracking is disabled');
            }

            $orders = $this->model_extension_module_ukrposhta_tracking->getOrdersForTracking();

            if (!$orders) {
                throw new Exception('No orders for tracking');
            }

            $status_map = $this->model_extension_module_ukrposhta_tracking->getOrderStatusMap();

            $json = $this->ukrposhtastatustracking->syncOrderStatuses($orders, $status_map, function ($order_id, $order_status_id) {
                $this->model_extension_module_ukrposhta_tracking->setOrderStatus($order_id, $order_status_id);
            });
        } catch (Exception $e) {
            $json['errors'][] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json, JSON_UNESCAPED_UNICODE));
    }
}
