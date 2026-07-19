<?php
class ModelToolEDAdaptor extends Model {
    
    /*
     * EDAdaptor default setting
     */
    
    private $edadaptor_version = '1.0.1.0';
    
    private $edadaptor_extension = 'edadaptor_opencart';
    
    private $platform = 'opencart';
    
    private $platform_version = '2.3';
    
    private $fields = array(
        'api' => array(
            'license_key'=>array('type'=>'input','default'=>''),
	    'private_key'=>array('type'=>'input','default'=>'','readonly'=>1),
	    'callback_host'=>array('type'=>'input'),
	    'callback_host_protocol'=>array('type'=>'select','default'=>'https://'),
	    'dir_download'=>array('type'=>'input','default'=>'','readonly'=>1),
	    'host'=>array('type'=>'input','default'=>'https://adaptor.e-distributer.com'),
        ),
    );
    
    /*
     * OpenCart default setting
     */
    
    private $path_opencart_version = 'extension/module';
    
    private $path_opencart_version_frontend = 'extension/feed';
    
    public $status = array();  
   
    private $callback_host_protocols = array('http://','https://');
    
    public $local_errors = array(
	'L1001' => array('code'=>'L1001','message'=>'Unable to connect. Enable the CURL extension on this hosting'),
	'L1002' => array('code'=>'L1002','message'=>'Missing or invalid EDAdaptor host. Specify the following value: '),
	'L1003' => array('code'=>'L1003','message'=>'Could not connect to EDAdaptor host. If the error persists, contact support'),
	'L1004' => array('code'=>'L1004','message'=>'License key is not activated. Get a license key, or activate an existing key'),
    );

    public function __construct($registry) {
        
        $this->registry = $registry;
        
        $this->install();
        
        $this->autorization();
        
    }
    
    public function getErrorText($error) {
        
	$result = '';
	
	if(is_array($error) && !isset($error['code'])){
	    
	    foreach ($error as $error2) {
		
		$result .= $this->getErrorText($error2).'</br>';
		
	    }
	    
	}
	else{
	    
	    $result .= 'Error #'.$error['code'].', '.$error['message'];
	    
	}
	
        return $result;
        
    }
    
    public function getErrorByCode($code,$errors) {
        
	$result = $this->getErrorText($errors[$code]);
	
        return $result;
        
    }
    
    public function get($property) {
	
	$result = NULL;
	
	if(isset($this->{$property})){
	    
	    $result = $this->{$property}; 
	    
	}
	
	return $result;
	
    }
    
    public function getFields($key,$hidden=0) {
        
        $result  = array();
        
        foreach ($this->fields[$key] as $field => $field_param) {
            
            if(!$hidden && (!isset($field_param['hidden']) || !$field_param['hidden'])){
                
                $result[$field] = $field_param;
                
            }elseif($hidden && isset($field_param['hidden']) && $field_param['hidden']){
                
                $result[$field] = $field_param; 
                
            }
            
        }
	
	if($key=='api' && !$hidden){
	    
	    $result['callback_host']['default'] = $_SERVER['HTTP_HOST'] .'/index.php?route='.$this->path_opencart_version_frontend.'/edadaptor';
	    
	    $result['private_key']['default'] = rand(10000000, 99999999);
	    
	    $result['dir_download']['default'] = DIR_DOWNLOAD;
	    
	}
        
        return $result;
        
    }
    
    private function autorization(){
	
	$autorization = $this->getSetting('api');
	
	if(!$autorization){
	    
	    $api_default = $this->getFields('api');
	    
	    $api['host'] = $api_default['host']['default'];
	    
	    $api['license_key'] = $api_default['license_key']['default'];
	    
	    $api['callback_host'] = $api_default['callback_host']['default'];
	    
	    $api['callback_host_protocol'] = $api_default['callback_host_protocol']['default'];
	    
	    $api['private_key'] = $api_default['private_key']['default'];
	    
	}else{
	    
	    $api = $autorization['api'];
	    
	}
	
	$param = array(
	    'accept' => 'xml',
	    'api' => $api,
	    'extension' => array(
		'platform'=>$this->platform,
		'platform_version'=>$this->platform_version,
		'edadaptor_extension'=>$this->edadaptor_extension,
		'edadaptor_version'=>$this->edadaptor_version,
	    )
	);

	$this->status = $this->getResponse('api.welcome',$param);
	
	return $this->status;
	
    }
    
    public function install() {
        
        $tables[] = 'edadaptor_setting';
        
        foreach ($tables as $table) {
	    
            $check = $this->db->query('SHOW TABLES FROM `'.DB_DATABASE.'` LIKE "'.DB_PREFIX.$table.'" ');
	    
            if(!$check->num_rows){
		
                $this->creatTables($table);
		
            }
	    
        }
        
        $columns[] = '';
	
	$columns = array();
        
        foreach ($columns as $column) {
            
            if(!$this->checkColumnsToTable('product',$column)){
                
                $this->creatTableColumn('product', $column, 'varchar(250)');
                
            }
            
        }
        
    }
    
    public function showTable($table,$prefix) {
        
        $query = $query = $this->db->query('SHOW TABLES from `'.DB_DATABASE.'` like "'.$prefix.$table.'" ');
        
        if($query->num_rows){
            
            return TRUE;
            
        }else{
            
            return FALSE;
            
        }
        
    }
    
