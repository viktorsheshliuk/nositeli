<?php
class ControllerExtensionModuleUkrposhtaTracking extends Controller {
    const DEFAULT_CRON_KEY = '77777';

    private $error = array();

    public function index() {
        $this->load->language('extension/module/ukrposhta_tracking');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $setting_data = $this->request->post;

            // Відповідність статусів Укрпошти статусам замовлень
            if (isset($this->request->post['module_ukrposhta_tracking_status_map'])) {
                $setting_data['module_ukrposhta_tracking_status_map'] = $this->normalizeStatusMap($this->request->post['module_ukrposhta_tracking_status_map']);
            } elseif (isset($this->request->post['ukr_status_map_present'])) {
                $setting_data['module_ukrposhta_tracking_status_map'] = array();
            }

            // Статуси замовлень, які відслідковуються
            if (!isset($this->request->post['module_ukrposhta_tracking_tracking_statuses']) && isset($this->request->post['ukr_tracking_statuses_present'])) {
                $setting_data['module_ukrposhta_tracking_tracking_statuses'] = array();
            }

            if (isset($setting_data['module_ukrposhta_tracking_tracking_statuses']) && is_array($setting_data['module_ukrposhta_tracking_tracking_statuses'])) {
                $setting_data['module_ukrposhta_tracking_tracking_statuses'] = array_values(array_filter(array_unique(array_map('intval', $setting_data['module_ukrposhta_tracking_tracking_statuses']))));
            }

            $this->model_setting_setting->editSetting('module_ukrposhta_tracking', $setting_data);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/ukrposhta_tracking', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/ukrposhta_tracking', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Статус модуля
        if (isset($this->request->post['module_ukrposhta_tracking_status'])) {
            $data['module_ukrposhta_tracking_status'] = $this->request->post['module_ukrposhta_tracking_status'];
        } else {
            $data['module_ukrposhta_tracking_status'] = $this->config->get('module_ukrposhta_tracking_status');
        }

        // Bearer токен Укрпошти
        if (isset($this->request->post['module_ukrposhta_tracking_bearer'])) {
            $data['module_ukrposhta_tracking_bearer'] = $this->request->post['module_ukrposhta_tracking_bearer'];
        } else {
            $data['module_ukrposhta_tracking_bearer'] = $this->config->get('module_ukrposhta_tracking_bearer');
        }

        // Автооновлення статусів
        if (isset($this->request->post['module_ukrposhta_tracking_tracking_enabled'])) {
            $data['module_ukrposhta_tracking_tracking_enabled'] = $this->request->post['module_ukrposhta_tracking_tracking_enabled'];
        } else {
            $data['module_ukrposhta_tracking_tracking_enabled'] = $this->config->get('module_ukrposhta_tracking_tracking_enabled');
        }

        // Статуси замовлень, які відслідковуються
        if (isset($this->request->post['module_ukrposhta_tracking_tracking_statuses'])) {
            $data['module_ukrposhta_tracking_tracking_statuses'] = array_values(array_map('intval', (array)$this->request->post['module_ukrposhta_tracking_tracking_statuses']));
        } else {
            $tracking_statuses = $this->config->get('module_ukrposhta_tracking_tracking_statuses');

            if (!is_array($tracking_statuses)) {
                $tracking_statuses = array();
            }

            $data['module_ukrposhta_tracking_tracking_statuses'] = array_values(array_map('intval', $tracking_statuses));
        }

        // Секретний ключ для cron
        if (isset($this->request->post['module_ukrposhta_tracking_cron_key'])) {
            $data['module_ukrposhta_tracking_cron_key'] = $this->request->post['module_ukrposhta_tracking_cron_key'];
        } else {
            $data['module_ukrposhta_tracking_cron_key'] = $this->config->get('module_ukrposhta_tracking_cron_key');
        }

        if (!$data['module_ukrposhta_tracking_cron_key']) {
            $data['module_ukrposhta_tracking_cron_key'] = self::DEFAULT_CRON_KEY;
        }

        // Карта відповідності статусів
        if (isset($this->request->post['module_ukrposhta_tracking_status_map'])) {
            $data['module_ukrposhta_tracking_status_map'] = $this->normalizeStatusMap($this->request->post['module_ukrposhta_tracking_status_map']);
        } else {
            $data['module_ukrposhta_tracking_status_map'] = $this->normalizeStatusMap($this->config->get('module_ukrposhta_tracking_status_map'));
        }

        // Довідник подій Укрпошти (для мультивибору)
        $this->load->model('extension/module/ukrposhta_tracking');
        $data['ukrposhta_statuses'] = $this->model_extension_module_ukrposhta_tracking->getUkrposhtaEvents();

        // Статуси замовлень магазину
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $data['cron_url'] = HTTP_CATALOG . 'index.php?route=api/ukrposhta/tracking&api_key=' . urlencode($data['module_ukrposhta_tracking_cron_key']);

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/ukrposhta_tracking', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/ukrposhta_tracking')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Нормалізація карти відповідності статусів
     * (прибирає порожні рядки та дублікати кодів Укрпошти)
     */
    protected function normalizeStatusMap($status_map) {
        $result = array();

        if (!is_array($status_map)) {
            return $result;
        }

        $used_codes = array();

        foreach ($status_map as $row) {
            if (!is_array($row) || empty($row['order_status_id'])) {
                continue;
            }

            $statuses = array();

            if (isset($row['statuses']) && is_array($row['statuses'])) {
                foreach ($row['statuses'] as $status_code) {
                    $status_code = (int)$status_code;

                    if ($status_code && !isset($used_codes[$status_code])) {
                        $used_codes[$status_code] = true;
                        $statuses[] = $status_code;
                    }
                }
            }

            if (!$statuses) {
                continue;
            }

            $result[] = array(
                'statuses'        => $statuses,
                'order_status_id' => (int)$row['order_status_id']
            );
        }

        return $result;
    }

    /**
     * Ручний запуск оновлення статусів замовлень (кнопка "Оновити статуси зараз")
     */
    public function runTracking() {
        $this->load->language('extension/module/ukrposhta_tracking');
        $this->load->model('extension/module/ukrposhta_tracking');
        $this->load->library('ukrposhtastatustracking');

        $json = array(
            'processed' => 0,
            'updated'   => 0,
            'skipped'   => 0,
            'errors'    => array()
        );

        try {
            if (!$this->user->hasPermission('modify', 'extension/module/ukrposhta_tracking')) {
                throw new Exception($this->language->get('error_permission'));
            }

            if (!$this->config->get('module_ukrposhta_tracking_tracking_enabled')) {
                throw new Exception($this->language->get('error_ukr_tracking_disabled'));
            }

            $orders = $this->model_extension_module_ukrposhta_tracking->getOrdersForTracking();

            if (!$orders) {
                throw new Exception($this->language->get('error_ukr_no_orders'));
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


