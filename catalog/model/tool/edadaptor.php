<?php
class ModelToolEDAdaptor extends Model {
    
    public function getSetting($code, $store_id = 0) {
	
            $setting = array();

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "edadaptor_setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

            foreach ($query->rows as $result) {
		
                    if (!$result['serialized']) {
			
                            $setting[$result['key']] = $result['value'];
			    
                    } else {
			
                            $setting[$result['key']] = json_decode($result['value'], true);
			    
                    }
		    
            }

            return $setting;
    }
    
}