    public function checkColumnsToTable($table,$column_needle) {
        
        $result = FALSE;
        
        if($this->showTable($table, DB_PREFIX)){
            
            $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $table."` " );
            
            foreach ($columns->rows as $column) {
                
                if($column['Field']==$column_needle){
                    
                    $result = TRUE;
                    
                }
                
            }
            
        }
        return $result;
        
    }
    
    private function creatTableColumn($table,$column,$data_type) {
        
        $sql = 'ALTER TABLE `'.DB_PREFIX.$table.'` ADD COLUMN(`'.$column.'` '.$data_type.');';
        
        $this->db->query($sql);
        
    }
    
    private function creatTables($table) {
        
        if($table=='edadaptor_setting'){
            
	    $this->db->query(
                "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $table . "` (
                  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                  `store_id` int(11) NOT NULL,
                  `code` varchar(128) NOT NULL,
                  `key` varchar(128) NOT NULL,
                  `value` longtext NOT NULL,
                  `serialized` tinyint(1) NOT NULL,
                  PRIMARY KEY (`setting_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
            );
	    
        }
	
    }
    
    public function getColumnsByTable($table) {
        
        $result = array();
        
        if($this->showTable($table, DB_PREFIX)){
            
            $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $table."` " );
            
            foreach ($columns->rows as $column) {
                
                $result[$column['Field']] = $column;
                
            }
            
        }
        return $result;
        
    }
    
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

    public function editSetting($code, $data, $store_id = 0) {
	
	$this->db->query("DELETE FROM `" . DB_PREFIX . "edadaptor_setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");

	foreach ($data as $key => $value) {

	    if (substr($key, 0, strlen($code)) == $code) {

		if (!is_array($value)) {

			$this->db->query("INSERT INTO " . DB_PREFIX . "edadaptor_setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");

		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "edadaptor_setting SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");

		}
		
	    }

	}
	    
    }

    public function deleteSetting($code, $store_id = 0) {
	
        $this->db->query("DELETE FROM " . DB_PREFIX . "edadaptor_setting WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
	    
    }
    
    public function load_language($config_admin_language) {
	
	    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");

	    $filename = $this->path_opencart_version.'/edadaptor';
	    
	    foreach ($query->rows as $result) {
		
		if($config_admin_language==$result['code']){
		    
		    if(file_exists(DIR_LANGUAGE . $result['directory'] . '/' . $filename . '.php')){
			
			$directory = $result['directory'];
			
		    }
		    elseif(file_exists(DIR_LANGUAGE . $result['code'] . '/' . $filename . '.php')){
			
			$directory = $result['code'];
			
		    }
		    
		}
	    }
	
	    $_ = array();

	    $file = DIR_LANGUAGE . $directory . '/' . $filename . '.php';

	    // Compatibility code for old extension folders
	    $old_file = DIR_LANGUAGE . $directory . '/' . str_replace('extension/', '', $filename) . '.php';

	    if (is_file($file)) {
		    require($file);
	    } elseif (is_file($old_file)) {
		    require($old_file);
	    }

	    return $_;
    }
    
    public function getParamDataAdaptations($adaptation_id,$sample_adaptation_id,$data_adaptation_id,$direction,$validation,$current_form) {
	
	$result = array(
	    'error' => '',
	    'data_adaptation' => '',
	);
	
	$autorization = $this->getSetting('api');
	
	if(!$autorization){
	    
	    $result['error'] = $this->getErrorByCode('L1004', $this->local_errors);
	    
	    return $result;
	    
	}else{
	    
	    $api = $autorization['api'];
	    
	}
	
	$param = array(
	    'accept' => 'xml',
	    'api' => $api,
	    'extension' => array(
		'platform'=>$this->platform,
		'platform_version'=>$this->platform_version,
		'edadaptor_extension'=>$this->edadaptor_extension,
		'edadaptor_version'=>$this->edadaptor_version,
	    ),
	    'adaptation_id'=>$adaptation_id,
	    'sample_adaptation_id'=>$sample_adaptation_id,
	    'data_adaptation_id'=>$data_adaptation_id,
	    'direction'=>  $direction,
	    'current_form' => $current_form,
	    'validation' => $validation
	);
	
	$object = 'api.adaptations.data_adaptation';

	$adaptation = $this->getResponse($object,$param);
	
	if($adaptation['edadapter_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	elseif($adaptation['local_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	else{
	    
	    $result['data_adaptation'] = $adaptation[$object];
	    
	}
	
	return $result;
	
    }
    
    public function saveAdaptation($adaptation_id,$current_form) {
	
	$result = array(
	    'error' => '',
	    'success' => '',
	);
	
	$autorization = $this->getSetting('api');
	
	if(!$autorization){
	    
	    $result['error'] = $this->getErrorByCode('L1004', $this->local_errors);
	    
	    return $result;
	    
	}else{
	    
	    $api = $autorization['api'];
	    
	}
	
	$param = array(
	    'accept' => 'xml',
	    'api' => $api,
	    'extension' => array(
		'platform'=>$this->platform,
		'platform_version'=>$this->platform_version,
		'edadaptor_extension'=>$this->edadaptor_extension,
		'edadaptor_version'=>$this->edadaptor_version,
	    ),
	    'adaptation_id'=>$adaptation_id,
	    'current_form' => $current_form,
	);
	
	$object = 'api.adaptations.save';

	$adaptation = $this->getResponse($object,$param);
	
	if($adaptation['edadapter_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	elseif($adaptation['local_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	else{
	    
	    $result['success'] = $adaptation[$object];
	    
	}
	
	return $result;
	
    }
    
    public function getAdaptation($adaptation_id,$sample_adaptation_id) {
	
	$result = array(
	    'error' => '',
	    'adaptation' => array(),
	);
	
	$autorization = $this->getSetting('api');
	
	if(!$autorization){
	    
	    $result['error'] = $this->getErrorByCode('L1004', $this->local_errors);
	    
	    return $result;
	    
	}else{
	    
	    $api = $autorization['api'];
	    
	}
	
	$param = array(
	    'accept' => 'xml',
	    'api' => $api,
	    'extension' => array(
		'platform'=>$this->platform,
		'platform_version'=>$this->platform_version,
		'edadaptor_extension'=>$this->edadaptor_extension,
		'edadaptor_version'=>$this->edadaptor_version,
	    ),
	    'adaptation_id'=>$adaptation_id,
	    'sample_adaptation_id'=>$sample_adaptation_id,
	);
	
	$object = 'api.adaptations.edit';

	$adaptation = $this->getResponse($object,$param);
	
	if($adaptation['edadapter_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	elseif($adaptation['local_error']){
	    
	    $result['error'] = $adaptation['edadapter_error'];
	    
	}
	else{
	    
	    $result['adaptation'] = $adaptation[$object];
	    
	}
	
	return $result;
	
    }
    
    public function getAdaptations() {
	
	$result = array(
	    'error' => '',
	    'adaptations' => array(),
	);
	
	$autorization = $this->getSetting('api');
	
	if(!$autorization){
	    
	    $result['error'] = $this->getErrorByCode('L1004', $this->local_errors);
	    
	    return $result;
	    
	}else{
	    
	    $api = $autorization['api'];
	    
	}
	
	$param = array(
	    'accept' => 'xml',
	    'api' => $api,
	    'extension' => array(
		'platform'=>$this->platform,
		'platform_version'=>$this->platform_version,
		'edadaptor_extension'=>$this->edadaptor_extension,
		'edadaptor_version'=>$this->edadaptor_version,
		'edadaptor_version'=>$this->edadaptor_version,
	    )
	);
	
	$object = 'api.adaptations';

	$adaptations = $this->getResponse($object,$param);
	
	if($adaptations['edadapter_error']){
	    
	    $result['error'] = $adaptations['edadapter_error'];
	    
	}
	elseif($adaptations['local_error']){
	    
	    $result['error'] = $adaptations['edadapter_error'];
	    
	}
	else{
	    
	    $result['adaptations'] = $adaptations[$object];
	    
	}
	
	return $result;
	
    }
    
    public function getResponse($object, $param=array()){
        
        $result = array('edadapter_error'=>'','local_error'=>'',$object=>array());
	
	if( !isset($param['api']['host']) || !$param['api']['host'] || !strstr($param['api']['host'], 'https://') ){
	    
	    $result['local_error'] = $this->getErrorByCode('L1002', $this->local_errors).$this->fields['api']['host']['default'];
	    
	}
	elseif(function_exists('curl_version')){
            
		$url = $param['api']['host']."/".$object;
                    
		$headers = array('json'=>'application/json','xml'=>'application/xml');

		$header = array('Accept: application/json');

		if(isset($param['accept']) && isset($headers[$param['accept']])){

		    $header = array('Accept: '.$headers[$param['accept']]);

		}

		$curl = curl_init($url);
		
		$curloptions = array(  
		    CURLOPT_POST           =>TRUE,      
		    CURLOPT_USERPWD     =>  'license_key:'.$param['api']['license_key'],
		    CURLOPT_RETURNTRANSFER => TRUE, 
		);
		
		curl_setopt_array($curl, $curloptions);
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($param));

		$response = curl_exec($curl);
		
		if(!curl_errno($curl) && $param['accept'] && isset($param['accept']) && $param['accept']=='xml'){
		    
		    if(strstr($response, '</edadaptor_feed>')){
			
			libxml_use_internal_errors(true);
			
			$xml = simplexml_load_string($response);
			
			if(!$xml){
			    
			    //var_dump($response);
			    
			}
			
		    }
		    else{
			
			$xml = FALSE;
			
		    }
		    
		    if($xml && isset($xml->output) && !strstr($response, 'errors')){
			
			$result[$object] = $this->{str_replace('.', '_', $object).'_'.$param['accept']}($xml->output);
			
		    }
		    elseif($xml && $xml->errors && strstr($response, 'errors')){
			
			$edadapter_error = array();
			
			$errors = $xml->errors;
			
			foreach ($errors->error as $error) {
			    
			    $edadapter_error[] = $error;
			    
			}
			
			$result['edadapter_error'] = implode('<br>', $edadapter_error);
			
		    }
		    else{
			
			$result['local_error'] = $this->getErrorByCode('L1003', $this->local_errors);
			
		    }

		}else{

		    $result['local_error'] = $this->getErrorByCode('L1003', $this->local_errors);

		}
		
		curl_close($curl);
                
            }
	    
	else{

	    $result['local_error'] = $this->getErrorByCode('L1001', $this->local_errors);

	}
            
	return $result;
            
    }
    
    private function api_adaptations_save_xml($output) {
	
	return (string)(string)$output->save;
	
    }
    
    private function api_welcome_xml($output) {
	
	$result['welcome'] = (string)$output->welcome;
	
	$result['information'] = (string)$output->information;
	
	return $result;
	
    }
    
    private function api_adaptations_edit_xml($output) {
	
	return (string)$output->adaptation;
	
    }
    
    private function api_adaptations_data_adaptation_xml($output) {
	
	return (string)$output->data_adaptation;
	
    }
    
    private function api_adaptations_xml($output) {
	
	$result = array();
	
	$adaptations = $output->adaptations;
	
	foreach ($adaptations->adaptation as $adaptation) {
	    
	    $attributes = (array)$adaptation->attributes();
	    
	    $adaptation_id = $attributes['@attributes']['adaptation_id'];
	    
	    $param = json_decode((string)$adaptation,TRUE);
	    
	    $result[$adaptation_id] = $param;
	    
	}
	
	return $result;
	
    }


























    public function setSettingAPI() {
        
        $edistributer_setting_api = $this->getSetting('edistributer_setting_api');
        
        foreach ($this->fields['edistributer_setting_api'] as $field => $field_param) {
            
            if(isset($edistributer_setting_api['edistributer_setting_api'][$field])){
                
                $this->{$field} = $edistributer_setting_api['edistributer_setting_api'][$field];

            }
            
            if( $field=='api_versions' && isset($edistributer_setting_api['edistributer_setting_api'][$field]) && $edistributer_setting_api['edistributer_setting_api'][$field]){
                
                $this->api_version = $edistributer_setting_api['edistributer_setting_api'][$field];
                
            }
            
            if( $field=='api_languages' && isset($edistributer_setting_api['edistributer_setting_api'][$field]) && $edistributer_setting_api['edistributer_setting_api'][$field]){
                
                $this->api_language = $edistributer_setting_api['edistributer_setting_api'][$field];
                
            }
            
        }
        
        if(!$this->api_language){
            
            $this->api_language = $this->config->get( 'config_admin_language' );
            
        }
        
        $this->api_host = $this->cleanUrl($this->api_host);
        
    }
    
    public function getSettingAPI() {
        
        foreach ($this->fields['edistributer_setting_api'] as $field => $field_param) {
            
            $edistributer_setting_api[$field] = $this->{$field};
            
        }
        
        return $edistributer_setting_api;
        
    }
    
    public function getMyProductListProducts($product_list_code,$data=array()) {
        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

			if (!empty($data['filter_category'])) {
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
			}

			$sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";


		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
			if ($data['filter_image'] == 1) {
				$sql .= " AND (p.image IS NOT NULL AND p.image <> '' AND p.image <> 'no_image.png')";
			} else {
				$sql .= " AND (p.image IS NULL OR p.image = '' OR p.image = 'no_image.png')";
			}
		}

        if (!empty($data['filter_category'])) {
            $sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
        }
        
        $sql .= " AND p.ed_product_list_code = '".$product_list_code."' ";
        
		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

                if(isset($data['count']) && $data['count']){
                    
                    $query = $this->db->query($sql);
                    
                    return $query->num_rows;
                    
                }
                
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
    }
    	
    public function getSettingValue($key, $store_id = 0) {
            $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "edistributer_setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");

            if ($query->num_rows) {
                    return $query->row['value'];
            } else {
                    return null;	
            }
    }
	
    public function editSettingValue($code = '', $key = '', $value = '', $store_id = 0) {
            if (!is_array($value)) {
                    $this->db->query("UPDATE " . DB_PREFIX . "edistributer_setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            } else {
                    $this->db->query("UPDATE " . DB_PREFIX . "edistributer_setting SET `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
            }
    }

    
    
    
    
    public function getResponse2($object,$param=array()){
        
        $response = array('error'=>array(),'response'=>array());
        
        $this->load->language('edistributer/edistributer');
        
        if(function_exists('curl_version')){
                
                if(!$this->api_host || !$this->application_id || !$this->customer_id || !$this->license_key || !$this->public_key || !$this->secret_key){
                    
                    $response['error'][] = array(
                        'error_code'    => 'local_error',
                        'error_message'    => $this->language->get('error_edistributer_setting_api'),
                        'error_explanation'    => ''
                    );
                    
                }else{
                    
                    $url = "https://".$this->api_host."/".$this->api_version.'/'.$this->api_language.'/'.$object;
                    
                    $headers = array('application/json','application/xml');
                    
                    $header = array('Accept: application/json');
                    
                    if(isset($param['header']) && in_array($param['header'], $headers)){
                        
                        $header = array('Accept: '.$param['header']);
                        
                    }
                    
                    $request = array();
                    
                    if(isset($param['request']) && is_array($param['request']) && $param['request']){
                        
                        $request = $param['request'];
                        
                    }
                    
                    $request['access_token'] = $this->access_token;
                    
                    $curl = curl_init($url);
                    $curloptions = array(  
                        CURLOPT_POST           =>TRUE,      
                        CURLOPT_USERPWD     =>  $this->customer_id.":".  md5($this->public_key.$this->secret_key.$this->license_key),
                        CURLOPT_RETURNTRANSFER => TRUE, 
                    );
                    curl_setopt_array($curl, $curloptions);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); 
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request));
                    
                    $response_result = curl_exec($curl);
                    
                    $response = json_decode($response_result,TRUE);
                    
                    if(!curl_errno($curl) && $response_result && isset($request['format']) && $request['format']=='xml' && !strstr($response_result, 'error_code') && !is_array($response)){
                        
                        $xml = simplexml_load_string($response_result);
                        
                        return $xml;
                        
                    }elseif(isset($response_result['response']['ed_product_list']['error'])){
                        
                        return FALSE;
                        
                    }
                    
                    if(!$response){
                        
                        $response['error'][] = array(
                            'error_code'    => 'local_error',
                            'error_message'    => $this->language->get('error_edistributer_setting_api_response_empty'),
                            'error_explanation'    => ''
                        );
                        
                    }
                    curl_close($curl);
                    
                }
                
            }else{
                
                $response['error'][] = array(
                    'error_code'    => 'local_error',
                    'error_message'    => $this->language->get('error_curl_exists'),
                    'error_explanation'    => ''
                );
                
            }
            
            return $response;
            
    }
    
    public function cleanUrl($url) {
        
        $url = str_replace(array('http://','https://'), array('',''), $url);
        
        return $url;
        
    }
    
    
    
    public function getDataSource($param=array()) {
        
        $columns_product = $this->db->query('SHOW COLUMNS FROM `'.DB_PREFIX.'product` ');
        
        $data_source['text_field'] = 'text_field';
        
        $data_source['option_value_id'] = 'option_value_id';
        
        $data_source['attribute_id'] = 'attribute_id';
        
        $data_source['keywords'] = 'keywords';
        
        $clean_product_fields = array();
        
        if(!in_array('product_list', $param)){
            
            $clean_product_fields = array('image','shipping','points','tax_class_id','date_available','weight_class_id','length_class_id','subtract','sort_order','viewed','date_added','date_modified');
            
        }
        
        
        
        $product_fileds = array();
        
        if($columns_product->rows){
            
            foreach($columns_product->rows as $column){
                
                if(!in_array($column['Field'], $clean_product_fields)){
                    
                    $product_fileds[$column['Field']] = $column['Field'];
                    
                }
                
            }
            
        }
        
        $data_source += $product_fileds;
        
        if(!in_array('price_policy', $param)){
            
            $columns_product_description = $this->db->query('SHOW COLUMNS FROM `'.DB_PREFIX.'product_description` ');
            
            $data_source['name'] = 'name';
            
            if($columns_product_description->rows){
                
                foreach($columns_product_description->rows as $column){
                    
                    if($column['Field']=='meta_title'){
                        
                        $data_source['meta_title'] = 'meta_title';
                        
                    }
                    
                    if($column['Field']=='meta_h1'){
                        
                        $data_source['meta_h1'] = 'meta_h1';
                        
                    }
                    
                    if($column['Field']=='seo_h1'){
                        
                        $data_source['seo_h1'] = 'seo_h1';
                        
                    }
                    
                    if($column['Field']=='seo_title'){
                        
                        $data_source['seo_title'] = 'seo_title';
                        
                    }
                    
                }
                
            }
            
            //$data_source['option_id'] = 'option_id';
            
            $data_source['category_id'] = 'category_id';
            
            $data_source['category_name'] = 'category_name';
            
            //$data_source['product_type'] = 'product_type';
            
            //$data_source['composite'] = 'composite';
            
        }elseif(in_array('price_policy', $param)){
            
            $data_source['discount_price'] = 'discount_price';

            $data_source['special_price'] = 'special_price';
            
        }
        
        if(in_array('product_list', $param)){
            
            $data_source['special_price'] = 'special_price';
            
            unset($data_source['composite']);
            
            unset($data_source['product_type']);
            
            unset($data_source['text_field']);
            
        }
        
        return $data_source;
        
    }
    
    public function getDBColumns($param=array()) {
        
        $columns_product = $this->db->query('SHOW COLUMNS FROM `'.DB_PREFIX.'product` ');
        
        $columns = array();
        
        $clean_product_fields = array();
        
        if($columns_product->rows){
            
            foreach($columns_product->rows as $column){
                
                if(!in_array($column['Field'], $clean_product_fields)){
                    
                    $columns[$column['Field']] = $column['Field'];
                    
                }
                
            }
            
        }
        
        return $columns;
        
    }
    
    public function getAttributes() {
        
            $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
            
            $query = $this->db->query($sql);
            
            $result = array();
            
            if($query->rows){
                
                foreach ($query->rows as $attribute) {
                    
                    $result[$attribute['attribute_group_id']][$attribute['attribute_id']]['name'] = $attribute['name'];
                    
                    $result[$attribute['attribute_group_id']][$attribute['attribute_id']]['attribute_group'] = $attribute['attribute_group'];
                
                    
                }
            }
            
            return $result;
    }
    
    public function getOptions() {
        
        $sql = "SELECT o.*, od.name as name, ovd.name as value_name, ovd.option_value_id as option_value_id FROM `" . DB_PREFIX . "option` o LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id)    LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (o.option_id = ovd.option_id) WHERE od.language_id = '" . (int)$this->config->get('config_language_id') . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ovd.option_value_id ";
        
        $query = $this->db->query($sql);
        
        $result = array();
        
        if($query->rows){
            
            foreach ($query->rows as $option) {
                
                $result[$option['option_id']][$option['option_value_id']]['option_id'] = $option['option_id'];
                
                $result[$option['option_id']][$option['option_value_id']]['name'] = $option['name'];
                
                $result[$option['option_id']][$option['option_value_id']]['value_name'] = $option['value_name'];
                
                $result[$option['option_id']][$option['option_value_id']]['option_value_id'] = $option['option_value_id'];
                
            }
            
        }
        
        return $result;
    }
    
    public function getCategories($data = array(),$config_language_id=0) {
        
                if(!$config_language_id){
                    
                    $config_language_id = $this->config->get('config_language_id');
                    
                }
        
		$sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, c1.parent_id, c1.sort_order, c1.status,(select count(product_id) as product_count from " . DB_PREFIX . "product_to_category pc where pc.category_id = c1.category_id) as product_count FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
                
                $fc = '';
                
                if (isset($data['filter_category_id']) && $data['filter_category_id']) {
                    
                    $fc = array();
                    
                    foreach ($data['filter_category_id'] as $category_id) {
                        
                        $fc[] = " cp.category_id = ".$category_id." ";
                        
                    }
                    
                    if($fc){
                        
                        $fc = ' AND ( '.implode(' OR ', $fc).' ) ';
                        
                    }
                    
                    $sql .= $fc;
                    
		}

		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'product_count',
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
                
    }
        
    public function getManufacturers($data = array()) {

            if($this->showTable('manufacturer_description', DB_PREFIX) && $this->checkColumnsToTable('manufacturer_description', 'name')){
                
                $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer m LEFT JOIN " . DB_PREFIX . "manufacturer_to_store m2s ON (m.manufacturer_id = m2s.manufacturer_id) LEFT JOIN " . DB_PREFIX . "manufacturer_description md ON (m.manufacturer_id = md.manufacturer_id) WHERE md.language_id = '" . (int)$this->config->get('config_language_id') . "' AND m2s.store_id = '" . (int)$this->config->get('config_store_id') . "'";

                $sort_data = array(
                        'name',
                        'sort_order'
                );

                if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                        $sql .= " ORDER BY " . $data['sort'];
                } else {
                        $sql .= " ORDER BY md.name";
                }

                if (isset($data['order']) && ($data['order'] == 'DESC')) {
                        $sql .= " DESC";
                } else {
                        $sql .= " ASC";
                }

                if (isset($data['start']) || isset($data['limit'])) {
                        if ($data['start'] < 0) {
                                $data['start'] = 0;
                        }

                        if ($data['limit'] < 1) {
                                $data['limit'] = 20;
                        }

                        $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                }
                
            }else{
                
                $sql = "SELECT * FROM " . DB_PREFIX . "manufacturer";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
                
            }

            

            $query = $this->db->query($sql);

            return $query->rows;
    }

    private function replaceOperator($operator){

        $find = array('&lt;','≤','=','≥','&gt;','≠');

        $replace = array('<','<=','=','>=','>','!=');

        $operator = str_replace($find, $replace, $operator);

        return $operator;

    }

    public function getProductPictures($product_id) {
        
        $HTTP_SERVER = HTTP_SERVER.'image/';
            
        if($this->config->get('config_secure')){
            $HTTP_SERVER = HTTPS_SERVER.'image/';
        }
        
        $pictures = array();
        
        if(isset($this->product_list['count_images']) && $this->product_list['count_images']!=''){
            
            $count_images = (int)$this->product_list['count_images'];
            
        }
        
        $num_image = 0;
        
        if(isset($this->product['main_image']) && $this->product['main_image'] && (!isset($count_images) || (isset($count_images) && $count_images))){
            
            $attributes = array(
                'main'=>  'true',
                'sort_order'=>  0
            );
            
            $pictures[] = array(
                    'tag_name' => 'picture',
                    'attributes' => $attributes,
                    'value'         => $this->product['main_image'],
                    'type'  => 'image'
            );
            
            $num_image++;
            
        }elseif( $this->product['image'] && (!isset($count_images) || (isset($count_images) && $count_images)) ){
            
            $attributes = array(
                'main'=>  'true',
                'sort_order'=>  0
            );
            
            $pictures[] = array(
                    'tag_name' => 'picture',
                    'attributes' => $attributes,
                    'value'         => $HTTP_SERVER.$this->product['image'],
                    'type'  => 'image'
            );
            
            $num_image++;
            
        }
        
        if(isset($this->product['pictures']) && $this->product['pictures'] && (!isset($count_images) || (isset($count_images) && $num_image<=$count_images))){
            
            $pictures_result = array();
            
            $pictures_parts = explode(',',$this->product['pictures']);
            
            if($pictures_parts){
                
                foreach ($pictures_parts as $pictures_part) {
                    
                    $pictures_part = trim($pictures_part);
                    
                    if($pictures_part){
                        
                        $pictures_result[] = $pictures_part;
                        
                    }
                    
                }
                
            }
            
            if($pictures_result){
                
                foreach ($pictures_result as $image) {
                
                    if( (!isset($count_images) || (isset($count_images) && $num_image<=$count_images)) ){

                        if(!$pictures){
                            
                            $attributes = array(
                                'main'=>  'true',
                                'sort_order'=>  0
                            );

                            $pictures[] = array(
                                    'tag_name' => 'picture',
                                    'attributes' => $attributes,
                                    'value'         => $image,
                                    'type'  => 'image'
                            );
                            
                        }else{
                            
                            $attributes = array(
                                'sort_order'=>  $num_image
                            );

                            $pictures[] = array(
                                    'tag_name' => 'picture',
                                    'attributes' => $attributes,
                                    'value'         => $image,
                                    'db_data'   => array(),
                                    'type'  => 'product_image'
                            );
                            
                        }

                        $num_image++;

                    }

                }
                
                
            }
            
        }else{
            
            $sql = "SELECT * FROM `" . DB_PREFIX . "product_image` WHERE product_id = '".$product_id."' AND image != 'no-image.jpg' AND image != 'no-image.png' AND image != '' AND image != 'no_image.png' AND image != 'no_image.jpg' ORDER BY sort_order ASC ";
            
            $query = $this->db->query($sql);

            if($query->rows && (!isset($count_images) || (isset($count_images) && $num_image<=$count_images))){

                foreach ($query->rows as $image) {

                    if( (!isset($count_images) || (isset($count_images) && $num_image<=$count_images)) ){

                        $attributes = array(
                            'sort_order'=>  $image['sort_order']
                        );

                        $pictures[] = array(
                                'tag_name' => 'picture',
                                'attributes' => $attributes,
                                'value'         => $HTTP_SERVER.$image['image'],
                                'db_data'   => $image,
                                'type'  => 'product_image'
                        );

                        $num_image++;

                    }



                }

            }
            
        }
        
        return $pictures;
    }
    
    public function getProductRelated($product_id) {
        
        $product_relateds = array();
        
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($query->rows as $result) {
            
                $attributes = array('product_code'=>'');
            
                $product_relateds[] = array(
                    'tag_name' => 'related_product',
                    'attributes' => $attributes,
                    'value'         => $result['related_id'],
                    'db_data'   => $result,
                    'type'  => 'product_related'
                );
                
        }

        return $product_relateds;
    }
    
    public function getProductCategories($product_id,$content_language_id){
        
        $product_categories = array();
        
        $sql = "SELECT * FROM " . DB_PREFIX . "product_to_category  WHERE product_id = '" . (int)$product_id . "' ";
        
        $query = $this->db->query($sql);
        
        $filter_category_id['filter_category_id'] = array();
        
        if($query->rows){
            
            foreach ($query->rows as $category){
                
                $filter_category_id['filter_category_id'][] = $category['category_id'];
                
            }
            
            $categories = $this->getCategories($filter_category_id,$content_language_id);
            
            foreach ($categories as $category) {
                
                $attributes = array(
                    'id'=>  $category['category_id'],
                    'parent_id'=>  $category['parent_id'],
                );
                
                $this->categories[$category['category_id']] = array(
                    'category_id' => $category['category_id'],
                    'parent_id' => $category['parent_id'],
                    'sort_order' => $category['sort_order'],
                    'value' => $category['name'],
                    'tag_name' => 'category',
                    'attributes' => $attributes
                );
            
                $product_categories[] = array(
                    'tag_name' => 'category',
                    'attributes' => $attributes,
                    'value'         => $category['name'],
                    'db_data'   => $category,
                    'type'  => 'product_to_category'
                );
                
            }
            
        }

        return $product_categories;
        
    }

        public function getProducts($product_list) {
            
            $this->product_list = $product_list;
            
            $result = array('count'=>0,'products'=>array(),'categories'=>array(),'count_excluding_assortment'=>0);
            
            $count_excluding_assortment = 0;
        
            $p2c = '';
            
            if(isset($product_list['category_id']) && $product_list['category_id']){
                
                $p2c = array();
                
                foreach ($product_list['category_id'] as $category_id) {
                    
                    $p2c[] = " p2c.category_id = '".$category_id."' ";
                    
                }
                
                if($p2c){
                    
                    $p2c = ' AND ( '.implode(' OR ', $p2c).' ) ';
                    
                }
                
            }
            
            $pm = '';
            
            if(isset($product_list['manufacturer_id']) && $product_list['manufacturer_id']){
                
                $pm = array();
                
                foreach ($product_list['manufacturer_id'] as $manufacturer_id) {
                    
                    $pm[] = " p.manufacturer_id = '".$manufacturer_id."' ";
                    
                }
                
                if($pm){
                    
                    $pm = ' AND ( '.implode(' OR ', $pm).' ) ';
                    
                }
                
            }
            
            $pfilter = array();
            
            $filter_attribute_id = array();
            
            $filter_option_id = array();
            
            $filter_option_value_id = array();
            
            for($i=0;$i<5;$i++){
                
                if(isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status'] && $this->checkColumnsToTable('product', $product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['value']!='' && $product_list['filter_source_'.$i]['operator']){
                    
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    if($operator!='±'){
                        
                        $pfilter[] = " p.".$column." ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                        
                    }elseif($operator=='±'){
                        
                        $pfilter[] = " p.".$column." >= p.".$column."-".(float)$value." AND p.".$column." <= p.".$column."+".(float)$value." ";
                        
                    }
                    
                }elseif(isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status']=='category_id'){
                    
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    $pfilter[] = " p2c.category_id ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                    
                }elseif(isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status']=='category_id'){
                    
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    $pfilter[] = " p2c.category_id ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                    
                }elseif (isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status']=='attribute_id') {
                
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    $filter_attribute_id[] = " a.attribute_id ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                    
                }elseif (isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status']=='option_id') {
                
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    $filter_option_id[] = " o.option_id ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                    
                }elseif (isset($product_list['filter_source_'.$i]['data_source']['status']) && $product_list['filter_source_'.$i]['data_source']['status']=='option_value_id') {
                
                    $operator = $product_list['filter_source_'.$i]['operator'];
                    
                    $column = $product_list['filter_source_'.$i]['data_source']['status'];
                    
                    $value = $product_list['filter_source_'.$i]['value'];
                    
                    $filter_option_value_id[] = " pov.option_value_id ".$this->replaceOperator($operator)." '".$this->db->escape($value)."' ";
                    
                }
                
            }
            
            if($pfilter){

                $pfilter = ' AND ( '.implode(' AND ', $pfilter).' ) ';

            }else{
                
                $pfilter = '';
                
            }
            
            $content_language_id = $product_list['content_language_id'];
            
            $sql = "SELECT p.*, pd.name, pd.*, m.name AS manufacturer, p2c.category_id, ps.price AS special_price, pds.price AS discount_special_price FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category AS p2c ON (p.product_id = p2c.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "product_special ps ON (p.product_id = ps.product_id) LEFT JOIN " . DB_PREFIX . "product_discount pds ON (p.product_id = pds.product_id)   WHERE pd.language_id = '" . (int)$content_language_id . "' ".$p2c." ".$pm." ".$pfilter." GROUP BY p.product_id";
            
            $query = $this->db->query($sql);
            
            if(!$query->rows){
                
                return $result;
                
            }
            
            
            
            foreach ($query->rows as $product) {
                
                $this->product = $product;
                
                $delete_attribute_id = array();
                
                $delete_option_id = array();
                
                if(isset($product_list['delete_attribute_id']) && $product_list['delete_attribute_id']){
                    
                    $delete_attribute_id = $product_list['delete_attribute_id'];
                    
                }
                
                if(isset($product_list['delete_option_id']) && $product_list['delete_option_id']){
                    
                    $delete_option_id = $product_list['delete_option_id'];
                    
                }
                
                $result['products'][$product['product_id']] = $product;
                $result['products'][$product['product_id']]['features'] = $this->getProductFeatures($product['product_id'], $delete_attribute_id, $filter_attribute_id ,$content_language_id);
                $feature2 = $this->getProductFeatures2($product['product_id'], $delete_option_id,$filter_option_id,$filter_option_value_id,$content_language_id);
                if($feature2){
                    $result['products'][$product['product_id']]['features'] = array_merge($result['products'][$product['product_id']]['features'],$feature2);
                }
                $result['products'][$product['product_id']]['all_product_attributes'] = $this->getProductFeatures($product['product_id'], array(), array() ,$content_language_id);
                $result['products'][$product['product_id']]['all_product_options'] = $this->getProductFeatures2($product['product_id'], array(),array(),array(),$content_language_id);
                $result['products'][$product['product_id']]['pictures'] = $this->getProductPictures($product['product_id']);
                $result['products'][$product['product_id']]['related_products'] = $this->getProductRelated($product['product_id']);
                $result['products'][$product['product_id']]['categories'] = $this->getProductCategories($product['product_id'],$content_language_id);
                $result['products'][$product['product_id']]['assortiment'] = array();
                if(isset($product_list['assortiment_option_id']) && $product_list['assortiment_option_id']){
                    
                    $assortiment = $this->getProductAssortiment($product['product_id'], $product_list['assortiment_option_id'],$content_language_id);
                    
                    if($assortiment){
                        
                        $new_id = 0;
                        
                        foreach ($assortiment as $assortiment_data) {
                            
                            $new_id = $product['product_id'].'-'.$assortiment_data['attributes']['id'];
                            
                            $result['products'][$new_id] = $result['products'][$product['product_id']];
                            
                            $result['products'][$new_id]['assortiment'] = $assortiment_data;
                            
                        }
                        
                        if($new_id){
                            
                            unset($result['products'][$product['product_id']]);
                            
                        }
                        
                    }
                    
                    //$result['products'][$product['product_id']]['assortiment'] = $this->getProductAssortiment($product['product_id'], $product_list['assortiment_option_id'],$content_language_id);
                    
                }
                
                $count_excluding_assortment++;
                
            }
            
            $result['categories'] = $this->categories;
            
            if($result['products']){
                
                $result['count'] = count($result['products']);
                $result['count_excluding_assortment'] = $count_excluding_assortment;
                
            }
        
        return $result;
    }
    
    private function getUnit($string){
        $units_parts = explode(' (', $string);
        $unit = '';
        if($units_parts && is_array($units_parts)){
            foreach ($units_parts as $units_part) {
                $parts = explode(')', $units_part);
                if($parts && count($parts)>1){
                    $unit = $parts[0];
                }
            }
        }
        return $unit;
    }
    
    public function getProductFeatures($product_id,$delete_attribute_id=array(),$filter_attribute_id=array(),$content_language_id) {
        
            $af = '';
            
            if($delete_attribute_id){
                
                $af = array();
                
                foreach ($delete_attribute_id as $attribute_id) {
                    
                    $af[] = " a.attribute_id != '".$attribute_id."' ";
                }
                
                if($af){
                    
                    $af = ' AND ( '.implode(' AND ', $af).' ) ';
                    
                }
                
            }
            
            if($filter_attribute_id){
                
                $af .= ' AND ( '.implode(' AND ', $filter_attribute_id).' ) ';
                
            }
            
            $product_features = array();

            $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$content_language_id . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");
            
            foreach ($product_attribute_group_query->rows as $product_attribute_group) {

                    $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text, a.sort_order FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$content_language_id . "' AND pa.language_id = '" . (int)$content_language_id . "' ".$af." ORDER BY a.sort_order, ad.name");

                    foreach ($product_attribute_query->rows as $product_attribute) {
                        
                        $attributes = array(
                            'group_title'=>$product_attribute_group['name'],
                            'title'=>$product_attribute['name'],
                            'unit'=>  $this->getUnit($product_attribute['name']),
                            'sort_order'=>  $product_attribute['sort_order']
                        );
                        
                        $product_features[] = array(
                                'tag_name' => 'feature',
                                'attributes' => $attributes,
                                'value'         => $product_attribute['text'],
                                'db_group_data'   => $product_attribute_group,
                                'db_data'   => $product_attribute,
                                'type'  => 'product_attribute'
                        );
                        
                    }
                    
            }

            return $product_features;
    }
    
    public function getProductFeatures2($product_id,$delete_option_id=array(),$filter_option_id=array(),$filter_option_value_id=array(),$content_language_id) {

            $product_features = array();

            $of = '';

            if($delete_option_id){

                $of = array();

                foreach ($delete_option_id as $option_id) {

                    $of[] = " o.option_id != '".$option_id."' ";
                }

                if($of){

                    $of = ' AND ( '.implode(' AND ', $of).' ) ';

                }

            }

            if($filter_option_id){

                $of .= ' AND ( '.implode(' AND ', $filter_option_id).' ) ';

            }

            $ovf = '';

            if($filter_option_value_id){

                $ovf .= ' AND ( '.implode(' AND ', $filter_option_value_id).' ) ';

            }
            
            
            $HTTP_SERVER = HTTP_SERVER.'image/';

            if($this->config->get('config_secure')){
                $HTTP_SERVER = HTTPS_SERVER.'image/';
            }

            $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$content_language_id . "' ".$of." ORDER BY o.sort_order");
            
            foreach ($product_option_query->rows as $product_option) {
                
                    $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$content_language_id . "' ".$ovf." ORDER BY ov.sort_order");

                    foreach ($product_option_value_query->rows as $product_option_value) {
                        
                        $picture = '';
                        
                        if(isset($product_option_value['image']) && $product_option_value['image'] &&  ($product_option_value['image']!='no-image.png' || $product_option_value['image']!='no_image.png' || $product_option_value['image']!='no-image.jpg' || $product_option_value['image']!='no_image.jpg') ){
                            
                            $picture = $HTTP_SERVER.$product_option_value['image'];
                            
                        }
                        
                        $attributes = array(
                            'title'=>$product_option['name'],
                            'unit'=>  $this->getUnit($product_option['name']),
                            'sort_order'=>  $product_option['sort_order'],
                            'picture' => $picture
                        );

                        $product_features[] = array(
                                'tag_name' => 'feature',
                                'attributes' => $attributes,
                                'value'         => $product_option_value['name'],
                                'db_group_data'   => $product_option,
                                'db_data'   => $product_option_value,
                                'type'  => 'product_option'
                        );
                        
                    }

            }

            return $product_features;
    }
    
    public function getProductAssortiment($product_id,$assortiment_option_id,$content_language_id) {
        
        $product_assortiment = array();
        
        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$content_language_id . "' AND o.option_id = ".$assortiment_option_id." ORDER BY o.sort_order");
        
        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$content_language_id . "' ORDER BY ov.sort_order");

            
            
            foreach ($product_option_value_query->rows as $product_option_value) {
                
                $feature_picture = array();
                
                $product_assortiment_features = array();

                $attributes = array(
                    'title'=>$product_option['name'],
                    'unit'=>  $this->getUnit($product_option['name']),
                    'sort_order'=>  $product_option['sort_order'],
                    'form_type'  => $product_option['type'],
                    'default'  => 'false'
                );

                $product_assortiment_features[] = array(
                        'tag_name' => 'feature',
                        'attributes' => $attributes,
                        'value'         => $product_option_value['name'],
                        'db_group_data'   => $product_option,
                        'db_data'   => $product_option_value
                );

                if(isset($product_option_value['image']) && is_file(DIR_IMAGE.$product_option_value['image'])){
                    
                    $HTTP_SERVER = HTTP_SERVER.'image/';
            
                    if($this->config->get('config_secure')){
                        $HTTP_SERVER = HTTPS_SERVER.'image/';
                    }
                    
                    $attributes = array(
                        'sort_order'=>  $product_option['sort_order'],
                        'main'  => 'false'
                    );
                    
                    $feature_picture[] = array(
                            'tag_name' => 'feature_picture',
                            'attributes' => $attributes,
                            'value'         => $HTTP_SERVER.$product_option_value['image'],
                            'db_group_data'   => $product_option,
                            'db_data'   => $product_option_value
                    );

                }
                
                if($feature_picture){

                    foreach ($feature_picture as $feature_picture_info) {
                        
                        $product_assortiment_features[] = $feature_picture_info;
                        
                    }

                }
                
                if($product_assortiment_features){

                    $attributes = array('id'=>$product_option_value['product_option_value_id']);
                    
                    $product_assortiment[] = array(
                        'tag_name' => 'assortiment',
                        'attributes' => $attributes,
                        'value' => $product_assortiment_features,
                        'type'  => 'product_assortiment'
                    );

                }
                
            }
                
        }
        
        return $product_assortiment;
}

