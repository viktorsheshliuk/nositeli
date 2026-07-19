<?php

class ModelExtensionShippingNovaposhta extends Model {
    private $error = array();

    public function getQuote($address) {
        $this->load->language('extension/shipping/novaposhta');

        $method_data = array();
        $quote_data = array();

        $language_id = $this->config->get('config_language_id');
        $subtotal = $this->cart->getSubTotal();

        // Получаем группу текущего пользователя
        $customer_group_id = 0;
        if ($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getGroupId();
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        // Доставка до відділення
        if ($this->config->get('shipping_novaposhta_department_status')) {
            $status = $this->checkGeoZoneStatus(
                $this->config->get('shipping_novaposhta_department_geo_zone_id'),
                $address
            );

            if ($status) {
                $settings = $this->getShippingSettings('department', $customer_group_id);

                $status = $this->checkOrderLimits(
                    $subtotal,
                    $settings['min_sum'],
                    $settings['max_sum']
                );
            }

            if ($status && $settings['status']) {
                $title = $this->config->get('shipping_novaposhta_department_title');
                $description = $this->config->get('shipping_novaposhta_department_description');

                $text = $this->getShippingText(
                    $settings['cost'],
                    $subtotal,
                    $settings['free_sum'],
                    $this->config->get('shipping_novaposhta_department_free_text'),
                    $language_id,
                    $this->config->get('shipping_novaposhta_department_tax_class_id'),
                    $settings['discount_percent']
                );

                $quote_data['department'] = array(
                    'code' => 'novaposhta.department',
                    'title' => isset($title[$language_id]) ? $title[$language_id] : $this->language->get('text_department_title'),
                    'description' => isset($description[$language_id]) ? html_entity_decode($description[$language_id]) : '',
                    'cost' => $settings['cost'],
                    'tax_class_id' => $this->config->get('shipping_novaposhta_department_tax_class_id'),
                    'text' => $text
                );
            }
        }

        // Доставка кур'єром
        if ($this->config->get('shipping_novaposhta_courier_status')) {
            $status = $this->checkGeoZoneStatus(
                $this->config->get('shipping_novaposhta_courier_geo_zone_id'),
                $address
            );

            if ($status) {
                $settings = $this->getShippingSettings('courier', $customer_group_id);

                $status = $this->checkOrderLimits(
                    $subtotal,
                    $settings['min_sum'],
                    $settings['max_sum']
                );
            }

            if ($status && $settings['status']) {
                $title = $this->config->get('shipping_novaposhta_courier_title');
                $description = $this->config->get('shipping_novaposhta_courier_description');

                $text = $this->getShippingText(
                    $settings['cost'],
                    $subtotal,
                    $settings['free_sum'],
                    $this->config->get('shipping_novaposhta_courier_free_text'),
                    $language_id,
                    $this->config->get('shipping_novaposhta_courier_tax_class_id'),
                    $settings['discount_percent']
                );

                $quote_data['courier'] = array(
                    'code' => 'novaposhta.courier',
                    'title' => isset($title[$language_id]) ? $title[$language_id] : $this->language->get('text_courier_title'),
                    'description' => isset($description[$language_id]) ? html_entity_decode($description[$language_id]) : '',
                    'cost' => $settings['cost'],
                    'tax_class_id' => $this->config->get('shipping_novaposhta_courier_tax_class_id'),
                    'text' => $text
                );
            }
        }

        // Доставка в почтомат
        if ($this->config->get('shipping_novaposhta_parcelbox_status')) {
            $status = $this->checkGeoZoneStatus(
                $this->config->get('shipping_novaposhta_parcelbox_geo_zone_id'),
                $address
            );

            if ($status) {
                $settings = $this->getShippingSettings('parcelbox', $customer_group_id);

                $status = $this->checkOrderLimits(
                    $subtotal,
                    $settings['min_sum'],
                    $settings['max_sum']
                );
            }

            if ($status && $settings['status']) {
                $title = $this->config->get('shipping_novaposhta_parcelbox_title');
                $description = $this->config->get('shipping_novaposhta_parcelbox_description');

                $text = $this->getShippingText(
                    $settings['cost'],
                    $subtotal,
                    $settings['free_sum'],
                    $this->config->get('shipping_novaposhta_parcelbox_free_text'),
                    $language_id,
                    $this->config->get('shipping_novaposhta_parcelbox_tax_class_id'),
                    $settings['discount_percent']
                );

                $quote_data['parcelbox'] = array(
                    'code' => 'novaposhta.parcelbox',
                    'title' => isset($title[$language_id]) ? $title[$language_id] : 'Доставка в почтомат',
                    'description' => isset($description[$language_id]) ? html_entity_decode($description[$language_id]) : '',
                    'cost' => $settings['cost'],
                    'tax_class_id' => $this->config->get('shipping_novaposhta_parcelbox_tax_class_id'),
                    'text' => $text
                );
            }
        }

        if ($quote_data) {
            $method_data = array(
                'code' => 'novaposhta',
                'title' => $this->language->get('text_title'),
                'quote' => $quote_data,
                'sort_order' => $this->config->get('shipping_novaposhta_sort_order'),
                'error' => false
            );
        }

        return $method_data;
    }

    /**
     * Получить настройки доставки для конкретной группы пользователей
     */
    private function getShippingSettings($type, $customer_group_id) {
        $settings = array();

        // Получаем базовые настройки
        $settings['status'] = $this->config->get('shipping_novaposhta_' . $type . '_status');
        $settings['cost'] = (float) $this->config->get('shipping_novaposhta_' . $type . '_cost');
        $settings['min_sum'] = (float) $this->config->get('shipping_novaposhta_' . $type . '_min_sum');
        $settings['max_sum'] = (float) $this->config->get('shipping_novaposhta_' . $type . '_max_sum');
        $settings['free_sum'] = (float) $this->config->get('shipping_novaposhta_' . $type . '_free_sum');
        $settings['discount_percent'] = 0;

        // Проверяем, есть ли специальные настройки для группы
        $group_status = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_status');

        if ($group_status !== null && $group_status != '') {
            // Применяем настройки для группы
            $settings['status'] = $group_status;

            $group_cost = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_cost');
            if ($group_cost !== null && $group_cost != '') {
                $settings['cost'] = (float) $group_cost;
            }

            $group_min_sum = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_min_sum');
            if ($group_min_sum !== null && $group_min_sum != '') {
                $settings['min_sum'] = (float) $group_min_sum;
            }

            $group_max_sum = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_max_sum');
            if ($group_max_sum !== null && $group_max_sum != '') {
                $settings['max_sum'] = (float) $group_max_sum;
            }

            $group_free_sum = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_free_sum');
            if ($group_free_sum !== null && $group_free_sum != '') {
                $settings['free_sum'] = (float) $group_free_sum;
            }

            $group_discount = $this->config->get('shipping_novaposhta_' . $type . '_group_' . $customer_group_id . '_discount_percent');
            if ($group_discount !== null && $group_discount != '') {
                $settings['discount_percent'] = (float) $group_discount;
            }
        }

        return $settings;
    }

    private function checkGeoZoneStatus($geo_zone_id, $address) {
        if (!$geo_zone_id) {
            return true;
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $geo_zone_id . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        return $query->num_rows > 0;
    }

    private function checkOrderLimits($subtotal, $min_sum, $max_sum) {
        if ($min_sum && $subtotal < $min_sum) {
            return false;
        }

        if ($max_sum && $subtotal > $max_sum) {
            return false;
        }

        return true;
    }

    private function getShippingText($cost, $subtotal, $free_sum, $free_text, $language_id, $tax_class_id, $discount_percent = 0) {
        // Применяем скидку по группе
        if ($discount_percent > 0) {
            $cost = $cost * (1 - $discount_percent / 100);
        }

        if ($cost > 0) {
            if ($free_sum && $subtotal >= $free_sum) {
                $cost = 0;
                if (isset($free_text[$language_id])) {
                    return $free_text[$language_id];
                } else {
                    return $this->language->get('text_free');
                }
            } else {
                $formatted_cost = $this->currency->format(
                    $this->tax->calculate($cost, $tax_class_id, $this->config->get('config_tax')),
                    $this->session->data['currency']
                );

                // Добавляем информацию о скидке, если она есть
                if ($discount_percent > 0) {
                    $original_cost = $this->currency->format(
                        $this->tax->calculate($cost / (1 - $discount_percent / 100), $tax_class_id, $this->config->get('config_tax')),
                        $this->session->data['currency']
                    );
                    return $formatted_cost . ' <small class="text-muted"><s>' . $original_cost . '</s> (-' . $discount_percent . '%)</small>';
                }

                return $formatted_cost;
            }
        } else {
            return $this->language->get('text_cost');
        }
    }

    /**
     * Получить общее количество регионов
     */
    public function getRegionsTotal() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "novaposhta_regions");
        return $query->row['total'];
    }

