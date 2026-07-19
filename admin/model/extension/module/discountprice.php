<?php
class ModelExtensionModuleDiscountprice extends Model {
	private $CSV_SEPARATOR = ';';
	private $CSV_ENCLOSURE = '"';
	private $data = array();
	
	public function import($fn) {

		if (($handle = fopen($fn, "r")) !== FALSE) {
			$row = 0;
		    
		    while (($data = fgetcsv($handle, 1000, $this->CSV_SEPARATOR, $this->CSV_ENCLOSURE)) !== FALSE) {
				$num = count($data);
				$row++;
				$item = array();
				
				for ($c=0; $c < $num; $c++) {
					$item[] = $data[$c];
				}

				// Update Price
				if( count($item) == 6 ) {
					//$sql = 'UPDATE '. DB_PREFIX . 'product SET quantity = "'.$item[4].'", price = '.$item[5].' WHERE product_id = '.(int)$item[0];
					//$this->db->query('UPDATE '. DB_PREFIX . 'product SET quantity = "'.$item[4].'", price = '.$item[5].' WHERE product_id = '.(int)$item[0]);
				} elseif ( count($item) == 3 ){
					//$sql = 'UPDATE '. DB_PREFIX . 'product SET quantity = "'.$item[1].'", price = '.$item[2].' WHERE model = "'.$item[0].'"';
					//$this->db->query('UPDATE '. DB_PREFIX . 'product SET quantity = "'.$item[1].'", price = '.$item[2].' WHERE model = "'.iconv('cp1251', 'UTF-8', $item[0]).'"');
				}elseif ( count($item) == 2 ){
					                         //$sql = 'UPDATE '. DB_PREFIX . 'product SET price = '.$item[1].' WHERE model = "'.$item[0].'"';
											 
											                                                 //WHERE model = "'.iconv('cp1251', 'UTF-8', $item[0]).'"');
						// SELECT p.product_id FROM " . DB_PREFIX . "product p WHERE p.model="'.iconv('cp1251', 'UTF-8', $item[0]).'"				 
					$this->db->query('UPDATE '. DB_PREFIX . 'product_discount SET price = "'.str_replace(',','.',$item[1] ).'" WHERE product_id = (SELECT product_id FROM ' . DB_PREFIX . 'product p WHERE p.model="'.iconv('cp1251', 'UTF-8', $item[0]).'" )');
				}
				
				unset($item);
			}
		    fclose($handle);
		}
		$this->cache->delete('product');
	}
  
  // Запись настроек в базу данных
  public function SaveSettings() {
    $this->load->model('setting/setting');
    $this->model_setting_setting->editSetting('module_discountprice', $this->request->post);
  }

  // Загрузка настроек из базы данных
  public function LoadSettings() {
    return $this->config->get('module_discountprice_status');
  }

}
?>