public function getCategoriesIdByPath($path_whis_categories_name,$language_id,$store_id,$delimiter,$seo_url_generator,$log_data = array()) {
        
        $status = 1;
        
        $result = array();
        
        $result_all_categories = array();
        
        $sql = '';
        
        $result['parent_id'] = 0;
        
        $result['category_id'] = 0;
        
        $table = 'category';
        
        if($this->showTable($table, DB_PREFIX)){
            
            
            $path = explode($delimiter, $path_whis_categories_name);

            if($path && is_array($path)){

                foreach ($path as $key => $category_name) {

                    $category_name = trim($category_name);

                    if($category_name){

                        $path[$key] = $category_name;

                    }else{

                        unset($path[$key]);

                    }

                }

                if($path){

                    foreach ($path as $category_name) {

                        //первый элемент - должен быть топовый
                        if(!isset($parent_id)){

                            $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id . "' AND c.parent_id = 0 ";

                            $parent_category = $this->db->query($sql_category_path);

                            //если есть, оставляем родительский id
                            if($parent_category->row){

                                $parent_id = $parent_category->row['category_id'];

                                $result['parent_id'] = $parent_category->row['parent_id'];

                                $result['category_id'] = $parent_category->row['category_id'];
                                
                                $result_all_categories[$result['category_id']]['category_id'] = $result['category_id'];
                                
                                $result_all_categories[$result['category_id']]['parent_id'] = $result['parent_id'];
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_category->row['category_id']=>$category_name));
                                    
                                }

                            }

                            // в противном случае, вставляем родителя и сохраняем его id
                            else{

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '0', `top` = '1', `column` = '1',`status` = '".$status."', sort_order = '0', date_modified = NOW(), date_added = NOW()");

                                //если не последний елемент в path эта категория будет родителем
                                $parent_id = $this->db->getLastId();

                                //если последний елемент вернет значение по этой категории
                                $result['parent_id'] = 0;

                                $result['category_id'] = $parent_id;
                                
                                $result_all_categories[$result['category_id']]['category_id'] = $result['category_id'];
                                
                                $result_all_categories[$result['category_id']]['parent_id'] = $result['parent_id'];

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$parent_id . "' ");
                                
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "', store_id = " . $store_id . " ");
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_id=>$category_name));
                                    
                                }

                            }

                        }else{

                            $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id . "' AND c.parent_id = '".$parent_id."' ";

                            $parent_category = $this->db->query($sql_category_path);
                            
                            if($parent_category->row){

                                $parent_id = $parent_category->row['category_id'];

                                $result['parent_id'] = $parent_category->row['parent_id'];

                                $result['category_id'] = $parent_category->row['category_id'];
                                
                                $result_all_categories[$result['category_id']]['category_id'] = $result['category_id'];
                                
                                $result_all_categories[$result['category_id']]['parent_id'] = $result['parent_id'];
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_category->row['category_id']=>$category_name));
                                    
                                }

                            }

                            
                            else{

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '".$parent_id."', `top` = '1', `column` = '1', sort_order = '0', status = '".$status."', date_modified = NOW(), date_added = NOW()");

                                $result['parent_id'] = $parent_id;

                                
                                $parent_id = $this->db->getLastId();

                                $result['category_id'] = $parent_id;
                                
                                $result_all_categories[$result['category_id']]['category_id'] = $result['category_id'];
                                
                                $result_all_categories[$result['category_id']]['parent_id'] = $result['parent_id'];

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$parent_id . "' ");
                                
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "',  store_id = " . $store_id . " ");
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_id=>$category_name));
                                    
                                }

                            }

                        }

                    }

                }
                
                $log_data['__line__'] = __LINE__; 
                
                $log_data['message'] = 'success job -> categories whis path -> json_message:'.  json_encode($result_all_categories);
                
                $this->writeLog($log_data);

            }else{
                
                $log_data['__line__'] = __LINE__; 
                
                $log_data['message'] = 'error job -> categories whis path -> string_message:'.$path_whis_categories_name;
                
                $this->writeLog($log_data);
                
            }
            
        }
        
        $this->repairCategories();
        
        return $result_all_categories;
    }
    
    public function getStringFromArray($array,$name_row='',$delimeter=', '){
        
        $string = $name_row.' ';
        
        foreach ($array as $key => $value) {
            
            if(is_array($value)){
                
                $string .= $key.': '.  json_encode($value).$delimeter;
                
            }else{
                
                $string .= $key.': '.$value.$delimeter;
                
            }
            
            
        }
        
        return $string."\n";
        
    }

    public function repairCategories($parent_id = 0) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category` WHERE parent_id = '" . (int)$parent_id . "'");

            foreach ($query->rows as $category) {
                    // Delete the path below the current one
                    $this->db->query("DELETE FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$category['category_id'] . "'");

                    // Fix for records with no paths
                    $level = 0;

                    $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "category_path` WHERE category_id = '" . (int)$parent_id . "' ORDER BY level ASC");

                    foreach ($query->rows as $result) {
                            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$result['path_id'] . "', level = '" . (int)$level . "'");

                            $level++;
                    }

                    $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` SET category_id = '" . (int)$category['category_id'] . "', `path_id` = '" . (int)$category['category_id'] . "', level = '" . (int)$level . "'");

                    $this->repairCategories($category['category_id']);
            }
    }
    
    public function seoUrlGenerateAndSave($query_part,$seos,$only_to_latin = TRUE){
        
            
        
            $keyword = '';
            if($seos){
                
                $check_last_seo_url = $this->getSeoUrl($query_part."=".  key($seos));
                
                if($check_last_seo_url){
                    return;
                }
                
                foreach ($seos as $id => $name) {
                    $name = html_entity_decode($name,ENT_QUOTES);
                    $name = strip_tags($name);
                    $name = trim($name);
                    if($name){
                        $keyword = $this->seoUrlGenerate($query_part,$name,array(),$only_to_latin);
                    }
                    if($keyword){
                        $this->seoUrlSave($query_part,array($id=>$keyword));
                    }
                }
            }
            
            return;
        }
    
    protected function getSeoUrl($seo_query){
        
        $sql = "SELECT * FROM `" . DB_PREFIX . "url_alias` WHERE query = '".$seo_query."' ";
        
        $query = $this->db->query($sql);
        
        $seo_alias = '';
        
        if($query->row){
            
            $seo_alias = $query->row['keyword'];
            
        }
        
        return   $seo_alias;
        
    }    
        
    protected function seoUrlGenerate($query_part,$name,$url_part_last=array(),$only_to_latin=TRUE){
        $keyword = $this->validateSeoUrl($name,$only_to_latin);
        $dublicate = '';
        if($keyword){
            $where = " WHERE keyword='".$keyword."' ";
            $sql = "SELECT * FROM `" . DB_PREFIX . "url_alias` ".$where;
            $query = $this->db->query($sql);
            if($query->row){
                $url_part = explode('-', $query->row['keyword']);
                $dublicate = TRUE;
                if($url_part && is_array($url_part)){
                    $name = '';
                    if((int)end($url_part)>0){
                        $end = '-'.((int)end($url_part)+1);
                        array_pop($url_part);
                    }else{
                        $end = '-1';
                    }
                    $name = implode('-', $url_part);

                }else{
                    $end = '-1';
                }
                $name = $name.$end;
                $keyword = $this->seoUrlGenerate($query_part,$name,$url_part_last,$only_to_latin);
            }
            while (isset($url_part_last[$keyword])) {
                $url_part = explode('-', $keyword);
                if($url_part && is_array($url_part)){
                    $keyword = '';
                    if((int)end($url_part)>0){
                        $end = '-'.((int)end($url_part)+1);
                        array_pop($url_part);
                    }else{
                        $end = '-1';
                    }
                    $keyword = implode('-', $url_part);

                }else{
                    $end = '-1';
                }
                $keyword = $keyword.$end;
            }
        }
        $url = $keyword;
        return $url;
    }

    protected function validateSeoUrl($string,$only_to_latin=TRUE){

        $string = html_entity_decode($string,ENT_QUOTES);
        $string = strip_tags($string);
        $string = trim($string);

        $arr = explode(" ", $string);
        $str = '';
        for($i=0;$i<count($arr);$i++){
            $arr[$i] = trim($arr[$i]);
            if($arr[$i]){
                $str .= ' '.$arr[$i];
            }
        }

        $str = trim($str);
        $find = array('«', '»','"', '&', '>', '<','`','&acute;','!', '^','*','$','\'','@','"', '±',' ','&','#',';','%','?',':','(',')','-','_','=','+','[',']',',','.','/','\\');
        $replace = array('','','','','','','','','','','','','','','','','-','','','','','','','','','-','-','-','-','','','-','','-','-');
        $str = str_replace($find, $replace, $str);
        $str = trim(mb_strtolower($str,'utf-8'));
        if($only_to_latin){
            $find = array('a','b','c','d','e','f','j','i','g','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
            $replace = array('a','b','c','d','e','f','j','i','g','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
            $str = str_replace($find, $replace, $str);
            $find = array('а','б','в','г','д','е', 'ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','ц','ч','ш','щ','у','ф','х','ъ','ь','ы','э','ю','я');
            $replace = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','ts','ch','sh','sch','u','f','kh','','','y','e','yu','ya');
            $str = str_replace($find, $replace, $str);
        }
        return $str;
    }

    public function getDublicates($query_part,$seos){
        $result = array();
        if($seos){
            foreach ($seos as $id => $keyword) {
                $keyword = trim($keyword);
                if($keyword){

                    $where = " WHERE keyword='".$keyword."' AND query!='".$this->db->escape($query_part).'='.(int)$id."' ";

                    $sql = "SELECT * FROM `" . DB_PREFIX . "url_alias` ".$where;
                    $query = $this->db->query($sql);
                    if($query->row){
                        $result[$id] = $keyword;
                    }
                }
            }
        }
        return $result;
    }

    public function seoUrlSave($query_part,$seos){
        
        $result = 0;
        
        if($seos){
            foreach ($seos as $id => $keyword) {
                $keyword = $this->validateSeoUrl($keyword);
                $keyword = trim($keyword);
                $where = " query='".$this->db->escape($query_part).'='.(int)$id."' ";
                $sql = "DELETE FROM `" . DB_PREFIX . "url_alias` WHERE ".$where;
                $query = $this->db->query($sql);
                $sql = "INSERT INTO `" . DB_PREFIX . "url_alias` SET ".$where. ', keyword = '."'".$this->db->escape($keyword)."'";
                $query = $this->db->query($sql);
                $result = 1;
            }
        }
        
        return $result;

    }
    
    public function writeLog($log_data=array()){
        
        $log_update = 1;
        
        if(isset ($log_data['log_update'])){
            
            $log_update = $log_data['log_update'];
            
        }
        
        if(!isset($log_data['line'])){
            
            $log_data['line'] = 'n/a';
            
        }
        
        if(!isset($log_data['class'])){
            
            $log_data['class'] = 'n/a';
            
        }
        
        if(!isset($log_data['method'])){
            
            $log_data['method'] = 'n/a';
            
        }
        
        if(!isset($log_data['action'])){
            
            $log_data['action'] = 'n/a';
            
        }
        
        if(!isset($log_data['message']) || !$log_data['message']){
            
            $message = 'n/a';
            
        }else{
            
            $message = $log_data['message'];
            
        }
        
        if(isset($log_data['create_log']) && !$log_data['create_log']){
            
            return;
            
        }
        
        if(!isset($log_data['ed_log_file']) || !$log_data['ed_log_file']){
            
            $log_file_name = 'ed_log.txt';
            
        }else{
            
            $log_file_name = $log_data['ed_log_file'];
            
        }
        unset($log_data['message']);
        unset($log_data['create_log']);
        unset($log_data['ed_log_file']);
        
        $file_name_and_path_array = explode('/', trim($log_file_name));
            
        $path_array = array();

        for ($i=0;$i<(count($file_name_and_path_array)-1);$i++) {

            $path_array[] = $file_name_and_path_array[$i];

        }

        $file_name = end($file_name_and_path_array);
        
        $write_path = DIR_LOGS;
        
        if($path_array){
            
            foreach ($path_array as $dir) {
                
                $write_path .= $dir.'/';
                
                if(!file_exists($write_path)){

                    mkdir($write_path,0777);

                }
                
            }
            
        }
        
        $mode_fopen = 'a+';
        
        if(!file_exists($write_path.'/'.$file_name)){
            
            $handle = fopen($write_path.'/'.$file_name, "w+");

        }elseif(file_exists($write_path.'/'.$file_name)){

            $handle = fopen($write_path.'/'.$file_name, $mode_fopen);

        }
        
        $log = 'P: '.date('Y-m-d H:i');
        
        foreach ($log_data as $param_name => $param_value) {
            if(is_array($param_value)){
                
                $param_value = json_encode($param_value);
                
            }
            $log .= ", ".$param_name.": ".$param_value;
        }
        
        $log .= "\r"."M: ".$message."\r............\r";
        
       if($handle){
            
            fwrite($handle, $log);
            
            fclose($handle);
            
            return TRUE;
            
        }else{
            
            return FALSE;
            
        }
        
    }
    
    public function getProductByEdProductCode($product_code) {
        
        $sql = " SELECT * FROM `".DB_PREFIX."product` WHERE ed_product_code = '".$product_code."' ";
        
        $query = $this->db->query($sql);
        
        return $query->row;
        
    }
    
    public function getManufacturerIdByName($manufacturer_name,$language_id,$store_id) {
        
        $manufacturer_id = 0;
        
        $query = $this->db->query(" SELECT * FROM `" . DB_PREFIX . "manufacturer` WHERE name = '".$this->db->escape($manufacturer_name)."' ");
        
        if($query->row){
            
            $manufacturer_id = $query->row['manufacturer_id'];
            
        }else{
            
            $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer` SET image = '', sort_order = 0, name = '".$this->db->escape($manufacturer_name)."' ");
            
            $manufacturer_id = $this->db->getLastId();
            
            if($this->showTable('manufacturer_description', DB_PREFIX) && $this->checkColumnsToTable('manufacturer_description', 'name')){
             
                $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET name = '".$this->db->escape($manufacturer_name)."', manufacturer_id = ".$manufacturer_id.", language_id = ".(int)$language_id." ");
                
            }
            
            if($this->showTable('manufacturer_to_store', DB_PREFIX)){
             
                $this->db->query(" DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = ".$manufacturer_id." ");
                
                $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET manufacturer_id = ".$manufacturer_id.", store_id = ".$store_id);
                
            }
            
        }
        
        return $manufacturer_id;
        
    }
    
    public function getImageByUrlOnImage($site_from_image,$additinal_settings=array('new_image_path'=>'','new_file_name'=>1)) {
        
            $new_image_name=FALSE;
            if(isset($additinal_settings['new_file_name']) && $additinal_settings['new_file_name']){
                $new_image_name=TRUE;
            }

            $image_new_path_parts=array();
            if(isset($additinal_settings['new_image_path']) && $additinal_settings['new_image_path']){
                $image_new_path =  trim($additinal_settings['new_image_path']);
                if($image_new_path){
                    $image_new_path_parts = explode('/', $image_new_path);
                }
                if($image_new_path_parts){
                    foreach ($image_new_path_parts as $key => $value) {
                        if(!$value){
                            unset($image_new_path_parts[$key]);
                        }
                    }
                }

            }

            $image_parts = explode('/', $site_from_image);

            $path_whis_path_array = array(); 

            if($image_parts && is_array($image_parts)){

                $check_url = array('http:'=>0,'https:'=>0);

                foreach ($image_parts as $key => $image_parts_check_http) {
                    if(isset($check_url[$image_parts_check_http])){
                        unset($check_url[$image_parts_check_http]);
                    }

                }

                if(count($check_url)>1){
                    return '';
                }else{
                    unset($image_parts[0]);
                    unset($image_parts[1]);
                    unset($image_parts[2]);
                }

            }

            if($image_new_path_parts){
                foreach ($image_new_path_parts as $url_part) {
                    $path_whis_path_array[] = $url_part;
                }
            }

            if($image_parts){
                foreach ($image_parts as $url_part) {
                    $path_whis_path_array[] = $url_part;
                }
            }

            if(!$path_whis_path_array){
                return '';
            }

            $image_name = $path_whis_path_array[count($path_whis_path_array)-1];
            unset($path_whis_path_array[count($path_whis_path_array)-1]);
            $image_path = '';
            if($path_whis_path_array){
                $image_path = implode('/', $path_whis_path_array).'/';
            }
            if($new_image_name){

                $image_name_parts = explode('.',$image_name);

                $image_name = md5($site_from_image).'.'.end($image_name_parts);

            }

            $image = $image_path.$image_name;

            $server_path_and_image = DIR_IMAGE.$image;

            if(!file_exists(dirname($server_path_and_image))){

                if($image_path){

                    $image_path_parts = explode('/', $image_path);

                    $dir_name = DIR_IMAGE;

                    foreach ($image_path_parts as $new_dir_name) {

                        $dir_name .= $new_dir_name.'/';

                        if(!file_exists($dir_name)){

                            mkdir($dir_name,0777);

                        }

                    }

                }

            }
            if(!file_exists(dirname($server_path_and_image))){

                return '';

            }elseif (file_exists($server_path_and_image)) {

                return $image;

            }

            $b = get_headers($site_from_image);
            $imt = array('Content-Type: image/png'=>'.png',
                    'Content-Type: image/jpeg'=>'.jpg',
                    'Content-Type: image/gif'=>'.gif',
                    'Content-Type: image/jpeg'=>'.jpeg',
                    'Content-Type: image/vnd.wap.wbmp'=>'.bmp');
            if($b && is_array($b)){

                $get_image = FALSE;

                foreach ($b as $key => $b_value) {

                    if(isset($imt[$b_value])){

                        $get_image = TRUE;

                    }

                }

                if($get_image){

                    $a = file_get_contents($site_from_image);

                    if($a){
                        file_put_contents($server_path_and_image, $a);
                        return $image;
                    }

                }

            }
        return '';
    }
    
    public function getAttributeOrFilterGroupByGroupIdOrGroupName($group_id, $group_name,$table, $language_id) {
        
        $group_id_result = 0;
        
        if($this->showTable($table, DB_PREFIX)){
            
            if($group_id){
                
                $sql = " SELECT * FROM `" . DB_PREFIX . $table . "_group` ag LEFT JOIN `" . DB_PREFIX . $table . "_group_description` agd ON (ag.".$table."_group_id = agd.".$table."_group_id) WHERE agd.language_id = '" . (int)$language_id . "' ";
                
                $sql .= " AND ag.".$table."_group_id = '" . $group_id . "' ";
                
                $query = $this->db->query($sql);
                
                if($query->row){
                    
                    $group_id_result = $query->row[$table."_group_id"];
                    
                }
                
            }elseif($group_name){
                
                $sql = " SELECT * FROM `" . DB_PREFIX . $table . "_group` ag LEFT JOIN `" . DB_PREFIX . $table . "_group_description` agd ON (ag.".$table."_group_id = agd.".$table."_group_id) WHERE agd.language_id = '" . (int)$language_id . "' ";
                
                $sql .= " AND agd.name = '" . $this->db->escape($group_name) . "' ";
                
                $query = $this->db->query($sql);
                
                if(!$query->row){
                    
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "_group` SET sort_order = '0'");
                    
                    $group_id_result = $this->db->getLastId();
                    
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "_group_description` SET name = '".$this->db->escape($group_name)."', language_id = '" . (int)$language_id . "', ".$table."_group_id = '" . (int)$group_id_result . "' ");
                    
                }else{
                    
                    $group_id_result = $query->row[$table."_group_id"];
                    
                }
                
            }
            
        }
        
        return $group_id_result;
        
    }
    
    public function getAttributeOrFilterByIdOrGroupNameAndGroupId($group_id, $id, $name,$table, $language_id) {
        
        $id_result = 0;
        
        if($this->showTable($table, DB_PREFIX)){
            
            if($id){
                
                $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . $table . "_group_description agd WHERE agd.".$table."_group_id = a.".$table."_group_id AND agd.language_id = '" . (int)$language_id . "') AS ".$table."_group FROM " . DB_PREFIX . $table." a LEFT JOIN " . DB_PREFIX .$table."_description ad ON (a.".$table."_id = ad.".$table."_id) WHERE ad.language_id = '" . (int)$language_id . "'";
                
                $sql .= " AND a.".$table."_group_id = '" . $group_id . "'";
                
                $sql .= " AND a.".$table."_id = '" . $id . "'";
                
                $query = $this->db->query($sql);
                
                if($query->row){
                    
                    $id_result = $query->row[$table."_id"];
                    
                }
                
            }elseif($name){
                
                $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . $table . "_group_description agd WHERE agd.".$table."_group_id = a.".$table."_group_id AND agd.language_id = '" . (int)$language_id . "') AS ".$table."_group FROM " . DB_PREFIX . $table." a LEFT JOIN " . DB_PREFIX .$table."_description ad ON (a.".$table."_id = ad.".$table."_id) WHERE ad.language_id = '" . (int)$language_id . "'";
                
                $sql .= " AND a.".$table."_group_id = '" . $group_id . "'";
                
                $sql .= " AND ad.name = '" . $name . "'";
                
                $query = $this->db->query($sql);
                
                if(!$query->row){
                    
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "` SET sort_order = '0', ".$table."_group_id = ".$group_id." ");
                    
                    $id_result = $this->db->getLastId();
                    
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "_description` SET name = '".$this->db->escape($name)."', language_id = '" . (int)$language_id . "', ".$table."_id = '" . (int)$id_result . "' ");
                    
                }else{
                    
                    $id_result = $query->row[$table."_id"];
                    
                }
                
            }
            
        }
        
        return $id_result;
        
    }
    
    public function getOptionIdByNameOrOptionId($option_id,$option_name,$option_columns,$language_id){
        
        $option_id_result = 0;
        
        if($option_id){
            
            $option = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '".$this->db->escape($option_id)."' ");
            
        }elseif($option_name){
            
            $option = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE language_id = ".$language_id ." AND name = '".$this->db->escape($option_name)."' ");
            
        }
        
        $types = array('select','checkbox','radio','image');

        if( isset($option->row) && !$option->row && isset($option_columns['type']) && $option_columns['type'] && in_array($option_columns['type'],$types)){

            $this->db->query("INSERT INTO " . DB_PREFIX . "option SET sort_order = 0, type = '".$option_columns['type']."' ");

            $option_id_result = $this->db->getLastId();

            $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = ".$option_id_result.", name = '".$this->db->escape($option_name)."',  language_id = ".$language_id." ");

        }elseif(isset ($option->row['option_id']) && $option->row['option_id']){
            
            $option_id_result = $option->row['option_id'];

        }
        
        return $option_id_result;
        
    }
    
    public function getOptionValueIdByNameOrOptionValueId($option_id,$option_value_id,$option_value_name,$option_value_columns,$language_id){
        
        $option_value_id_result = 0;

        if($option_value_id && $option_id){

            $option_value = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE  option_id = " . $option_id . " AND language_id = ".$language_id." AND option_value_id = '".$option_value_id."' ");

        }elseif($option_value_name && $option_id){

            $option_value = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_value_description WHERE  option_id = " . $option_id . " AND language_id = ".$language_id." AND name = '".$this->db->escape($option_value_name)."' ");
            
        }
        
        if( (!isset($option_value->row) || !$option_value->row) && $option_value_name){
            
            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value SET option_id = '" . (int)$option_id . "' , image = '".$option_value_columns['image']."',  sort_order = 0 ");

            $option_value_id_result = $this->db->getLastId();

            $this->db->query("INSERT INTO " . DB_PREFIX . "option_value_description SET option_value_id = ".$option_value_id_result.", option_id = '" . (int)$option_id . "', name = '".$this->db->escape($option_value_name)."',  language_id = ".$language_id." ");
            
        }elseif(isset($option_value->row['option_value_id']) && $option_value->row['option_value_id']){
            
            $option_value_id_result = $option_value->row['option_value_id'];
            
            $this->db->query("UPDATE " . DB_PREFIX . "option_value SET image = '".$option_value_columns['image']."' WHERE option_id = '" . (int)$option_id . "' AND option_value_id = ".$option_value_id_result);
            
        }
        
        return $option_value_id_result;
        
    }
    
    public function checkProductByProductCodeAndProductListCode($product_list_code,$product_code) {
        $query = $this->db->query("SELECT product_id FROM `".DB_PREFIX."product` WHERE  ed_product_list_code='".$product_list_code."' AND ed_product_code='".$product_code."' ");
        return $query->row;
    }
    
    public function insertNewDataToMainTable($table,$new_data,$store_id){
        
        $id_resutl = 0;
        
        if($this->showTable($table, DB_PREFIX) && $new_data){
            
            $set = array();
            
            foreach($new_data as $column => $data){
                
                $set[] = " `".$column."` = '".$this->db->escape($data)."' ";
                
            }
            
            $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set);
            
            $this->db->query($sql);
            
            $id_resutl = $this->db->getLastId();
            
            $this->dataToStore('product', 'product_id', $id_resutl, $store_id);
            
        }
        
        return $id_resutl;
        
    }
    
    public function insertDataToDescriptionTable($table,$new_data,$language_id,$id_name,$id,$seo_url_generator=0){
        
        $result = 0;
        
        if($this->showTable($table, DB_PREFIX) && $new_data && $language_id && $id_name && $id){
            
            $set = array();
            
            foreach($new_data as $column => $data){
                
                if($column!='language_id' && $column!=$id_name){
                    
                    $set[] = " `".$column."` = '".$this->db->escape($data)."' ";
                    
                }
                
            }
            
            if($set){
                
                $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set).", ".$id_name." = ".$id.", language_id = ".$language_id;
            
                $this->db->query($sql);

                $result = 1;

                if($seo_url_generator && $this->checkColumnsToTable($table, 'name')){

                    $data = $this->db->query(" SELECT name FROM `".DB_PREFIX.$table."` WHERE  ".$id_name." = ".$id." AND language_id = ".$language_id);

                    if(isset($data->row['name']) && $data->row['name']){
                        
                        $this->seoUrlGenerateAndSave($id_name,array($id=>$data->row['name']));

                    }

                }
                
            }
            
        }
        
        return $result;
        
    }
    
    public function insertProductAttribute($table,$new_data,$language_id,$id_name,$id,$delete_data=FALSE) {
        
        $result = 0;
        
        $product_attribute = $this->db->query("SELECT * FROM `" . DB_PREFIX . $table . "` WHERE ".$id_name." = '" . (int)$id . "' AND language_id = ".$language_id." AND attribute_id = ".$new_data['attribute_id']." ");
        
        if(!$product_attribute->row){
            
            $set = array();
            
            foreach($new_data as $column => $data){

                if($column!=$id_name){

                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                }

            }
            
            if($set){
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "' ");
                
                $result = 1;
                
            }
            
        }else{
            
            $set = array();
            
            foreach($new_data as $column => $data){

                if($column!=$id_name && $column!='language_id' && $column!='attribute_id' && !$delete_data){

                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                }elseif($column!=$id_name && $column!='language_id' && $column!='attribute_id' && $delete_data){
                    
                    $set[$column] = " `".$column."` = '' ";
                    
                }

            }
            
            if($set){
                
                $this->db->query("UPDATE `" . DB_PREFIX . $table . "` SET ".implode(',', $set)." WHERE ".$id_name." = '" . (int)$id . "' AND language_id = ".$language_id." AND attribute_id = ".$new_data['attribute_id']." ");
                
                $result = 1;
                
            }
            
        }
        
        return $result;
        
    }
    
    public function insertProductCategories($categories,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . (int)$product_id . "'");
        
        $columns = $this->getColumnsByTable('product_to_category');
        
        $main_category_id = '';
        
        foreach ($categories as $category_id) {
            
            if(isset($columns['main_category'])){
            
                $main_category_id = ", main_category = " .   $category_id;

            }
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET category_id = " . $category_id . ", product_id = '" . $product_id . "' ".$main_category_id);
            
        }
        
    }
    
    public function insertProductOptionValue($new_data,$id_name,$id, $delete_data = FALSE) {
        
        $result = 0;
        
        $product_option = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_option` WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." ");
        
        if(!$product_option->row){
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET option_id = ".(int)$new_data['option_id'].", required = ".(int)$new_data['required'].", ".$id_name." = '" . (int)$id . "' ");
            
            $product_option_id = $this->db->getLastId();
            
        }else{
            
            $product_option_id = $product_option->row['product_option_id'];
            
            if($delete_data){
                
                $this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET required = 0 WHERE product_option_id = ".$product_option_id." ");
                
            }else{
                
                $this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET required = ".(int)$new_data['required']." WHERE product_option_id = ".$product_option_id." ");
                
            }
            
        }
        
        $product_option_value = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_option_value` WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." AND product_option_id = ".$product_option_id." AND option_value_id = ".$new_data['option_value_id']." ");
        
        if(isset($new_data['price_whis_delta'])){
            
            $product_price = $this->db->query("SELECT price FROM `" . DB_PREFIX  . "product` WHERE ".$id_name." = '" . (int)$id . "' ");
            
            if($product_price->row && isset($product_price->row['price'])){
                
                $new_data['price'] = $new_data['price_whis_delta'] - (float)$product_price->row['price'];
                
                if($new_data['price']>=0){
                    
                    $new_data['price_prefix'] = '+';
                    
                }else{
                    
                    $new_data['price_prefix'] = '-';
                    
                }
                
                $new_data['price'] = abs($new_data['price']);
                
            }
            
            unset($new_data['price_whis_delta']);
            
            if(!isset($new_data['price'])){
                
                $new_data['price'] = 0.0;
                
            }
            
        }
        
        if(!$product_option_value->row){
            
            $set = array();
            
            foreach($new_data as $column => $data){

                if($column!=$id_name && $column!='required' && $column!='value' && $column!='option_value_id' && $column!='option_id'){

                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                }

            }
            
            if($set){
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "', option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", option_value_id = ".$new_data['option_value_id']." ");
                
                $result = 1;
                
            }
            
        }else{
            
            $set = array();
            
            foreach($new_data as $column => $data){

                if($column!=$id_name && $column!='required' && $column!='value' && $column!='option_value_id' && $column!='option_id' && !$delete_data){

                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                }elseif($column!=$id_name && $column!='required' && $column!='value' && $column!='option_value_id' && $column!='option_id' && $delete_data){
                    
                    $set[$column] = " `".$column."` = '' ";
                    
                }

            }
            
            if($set){
                
                $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set)." WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." AND product_option_id = ".$product_option_id." AND option_value_id = ".$new_data['option_value_id']." ");
                
                $result = 1;
                
            }
            
        }
        
        return $result;
        
    }
    
    public function insertProductImages($images,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($images as $image) {
            
            if($image && $image!='no-image.png' && $image!='no_image.png'){
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET product_id = '" . (int)$product_id . "', image = '" . $image . "', sort_order = '0'");
                
            }
            
            
            
        }
        
    } 
    
    public function insertProductSpecialPrice($product_special,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");
        
        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_special` SET price = '" . (float) $product_special['price']. "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "' , product_id = '" . (int)$product_id . "' ");
        
    }
    
    public function disableQuantityProductByProductListCode($product_list_code){
        
        $sql = " UPDATE `".DB_PREFIX."product` SET quantity = 0 WHERE ed_product_list_code = '".$product_list_code."' ";
        
        $this->db->query($sql);
        
    }
    
    public function updateDataToTable($table,$new_data,$id_name,$id,$aditional_id=array(),$after_delete=FALSE,$delete_data=FALSE,$store_id=0){
        
        $result = 0;
        
        if($this->showTable($table, DB_PREFIX) && $new_data && $id){
            
            $set_where = array();
            
            if($aditional_id){
                
                foreach($aditional_id as $aditional_id_column=>$aditional_id_value){
                    
                    if(isset($new_data[$aditional_id_column])){
                        
                        $set_where[$aditional_id_column] = $aditional_id_column." = '".$new_data[$aditional_id_column]."' ";
                    }else{
                        
                        $set_where[$aditional_id_column] = $aditional_id_column." = '".$aditional_id_value."' ";
                        
                    }
                    
                    
                }
                
            }
            
            $set_where[$id_name] = " ".$id_name." = ".$id." ";
            
            if($after_delete && $set_where){
                
                $this->db->query(" DELETE FROM `".DB_PREFIX.$table."` WHERE  ".  implode(' AND ',$set_where));
                
            }
            
            $last_data = $this->db->query(" SELECT * FROM `".DB_PREFIX.$table."` WHERE  ".  implode(' AND ',$set_where));
            
            $set = array();
            
            foreach($new_data as $column => $data){
                
                if($column!=$id_name && !isset($aditional_id[$column])){
                    
                    if(!$delete_data){
                        
                        $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";
                        
                    }elseif($delete_data){
                        
                        $set[$column] = " `".$column."` = '' ";
                        
                    }
                    
                }
                
            }
            
            if( ($set || $set_where) && !$last_data->row){
                
                $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ";
                
                if($set){
                    
                    $sql .= " ".implode(',', $set);
                    
                }
                
                if($set && $set_where){
                    
                    $sql .= ", ".implode(', ',$set_where);
                    
                }else{
                    
                    $sql .= " ".implode(', ',$set_where);
                    
                }
                
                $this->db->query($sql);

                $result = 1;
                
            }elseif($set && $last_data->row){
                
                $result = 1;
                
                $sql = " UPDATE `".DB_PREFIX.$table."` SET ".implode(',', $set)." WHERE ".implode(' AND ',$set_where);
            
                $this->db->query($sql);
                
            }
            
            $this->dataToStore('product', 'product_id', $id, $store_id);
            
        }
        
        return $result;
        
    }
    
    public function updateDataToDescriptionTable($table,$new_data,$language_id,$id_name,$id,$seo_url_generator=0,$delete_data=FALSE){
        
        $result = 0;
        
        if($this->showTable($table, DB_PREFIX) && $new_data && $language_id && $id_name && $id){
            
            $last_data = $this->db->query(" SELECT * FROM `".DB_PREFIX.$table."` WHERE  ".$id_name." = ".$id." AND language_id = ".$language_id);
            
            $set = array();
            
            foreach($new_data as $column => $data){
                
                if($column!='language_id' && $column!=$id_name){
                    
                    if($delete_data){
                        
                        $set[$column] = " `".$column."` = '' ";
                        
                    }else{
                        
                        $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";
                        
                    }
                    
                }
                
            }
            
            if($set && !$last_data->row){
                
                $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set).", ".$id_name." = ".$id.", language_id = ".$language_id;
            
                $this->db->query($sql);

                $result = 1;
                
            }elseif($set && $last_data->row){
                
                $result = 1;
                
                $sql = " UPDATE `".DB_PREFIX.$table."` SET ".implode(',', $set)." WHERE ".$id_name." = ".$id." AND language_id = ".$language_id;
            
                $this->db->query($sql);
                
            }
            
            if($seo_url_generator && $this->checkColumnsToTable($table, 'name') && $result){

                $data = $this->db->query(" SELECT name FROM `".DB_PREFIX.$table."` WHERE  ".$id_name." = ".$id." AND language_id = ".$language_id);

                if(isset($data->row['name']) && $data->row['name']){

                    $this->seoUrlGenerateAndSave($id_name,array($id=>$data->row['name']));

                }

            }
            
        }
        
        return $result;
        
    }
    
    public function dataToStore($main_table,$id_name,$id,$store_id){
        
        if($this->showTable($main_table."_to_store", DB_PREFIX)){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $main_table."_to_store` WHERE store_id = '" . (int)$store_id . "' AND ".$id_name." = " . $id . " ");
                
            $this->db->query("INSERT INTO `" . DB_PREFIX . $main_table."_to_store` SET store_id = '" . (int)$store_id . "', ".$id_name." = " . $id . " ");
            
        }
        
    }
    
    public function checkImageByUrl($url) {
        
        return TRUE;
        
            $get_image = FALSE;
            
            $check_url = array('http://','https://');
            
            if(!strstr($url, $check_url[0])&& !strstr($url, $check_url[1])){

                return $get_image;

            }
            
            $imt = array('Content-Type: image/png'=>'.png',
                    'Content-Type: image/jpeg'=>'.jpg',
                    'Content-Type: image/gif'=>'.gif',
                    'Content-Type: image/jpeg'=>'.jpeg',
                    'Content-Type: image/vnd.wap.wbmp'=>'.bmp');
            
            $b = get_headers($url);
            
           
            
            if($b && is_array($b)){

                foreach ($b as $key => $b_value) {

                    if(isset($imt[$b_value])){

                        $get_image = TRUE;

                    }

                }

            }
            
            return $get_image;
        
    }
    
}