    /**
     * Получить общее количество городов
     */
    public function getCitiesTotal() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "novaposhta_cities");
        return $query->row['total'];
    }

    /**
     * Получить общее количество отделений
     */
    public function getDepartmentsTotal() {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "novaposhta_departments");
        return $query->row['total'];
    }

    /**
     * Обновить регионы
     */
    public function truncateRegionsTable() {
        //error_log("[MODEL] Truncating regions table");
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "novaposhta_regions");
    }

    /**
     * Добавить батч регионов (без очистки таблицы)
     */
    public function addRegionsBatch($regions) {
        //error_log("[MODEL] addRegionsBatch() started. Processing " . count($regions) . " regions");
        
        $inserted = 0;
        foreach ($regions as $region) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "novaposhta_regions SET 
                Ref = '" . $this->db->escape($region['Ref']) . "',
                AreasCenter = '" . $this->db->escape($region['AreasCenter']) . "',
                DescriptionRu = '" . $this->db->escape($region['DescriptionRu']) . "',
                Description = '" . $this->db->escape($region['Description']) . "'");
            $inserted++;
        }
        
        //error_log("[MODEL] addRegionsBatch() completed. Inserted $inserted regions");
        return $inserted;
    }

    /**
     * Обновить регионы (старый метод для обратной совместимости)

    /**
     * Обновить города
     */
    public function truncateCitiesTable() {
        //error_log("[MODEL] Truncating cities table");
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "novaposhta_cities");
    }

    /**
     * Добавить батч городов (без очистки таблицы)
     */
    public function addCitiesBatch($cities) {
        //error_log("[MODEL] addCitiesBatch() started. Processing " . count($cities) . " cities");
        
        $inserted = 0;
        foreach ($cities as $city) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "novaposhta_cities SET 
                DescriptionRu = '" . $this->db->escape($city['DescriptionRu']) . "',
                Description = '" . $this->db->escape($city['Description']) . "',
                Ref = '" . $this->db->escape($city['Ref']) . "',
                Delivery1 = '" . $this->db->escape($city['Delivery1']) . "',
                Delivery2 = '" . $this->db->escape($city['Delivery2']) . "',
                Delivery3 = '" . $this->db->escape($city['Delivery3']) . "',
                Delivery4 = '" . $this->db->escape($city['Delivery4']) . "',
                Delivery5 = '" . $this->db->escape($city['Delivery5']) . "',
                Delivery6 = '" . $this->db->escape($city['Delivery6']) . "',
                Delivery7 = '" . $this->db->escape($city['Delivery7']) . "',
                SettlementType = '" . $this->db->escape($city['SettlementType']) . "',
                IsBranch = '" . $this->db->escape($city['IsBranch']) . "',
                PreventEntryNewStreetsUser = '" . $this->db->escape($city['PreventEntryNewStreetsUser']) . "',
                CityID = '" . $this->db->escape($city['CityID']) . "',
                SettlementTypeDescription = '" . $this->db->escape($city['SettlementTypeDescription']) . "',
                SettlementTypeDescriptionRu = '" . $this->db->escape($city['SettlementTypeDescriptionRu']) . "',
                SpecialCashCheck = '" . $this->db->escape($city['SpecialCashCheck']) . "',
                AreaDescription = '" . $this->db->escape($city['AreaDescription']) . "',
                AreaDescriptionRu = '" . $this->db->escape($city['AreaDescriptionRu']) . "',
                Area = '" . $this->db->escape($city['Area']) . "'");
            $inserted++;
        }
        
       // error_log("[MODEL] addCitiesBatch() completed. Inserted $inserted cities");
        return $inserted;
    }

    /**
     * Обновить города (старый метод для обратной совместимости)

    /**
     * Обновить отделения
     */
    public function truncateDepartmentsTable() {
        //error_log("[MODEL] Truncating departments table");
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "novaposhta_departments");
    }

    /**
     * Добавить батч отделений (без очистки таблицы)
     */
    public function addDepartmentsBatch($departments) {
        $inserted = 0;
        $failed = 0;
        
        foreach ($departments as $department) {
            try {
                $fields = array();
                $values = array();
                
                // Маппируем все поля из API в таблицу
                $field_mapping = array(
                    'Ref', 'TypeOfWarehouse', 'CityRef', 'SiteKey', 'Description', 'DescriptionRu',
                    'ShortAddress', 'ShortAddressRu', 'Phone', 'Number', 'CityDescription', 'CityDescriptionRu',
                    'SettlementRef', 'SettlementDescription', 'SettlementAreaDescription', 'SettlementRegionsDescription',
                    'SettlementTypeDescription', 'SettlementTypeDescriptionRu', 'Longitude', 'Latitude',
                    'PostFinance', 'BicycleParking', 'PaymentAccess', 'POSTerminal', 'InternationalShipping',
                    'SelfServiceWorkplacesCount', 'TotalMaxWeightAllowed', 'PlaceMaxWeightAllowed',
                    'SendingLimitationsOnDimensions', 'ReceivingLimitationsOnDimensions', 'Reception', 'Delivery',
                    'Schedule', 'DistrictCode', 'WarehouseStatus', 'WarehouseStatusDate', 'WarehouseIllusha',
                    'CategoryOfWarehouse', 'Direct', 'RegionCity', 'WarehouseForAgent', 'GeneratorEnabled',
                    'MaxDeclaredCost', 'WorkInMobileAwis', 'DenyToSelect', 'CanGetMoneyTransfer', 'HasMirror',
                    'HasFittingRoom', 'OnlyReceivingParcel', 'PostMachineType', 'PostalCodeUA', 'WarehouseIndex',
                    'BeaconCode', 'PostomatFor', 'Location'
                );
                
                foreach ($field_mapping as $field) {
                    $value = isset($department[$field]) ? $department[$field] : '';
                    // Если значение массив - конвертируем в JSON
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $fields[] = $field;
                    $values[] = "'" . $this->db->escape($value) . "'";
                }
                
                $sql = "INSERT INTO " . DB_PREFIX . "novaposhta_departments SET " . 
                       implode(', ', array_map(function($f, $v) { return "$f = $v"; }, $fields, $values));
                
                $this->db->query($sql);
                $inserted++;
                
            } catch (Exception $e) {
                $failed++;
            }
        }
        
        return $inserted;
    }

    /**
     * Обновить отделения (старый метод для обратной совместимости)
     */
    public function updateDepartments($departments) {
        //error_log("[MODEL] updateDepartments() started. Processing " . count($departments) . " departments");
        
        $this->db->query("TRUNCATE TABLE " . DB_PREFIX . "novaposhta_departments");
        //error_log("[MODEL] Table truncated");

        $inserted = 0;
        $failed = 0;
        
        foreach ($departments as $department) {
            try {
                $fields = array();
                $values = array();
                
                // Маппируем все поля из API в таблицу
                $field_mapping = array(
                    'Ref', 'TypeOfWarehouse', 'CityRef', 'SiteKey', 'Description', 'DescriptionRu',
                    'ShortAddress', 'ShortAddressRu', 'Phone', 'Number', 'CityDescription', 'CityDescriptionRu',
                    'SettlementRef', 'SettlementDescription', 'SettlementAreaDescription', 'SettlementRegionsDescription',
                    'SettlementTypeDescription', 'SettlementTypeDescriptionRu', 'Longitude', 'Latitude',
                    'PostFinance', 'BicycleParking', 'PaymentAccess', 'POSTerminal', 'InternationalShipping',
                    'SelfServiceWorkplacesCount', 'TotalMaxWeightAllowed', 'PlaceMaxWeightAllowed',
                    'SendingLimitationsOnDimensions', 'ReceivingLimitationsOnDimensions', 'Reception', 'Delivery',
                    'Schedule', 'DistrictCode', 'WarehouseStatus', 'WarehouseStatusDate', 'WarehouseIllusha',
                    'CategoryOfWarehouse', 'Direct', 'RegionCity', 'WarehouseForAgent', 'GeneratorEnabled',
                    'MaxDeclaredCost', 'WorkInMobileAwis', 'DenyToSelect', 'CanGetMoneyTransfer', 'HasMirror',
                    'HasFittingRoom', 'OnlyReceivingParcel', 'PostMachineType', 'PostalCodeUA', 'WarehouseIndex',
                    'BeaconCode', 'PostomatFor', 'Location'
                );
                
                foreach ($field_mapping as $field) {
                    $value = isset($department[$field]) ? $department[$field] : '';
                    $fields[] = $field;
                    $values[] = "'" . $this->db->escape($value) . "'";
                }
                
                $sql = "INSERT INTO " . DB_PREFIX . "novaposhta_departments SET " . 
                       implode(', ', array_map(function($f, $v) { return "$f = $v"; }, $fields, $values));
                
                $this->db->query($sql);
                $inserted++;
                
            } catch (Exception $e) {
                //error_log("[MODEL] Error inserting department " . (isset($department['Ref']) ? $department['Ref'] : 'UNKNOWN') . ": " . $e->getMessage());
                $failed++;
            }
        }
        
        error_log("[MODEL] updateDepartments() completed. Inserted: $inserted, Failed: $failed");
    }
}