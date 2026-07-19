<?php
class ControllerExtensionModuleShippingData extends Controller {
    public function getShippingData() {
        $json = array();

        $action = '';
        if (isset($this->request->post['action'])) {
            $action = $this->request->post['action'];
        } elseif (isset($this->request->get['action'])) {
            $action = $this->request->get['action'];
        }

        $filter = '';
        if (isset($this->request->post['filter'])) {
            $filter = $this->request->post['filter'];
        } elseif (isset($this->request->get['filter'])) {
            $filter = $this->request->get['filter'];
        }

        $search = '';
        if (isset($this->request->post['search'])) {
            $search = $this->request->post['search'];
        } elseif (isset($this->request->get['search'])) {
            $search = $this->request->get['search'];
        }

        $shipping = '';
        if (isset($this->request->post['shipping'])) {
            $shipping = $this->request->post['shipping'];
        } elseif (isset($this->request->get['shipping'])) {
            $shipping = $this->request->get['shipping'];
        }

        $lang_code = isset($this->session->data['language'])? $this->session->data['language'] : 'uk';
        $is_ru = (stripos($lang_code, 'ru')!== false);

        if ($action == 'getCities') {
            $where = array();

            if ($filter) {
                $where[] = "`Area` = '" . $this->db->escape($filter) . "'";
            }

            if ($search) {
                $where[] = "(`Description` LIKE '%". $this->db->escape($search). "%' OR `DescriptionRu` LIKE '%". $this->db->escape($search). "%')";
            }

            $sql = "SELECT Ref as ref, Description as description, DescriptionRu as description_ru, AreaDescription as area, AreaDescriptionRu as area_ru, SettlementType as st_type, SettlementTypeDescription as st_desc, SettlementTypeDescriptionRu as st_desc_ru FROM ". DB_PREFIX. "novaposhta_cities";
            if ($where) {
                $sql.= " WHERE ". implode(" AND ", $where);
            }
            $sql.= " ORDER BY Description ASC LIMIT 500";

            $query = $this->db->query($sql);

            foreach ($query->rows as $row) {
                $description = $is_ru? $row['description_ru'] : $row['description'];
                $area = $is_ru? $row['area_ru'] : $row['area'];
                $type = $is_ru? $row['st_desc_ru'] : $row['st_desc'];

                $full_description = $type ? $type . ':' . $description : $description;
                if ($area) {
                    $full_description .= $area . ' бл.)';
                }

                $json[] = array(
                    'id'               => $row['ref'],
                    'label'            => $description,
                    'value'            => $description,
                    'full_description' => $full_description,
                    'description'      => $description
                );
            }

        } elseif ($action == 'getRegions') {
            $json = array();
            $lang_code = isset($this->session->data['language'])? $this->session->data['language'] : 'uk';
            $is_ru = (stripos($lang_code, 'ru')!== false);
            $sql = "SELECT Ref AS id, Description AS description, DescriptionRu AS description_ru FROM " . DB_PREFIX . "novaposhta_regions";
            $query = $this->db->query($sql);
            foreach ($query->rows as $row) {
                $json[] = array(
                    'id' => $row['id'],
                    'label' => $row['description_ru'] ?? $row['description'],
                    'value' => $row['description'],
                    'full_description' => ($row['description_ru'] ?? $row['description'])
                );
            }
        } elseif (($action == 'getDepartments')|| ($action == 'getPoshtomats')){
            $where = array();

            if (preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', trim($filter))) {
                $where[] = "CityRef = '". $this->db->escape(trim($filter)). "'";
            } elseif ($filter) {
                $where[] = "(CityDescription = '". $this->db->escape(trim($filter)). "' OR CityDescriptionRu = '". $this->db->escape(trim($filter)). "')";
            }

            if (stripos($shipping, 'parcelbox')!== false) {
                $where[] = "(TypeOfWarehouse = 'f9316480-5f2d-425d-bc2c-ac7cd29decf0' OR Description LIKE '%Поштомат%' OR Description LIKE '%Почтомат%')";
            } else {
                $where[] = "TypeOfWarehouse <> 'f9316480-5f2d-425d-bc2c-ac7cd29decf0'";
            }

            if ($search) {
                $where[] = "(Description LIKE '%". $this->db->escape($search). "%' OR DescriptionRu LIKE '%". $this->db->escape($search). "%')";
            }

            if ($where) {
                $sql = "SELECT Ref as ref, Description as description, DescriptionRu as description_ru, PostalCodeUA as postcode FROM ". DB_PREFIX. "novaposhta_departments WHERE ". implode(" AND ", $where). " ORDER BY CAST(Number AS UNSIGNED) ASC, Description ASC LIMIT 100";
                $query = $this->db->query($sql);

                foreach ($query->rows as $row) {
                    $description = $is_ru? $row['description_ru'] : $row['description'];
                    $json[] = array(
                        'id'               => $row['ref'],
                        'label'            => $description,
                        'value'            => $description,
                        'description'      => $description,
                        'full_description' => $description,
                        'postcode'         => $row['postcode']
                    );
                }
            }
        } 
        // elseif ($action == 'getPoshtomats') {

        // }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}