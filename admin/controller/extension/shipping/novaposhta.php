<?php
class ControllerExtensionShippingNovaposhta extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/shipping/novaposhta');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('shipping_novaposhta', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['api_key'])) {
            $data['error_api_key'] = $this->error['api_key'];
        } else {
            $data['error_api_key'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/novaposhta', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/shipping/novaposhta', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);

        // ГЛОБАЛЬНІ налаштування (статус модуля, API ключ, загальний порядок сортування)
        if (isset($this->request->post['shipping_novaposhta_api_key'])) {
            $data['shipping_novaposhta_api_key'] = $this->request->post['shipping_novaposhta_api_key'];
        } else {
            $data['shipping_novaposhta_api_key'] = $this->config->get('shipping_novaposhta_api_key');
        }

        if (isset($this->request->post['shipping_novaposhta_status'])) {
            $data['shipping_novaposhta_status'] = $this->request->post['shipping_novaposhta_status'];
        } else {
            $data['shipping_novaposhta_status'] = $this->config->get('shipping_novaposhta_status');
        }

        if (isset($this->request->post['shipping_novaposhta_sort_order'])) {
            $data['shipping_novaposhta_sort_order'] = $this->request->post['shipping_novaposhta_sort_order'];
        } else {
            $data['shipping_novaposhta_sort_order'] = $this->config->get('shipping_novaposhta_sort_order');
        }

        // НАЛАШТУВАННЯ ДЛЯ ГРУП КОРИСТУВАЧІВ
        if (isset($this->request->post['shipping_novaposhta_customer_group_settings'])) {
            $data['shipping_novaposhta_customer_group_settings'] = $this->request->post['shipping_novaposhta_customer_group_settings'];
        } else {
            $data['shipping_novaposhta_customer_group_settings'] = $this->config->get('shipping_novaposhta_customer_group_settings');
        }

        // Для зворотної сумісності - всі старі поля для всіх методів
        $methods = ['courier', 'department', 'parcelbox'];

        foreach ($methods as $method) {
            $legacy_fields = [
                "shipping_novaposhta_{$method}_status",
                "shipping_novaposhta_{$method}_title",
                "shipping_novaposhta_{$method}_description",
                "shipping_novaposhta_{$method}_cost",
                "shipping_novaposhta_{$method}_min_sum",
                "shipping_novaposhta_{$method}_max_sum",
                "shipping_novaposhta_{$method}_free_sum",
                "shipping_novaposhta_{$method}_free_text",
                "shipping_novaposhta_{$method}_tax_class_id",
                "shipping_novaposhta_{$method}_geo_zone_id",
                "shipping_novaposhta_{$method}_sort_order"
            ];

            foreach ($legacy_fields as $field) {
                if (isset($this->request->post[$field])) {
                    $data[$field] = $this->request->post[$field];
                } else {
                    $data[$field] = $this->config->get($field);
                }
            }
        }

        // Завантаження моделей для дропдаунів
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // ДОДАЄМО ГРУПИ КОРИСТУВАЧІВ
        $this->load->model('customer/customer_group');
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        // API дані
        $this->load->model('extension/shipping/novaposhta');
        $data['regions_total'] = $this->model_extension_shipping_novaposhta->getRegionsTotal();
        $data['cities_total'] = $this->model_extension_shipping_novaposhta->getCitiesTotal();
        $data['departments_total'] = $this->model_extension_shipping_novaposhta->getDepartmentsTotal();

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/novaposhta', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/novaposhta')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['shipping_novaposhta_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    // API методи для оновлення довідників
    public function updateDepartments() {
        $this->load->language('extension/shipping/novaposhta');
        $this->load->model('extension/shipping/novaposhta');
        $this->load->library('novaposhta');

        $json = ['old_total' => 0, 'new_total' => 0];

        try {
            $json['old_total'] = $this->model_extension_shipping_novaposhta->getDepartmentsTotal();

            $total_processed = 0;
            $first_batch_loaded = false;
            
            // Загружаем и вставляем батчами
            $callback = function($chunk) use (&$total_processed, &$first_batch_loaded) {
                // Если первый батч успешно загружен - очищаем таблицу
                if (!$first_batch_loaded) {
                    $this->model_extension_shipping_novaposhta->truncateDepartmentsTable();
                    $first_batch_loaded = true;
                }
                
                // Вставляем батч
                $processed = $this->model_extension_shipping_novaposhta->addDepartmentsBatch($chunk);
                $total_processed += $processed;
                return $processed;
            };
            
            $total_from_api = $this->novaposhta->getDepartmentsChunked($callback);
            
            // Проверяем что загрузилось
            if ($total_processed === 0) {
                throw new Exception("Failed to retrieve departments from API - no data received");
            }
            
            $json['api_returned'] = $total_from_api;
            $json['new_total'] = $total_processed;
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function updateCities() {
        $this->load->language('extension/shipping/novaposhta');
        $this->load->model('extension/shipping/novaposhta');
        $this->load->library('novaposhta');

        $json = ['old_total' => 0, 'new_total' => 0];

        try {
            $json['old_total'] = $this->model_extension_shipping_novaposhta->getCitiesTotal();

            $total_processed = 0;
            $first_batch_loaded = false;
            
            // Загружаем и вставляем батчами
            $callback = function($chunk) use (&$total_processed, &$first_batch_loaded) {
                // Если первый батч успешно загружен - очищаем таблицу
                if (!$first_batch_loaded) {
                    $this->model_extension_shipping_novaposhta->truncateCitiesTable();
                    $first_batch_loaded = true;
                }
                
                // Вставляем батч
                $processed = $this->model_extension_shipping_novaposhta->addCitiesBatch($chunk);
                $total_processed += $processed;
                return $processed;
            };
            
            $total_from_api = $this->novaposhta->getCitiesChunked($callback);
            
            // Проверяем что загрузилось
            if ($total_processed === 0) {
                throw new Exception("Failed to retrieve cities from API - no data received");
            }
            
            $json['api_returned'] = $total_from_api;
            $json['new_total'] = $total_processed;
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function updateRegions() {
        $this->load->language('extension/shipping/novaposhta');
        $this->load->model('extension/shipping/novaposhta');
        $this->load->library('novaposhta');

        $json = ['old_total' => 0, 'new_total' => 0];

        try {
            $json['old_total'] = $this->model_extension_shipping_novaposhta->getRegionsTotal();

            $total_processed = 0;
            $first_batch_loaded = false;
            
            // Загружаем и вставляем батчами
            $callback = function($chunk) use (&$total_processed, &$first_batch_loaded) {
                // Если первый батч успешно загружен - очищаем таблицу
                if (!$first_batch_loaded) {
                    $this->model_extension_shipping_novaposhta->truncateRegionsTable();
                    $first_batch_loaded = true;
                }
                
                // Вставляем батч
                $processed = $this->model_extension_shipping_novaposhta->addRegionsBatch($chunk);
                $total_processed += $processed;
                return $processed;
            };
            
            $total_from_api = $this->novaposhta->getRegionsChunked($callback);
            
            // Проверяем что загрузилось
            if ($total_processed === 0) {
                throw new Exception("Failed to retrieve regions from API - no data received");
            }
            
            $json['api_returned'] = $total_from_api;
            $json['new_total'] = $total_processed;
        } catch (Exception $e) {
            $json['error'] = $e->getMessage();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}