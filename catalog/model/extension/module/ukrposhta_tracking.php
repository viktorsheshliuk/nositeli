<?php
class ModelExtensionModuleUkrposhtaTracking extends Model {
    /**
     * Замовлення, які потрібно відслідковувати (за налаштованими статусами)
     */
    public function getOrdersForTracking() {
        $statuses = $this->config->get('module_ukrposhta_tracking_tracking_statuses');

        if (!is_array($statuses) || !$statuses) {
            return array();
        }

        $statuses = array_filter(array_map('intval', $statuses));

        if (!$statuses) {
            return array();
        }

        // Укрпошта зберігає трек-номери в тій самій таблиці, що й Нова пошта.
        // Замовлення Укрпошти має shipping_code = 'filterit2.filterit0'
        $query = $this->db->query("
            SELECT nttn.ttn AS ttn, o.order_status_id AS order_status_id, nttn.order_id AS order_id
            FROM `" . DB_PREFIX . "novaposhta_ttn` nttn
            LEFT JOIN `" . DB_PREFIX . "order` o ON (nttn.order_id = o.order_id)
            WHERE o.order_status_id IN (" . implode(',', $statuses) . ")
            AND nttn.ttn != ''
            AND o.shipping_code = 'filterit2.filterit0'
            AND o.date_added >= NOW() - INTERVAL 50 DAY
        ");

        return $query->rows;
    }

    /**
     * Карта відповідності: event Укрпошти => order_status_id магазину
     */
    public function getOrderStatusMap() {
        $status_map = $this->config->get('module_ukrposhta_tracking_status_map');
        $result = array();

        if (!is_array($status_map)) {
            return $result;
        }

        foreach ($status_map as $row) {
            if (empty($row['order_status_id']) || empty($row['statuses']) || !is_array($row['statuses'])) {
                continue;
            }

            foreach ($row['statuses'] as $status_code) {
                $status_code = (int)$status_code;

                // Перший рядок має пріоритет
                if ($status_code && !isset($result[$status_code])) {
                    $result[$status_code] = (int)$row['order_status_id'];
                }
            }
        }

        return $result;
    }

    /**
     * Зміна статусу замовлення без сповіщень
     */
    public function setOrderStatus($order_id, $order_status_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_history` SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = 'Автоматическое обновление', date_added = NOW()");
    }
}