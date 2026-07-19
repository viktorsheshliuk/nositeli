<?php
class ModelExtensionModuleShippingData extends Model {
    public function getShippingMethod() {
        // Определяем текущий метод доставки из сессии или настроек.
        // Вернем структуру, чтобы не конфликтовать с модификатором.
        $method = '';
        if (isset($this->session->data['shipping_method']['code'])) {
            $code = $this->session->data['shipping_method']['code'];
            $parts = explode('.', $code);
            $method = $parts[0];
        }

        return array(
            'method'     => $method ? $method : 'novaposhta',
            'sub_method' => ''
        );
    }

    public function getNovaPoshtaRegions(){
        $regions_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "novaposhta_regions");

        return $regions_query->num_rows ? $regions_query->rows : [];
    }

    public function getNovaPoshtaCities($zone_id = 0) {
        $cities = array();

        $zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$zone_id . "'");

        $where = "";
        if ($zone_query->num_rows) {
            $zone_name = $zone_query->row['name'];
            // Убираем слово "область", "обл."
            $zone_name = trim(str_ireplace(array('область', 'обл.', 'обл'), '', $zone_name));
            $where = " WHERE AreaDescription LIKE '%" . $this->db->escape($zone_name) . "%' OR AreaDescriptionRu LIKE '%" . $this->db->escape($zone_name) . "%'";
        }

        $sql = "SELECT Ref as ref, Description as description, DescriptionRu as description_ru FROM " . DB_PREFIX . "novaposhta_cities" . $where . " ORDER BY Description ASC";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getNovaPoshtaDepartments($city_ref_or_name) {
        $where = array();

        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', trim($city_ref_or_name))) {
            $where[] = "CityRef = '" . $this->db->escape(trim($city_ref_or_name)) . "'";
        } else {
            $where[] = "CityDescription LIKE '%" . $this->db->escape(trim($city_ref_or_name)) . "%' OR CityDescriptionRu LIKE '%" . $this->db->escape(trim($city_ref_or_name)) . "%'";
        }

        // Исключаем почтоматы из обычных отделений
        $where[] = "TypeOfWarehouse <> 'f9316480-5f2d-425d-bc2c-ac7cd29decf0'";

        $sql = "SELECT Ref as ref, Description as description, DescriptionRu as description_ru, PostalCodeUA as postcode FROM " . DB_PREFIX . "novaposhta_departments WHERE " . implode(" AND ", $where) . " ORDER BY CAST(Number AS UNSIGNED) ASC, Description ASC";
        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getNovaPoshtaPoshtomats($city_ref_or_name) {
        $where = array();

        if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', trim($city_ref_or_name))) {
            $where[] = "CityRef = '" . $this->db->escape(trim($city_ref_or_name)) . "'";
        } else {
            $where[] = "CityDescription LIKE '%" . $this->db->escape(trim($city_ref_or_name)) . "%' OR CityDescriptionRu LIKE '%" . $this->db->escape(trim($city_ref_or_name)) . "%'";
        }

        // Только почтоматы
        $where[] = "(TypeOfWarehouse = 'f9316480-5f2d-425d-bc2c-ac7cd29decf0' OR Description LIKE '%Поштомат%' OR Description LIKE '%Почтомат%')";

        $sql = "SELECT Ref as ref, Description as description, DescriptionRu as description_ru, PostalCodeUA as postcode FROM " . DB_PREFIX . "novaposhta_departments WHERE " . implode(" AND ", $where) . " ORDER BY CAST(Number AS UNSIGNED) ASC, Description ASC";
        $query = $this->db->query($sql);

        return $query->rows;
    }
}
