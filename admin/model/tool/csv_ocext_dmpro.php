<?php
class ModelToolCSVOcextDMPRO extends Model {
	
    protected $registry;
    protected $odmpro_tamplate_data = array();
    protected $log_data = array();
    protected $path_oc_version = 'extension/module';
    protected $table_seo_url = 'seo_url';
    protected $image_upload_curl = FALSE;
    protected $convert_currency = array();
    protected $repair_categories = FALSE;
    private $temp = array();
    private $setting_version_settings;
    private $ro = array();
    private $general_setting = array();
    private $lic = FALSE;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->install();
        $this->setSettingVersion();
        $this->getLincenceStatus();
        $this->getSettingVersionSettings();
    }
    
    public $max_memory_usage = array('memory_usage'=>0,'memory_usage_txt'=>'');
        
    public function setMaxMemoryUsage() {

        $memory_usage = memory_get_usage();

        if($memory_usage>$this->max_memory_usage['memory_usage']){

            $this->max_memory_usage['memory_usage'] = $memory_usage;

            $this->max_memory_usage['memory_usage_txt'] = round(($memory_usage/1024/1024),3).'Mb';

        }

    }
    
    public function setSettingVersion(){
        
        require_once(DIR_SYSTEM . 'library/vendor/ocext/anydsvxls_setting_version.php');
        $this->registry->set('setting_version',new anyDSVXLSSettingVersion($this->registry,  $this->path_oc_version, $this->language,$this->load,$this->db));
        
    }
    
    public function getEdistributerAdaptor(){
	
	$this->setting_version_settings['functional']['edistributier_adaptor'] = FALSE;
        
	if(file_exists(DIR_SYSTEM . 'library/vendor/ocext/edistributier_adaptor.php')){
	    
	    require_once(DIR_SYSTEM . 'library/vendor/ocext/edistributier_adaptor.php');
	    
	    $this->registry->set('edistributier_adaptor',new edistributierAdaptor($this->registry,  $this->path_oc_version, $this->language,$this->load,$this->db));
	    
	    $this->setting_version_settings['functional']['edistributier_adaptor'] = TRUE;
	    
	}
        
    }
    
    public function setLogDataRow($log_write_row,$log_data){
        
        $this->log_data[] = array('log_write_row'=>$log_write_row,'log_data'=>$log_data);
        
    }
    
    public function writeLogDataRows($odmpro_tamplate_data=array()){
        
        if($this->log_data){
            
            foreach($this->log_data as $log_start_position => $log_data_row){
                
                $log_write_row = $log_data_row['log_write_row'];
                
                $log_data = $log_data_row['log_data'];
                
                $log_data['log_start_position'] = $log_start_position;
                
                if(!$odmpro_tamplate_data){
                    
                    $odmpro_tamplate_data = $this->odmpro_tamplate_data;
                    
                }
                
                if( ($log_start_position+1) < count($this->log_data)){
                    
                    //$log_data['num_process'] = 0;
                    
                }
                
                $this->writeLog($log_write_row['action'], $log_write_row['message'], $odmpro_tamplate_data, $log_data);
                
            }
            
        }
        
    }
    
    public function install() {
        
        $tables[] = 'ocext_csv_ocext_dmpro_setting';
        
        foreach ($tables as $table) {
            $check = $query = $this->db->query('SHOW TABLES FROM `'.DB_DATABASE.'` LIKE "'.DB_PREFIX.$table.'" ');
            if(!$check->num_rows){
                $this->creatTables($table);
            }
        }
        
    }
    
    public function getSetting($code,$key='',$setting_version=FALSE) {
        
            $setting_data = array();
        
            if($setting_version){
                
                return $this->setting_version->getSetting($code,$key);
                
            }

            $query = $this->db->query("SELECT * FROM " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting WHERE `code` = '" . $this->db->escape($code) . "'");

            foreach ($query->rows as $result) {
                    if (!$result['serialized']) {
                            $setting_data[$result['key']] = $result['value'];
                    } else {
                            $setting_data[$result['key']] = json_decode($result['value'], true);
                    }
            }

            if($key && isset($setting_data[$key])){

                $setting_data = $setting_data[$key];

            }elseif($key && !isset($setting_data[$key])){

                $setting_data = array();

            }

            return $setting_data;
    }

    public function editSetting($code, $data, $setting_version = FALSE) {
        
            if($setting_version){
                
                $this->setting_version->editSetting($code, $data);
                
                return;
                
            }
        
            $this->db->query("DELETE FROM `" . DB_PREFIX ."ocext_csv_ocext_dmpro_setting` WHERE `code` = '" . $this->db->escape($code) . "'");

            foreach ($data as $key => $value) {
                    if (substr($key, 0, strlen($code)) == $code) {
                            if (!is_array($value)) {
                                    $this->db->query("INSERT INTO " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting SET `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                            } else {
                                    $this->db->query("INSERT INTO " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting SET `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
                            }
                    }
            }
    }
    
    public function getTFileByFileName($get) {
        
        $this->setting_version->getTFileByFileName($get);
            
    }
    
    public function saveTemplateSetting($tid,$templates_setting) {
        
        $this->setting_version->saveTemplateSetting($tid,$templates_setting);
            
    }
    

    public function deleteSetting($code) {
            $this->db->query("DELETE FROM " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting WHERE `code` = '" . $this->db->escape($code) . "'");
    }
	
    public function getSettingValue($key) {
            $query = $this->db->query("SELECT value FROM " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting WHERE `key` = '" . $this->db->escape($key) . "'");

            if ($query->num_rows) {
                    return $query->row['value'];
            } else {
                    return null;	
            }
    }
	
    public function editSettingValue($code = '', $key = '', $value = '') {
            if (!is_array($value)) {
                    $this->db->query("UPDATE " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting SET `value` = '" . $this->db->escape($value) . "', serialized = '0'  WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' ");
            } else {
                    $this->db->query("UPDATE " . DB_PREFIX ."ocext_csv_ocext_dmpro_setting SET `value` = '" . $this->db->escape(json_encode($value)) . "', serialized = '1' WHERE `code` = '" . $this->db->escape($code) . "' AND `key` = '" . $this->db->escape($key) . "' ");
            }
    }
    
    private function creatTables($table) {
        
        if($table=='ocext_csv_ocext_dmpro_setting'){
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $table . "` (
                  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
                  `value` longtext NOT NULL,
                  `code` varchar(250) NOT NULL,
                  `key` varchar(250) NOT NULL,
                  `status` int(2) NOT NULL,
                  `serialized` tinyint(2) NOT NULL,
                  PRIMARY KEY (`setting_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;"
            );
        }
        
    }
    
    public function getColumns($type_data,$odmpro_tamplate_data,$type_process='import',$only_columns=FALSE) {
        
        $result = array();
	
	$cache_file_name = 'anyCSV-getColumns-'.$odmpro_tamplate_data['level'].'-'.$odmpro_tamplate_data['language_id'].'-'.$type_data.'-'.$type_process.'-'.(int)$only_columns.'.txt';
	
	$cahce_result = $this->getAnyCSVActionsCache($cache_file_name);
        
	if(!is_null($cahce_result)){
	    
	    return $cahce_result;
	    
	}
	
        $abstract_field = $this->getAbstractFields();
        
        $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
        
        if($type_data=='order_product' || $type_data=='filter' || $type_data=='review' || $type_data=='filter_group' || $type_data=='option_value' || $type_data=='option' || $type_data=='attribute_group' || $type_data=='attribute' || $type_data=='category' || $type_data=='product' || $type_data=='information' || $type_data=='manufacturer'){
            
            $language_exists = FALSE;
            
            if($type_data=='option_value'){
                
                $this->load->language('catalog/option');
                
            }elseif($type_data=='order_product'){
                
                $this->load->language('sale/order');
                
                $this->load->language('catalog/product');
                
            }else{
                
                $this->model('localisation/language');
                $languages = array();
                if($this->lic){
                    $languages = $this->getLanguages();
                }
                
                $select_language_id = $odmpro_tamplate_data['language_id'];
                
                foreach ($languages as $language_localisation) {
                    
                    if(isset($language_localisation['language_id']) && $language_localisation['language_id']==$select_language_id){
                        
                        if(isset($language_localisation['directory']) && file_exists(DIR_LANGUAGE.$language_localisation['directory'].'/catalog/'.$type_data.'.php')){
                 
                            $this->load->language('catalog/'.$type_data);

                            $language_exists = TRUE;

                        }elseif(isset($language_localisation['code']) && file_exists(DIR_LANGUAGE.$language_localisation['code'].'/catalog/'.$type_data.'.php')){
                            
                            $this->load->language('catalog/'.$type_data);

                            $language_exists = TRUE;
                            
                        }
                        
                    }
                    
                }
                
                if(!$language_exists){
                    
                    foreach ($languages as $language_localisation) {
                        
                        if(isset($language_localisation['directory']) && file_exists(DIR_LANGUAGE.$language_localisation['directory'].'/catalog/'.$type_data.'.php')){
                 
                            $this->load->language('catalog/'.$type_data);

                            $language_exists = TRUE;

                        }elseif(isset($language_localisation['code']) && file_exists(DIR_LANGUAGE.$language_localisation['code'].'/catalog/'.$type_data.'.php')){
                            
                            $this->load->language('catalog/'.$type_data);

                            $language_exists = TRUE;
                            
                        }
                        
                    }
                    
                }
                
            }
             
            $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'` ' );
            
            foreach ($columns->rows as $column) {
                
                if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                    
                    $result[$type_data][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                    
                }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                    
                    $result[$type_data][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                    
                }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                    
                    $result[$type_data][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    
                }
                
            }
            
            ksort($result[$type_data]);
            
            if($this->showTable($type_data.'_description', DB_PREFIX)){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_description'.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_description'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_description'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_description'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        
                    }
                }
                
                ksort($result[$type_data.'_description']);
                
            }
            
            if($this->showTable($type_data.'_filter', DB_PREFIX)){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_filter`' );
                
                $result[$type_data.'_filter'] = array();
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_filter'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_filter'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_filter'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        
                    }
                }
                
                ksort($result[$type_data.'_filter']);
                
            }
            
            if($type_data=='order_product'){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX .'order`' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result['order_product'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],'product');
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result['order_product'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],'product');
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result['order_product'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],'order');
                        
                    }
                }
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX .'product`' );
                
                $this->load->language('catalog/product');
                
                $result['product_main_data'] = array();
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result['product_main_data'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],'product');
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result['product_main_data'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],'product');
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result['product_main_data'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],'product');
                        
                    }
                }
                
                ksort($result['product_main_data']);
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX .'order_option`' );
                
                $result['order_option'] = array();
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result['order_option'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],'order');
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result['order_option'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],'order');
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result['order_option'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],'order');
                        
                    }
                    
                }
                
            }
            
            if($type_data=='category'){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_path`' );
                
                $result[$type_data.'_path'] = array();
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_path'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_path'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_path'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        
                    }
                }
                
                ksort($result[$type_data.'_path']);
                
            }
            if($type_data=='product'){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_image`' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_image'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_image'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_image'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        
                    }
                }
                
                ksort($result[$type_data.'_image']);
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_option_value`' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_option_value'][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_option_value'][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_option_value'][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        
                    }
                }
                
                ksort($result[$type_data.'_option_value']);
                
                $type_data_prefix_db = 'attribute';
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    }
                }
                
                if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                    ksort($result[$type_data.'_'.$type_data_prefix_db]);
                }
                
                $type_data_prefix_db = 'filter';
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    }
                }
                
                if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                    ksort($result[$type_data.'_'.$type_data_prefix_db]);
                }
                
                $type_data_prefix_db = 'related';
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    }
                }
                
                if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                    ksort($result[$type_data.'_'.$type_data_prefix_db]);
                }
                
                $type_data_prefix_db = 'discount';
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    }
                }
                
                if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                    ksort($result[$type_data.'_'.$type_data_prefix_db]);
                }
                
                $type_data_prefix_db = 'special';
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                foreach ($columns->rows as $column) {
                    
                    if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);
                        
                    }elseif($odmpro_tamplate_data['level'] || !$language_exists){
                        
                        $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                    }
                }
                
                if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                    ksort($result[$type_data.'_'.$type_data_prefix_db]);
                }
                
                $type_data_prefix_db = 'assortiment_value';
                
                if($this->showTable($type_data.'_'.$type_data_prefix_db, DB_PREFIX)){
                    
                    $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $type_data.'_'.$type_data_prefix_db.'` ' );
                
                    foreach ($columns->rows as $column) {

                        if($language_exists && $this->language->get('column_'.$column['Field']) && $this->language->get('column_'.$column['Field'])!='column_'.$column['Field']){

                            $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('column_'.$column['Field']),$column['Field'],$column['Type'],$type_data);

                        }elseif ($language_exists && $this->language->get('entry_'.$column['Field']) && $this->language->get('entry_'.$column['Field'])!='entry_'.$column['Field']) {

                            $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption($this->language->get('entry_'.$column['Field']),$column['Field'],$column['Type'],$type_data);

                        }elseif($odmpro_tamplate_data['level'] || !$language_exists){

                            $result[$type_data.'_'.$type_data_prefix_db][$column['Field']] = $this->getInstructionToOption('',$column['Field'],$column['Type'],$type_data);
                        }
                    }

                    if(isset($result[$type_data.'_'.$type_data_prefix_db])){
                        ksort($result[$type_data.'_'.$type_data_prefix_db]);
                    }
                    
                }
                
                
            }
        }
        
        if($only_columns){
            
            foreach ($result as $key => $value) {

                if(!$value){

                    unset($result[$key]);

                }else{

                    foreach ($value as $key2 => $value2) {

                        if(!$value2){

                           $result[$key][ $key2 ] = $key2;

                       }

                    }

                }

            }

            return $result;
            
        }
        
        foreach ($result as $key => $value) {
            
            //вставка идентификатора
            $key_parts = explode('_', $key);
            
            if( $key_parts[0]=='option' || (isset($key_parts[1]) && $key_parts[1]=='group')){
                
                $key_parts[0] .= '_'.$key_parts[1];
                
            }elseif($key == 'product_main_data' || $key == 'order_product' || $key == 'order_option'){
                
                $key_parts[0] = '';
                
            }
            
            if($key_parts[0]){
                
                $result[$key_parts[0].'_identificator']['identificator'] = $abstract_field['identificator']['identificator']['name'];
                
            }
            
            if(isset($abstract_field[$key])){

                foreach ($abstract_field[$key] as $absctract_field_field => $absctract_field__row) {
                    
                    if(!isset($absctract_field__row[$type_process]) || (isset($absctract_field__row[$type_process]) && $absctract_field__row[$type_process]) ){
                    
                        
                        if(!isset($result[$key][ $absctract_field_field ])){
                            $result[$key][ $absctract_field__row['field'] ] = $absctract_field__row['name'];
                        }
                        elseif(isset($result[$key][ $absctract_field_field ]) && $absctract_field__row['field']!=$absctract_field_field){
                            $result[$key][ $absctract_field__row['field'] ] = $result[$key][ $absctract_field_field ];
                            unset($result[$key][ $absctract_field_field ]);
                        }
                   
                    }

                }

            }
            
            $result[$key] = array_reverse($result[$key]);
            
        }
        
        foreach ($result as $key => $value) {
            
            if(!$value){
                
                unset($result[$key]);
                
            }else{
                
                foreach ($value as $key2 => $value2) {
                    
                    if(!$value2){
                       
                       $result[$key][ $key2 ] = $key2;
                       
                   }
                   
                }
                
            }
            
        }
        
	$this->writeAnyCSVActionsCache($cache_file_name, $result);
	
        return $result;
    }
    
    public function getInstructionToOption($title='',$field,$type,$type_data) {
        
        $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
        
        $type_parts = explode('(', $type);
        
        if(isset($type_parts[0]) && isset($type_parts[1])){
            
            $type_parts[1] = str_replace(')', '', $type_parts[1]);
            
        }
        
        if($type_parts[0]=='int' || $type_parts[0]=='tinyint' || $type_parts[0]=='varchar'){
            
            $type_instruction = sprintf($this->language->get('entry_instruction_to_select_option_'.$type_parts[0]), $type_parts[1]);
            
        }
        
        elseif($type_parts[0]=='date' || $type_parts[0]=='text'){
            
            $type_instruction = $this->language->get('entry_instruction_to_select_option_'.$type_parts[0]);
            
        }
        
        elseif($type_parts[0]=='decimal' ){
            
            $type_parts[1] = explode(',', $type_parts[1]);
            
            $type_instruction = sprintf($this->language->get('entry_instruction_to_select_option_'.$type_parts[0]), $type_parts[1][1]);
            
        }else{
            
            $type_instruction = $type;
            
        }
        
        if($title){
            
            return $title.' '.$this->language->get('entry_instruction_to_select_option_field_to_db').'.........................................('.$field.' - '.$type_instruction.')';
            
        }else{
            
            return $field.'.........................................('.$type_instruction.')';
            
        }
        
        
    }

    public function prepareValue($value, $types_prepare=array('csv'),$clean_all_html=FALSE,$length=0,$add_str_end='',$htmlentities=FALSE,$encoding='UTF-8') {
        
        if($encoding!=='UTF-8' && $encoding){
            
            $value = $this->convertCSVValue($value, $encoding, 'UTF-8');
            
        }
        /*
        $find = array('___quot___', '___ap___', '___amp___');

        $replace = array('"', "'", '&');
        
        $value = str_replace($find, $replace, $value);
        */
        foreach ($types_prepare as $type) {
            
            if($type=='csv'){
                
                $value = trim(ltrim($value));
                
                if($clean_all_html){
                    
                    $value = strip_tags($value);
                    
                }elseif($htmlentities){
                    
                    $value = htmlentities($value,ENT_QUOTES,'UTF-8');
                    
                }
                
                if($length>0){
                    
                    $last_value = $value;
                    
                    $value = mb_strcut($value, 0, $length, 'UTF-8');
                    
                    if($add_str_end && $last_value!=$value){
                        
                        $value .= $add_str_end;
                        
                    }
                    
                    
                }
                
                $first_letter = mb_strcut($value, 0, 1, 'UTF-8');
                
                $last_letter = mb_strcut($value, (mb_strlen($value, 'UTF-8')-1), mb_strlen($value, 'UTF-8'), 'UTF-8');
                
                if($first_letter=='"'){
                    
                    $value = mb_strcut($value, 1, mb_strlen($value, 'UTF-8'), 'UTF-8');
                    
                }
                if($last_letter=='"'){
                    
                    $value = mb_strcut($value, 0, (mb_strlen($value, 'UTF-8')-1), 'UTF-8');
                    
                }
                
            }
            
        }
        
        return $value;
    }
    
    //$this->replaceSpecData($spec_data, json_encode($offer[$csv_column_name]))

    public function returnReplaceSpecData($spec_data,$string){
	
	$find = array();
	
	$replace = array();
	
	foreach ($spec_data as $key => $value) {
	    
	    $replace[] = $value;
	    
	    $find[] = $key;
	    
	}
	
	$string = str_replace($find, $replace, $string);
	
	return $string;
	
    }
    
    public function getCsvRows($file,$start,$limit,$template_data,$url='',$clean_all_html=FALSE,$length=0,$add_str_end='',$htmlentities=FALSE,$last_row=FALSE,$file_upload=''){
        
        $result['count_rows'] = 0;
        
        $result['count_fields'] = 0;
        
        $result['field_position'] = array();
        
        $result['data'] = array();
        
        $first_row = TRUE;
        
        $this->odmpro_tamplate_data = $template_data;
        
        if($url && !$file){
            
            $file = $this->getFileByURL($url);
            
        }elseif($file_upload && !$file){
            
            $file = $this->getFileByFileName($file_upload);
            
        }
        
        if($file){
            
            $template_data['csv_delimiter'] = trim($template_data['csv_delimiter']);
            if(isset($template_data['csv_enclosure']) && $template_data['csv_enclosure']!==''){
                $template_data['csv_enclosure'] = trim($template_data['csv_enclosure']);
            }else{
                $template_data['csv_enclosure'] = '"';
            }
            if(isset($template_data['csv_escape'])){
                $template_data['csv_escape'] = trim($template_data['csv_escape']);
            }
            if(!$template_data['csv_escape']){
                $template_data['csv_escape'] = "\\";
            }
            $template_data['csv_enclosure'] = html_entity_decode($template_data['csv_enclosure']);
            $template_data['csv_escape'] = html_entity_decode($template_data['csv_escape']);
	    
	    $spec_data = array(
		'_-D-_' => $template_data['csv_delimiter'],
		'_-E-_' => $template_data['csv_enclosure'],
	    );
            
            while (($data = fgetcsv($file, 0, $template_data['csv_delimiter'], $template_data['csv_enclosure'], $template_data['csv_escape'])) !== FALSE) {

                if($first_row){

                    foreach ($data as $position => $field) {

                        $replace = array('','','','');
                        
                        $find = array('"','&#34;','&#39;',"'");
                        
                        $field = str_replace($find, $replace, $field);
			
			$field = $this->returnReplaceSpecData($spec_data, $field);
                        
                        $result['field_position'][$position] = $this->prepareValue($field,array('csv'),$clean_all_html,$length,$add_str_end,$htmlentities,$template_data['encoding']);

                    }

                    $first_row = FALSE;

                }

                $result['count_fields'] = count($data);

                if($result['count_rows']    >=  $start && count($result['data'])    <   $limit && !$last_row){

                    foreach ($data as $position => $value) {

			$value = $this->returnReplaceSpecData($spec_data, $value);
			
                        $data[$position] = $this->prepareValue($value,array('csv'),$clean_all_html,$length,$add_str_end,$htmlentities,$template_data['encoding']);

                    }

                    $result['data'][] = $data;

                }elseif($last_row && $result['count_rows'] == count($result['data'])){
                    
                    $result['data'] = array();
                    
                    foreach ($data as $position => $value) {

			$value = $this->returnReplaceSpecData($spec_data, $value);
			
                        $data[$position] = $this->prepareValue($value,array('csv'),$clean_all_html,$length,$add_str_end,$htmlentities,$template_data['encoding']);

                    }
                    
                    $result['data'][] = $data;
                    
                }

                $result['count_rows']++;

            }
            
        }
        
        return $result;
    } 
    
    public function exportCSV($odmpro_tamplate_data,$import_steps,$log_data){
        
        if(!$this->lic){
            return;
        }
        
        $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
        
        $store_id['value_array'] = $odmpro_tamplate_data['store_id'];
        
        $store_id['sql'] = array();
        
        foreach ($store_id['value_array'] as $store_id_selected) {
            
            $store_id['sql'][$store_id_selected] = " store_id = '".$store_id_selected."' ";
            
        }
        
        $start = (int)$this->request->get['start'];
        
        $limit = $odmpro_tamplate_data['limit'];
        
        $this_num_row = $start;
        
        if(isset($odmpro_tamplate_data['php_after_import']) && $odmpro_tamplate_data['php_after_import']!==''){
            
            $odmpro_tamplate_data['php_after_import'] = html_entity_decode($odmpro_tamplate_data['php_after_import']);
            
            $odmpro_tamplate_data['php_after_import'] = str_replace('"', "'", $odmpro_tamplate_data['php_after_import']);
            
            eval($odmpro_tamplate_data['php_after_import']);
            
        }
        
        $log_write_rows = array();
        
        $no_store_id_tables = array_flip(array('attribute','attribute_group','option_value','option','filter_group','filter'));
        
        $language_id['value_string'] = $odmpro_tamplate_data['language_id'];
        
        $language_id['sql']['language_id'] = " language_id = '".$odmpro_tamplate_data['language_id']."' ";
        
        $currency = $this->getCurrencyByCode($odmpro_tamplate_data['currency_code']);
        
        $currency_code = $odmpro_tamplate_data['currency_code'];
        
        if(!isset($currency['currency_id'])){
            
            $currency['currency_id'] = 0;
            
        }
        
        $currency_id = $currency['currency_id'];
        
        $type_change = $odmpro_tamplate_data['type_change'];
        
        $abstract_fields = $this->getAbstractFields();
        
        $this->odmpro_tamplate_data = $odmpro_tamplate_data;
        
        $csv_columns = array();
        
        $write_data = array();
        
        $HTTP_CATALOG = '';
        if(defined('HTTP_CATALOG')){
            $HTTP_CATALOG = HTTP_CATALOG;
        }elseif(defined('HTTP_SERVER')){
            $HTTP_CATALOG = HTTP_SERVER;
        }
        
        
        if($this->config->get('config_secure')){
            
            if(defined('HTTPS_CATALOG')){
                $HTTP_CATALOG = HTTPS_CATALOG;
            }elseif(defined('HTTPS_SERVER')){
                $HTTP_CATALOG = HTTPS_SERVER;
            }
            
        }
        
        $related_data_column = 0;
        
        $no_csv_headers = 0;
        
        $total = 0;
        
        $self_column = array();
        
        if(isset($odmpro_tamplate_data['self_column'])){
            
            $self_column = $odmpro_tamplate_data['self_column'];
            
        }
        
        foreach ($import_steps as $type_data => $step) {
            
            $general_setting = $step['general_settings'];
            
            $column_settings = $step['column_settings'];
            
            $identificator = array();
            
            if(isset($step['identificator'])){
                
                $identificator = $step['identificator'];
                
            }

            $skip = array();

            if($type_data=='product' && !$skip){

                $id_name = 'product_id';

                $main_table = 'product';

                $identificator_field_name = $id_name;

                $identificator_table = $main_table;
                
                $identificator_field_field = '';

                if($identificator){

                    foreach ($identificator as $identificator_param) {

                        if($identificator_param['identificator_type'] == 'name'){

                            $identificator_field_name = $identificator_param['identificator_type'];

                            $identificator_table = 'product_description';

                        }elseif($identificator_param['identificator_type'] == 'aid'){

                            $identificator_field_name = $id_name;

                            $identificator_table = $main_table;

                        }elseif($identificator_param['identificator_type']){

                            $identificator_field_name = $identificator_param['identificator_type'];

                            $identificator_table = $main_table;

                        }
                        
                        $identificator_field_field = $identificator_param['field'];

                    }

                }
                
                $data_to_db = $this->getDataToDB($main_table,$odmpro_tamplate_data,$log_data,$language_id['value_string'],$store_id['value_array'],$start,$limit,$general_setting);
                
                $total_data_to_db = $this->getDataToDB($main_table,$odmpro_tamplate_data,$log_data,$language_id['value_string'],$store_id['value_array'],$odmpro_tamplate_data['start'],100000,$general_setting,TRUE);
                
                $total = $total_data_to_db->row['total'];
                
                if(!$skip && $identificator_field_name && $data_to_db){
                    
                    foreach($data_to_db as ${$id_name} => $data_to_db_row){
                        
                        $write_data_row = array();
            
                        $skip_by_column_request = array();
                        
                        foreach ($column_settings as $field => $setting){
                            
                            $csv_columns[$field] = $field;

                            $write_data_row[$field]['value'] = '';
                            
                            $write_data_row[$field]['values'] = array();
                            
                            $additinal_settings = array();

                            if($setting['additinal_settings']){

                                $additinal_settings = $setting['additinal_settings'];

                            }

                            $data_action = 'export_values';

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);

                            $column_request = 0;

                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']){

                                $column_request = 1;

                            }
                            
                            /*
                             * Сохраняю данные для записи
                             */
                            /*
                             * Если это идентификатор, он может быть названием - в остальных случаях, пройдет мимо и присвоится в следующем if
                             */
                            if($identificator_field_field && $identificator_field_field == $field){
                                
                                if($identificator_field_name=='name' && isset($data_to_db_row[$main_table.'_description']['rows'])){
                                    
                                    $data_description = end($data_to_db_row[$main_table.'_description']['rows']);
                                
                                    if(isset ($data_description['name'])){

                                        $write_data_row[$field]['value'] = $data_description['name'];

                                    }
                                    
                                }else{
                                    
                                    if(isset($data_to_db_row[$main_table]['row']) && isset($data_to_db_row[$main_table]['row'][$identificator_field_name])){
                                        
                                        $write_data_row[$field]['value'] = $data_to_db_row[$main_table]['row'][$identificator_field_name];
                                        
                                    }
                                    
                                }
                                
                                
                            }
                            elseif(isset($data_to_db_row[$db_table]['rows']) && isset($data_to_db_row[$db_table]['columns'][$db_column])){

                                foreach($data_to_db_row[$db_table]['rows'] as $data_to_db_row_row){
                                    
                                    if(isset($data_to_db_row_row[$db_column])){
                                        
                                        $write_data_row[$field]['values'][] = $data_to_db_row_row[$db_column];
                                        
                                    }
                                    
                                }

                            }
                            elseif(isset($data_to_db_row[$db_table]['row']) && isset($data_to_db_row[$db_table]['columns'][$db_column])){
                                
                                if(isset($data_to_db_row[$db_table]['row'][$db_column])){
                                    
                                    $write_data_row[$field]['value'] = $data_to_db_row[$db_table]['row'][$db_column];
                                    
                                }
                                

                            }

                            /*
                             * Расширенные колонки
                             */

                            else{
                                
                                if($db_column=='image_advanced' || $db_column=='images'){

                                    if($db_column=='image_advanced'){

                                        $image = '';
                                        
                                        if(isset($data_to_db_row['product']['row']['image'])){
                                            
                                            $image = $data_to_db_row['product']['row']['image'];
                                            
                                        }
                                        
                                        if(isset($additinal_settings['image_upload']) && $additinal_settings['image_upload']){
                                            
                                            
                                            $image = $HTTP_CATALOG.basename(DIR_IMAGE).'/'.$image;
                                            
                                        }
                                        
                                        $write_data_row[$field]['value'] = $image;

                                    }else{
                                        
                                        $images = array();
                                        
                                        if(isset($additinal_settings['first_image_add']) && $additinal_settings['first_image_add']){

                                            if(isset($additinal_settings['image_upload']) && $additinal_settings['image_upload'] && file_exists(DIR_IMAGE.$data_to_db_row['product']['row']['image']) && is_file(DIR_IMAGE.$data_to_db_row['product']['row']['image'])){
                                                
                                                $images[$data_to_db_row['product']['row']['image']] = $HTTP_CATALOG.basename(DIR_IMAGE).'/'.$data_to_db_row['product']['row']['image'];

                                            }elseif(file_exists(DIR_IMAGE.$data_to_db_row['product']['row']['image']) && is_file(DIR_IMAGE.$data_to_db_row['product']['row']['image'])){

                                                $images[$data_to_db_row['product']['row']['image']] = $data_to_db_row['product']['row']['image'];

                                            }

                                        }
                                        
                                        
                                        
                                        if(isset($data_to_db_row['product_image']['rows']) && $data_to_db_row['product_image']['rows']){
                                            
                                            
                                            foreach($data_to_db_row['product_image']['rows'] as $image_data){
                                                
                                                if($image_data['image'] && file_exists(DIR_IMAGE.$image_data['image'])  && is_file(DIR_IMAGE.$image_data['image'])){
                                                    
                                                    if(isset($additinal_settings['image_upload']) && $additinal_settings['image_upload']){

                                                        $image_data['image'] = $HTTP_CATALOG.basename(DIR_IMAGE).'/'.$image_data['image'];

                                                    }

                                                    $images[$image_data['image']] = $image_data['image'];
                                                    
                                                }

                                            }
                                            
                                        }
                                        
                                        if($images){
                                            
                                            $delimeter = ',';

                                            if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']!==''){

                                                $delimeter = $additinal_settings['delimeter'];

                                            }
                                            
                                            $write_data_row[$field]['value'] = implode($delimeter, $images);
                                            
                                        }

                                    }

                                }
                                
                                elseif($db_column=='category_whis_path'){

                                    $delimeter = '';

                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '/';

                                    }
                                    
                                    $product_categories = array();
                                    
                                    if(isset($data_to_db_row['product_to_category']['rows']) && $data_to_db_row['product_to_category']['rows']){
                                        
                                        $product_categories = $data_to_db_row['product_to_category']['rows'];
                                        
                                        foreach($product_categories as $product_category){
                                            
                                            $category_whis_path = $this->getCategories($delimeter,$language_id['value_string'], $product_category['category_id']);
                                            
                                            if($category_whis_path){
                                                
                                                foreach($category_whis_path as $category_whis_path_row){
                                                    
                                                    $write_data_row[$field]['values'][$category_whis_path_row['name']] = $category_whis_path_row['name'];
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='category_name_and_parent_level'){

                                    $product_categories = array();
                                    
                                    $category_name_and_parent_level = array();
                                    
                                    $categories_name_and_parent_level = array();
                                    
                                    $max_count_categories_on_level = 0;
                                    
                                    $this_parent_level = (int)$additinal_settings['parent_level'];
                                    
                                    if($additinal_settings['parent_level']!==''){

                                        $this_parent_level = (int)$additinal_settings['parent_level'];

                                    }
                                    
                                    if(isset($data_to_db_row['product_to_category']['rows']) && $data_to_db_row['product_to_category']['rows']){
                                        
                                        $product_categories = $data_to_db_row['product_to_category']['rows'];
                                        
                                        foreach($product_categories as $product_category){
                                            
                                            $category_whis_path = $this->getCategories('^',$language_id['value_string'], $product_category['category_id']);
                                            
                                            if($category_whis_path){
                                                
                                                foreach($category_whis_path as $category_whis_path_row){
                                                    
                                                    $category_name_and_parent_level = explode('^',$category_whis_path_row['name']);
                                                    
                                                    foreach($category_name_and_parent_level as $parent_level => $category_name_and_parent_level_category_name){
                                                        
                                                        $categories_name_and_parent_level[$parent_level]['categories'][] = $category_name_and_parent_level_category_name;
                                                        
                                                    }
                                                    
                                                }
                                                
                                                if($categories_name_and_parent_level){
                                                    
                                                    foreach($categories_name_and_parent_level as $parent_level=>$tmp){
                                                    
                                                        $categories_name_and_parent_level[$parent_level]['count_categories_on_level'] = count($categories_name_and_parent_level[$parent_level]['categories']);

                                                    }
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    
                                    

                                    foreach($categories_name_and_parent_level as $parent_level => $categories_on_level){

                                        if($categories_on_level['count_categories_on_level'] > $max_count_categories_on_level){

                                            $max_count_categories_on_level = $categories_on_level['count_categories_on_level'];

                                        }

                                    }
                                    
                                    if($max_count_categories_on_level){
                                        
                                        for($num_categories_on_level=0;$num_categories_on_level<=$max_count_categories_on_level;$num_categories_on_level++){
                                            
                                            if(isset($categories_name_and_parent_level[$this_parent_level])){
                                                
                                                if(isset($categories_name_and_parent_level[$this_parent_level]['categories'][$num_categories_on_level])){
                                                    
                                                    $write_data_row[$field]['values'][] = $categories_name_and_parent_level[$this_parent_level]['categories'][$num_categories_on_level];
                                                    
                                                }else{
                                                    
                                                    $write_data_row[$field]['values'][] = '';
                                                    
                                                }
                                                
                                            }else{
                                                    
                                                $write_data_row[$field]['values'][] = '';

                                            }
                                            
                                        }
                                        
                                    }
                                    

                                    $other_category_name_and_parent_level = array();

                                    foreach($column_settings as $field_tmp => $setting_tmp){

                                        $db_table___db_column_tmp = explode('___', $setting_tmp['db_table___db_column']);

                                        $db_column_tmp = $db_table___db_column_tmp[1];

                                        if($db_column_tmp=='category_name_and_parent_level' && $field != $field_tmp){

                                            if(isset($setting_tmp['additinal_settings']) && isset($csv_data[$field_tmp]) && $setting_tmp['additinal_settings']['parent_level']!==''){

                                                $other_category_name_and_parent_level[] = array(
                                                    'additinal_settings'    => $setting_tmp['additinal_settings'],
                                                    'value' => trim(ltrim($csv_data[$field_tmp]))
                                                ); 

                                            }

                                        }

                                    }

                                    $category_name_and_parent_level = array();

                                    if(isset($csv_data[$field]) && $additinal_settings['parent_level']!==''){

                                        $other_category_name_and_parent_level[] = array(
                                            'additinal_settings'    => $additinal_settings,
                                            'value' => trim(ltrim($csv_data[$field]))
                                        );

                                    }

                                    if($other_category_name_and_parent_level){

                                        foreach ($other_category_name_and_parent_level as $other_category_name_and_parent_level_row) {

                                            $category_name_and_parent_level[$other_category_name_and_parent_level_row['additinal_settings']['parent_level']] = $other_category_name_and_parent_level_row;

                                        }

                                        ksort($category_name_and_parent_level);

                                    }

                                    $category_names_whise_parents = array();

                                    $category_names = array();

                                    if($category_name_and_parent_level){

                                        for($c=0;$c<count($category_name_and_parent_level);$c++){

                                            if(isset($category_name_and_parent_level[$c]) && $category_name_and_parent_level[$c]['value']){

                                                $category_names[] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['category_name'] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['additinal_settings'] = $category_name_and_parent_level[$c]['additinal_settings'];

                                            }

                                        }

                                    }

                                    $delimiter_tmp = '/';

                                    $path_whis_categories_name = '';

                                    if($category_names_whise_parents && count($category_names_whise_parents) == count($category_name_and_parent_level)){

                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);

                                    }
                                    /*
                                     * Если одна и парент нуль, то одна категория
                                     */
                                    elseif($category_names_whise_parents && count($category_names_whise_parents) == 1 && isset($category_names_whise_parents[0])){

                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);

                                    }

                                    $path_whis_parent_categories = '';

                                    if($additinal_settings['parent_category_id']){

                                        $parent_categories = $this->getCategories($delimiter_tmp,$language_id['value_string'],$additinal_settings['parent_category_id']);

                                        if($parent_categories){

                                            $path_whis_parent_categories = $parent_categories[$additinal_settings['parent_category_id']]['name'].$delimiter_tmp;

                                        }

                                    }

                                    if($path_whis_categories_name){

                                        $path_whis_categories_name = $path_whis_parent_categories.$path_whis_categories_name;

                                    }

                                    if(!$path_whis_categories_name){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_path_whis_categories_name'),  $field)),

                                            'action'    => $log_data['type_process']
                                        );

                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }else{
                                        $additinal_settings['delimeter']=$delimiter_tmp;
                                        /*
                                        * Категории в товарах всегда, только добавляются. Чистка категорий возможна при импорте категорий - той же колонки, например, но настройки импорт категолрий
                                        * В этом случае delete_values в последнем аргументе
                                        */
                                       $categories = $this->getCategoriesIdByPath($path_whis_categories_name,$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, 'add_values', $log_data);

                                       if($categories){

                                           $c = 1;

                                           foreach ($categories as $category_id) {

                                                $product_to_category = array();

                                                /*
                                                 * Продукт в во всех категория
                                                 */
                                                if(isset($additinal_settings['all_product_category']) && $additinal_settings['all_product_category']){

                                                    if($c == count($categories) && $this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }elseif($c == count($categories)){

                                                    if($this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }

                                                $c++;

                                           }

                                       }else{

                                           $log_data['__line__'] = __LINE__; 

                                           $log_write_row = array(
                                               'log_data' => $log_data,
                                               'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_catogory'),  $field)),

                                               'action'    => $log_data['type_process']
                                           );

                                           $this->setLogDataRow($log_write_row,$log_data);

                                       }

                                    }

                                }

                                elseif($db_column=='category_id'){

                                    $product_categories = array();
                                    
                                    if(isset($data_to_db_row['product_to_category']['rows']) && $data_to_db_row['product_to_category']['rows']){
                                        
                                        $product_categories = $data_to_db_row['product_to_category']['rows'];
                                        
                                        foreach($product_categories as $product_category){
                                            
                                            $write_data_row[$field]['values'][$product_category['category_id']] = $product_category['category_id'];
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='manufacturer_name'){
                                    
                                    $manufacturer_name = $this->getManufacturerNamrByManufacturerId($data_to_db_row['product']['row']['manufacturer_id'], $language_id['value_string']);
                                    
                                    if($manufacturer_name){
                                        
                                        $write_data_row[$field]['value'] = $manufacturer_name;
                                        
                                    }

                                }

                                elseif($db_column=='price_advanced'){
                                    
                                    if($db_table=='product'){
                                        
                                        $price = $this->getPriceBySettings($data_to_db_row['product']['row']['price'], $additinal_settings);
                                        
                                        $write_data_row[$field]['value'] = $price;
                                        
                                    }elseif($db_table=='product_discount'){
                                        
                                        if($data_to_db_row['product_discount']['rows']){
                                            
                                            foreach($data_to_db_row['product_discount']['rows'] as $product_discount_row){
                                        
                                                $price = $this->getFloat($product_discount_row['price']);

                                                if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                                    $price *= $this->getFloat($additinal_settings['price_rate']);

                                                }

                                                if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                                    $price *= $this->getFloat($additinal_settings['price_delta']);

                                                }

                                                if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                                    $price = round($price,0);

                                                }

                                                $write_data_row[$field]['values'][] = $price;
                                                
                                            }
                                            
                                        }else{
                                            
                                            $write_data_row[$field]['values'][] = '';
                                            
                                        }
                                        
                                    }elseif($db_table=='product_special'){
                                        
                                        if($data_to_db_row['product_special']['rows']){
                                            
                                            foreach($data_to_db_row['product_special']['rows'] as $product_discount_row){
                                        
                                                $price = $this->getFloat($product_discount_row['price']);

                                                if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                                    $price *= $this->getFloat($additinal_settings['price_rate']);

                                                }

                                                if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                                    $price *= $this->getFloat($additinal_settings['price_delta']);

                                                }

                                                if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                                    $price = round($price,0);

                                                }

                                                $write_data_row[$field]['values'][] = $price;
                                                
                                            }
                                            
                                        }else{
                                            
                                            $write_data_row[$field]['values'][] = '';
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='quantity_advanced'){

                                    $quantity = (int)$data_to_db_row['product']['row']['quantity'];

                                    if(isset($additinal_settings['quantity_update']) && $additinal_settings['quantity_update'] && !$quantity){

                                        $quantity = (int)$additinal_settings['quantity_update'];

                                    }
                                    
                                    if(isset($general_setting['quantity_default']) && $general_setting['quantity_default']!==''){
                                        
                                        $quantity = (int)$general_setting['quantity_default'];
                                        
                                    }
                                    
                                    $write_data_row[$field]['value'] = $quantity;

                                }

                                elseif($db_column=='seo_url'){

                                    $seo_url = $this->getSeoUrl($id_name.'='.${$id_name});

                                    $write_data_row[$field]['value'] = $seo_url;

                                }
                                
                                elseif($db_column=='seo_url_aut'){

                                    $seo_url_aut = $this->url->link('product/product', $id_name.'='.${$id_name});

                                    $write_data_row[$field]['value'] = $seo_url_aut;

                                }
                                
                                elseif($db_column=='url_whis_params'){

                                    $url_whis_params = $HTTP_CATALOG.'index.php?route=product/product&'.$id_name.'='.${$id_name};

                                    $write_data_row[$field]['value'] = $url_whis_params;

                                }

                                elseif($db_column=='attribute_value' || $db_column=='attribute_values'){
                                    
                                    $attribute_id = 0;
                                    
                                    $attribute_group_id = 0;
                                    
                                    if(isset($additinal_settings['attribute_group_id___attribute_id']) && $additinal_settings['attribute_group_id___attribute_id']){
                                        
                                        $attribute_group_id___attribute_id = explode('___', $additinal_settings['attribute_group_id___attribute_id']);
                                        
                                        $attribute_group_id = $attribute_group_id___attribute_id[0];
                                        
                                        $attribute_id = $attribute_group_id___attribute_id[1];
                                        
                                    }

                                    $product_attribute = $data_to_db_row['product_attribute']['rows'];

                                    $attribute_values_result = array();
                                    
                                    if($product_attribute){
                                        
                                        foreach($product_attribute as $attribute_value_data){
                                            
                                            if( (!$attribute_id) || ($attribute_id && $attribute_id == $attribute_value_data['attribute_id']) ){
                                                
                                                $attribute_values_result[] = $attribute_value_data['text'];
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    if($db_column=='attribute_values' && $attribute_values_result){

                                        $delimeter = '';

                                        if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                            $delimeter = $additinal_settings['delimeter'];

                                        }

                                        if(!$delimeter){

                                            $delimeter = '|';

                                        }
                                        
                                        $write_data_row[$field]['values'][implode($delimeter,$attribute_values_result)] = implode($delimeter,$attribute_values_result);

                                    }elseif($attribute_values_result){
                                        
                                        foreach($attribute_values_result as $attribute_value_result){
                                            
                                            $write_data_row[$field]['values'][$attribute_value_result] = $attribute_value_result;
                                            
                                        }

                                    }

                                }

                                elseif($db_column=='attribute_values_whis_attrubute_name'){

                                    if(isset($additinal_settings['attribute_group_id'])){

                                        $attribute_group_id = (int)$additinal_settings['attribute_group_id'];
                                        
                                        $delimiter_1 = '';

                                        if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                            $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                        }

                                        if(!$delimiter_1){

                                            $delimiter_1 = '|';

                                        }

                                        $delimiter_2 = '';

                                        if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                            $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                        }

                                        if(!$delimiter_2){

                                            $delimiter_2 = '---';

                                        }
                                        
                                        $product_data = $data_to_db_row['product_attribute']['rows'];
                                        
                                        $product_data_whis_text = $this->getDataByGroupIdAndProducID($language_id['value_string'], 'attribute' , $product_data);

                                        $pattribute_data_result = array();
                                        
                                        if($product_data_whis_text){

                                            foreach($product_data_whis_text as $product_data_whis_text_row){
                                                
                                                if( ($attribute_group_id && $attribute_group_id == $product_data_whis_text_row['attribute_group_id']) || (!$attribute_group_id) ){
                                                    
                                                    foreach($product_data_whis_text_row['attribute'] as $product_data_whis_text_row_attribute){
                                                        
                                                        $pattribute_data_result[$product_data_whis_text_row_attribute['name'].$delimiter_2.$product_data_whis_text_row_attribute['text']] = $product_data_whis_text_row_attribute['name'].$delimiter_2.$product_data_whis_text_row_attribute['text'];
                                                        
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }
                                        
                                        if($pattribute_data_result){
                                            
                                            $write_data_row[$field]['values'][implode($delimiter_1,$pattribute_data_result)] = implode($delimiter_1,$pattribute_data_result);
                                            
                                        }

                                    }

                                }

                                elseif($db_column=='attribute_values_whis_attrubute_name_and_group_name'){
                                    
                                    if(isset($additinal_settings['attribute_group_id'])){

                                        $attribute_group_id = (int)$additinal_settings['attribute_group_id'];
                                        
                                        $delimiter_1 = '';

                                        if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                            $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                        }

                                        if(!$delimiter_1){

                                            $delimiter_1 = '|';

                                        }

                                        $delimiter_2 = '';

                                        if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                            $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                        }

                                        if(!$delimiter_2){

                                            $delimiter_2 = '---';

                                        }
                                        
                                        $product_data = $data_to_db_row['product_attribute']['rows'];
                                        
                                        $product_data_whis_text = $this->getDataByGroupIdAndProducID($language_id['value_string'], 'attribute' , $product_data);

                                        $pattribute_data_result = array();
                                        
                                        if($product_data_whis_text){

                                            foreach($product_data_whis_text as $product_data_whis_text_row){
                                                
                                                if( ($attribute_group_id && $attribute_group_id == $product_data_whis_text_row['attribute_group_id']) || (!$attribute_group_id) ){
                                                    
                                                    foreach($product_data_whis_text_row['attribute'] as $product_data_whis_text_row_attribute){
                                                        
                                                        $pattribute_data_result[$product_data_whis_text_row['name'].$delimiter_2.$product_data_whis_text_row_attribute['name'].$delimiter_2.$product_data_whis_text_row_attribute['text']] = $product_data_whis_text_row['name'].$delimiter_2.$product_data_whis_text_row_attribute['name'].$delimiter_2.$product_data_whis_text_row_attribute['text'];
                                                        
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }
                                        
                                        if($pattribute_data_result){
                                            
                                            $write_data_row[$field]['values'][implode($delimiter_1,$pattribute_data_result)] = implode($delimiter_1,$pattribute_data_result);
                                            
                                        }

                                    }
                                    
                                }

                                elseif($db_column=='filter_name' || $db_column=='filter_values_whis_filter_name'){

                                    if(isset($additinal_settings['filter_group_id'])){
                                        
                                        $filter_group_id = (int)$additinal_settings['filter_group_id'];
                                        
                                        $delimiter = '|';

                                        if(isset($additinal_settings['delimiter']) && $additinal_settings['delimiter']){

                                            $delimiter = trim(ltrim($additinal_settings['delimiter']));

                                        }
                                        
                                        $product_data = $data_to_db_row['product_filter']['rows'];
                                        
                                        $product_data_whis_text = $this->getDataByGroupIdAndProducID($language_id['value_string'], 'filter' , $product_data);

                                        $filter_data_result = array();
                                        
                                        if($product_data_whis_text){

                                            foreach($product_data_whis_text as $product_data_whis_text_row){
                                                
                                                if( ($filter_group_id && $filter_group_id == $product_data_whis_text_row['filter_group_id']) || (!$filter_group_id) ){
                                                    
                                                    foreach($product_data_whis_text_row['filter'] as $product_data_whis_text_row_filter){
                                                        
                                                        $filter_data_result[$product_data_whis_text_row_filter['name']] = $product_data_whis_text_row_filter['name'];
                                                        
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }
                                        
                                        if($filter_data_result){
                                            
                                            if($db_column=='filter_values_whis_filter_name'){
                                                
                                                $write_data_row[$field]['values'][implode($delimiter,$filter_data_result)] = implode($delimiter,$filter_data_result);
                                                
                                            }else{
                                                
                                                foreach($filter_data_result as $filter_data_result_row){
                                                    
                                                    $write_data_row[$field]['values'][$filter_data_result_row] = $filter_data_result_row;
                                                    
                                                }
                                                
                                            }
                                            
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='filter_values_whis_filter_name_and_group_name'){

                                    if(isset($additinal_settings['filter_group_id'])){
                                        
                                        $filter_group_id = (int)$additinal_settings['filter_group_id'];
                                        
                                        $delimiter_1 = '';

                                        if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                            $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                        }

                                        if(!$delimiter_1){

                                            $delimiter_1 = '|';

                                        }
                                        
                                        $delimiter_2 = '';

                                        if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                            $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                        }

                                        if(!$delimiter_2){

                                            $delimiter_2 = '---';

                                        }
                                        
                                        $product_data = $data_to_db_row['product_filter']['rows'];
                                        
                                        $product_data_whis_text = $this->getDataByGroupIdAndProducID($language_id['value_string'], 'filter' , $product_data);

                                        $filter_data_result = array();
                                        
                                        if($product_data_whis_text){

                                            foreach($product_data_whis_text as $product_data_whis_text_row){
                                                
                                                if( ($filter_group_id && $filter_group_id == $product_data_whis_text_row['filter_group_id']) || (!$filter_group_id) ){
                                                    
                                                    foreach($product_data_whis_text_row['filter'] as $product_data_whis_text_row_filter){
                                                        
                                                        $filter_data_result[$product_data_whis_text_row['name'] .$delimiter_2. $product_data_whis_text_row_filter['name']] = $product_data_whis_text_row['name'] .$delimiter_2. $product_data_whis_text_row_filter['name'];
                                                        
                                                    }
                                                    
                                                }
                                                
                                            }

                                        }
                                        
                                        if($filter_data_result){
                                            
                                            $write_data_row[$field]['values'][implode($delimiter_1,$filter_data_result)] = implode($delimiter_1,$filter_data_result);
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='relate_by_product_id'){

                                    $delimeter = '|';

                                    if($additinal_settings['delimeter']!==''){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    $related_product_ids = array();

                                    $product_data = $data_to_db_row['product_related']['rows'];
                                    
                                    if($product_data){
                                        
                                        foreach($product_data as $product_data_row){
                                            
                                            $related_product_ids[$product_data_row['related_id']] = $product_data_row['related_id'];
                                            
                                        }
                                        
                                    }

                                    if($related_product_ids){

                                        $write_data_row[$field]['values'][implode($delimeter,$related_product_ids)] = implode($delimeter,$related_product_ids);

                                    }

                                }

                                elseif($db_column=='relate_by_sku'){

                                    $delimeter = '|';

                                    if($additinal_settings['delimeter']!==''){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    $related_product_ids = array();

                                    $product_data = $data_to_db_row['product_related']['rows'];
                                    
                                    if($product_data){
                                        
                                        foreach($product_data as $product_data_row){
                                            
                                            $related_by_sku = $this->getValueFromDB('product', 'sku', $where = array('product_id'=>$product_data_row['related_id']));
                                            
                                            if($related_by_sku){
                                                
                                                $related_product_ids[] = $related_by_sku;
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    if($related_product_ids){

                                        $write_data_row[$field]['values'][implode($delimeter,$related_product_ids)] = implode($delimeter,$related_product_ids);

                                    }

                                }

                                elseif($db_column=='option_value_option_microdata_1'){
                                    
                                    $product_data = $this->getProductOptions(${$id_name},$language_id['value_string']);

                                    $delimeter = '';

                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '|';

                                    }
                                    
                                    $price = $this->getFloat($data_to_db_row['product']['row']['price']);
                                    
                                    $price_whis_delta = '';
                                    
                                    if(isset($additinal_settings['price_whis_delta']) && !$additinal_settings['price_whis_delta']){
                                            
                                        $price_whis_delta = $price;

                                    }
                                    
                                    $option_values = array();
                                    
                                    if(isset($additinal_settings['option_id']) && $product_data){
                                        
                                        $option_id = (int)$additinal_settings['option_id'];
                                        
                                        foreach($product_data as $key => $product_data_row){
                                            
                                            if( ($option_id && $option_id==$product_data_row['option_id']) || !$option_id){
                                                
                                                $option_values_row = array();
                                                
                                                if($product_data_row['product_option_value']){
                                                    
                                                    foreach($product_data_row['product_option_value'] as $product_data_row_product_option_value){
                                                        
                                                        $product_option_value_id = $product_data_row_product_option_value['product_option_value_id'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row['type'];
                                                
                                                        $option_values_row[$product_option_value_id][] = $product_data_row['name'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['name'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row['required'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['quantity'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['subtract'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['price_prefix'];
                                                        
                                                        $option_price = (float)$product_data_row_product_option_value['price'];
                                                        
                                                        if($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='+'){
                                                            
                                                            $option_price += $price_whis_delta; 
                                                            
                                                        }elseif($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='-'){
                                                            
                                                            $option_price = $price_whis_delta - $option_price; 
                                                            
                                                        }
                                                        
                                                        $option_values_row[$product_option_value_id][] = $option_price;
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['points_prefix'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['points'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['weight_prefix'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['weight'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['image'];
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    for($o = 0; $o<14; $o++){
                                                        
                                                        $option_values_row[$key][$o] = '';
                                                        
                                                    }
                                                    
                                                }
                                                
                                                if($option_values_row){
                                                    
                                                    $option_values[] = $option_values_row;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        if($option_values){
                                            
                                            foreach($option_values as $option_values_row){
                                                
                                                foreach($option_values_row as $option_values_row_row){
                                                    
                                                    $write_data_row[$field]['values'][implode($delimeter,$option_values_row_row)] = implode($delimeter,$option_values_row_row);
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='option_value_option_microdata_2'){
                                    
                                    $product_data = $this->getProductOptions(${$id_name},$language_id['value_string']);

                                    $delimeter = '';

                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '|';

                                    }
                                    
                                    $price = $this->getFloat($data_to_db_row['product']['row']['price']);
                                    
                                    $price_whis_delta = '';
                                    
                                    if(isset($additinal_settings['price_whis_delta']) && !$additinal_settings['price_whis_delta']){
                                            
                                        $price_whis_delta = $price;

                                    }
                                    
                                    $option_values = array();
                                    
                                    if(isset($additinal_settings['option_id']) && $product_data){
                                        
                                        $option_id = (int)$additinal_settings['option_id'];
                                        
                                        foreach($product_data as $key => $product_data_row){
                                            
                                            if( ($option_id && $option_id==$product_data_row['option_id']) || !$option_id){
                                                
                                                $option_values_row = array();
                                                
                                                if($product_data_row['product_option_value']){
                                                    
                                                    foreach($product_data_row['product_option_value'] as $product_data_row_product_option_value){
                                                        
                                                        $product_option_value_id = $product_data_row_product_option_value['product_option_value_id'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row['name'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['name'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['quantity'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['price_prefix'];
                                                        
                                                        $option_price = (float)$product_data_row_product_option_value['price'];
                                                        
                                                        if($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='+'){
                                                            
                                                            $option_price += $price_whis_delta; 
                                                            
                                                        }elseif($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='-'){
                                                            
                                                            $option_price = $price_whis_delta - $option_price; 
                                                            
                                                        }
                                                        
                                                        $option_values_row[$product_option_value_id][] = $option_price;
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['image'];
                                                        
                                                        $option_values_row[$product_option_value_id][] = $product_data_row['type'];
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    for($o = 0; $o<14; $o++){
                                                        
                                                        $option_values_row[$key][$o] = '';
                                                        
                                                    }
                                                    
                                                }
                                                
                                                if($option_values_row){
                                                    
                                                    $option_values[] = $option_values_row;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        if($option_values){
                                            
                                            foreach($option_values as $option_values_row){
                                                
                                                foreach($option_values_row as $option_values_row_row){
                                                    
                                                    $write_data_row[$field]['values'][implode($delimeter,$option_values_row_row)] = implode($delimeter,$option_values_row_row);
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='option_value_option_value_name'){
                                    
                                    $product_data = $this->getProductOptions(${$id_name},$language_id['value_string']);

                                    $option_values = array();
                                    
                                    if(isset($additinal_settings['option_id']) && $product_data){
                                        
                                        $option_id = (int)$additinal_settings['option_id'];
                                        
                                        foreach($product_data as $key => $product_data_row){
                                            
                                            if( ($option_id && $option_id==$product_data_row['option_id']) || !$option_id){
                                                
                                                $option_values_row = array();
                                                
                                                if($product_data_row['product_option_value']){
                                                    
                                                    foreach($product_data_row['product_option_value'] as $product_data_row_product_option_value){
                                                        
                                                        $product_option_value_id = $product_data_row_product_option_value['product_option_value_id'];
                                                        
                                                        $option_values_row[$product_option_value_id] = $product_data_row_product_option_value['name'];
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    $option_values_row[$key] = '';
                                                    
                                                }
                                                
                                                if($option_values_row){
                                                    
                                                    $option_values[] = $option_values_row;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        if($option_values){
                                            
                                            foreach($option_values as $option_values_row){
                                                
                                                foreach($option_values_row as $option_values_row_row){
                                                    
                                                    $write_data_row[$field]['values'][$option_values_row_row] = $option_values_row_row;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                }

                                elseif($db_column=='option_value_option_microdata_4'){

                                    $option_id___option_value_id = array();

                                    if(isset($additinal_settings['option_id___option_value_id']) && $additinal_settings['option_id___option_value_id']){

                                        $option_id___option_value_id = explode('___',trim(ltrim($additinal_settings['option_id___option_value_id'])));

                                    }
                                    
                                    $option_id = 0;
                                    
                                    $option_value_id = 0;
                                    
                                    $price = $this->getFloat($data_to_db_row['product']['row']['price']);
                                    
                                    $price_whis_delta = '';
                                    
                                    if(isset($additinal_settings['price_whis_delta']) && !$additinal_settings['price_whis_delta']){
                                            
                                        $price_whis_delta = $price;

                                    }

                                    if(isset($option_id___option_value_id[0]) && $option_id___option_value_id[0] && isset($option_id___option_value_id[1]) && $option_id___option_value_id[1]){

                                        $option_id = (int)$option_id___option_value_id[0];

                                        $option_value_id = (int)$option_id___option_value_id[1];

                                    }

                                    $delimeter = '';

                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '|';

                                    }
                                    
                                    $product_data = $this->getProductOptions(${$id_name},$language_id['value_string']);
                                    
                                    $option_values = array();
                                    
                                    if($product_data){
                                        
                                        foreach($product_data as $key => $product_data_row){
                                            
                                            if( ($option_id && $option_id==$product_data_row['option_id']) || !$option_id){
                                                
                                                $option_values_row = array();
                                                
                                                if($product_data_row['product_option_value']){
                                                    
                                                    foreach($product_data_row['product_option_value'] as $product_data_row_product_option_value){
                                                        
                                                        $product_option_value_id = $product_data_row_product_option_value['product_option_value_id'];
                                                        
                                                        if( ($option_value_id && $option_value_id==$product_data_row_product_option_value['option_value_id']) || !$option_value_id){
                                                            
                                                            $option_price = (float)$product_data_row_product_option_value['price'];
                                                            
                                                            if($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='+'){

                                                                $option_price += $price_whis_delta; 

                                                            }elseif($price_whis_delta && $product_data_row_product_option_value['price_prefix'] && $product_data_row_product_option_value['price_prefix']=='-'){

                                                                $option_price = $price_whis_delta - $option_price; 

                                                            }
                                                            
                                                            $option_values_row[$product_option_value_id][] = $option_price;
                                                            
                                                            $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['quantity'];
                                                            
                                                            $option_values_row[$product_option_value_id][] = $product_data_row_product_option_value['name'];
                                                            
                                                            $option_values_row[$product_option_value_id][] = $product_data_row['name'];
                                                            
                                                        }
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    for($o = 0; $o<4; $o++){
                                                        
                                                        $option_values_row[$key][$o] = '';
                                                        
                                                    }
                                                    
                                                }
                                                
                                                if($option_values_row){
                                                    
                                                    $option_values[] = $option_values_row;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        if($option_values){
                                            
                                            foreach($option_values as $option_values_row){
                                                
                                                foreach($option_values_row as $option_values_row_row){
                                                    
                                                    $write_data_row[$field]['values'][implode($delimeter,$option_values_row_row)] = implode($delimeter,$option_values_row_row);
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }

                                }

                            }
                            
                            if(isset($additinal_settings['column_replace_from']) && $additinal_settings['column_replace_from']!=='' && isset($write_data_row[$field])){
			    
                                $column_replace_from = $additinal_settings['column_replace_from'];

                                $column_replace_to = $additinal_settings['column_replace_to'];
                                
                                if(!is_array($write_data_row[$field])){
                                    
                                    $write_data_row[$field] = $this->getFindReplace($column_replace_from, $column_replace_to, $write_data_row[$field]);
                                    
                                }
                                elseif(isset ($write_data_row[$field]['value']) && $write_data_row[$field]['value']!==''){
                                    
                                    $write_data_row[$field]['value'] = $this->getFindReplace($column_replace_from, $column_replace_to, $write_data_row[$field]['value']);
                                    
                                }
                                elseif(isset ($write_data_row[$field]['values']) && $write_data_row[$field]['values']){
                                    
                                    foreach ($write_data_row[$field]['values'] as $values_key => $values_value) {
                                        
                                        $write_data_row[$field]['values'][$values_key] = $this->getFindReplace($column_replace_from, $column_replace_to, $values_value);
                                        
                                    }
                                    
                                }

                            }
                            
                            if( ($column_request) && $write_data_row[$field]['value']==='' && !$write_data_row[$field]['values']){
                                
                                $skip_by_column_request[$field] = $field;
                                
                            }

                        }
                        
                        $self_column_rename = array();
                            
                        if($self_column){

                            foreach ($self_column as $self_column_field => $self_column_setting) {

                                $result_php = array();
                                
                                $self_column_rename[$self_column_field] = $self_column_setting['import_self_column_name'];

                                if(isset($column_settings[$self_column_field])){

                                    $column_settings[$self_column_field]['column_settings'] = $self_column_setting;

                                    if(isset($self_column_setting['string']) && $self_column_setting['string']!==''){

                                        $write_data_row[ $self_column_field ]['value'] = $self_column_setting['string'];
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($self_column_setting['rule'])){

                                        $text_rule_empty = array('','skip_this_part','','skip_this_part','','skip_this_part','');

                                        foreach ($self_column_setting['rule'] as $rule) {

                                            if($rule['operator'] && $rule['field']!=='' && isset($write_data_row[$rule['field']])){

                                                $rule_field_value = '';
                                                
                                                if($write_data_row[$rule['field']]['value']){
                                                    
                                                    $rule_field_value = $write_data_row[$rule['field']]['value'];
                                                    
                                                }
                                                elseif($write_data_row[$rule['field']]['values'] && count($write_data_row[$rule['field']]['values'])==1){
                                                    
                                                    $rule_field_value = $write_data_row[$rule['field']]['values'][0];
                                                    
                                                }
                                                
                                                $rule_result = $this->checkValueByOperatorAndFieldValue($rule_field_value,$rule['value'],$rule['operator']);

                                                if($rule_result){

                                                    $write_data_row[ $self_column_field ] = $this->getValueByRuleText($write_data_row,$rule['text']);

                                                }

                                            }elseif($text_rule_empty!==$rule['text'] && (!$rule['operator'] || $rule['field']==='')){

                                                $write_data_row[ $self_column_field ] = $this->getValueByRuleText($write_data_row,$rule['text']);
                                                

                                            }

                                        }


                                    }

                                    $x_path_file = '';

                                    if(isset($self_column_setting['xpath_url_as_string']) && $self_column_setting['xpath_url_as_string']!==''){

                                        $x_path_file = $this->getCacheFileByURL($self_column_setting['xpath_url_as_string'],$odmpro_tamplate_data);

                                    }

                                    if(!$x_path_file && isset($self_column_setting['xpath_url_from_column']) && $self_column_setting['xpath_url_from_column']!='skip_this_name' && isset($write_data_row[$self_column_setting['xpath_url_from_column']])){

                                        $x_path_file = $this->getCacheFileByURL($write_data_row[$self_column_setting['xpath_url_from_column']],$odmpro_tamplate_data);

                                    }

                                    if($x_path_file){

                                        $x_path_params['xpath_stags'] = $self_column_setting['xpath_stags'];

                                        $result_xpath = $this->getXPathResult($x_path_file,$self_column_setting['xpath'],$x_path_params);

                                        $write_data_row[ $self_column_field ]['value'] = $result_xpath;
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($self_column_setting['php']) && $self_column_setting['php']!==''){

                                        if(!isset($result_xpath)){

                                            $result_xpath = '';

                                        }
                                        
                                        $write_data_row_php = $write_data_row;
                                        
                                        $write_data_row_php[ $self_column_rename[$self_column_field] ] = $write_data_row_php[ $self_column_field ];
                                        
                                        unset($write_data_row_php[ $self_column_field ]);
                                        
                                        $result_php = $this->getPhpResult($write_data_row_php,$self_column_setting['php'], $this_num_row,$result_xpath,${$id_name});

                                        
                                    }

                                    if(isset($result_php['result'])){

                                        $write_data_row[ $self_column_field ]['value'] = $result_php['result'];
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($result_php['csv_row']) && $result_php['csv_row']){

                                        foreach($result_php['csv_row'] as $php_csv_row_field_this => $php_csv_row_vaule_this){

                                            $write_data_row[$php_csv_row_field_this] = $php_csv_row_vaule_this;

                                        }

                                    }

                                }

                                if(!isset($write_data_row[ $self_column_field ])){

                                    //unset($column_settings[$self_column_field]);
                                    
                                    $skip_by_column_request[$field] = $field;

                                }elseif($write_data_row[ $self_column_field ]==='' && $column_settings[$self_column_field]['additinal_settings']['column_request']==1){

                                    //unset($column_settings[$self_column_field]);
                                    
                                    $skip_by_column_request[$field] = $field;

                                }else{
                                    
                                    $write_data_row[ $self_column_rename[$self_column_field] ] = $write_data_row[ $self_column_field ];

                                    unset($write_data_row[ $self_column_field ]);
                                    
                                }

                            }

                        }
                        
                        
                        
                        if(!$skip_by_column_request){
                            
                            if(isset($odmpro_tamplate_data['encoding']) && $odmpro_tamplate_data['encoding']!='UTF-8'){
                                
                                foreach ($write_data_row as $write_data_row_column_name => $write_data_row_csv_value) {
                                    
                                    $write_data_row_column_name_converted = $this->convertCSVValue($write_data_row_column_name, 'UTF-8', $odmpro_tamplate_data['encoding']);
                                    
                                    $write_data_row_converted = array('value'=>'','values'=>'');
                                    
                                    if(is_array($write_data_row_csv_value['values']) && $write_data_row_csv_value['values']){
                                        
                                        foreach($write_data_row_csv_value['values'] as $write_data_row_csv_value_key => $write_data_row_csv_value_value){
                                            
                                            $write_data_row_converted['values'][ $write_data_row_csv_value_key ] = $this->convertCSVValue($write_data_row_csv_value_value, 'UTF-8', $odmpro_tamplate_data['encoding']);
                                            
                                        }
                                        
                                    }else{
                                        
                                        $write_data_row_converted['value'] = $this->convertCSVValue($write_data_row_csv_value['value'], 'UTF-8', $odmpro_tamplate_data['encoding']);
                                        
                                    }
                                    
                                    if($write_data_row_column_name_converted!==$write_data_row_column_name){
                                        
                                        unset($write_data_row[$write_data_row_column_name]);
                                        
                                        $write_data_row[$write_data_row_column_name_converted] = $write_data_row_converted;
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            $write_data[] = $write_data_row;
                            
                            $this_num_row++;
                            
                            if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){

                                $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});

                            }

                        }else{

                            $log_data['__line__'] = __LINE__;

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_request_empty_value_2'),  implode(', ', $skip_by_column_request), $id_name.'='.${$id_name})),
                                'action'    => $log_data['type_process']
                            );

                            $this->setLogDataRow($log_write_row,$log_data);

                        }
                        
                        $log_data['start'] += 1;
                        
                    }

                }

            }
            elseif($type_data=='order_product' && !$skip){

                $id_name = 'order_product_id';

                $main_table = 'order_product';
                
                $join_main_table = 'product';

                $identificator_field_name = $id_name;

                $identificator_table = $main_table;
                
                $identificator_field_field = '';

                if($identificator){

                    foreach ($identificator as $identificator_param) {

                        if($identificator_param['identificator_type'] == 'name'){

                            $identificator_field_name = $identificator_param['identificator_type'];

                            $identificator_table = 'product_description';

                        }elseif($identificator_param['identificator_type'] == 'aid'){

                            $identificator_field_name = $id_name;

                            $identificator_table = $main_table;

                        }elseif($identificator_param['identificator_type']){

                            $identificator_field_name = $identificator_param['identificator_type'];

                            $identificator_table = $main_table;

                        }
                        
                        $identificator_field_field = $identificator_param['field'];

                    }

                }
                
                
                $data_to_db = $this->getDataToDB($main_table,$odmpro_tamplate_data,$log_data,$language_id['value_string'],$store_id['value_array'],$start,$limit,$general_setting,FALSE,$join_main_table);
                
                $total_data_to_db = $this->getDataToDB($main_table,$odmpro_tamplate_data,$log_data,$language_id['value_string'],$store_id['value_array'],$odmpro_tamplate_data['start'],100000,$general_setting,TRUE,$join_main_table);
                
                $total = $total_data_to_db->row['total'];
                
                if(!$skip && $identificator_field_name && $data_to_db){
                    
                    foreach($data_to_db as ${$id_name} => $data_to_db_row){
                        
                        $write_data_row = array();
            
                        $skip_by_column_request = array();
                        
                        foreach ($column_settings as $field => $setting){
                            
                            $csv_columns[$field] = $field;

                            $write_data_row[$field]['value'] = '';
                            
                            $write_data_row[$field]['values'] = array();
                            
                            $additinal_settings = array();

                            if($setting['additinal_settings']){

                                $additinal_settings = $setting['additinal_settings'];

                            }

                            $data_action = 'export_values';

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);

                            $column_request = 0;

                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']){

                                $column_request = 1;

                            }
                            /*
                             * Сохраняю данные для записи
                             */
                            /*
                             * Если это идентификатор, он может быть названием - в остальных случаях, пройдет мимо и присвоится в следующем if
                             */
                            if($identificator_field_field && $identificator_field_field == $field){
                                
                                if($identificator_field_name=='name' && isset($data_to_db_row[$main_table.'_description']['rows'])){
                                    
                                    $data_description = end($data_to_db_row[$main_table.'_description']['rows']);
                                
                                    if(isset ($data_description['name'])){

                                        $write_data_row[$field]['value'] = $data_description['name'];

                                    }
                                    
                                }else{
                                    
                                    if(isset($data_to_db_row[$main_table]['row']) && isset($data_to_db_row[$main_table]['row'][$identificator_field_name])){
                                        
                                        $write_data_row[$field]['value'] = $data_to_db_row[$main_table]['row'][$identificator_field_name];
                                        
                                    }
                                    
                                }
                                
                                
                            }
                            elseif(isset($data_to_db_row[$db_table]['rows']) && isset($data_to_db_row[$db_table]['columns'][$db_column])){

                                foreach($data_to_db_row[$db_table]['rows'] as $data_to_db_row_row){
                                    
                                    if(isset($data_to_db_row_row[$db_column])){
                                        
                                        $write_data_row[$field]['values'][] = $data_to_db_row_row[$db_column];
                                        
                                    }
                                    
                                }

                            }
                            elseif(isset($data_to_db_row[$db_table]['row']) && isset($data_to_db_row[$db_table]['columns'][$db_column])){
                                
                                if(isset($data_to_db_row[$db_table]['row'][$db_column])){
                                    
                                    $write_data_row[$field]['value'] = $data_to_db_row[$db_table]['row'][$db_column];
                                    
                                }
                                
                            }
                            
                            
                            /*
                             * Расширенные колонки
                             */

                            else{
                                
                                if($db_column=='order_option_microdata_1'){
                                    
                                    $order_option_result = array();
                                    
                                    if(isset($data_to_db_row['order_option']) && isset($data_to_db_row['order_product'])){
                                        
                                        foreach ($data_to_db_row['order_option']['rows'] as $order_option) {

                                            if($order_option['order_product_id'] == $data_to_db_row['order_product']['row']['order_product_id']){

                                                $order_option_result[] = $order_option['name'].':'.$order_option['value'];

                                            }

                                        }
                                        
                                    }
                                    
                                    $write_data_row[$field]['values'] = array();

                                    $write_data_row[$field]['value'] = implode('|',$order_option_result);

                                }
                                
                            }
                            
                            
                            
                            if( ($column_request) && $write_data_row[$field]['value']==='' && !$write_data_row[$field]['values']){
                                
                                $skip_by_column_request[$field] = $field;
                                
                            }

                        }
                        
                        $self_column_rename = array();
                            
                        if($self_column){

                            foreach ($self_column as $self_column_field => $self_column_setting) {

                                $result_php = array();
                                
                                $self_column_rename[$self_column_field] = $self_column_setting['import_self_column_name'];

                                if(isset($column_settings[$self_column_field])){

                                    $column_settings[$self_column_field]['column_settings'] = $self_column_setting;

                                    if(isset($self_column_setting['string']) && $self_column_setting['string']!==''){

                                        $write_data_row[ $self_column_field ]['value'] = $self_column_setting['string'];
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($self_column_setting['rule'])){

                                        $text_rule_empty = array('','skip_this_part','','skip_this_part','','skip_this_part','');

                                        foreach ($self_column_setting['rule'] as $rule) {

                                            if($rule['operator'] && $rule['field']!=='' && isset($write_data_row[$rule['field']])){

                                                $rule_field_value = '';
                                                
                                                if($write_data_row[$rule['field']]['value']){
                                                    
                                                    $rule_field_value = $write_data_row[$rule['field']]['value'];
                                                    
                                                }
                                                elseif($write_data_row[$rule['field']]['values'] && count($write_data_row[$rule['field']]['values'])==1){
                                                    
                                                    $rule_field_value = $write_data_row[$rule['field']]['values'][0];
                                                    
                                                }
                                                
                                                $rule_result = $this->checkValueByOperatorAndFieldValue($rule_field_value,$rule['value'],$rule['operator']);

                                                if($rule_result){

                                                    $write_data_row[ $self_column_field ] = $this->getValueByRuleText($write_data_row,$rule['text']);

                                                }

                                            }elseif($text_rule_empty!==$rule['text'] && (!$rule['operator'] || $rule['field']==='')){

                                                $write_data_row[ $self_column_field ] = $this->getValueByRuleText($write_data_row,$rule['text']);
                                                

                                            }

                                        }


                                    }

                                    $x_path_file = '';

                                    if(isset($self_column_setting['xpath_url_as_string']) && $self_column_setting['xpath_url_as_string']!==''){

                                        $x_path_file = $this->getCacheFileByURL($self_column_setting['xpath_url_as_string'],$odmpro_tamplate_data);

                                    }

                                    if(!$x_path_file && isset($self_column_setting['xpath_url_from_column']) && $self_column_setting['xpath_url_from_column']!='skip_this_name' && isset($write_data_row[$self_column_setting['xpath_url_from_column']])){

                                        $x_path_file = $this->getCacheFileByURL($write_data_row[$self_column_setting['xpath_url_from_column']],$odmpro_tamplate_data);

                                    }

                                    if($x_path_file){

                                        $x_path_params['xpath_stags'] = $self_column_setting['xpath_stags'];

                                        $result_xpath = $this->getXPathResult($x_path_file,$self_column_setting['xpath'],$x_path_params);

                                        $write_data_row[ $self_column_field ]['value'] = $result_xpath;
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($self_column_setting['php']) && $self_column_setting['php']!==''){

                                        if(!isset($result_xpath)){

                                            $result_xpath = '';

                                        }
                                        
                                        $write_data_row_php = $write_data_row;
                                        
                                        $write_data_row_php[ $self_column_rename[$self_column_field] ] = $write_data_row_php[ $self_column_field ];
                                        
                                        unset($write_data_row_php[ $self_column_field ]);
                                        
                                        $result_php = $this->getPhpResult($write_data_row_php,$self_column_setting['php'], $this_num_row,$result_xpath,${$id_name});

                                    }

                                    if(isset($result_php['result'])){

                                        $write_data_row[ $self_column_field ]['value'] = $result_php['result'];
                                        
                                        $write_data_row[ $self_column_field ]['values'] = array();

                                    }

                                    if(isset($result_php['csv_row']) && $result_php['csv_row']){

                                        foreach($result_php['csv_row'] as $php_csv_row_field_this => $php_csv_row_vaule_this){

                                            $write_data_row[$php_csv_row_field_this] = $php_csv_row_vaule_this;

                                        }

                                    }

                                }

                                if(!isset($write_data_row[ $self_column_field ])){

                                    //unset($column_settings[$self_column_field]);
                                    
                                    $skip_by_column_request[$field] = $field;

                                }elseif($write_data_row[ $self_column_field ]==='' && $column_settings[$self_column_field]['additinal_settings']['column_request']==1){

                                    //unset($column_settings[$self_column_field]);
                                    
                                    $skip_by_column_request[$field] = $field;

                                }else{
                                    
                                    $write_data_row[ $self_column_rename[$self_column_field] ] = $write_data_row[ $self_column_field ];

                                    unset($write_data_row[ $self_column_field ]);
                                    
                                }

                            }

                        }
                        
                        if(!$skip_by_column_request){
                            
                            if(isset($odmpro_tamplate_data['encoding']) && $odmpro_tamplate_data['encoding']!='UTF-8'){
                                
                                foreach ($write_data_row as $write_data_row_column_name => $write_data_row_csv_value) {
                                    
                                    $write_data_row_column_name_converted = $this->convertCSVValue($write_data_row_column_name, 'UTF-8', $odmpro_tamplate_data['encoding']);
                                    
                                    $write_data_row_converted = array('value'=>'','values'=>array());
                                    
                                    if(is_array($write_data_row_csv_value['values']) && $write_data_row_csv_value['values']){
                                        
                                        foreach($write_data_row_csv_value['values'] as $write_data_row_csv_value_key => $write_data_row_csv_value_value){
                                            
                                            $write_data_row_converted['values'][ $write_data_row_csv_value_key ] = $this->convertCSVValue($write_data_row_csv_value_value, 'UTF-8', $odmpro_tamplate_data['encoding']);
                                            
                                        }
                                        
                                    }else{
                                        
                                        $write_data_row_converted['value'] = $this->convertCSVValue($write_data_row_csv_value['value'], 'UTF-8', $odmpro_tamplate_data['encoding']);
                                        
                                    }
                                    
                                    if($write_data_row_column_name_converted!==$write_data_row_column_name){
                                        
                                        unset($write_data_row[$write_data_row_column_name]);
                                        
                                        $write_data_row[$write_data_row_column_name_converted] = $write_data_row_converted;
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            $write_data[] = $write_data_row;
                            
                            $this_num_row++;
                            
                            if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){

                                $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});

                            }

                        }else{

                            $log_data['__line__'] = __LINE__;

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_request_empty_value_2'),  implode(', ', $skip_by_column_request), $id_name.'='.${$id_name})),
                                'action'    => $log_data['type_process']
                            );

                            $this->setLogDataRow($log_write_row,$log_data);

                        }
                        
                        $log_data['start'] += 1;
                        
                    }

                }

            }
            
            if(isset($general_setting['related_data_column']) && $general_setting['related_data_column']){
                
                $related_data_column = $general_setting['related_data_column'];
                
            }
            
            if(isset($general_setting['no_csv_headers']) && $general_setting['no_csv_headers']){
                
                $no_csv_headers = $general_setting['no_csv_headers'];
                
            }
            
        }
        
        $addit_delim = '_____';
        
        if(isset($general_setting['addit_delim']) && $general_setting['addit_delim']!==''){
                
            $addit_delim = $general_setting['addit_delim'];
                
        }
        
        $first_row = 0;
                
        $first_write = 0;
        
        $csv_rows = array();
        
        $csv_rows_new = array();

        if($this->request->get['first_row']){

            $first_row = 1;

            $first_write = 1;

        }
        
        $csv_columns_new = array();
        
        if($write_data){
            
            if(!$related_data_column || $related_data_column==1 || $related_data_column==2){
                
                foreach($write_data as $k => $write_data_row){
                    
                    $csv_row = array();
                    
                    $csv_row_new = array();
                    
                    $num_csv_columns_new = array();
                    
                    foreach ($write_data_row as $column => $write_data_column_value) {
                        
                        if(!isset($num_csv_columns_new[$column])){
                            
                            $num_csv_columns_new[$column] = 0;
                            
                        }
                     
                        if(!is_array($write_data_column_value) && $write_data_column_value !==''){

                            $csv_row[] = $write_data_column_value;
                            
                            $csv_row_new[$column] = $write_data_column_value;
                            
                            $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;

                        }elseif($write_data_column_value['value']!==''){

                            $csv_row[] = $write_data_column_value['value'];
                            
                            $csv_row_new[$column] = $write_data_column_value['value'];
                            
                            $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;

                        }elseif($write_data_column_value['values'] && count($write_data_column_value['values'])==1){
                            
                            $csv_row[] = end($write_data_column_value['values']);
                            
                            $csv_row_new[$column] = end($write_data_column_value['values']);
                            
                            $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;

                        }elseif($write_data_column_value['values']){

                            $csv_row_values = array();
                            
                            $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;

                            foreach($write_data_column_value['values'] as $write_data_row_value_index => $write_data_row_value){

                                $csv_row_values[] = $write_data_row_value;
                                
                                if($related_data_column==2){
                                    
                                    $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;
                                    
                                    $num_csv_columns_new[$column]++;
                                    
                                }

                            }
                            
                            if($related_data_column==1){
                                
                                foreach ($csv_row_values as $csv_row_value_key_this => $csv_row_value_value_this) {
                                    
                                    if(trim($csv_row_value_value_this)===''){
                                        
                                        unset($csv_row_values[$csv_row_value_key_this]);
                                        
                                    }
                                    
                                }
                                
                                if(!isset($csv_row_values)){
                                    
                                    $csv_row_values = array();
                                    
                                }
                                
                                $csv_row[] = implode($addit_delim, $csv_row_values);
                                
                                $csv_row_new[$column] = implode($addit_delim, $csv_row_values);
                                
                                $csv_columns_new[$column][0] = $column;
                                
                            }elseif($related_data_column==2){
                                
                                $csv_row[] = $csv_row_values;
                                
                                $csv_row_new[$column] = $csv_row_values;
                                
                            }else{
                                
                                $csv_row[] = $csv_row_values;
                                
                                $csv_columns_new[$column][0] = $column;
                                
                            }


                        }else{

                            $csv_row[] = '';
                            
                            $csv_row_new[$column] = '';
                            
                            $csv_columns_new[$column][$num_csv_columns_new[$column]] = $column;

                        }
                        
                    }
                    
                    /*
                     * Первая строка занята под возможные заголовки
                     */
                    
                    //$csv_rows[ $k+1 ] = $csv_row;
                    
                    $csv_rows_new[ $k+1 ] = $csv_row_new;
                    
                    unset($write_data[$k]);
                    
                }
                
            }
            
        }else{

            $log_data['__line__'] = __LINE__;

            $log_write_row = array(
                'log_data' => $log_data,
                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_empty_write'),  $log_data['start'], $limit)),
                'action'    => $log_data['type_process']
            );

            $this->setLogDataRow($log_write_row,$log_data);

        }
        
        $csv_rows_result = array();
        
        if($csv_rows_new){
            
            foreach($csv_rows_new as $num_row => $csv_row_values){
                
                foreach($csv_row_values as $num_value => $csv_row_value){
                    
                    if(is_array($csv_row_value)){
                        
                        foreach($csv_row_value as $num_values => $csv_row_value_value){
                            
                            $csv_rows_result[$num_row][$num_value][$num_values] = $csv_row_value_value;
                            
                        }
                        
                        if(count($csv_rows_result[$num_row][$num_value]) < count($csv_columns_new[$num_value])){
                            
                            for(($num_values+1);$num_values< count($csv_columns_new[$num_value]) ;$num_values++){
                                
                                $csv_rows_result[$num_row][$num_value][$num_values] = '';
                                
                            }
                            
                        }
                        
                    }else{
                        
                        if(count($csv_columns_new[$num_value])>1){
                            for($c=0;$c<count($csv_columns_new[$num_value]);$c++){
                                $csv_rows_result[$num_row][$num_value][$c] = '';
                            }
                        }else{
                            $csv_rows_result[$num_row][$num_value] = $csv_row_value;
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        /*
        
        ksort($csv_rows_result);
        
        foreach($csv_rows_result as $num_row => $csv_row_value){
            
            if(stristr($num_row, '___')){
                
                $index_parrent = explode('___',$num_row);
                
                $index_parrent = (int)$index_parrent[0];
                
                foreach($csv_rows_result[$index_parrent] as $num_index_value => $value_index_value){
                    
                    if(!isset($csv_rows_result[$num_row][$num_index_value])){
                        
                        $csv_rows_result[$num_row][$num_index_value] = $value_index_value;
                        
                    }
                    
                }
                
                ksort($csv_rows_result[$num_row]);
                
            }
            
        }
        
        */
        
        if($first_row && !$no_csv_headers){
            
            $csv_columns_new_tmp = $csv_columns_new;
            
            $csv_columns_new = array();
            
            foreach($csv_columns_new_tmp as $csv_columns_new_tmp_column => $csv_columns_new_tmp_sub_column){
                
                if(count($csv_columns_new_tmp_sub_column)==1){
                    
                    $csv_columns_new[$csv_columns_new_tmp_column] = $csv_columns_new_tmp_column;
                    
                }else{
                    
                    for($c=0;$c<count($csv_columns_new_tmp_sub_column);$c++){
                        
                         $csv_columns_new[$csv_columns_new_tmp_column][$c] = $csv_columns_new_tmp_column;
                        
                    }
                    
                }
                
            }
            
            $csv_rows_result[0] = $csv_columns_new;
            
        }
        
        ksort($csv_rows_result);
        
        $csv_delimiter = $odmpro_tamplate_data['csv_delimiter'];
            
        $csv_enclosure = $odmpro_tamplate_data['csv_enclosure'];

        $csv_escape = $odmpro_tamplate_data['csv_escape'];

        $encoding = $odmpro_tamplate_data['encoding'];

        $file_name_and_path = $odmpro_tamplate_data['export_file_name'];
        
        $send_on_email_file_name = $file_name_and_path.'.csv';
        
        $send_on_email_url_on_file = HTTPS_CATALOG.'image/'.$send_on_email_file_name;
        
        $result['count_rows'] = $total;
        
        if(isset($this->setting_version_settings['functional']['export_to_xls']) && $this->setting_version_settings['functional']['export_to_xls'] && isset($odmpro_tamplate_data['export_format_selected']) && $odmpro_tamplate_data['export_format_selected']=='xls'){

            $result_xls = $this->setting_version->writeXls($csv_rows_result,$first_write,$odmpro_tamplate_data,$start,$limit);
            
            $send_on_email_file_name = $odmpro_tamplate_data['export_file_name_xls'];
        
            if($odmpro_tamplate_data['file_name_write_time_xls']){

                $send_on_email_file_name.= date("Y-m-d_H:i:s");

            }

            $send_on_email_file_name .= '.xlsx';
            
            $send_on_email_url_on_file = HTTPS_CATALOG.'image/'.$send_on_email_file_name;
            
        }else{
            
            $this->writeCsv($csv_rows_result,$first_write,$csv_delimiter,$csv_enclosure,$csv_escape,$encoding,$file_name_and_path,$log_data);
            
        }
        
        $this->writeLogDataRows();
        
        if(isset($odmpro_tamplate_data['send_on_email']) && $odmpro_tamplate_data['send_on_email']!==''){
            
            $send_on_email_file_name = DIR_IMAGE.$send_on_email_file_name;
            
            $this->sendFileOnEmail($send_on_email_file_name, $send_on_email_url_on_file, $odmpro_tamplate_data);
            
        }
        
        //send_on_email
        
        return $result;
        
    }
    
    public function sendFileOnEmail($send_on_email_file_name,$send_on_email_url_on_file,$odmpro_tamplate_data) {
        
        require_once DIR_SYSTEM.'library/mail.php';
        
        $emails = explode(',',$odmpro_tamplate_data['send_on_email']);
            
        $mail = new Mail();

        $mail->setFrom('anycsvxlsyml@'.str_replace(array('https://','http://','/'), array(''), $_SERVER['HTTP_HOST']));

        $mail->setSender('anycsvxlsyml-notice');
        
        $text = 'Файл экспорта успешно записан';
        
        $as_link = '';
        
        if(file_exists($send_on_email_file_name) && is_file($send_on_email_file_name)){
            
            if(!isset($odmpro_tamplate_data['send_on_email_as_link']) || $odmpro_tamplate_data['send_on_email_as_link']=='' || filesize($send_on_email_file_name) < ($odmpro_tamplate_data['send_on_email_as_link']*1024*1024)  ){
                
                $mail->addAttachment($send_on_email_file_name);
                
            }
            else{
                
                $as_link = PHP_EOL.'Ссылка на файл: '.$send_on_email_url_on_file;
                
            }
            
        }
        else{
            
            $text = 'Не удалось записать файл экспорта';
            
        }

        foreach ($emails as $email) {

            if(filter_var(trim($email), FILTER_VALIDATE_EMAIL)){

                $mail->setTo($email);
                
                $mail->setSubject('anycsvxlsyml-notice: '.$text);

                $mail->setText($text.$as_link);

                $mail->send();

                sleep(1);

            }

        }
        
    }
    
    public function getProducts($language_id) {
        
            $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$language_id . "'";

            $sql .= " GROUP BY p.product_id";
            
            $sql .= " ORDER BY p.product_id";

            $sql .= " ASC";

            $query = $this->db->query($sql);

            return $query->rows;
    }
    
    public function insertProductOptions($product_options, $product_id){
        
        foreach ($product_options as $option_id => $option_values) {
            
            $product_option_id = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$option_id . "'");
            
            if($product_option_id->row){

                $product_option_id = $product_option_id->row['product_option_id'];

            }else{

                $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$option_id . "', required = '0'");

                $product_option_id = $this->db->getLastId();

            }
            
            foreach ($option_values as $option_value_id => $option_value) {
                
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "' AND option_id = '" . (int)$option_id . "' AND option_value_id = '" . (int)$option_value_id . "' AND product_option_id = ".$product_option_id);
                
                $sql = array();
                
                foreach ($option_value as $column => $value) {
                    
                    if($column=='option_value_option_price_whis_delta'){
                        
                        $price = (float)$value;
                        
                        if($price>=0){
                            
                            $sql[] = " price = ".$price." ";
                            
                            $sql[] = " price_prefix = '+' ";
                            
                        }else{
                            
                            $sql[] = " price = ".  abs($price)." ";
                            
                            $sql[] = " price_prefix = '-' ";
                            
                        }
                        
                        
                    }elseif($column=='option_value_option_price_without_delta'){
                        
                        $product = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "' ");
                        
                        $price_option = (float)$value;
                        
                        $price = $product->row['price'] + $price_option;
                        
                        if($price>=0){
                            
                            $sql[] = " price = ".$price." ";
                            
                            $sql[] = " price_prefix = '+' ";
                            
                        }else{
                            
                            $sql[] = " price = ".  abs($price)." ";
                            
                            $sql[] = " price_prefix = '-' ";
                            
                        }
                        
                    }elseif($column=='option_value_option_value_name'){
                        
                        $sql[] = " name = '".$this->db->escape($value)."' ";
                        
                    }else{
                        
                        $sql[] = $column." = '".$this->db->escape($value)."' ";
                        
                    }
                    
                }
                
                if($sql){
                    
                    $sql = "INSERT INTO " . DB_PREFIX . "product_option_value SET ".implode(', ', $sql).", product_id = '" . (int)$product_id . "', option_value_id = '" . (int)$option_value_id . "', option_id = '" . (int)$option_id . "', product_option_id = ".$product_option_id;
                    
                    $this->db->query($sql);
                    
                }
                
            }
            
        }
        
    }
    
    public function insertProductImages($images,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_image` WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($images as $product_image) {
            
            //echo "INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', " . $product_image . ", sort_order = '0' ----<br>";
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_image` SET product_id = '" . (int)$product_id . "', " . $product_image . ", sort_order = '0'");
        }
        
    }
    
    public function insertProductCategories($categories,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_to_category` WHERE product_id = '" . (int)$product_id . "'");
        
        $columns = $this->getColumnsByTable('product_to_category');
        
        $main_category_id = '';
        
        foreach ($categories as $category_id) {
            
            if(isset($columns['main_category'])){
            
                $main_category_id = ", main_category = " .   $category_id;

            }
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_to_category` SET " . $category_id . ", product_id = '" . $product_id . "' ".$main_category_id);
            
        }
        
    }
    
    public function insertFilter($filter_ids, $product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_filter` WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($filter_ids as $filter_id) {
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_filter` SET product_id = '" . $product_id . "', ".  $filter_id);
            
        }
        
    }
    
    public function insertProductAttributeValues($atribute_values,$product_id) {
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_attribute` WHERE product_id = '" . (int)$product_id . "'");
        
        foreach ($atribute_values as $atribute_value) {
            
            foreach ($atribute_value as $atribute_value_sql) {
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_attribute` SET product_id = '" . $product_id . "', ".  $atribute_value_sql);
                
            }
            
        }
        
    }
    
    public function getManufacturerIdByName($manufacturer_name,$language_id,$store_id_sql) {
        
        $manufacturer_id = 0;
        
        $query = $this->db->query(" SELECT * FROM `" . DB_PREFIX . "manufacturer` WHERE name = '".$this->db->escape($manufacturer_name)."' ");
        
        if($query->row){
            
            $manufacturer_id = $query->row['manufacturer_id'];
            
        }else{
            
            $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer` SET image = '', sort_order = 0, name = '".$this->db->escape($manufacturer_name)."' ");
            
            $manufacturer_id = $this->db->getLastId();
            
            if($this->showTable('manufacturer_description', DB_PREFIX)){
             
                $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET manufacturer_id = ".$manufacturer_id.", language_id = ".(int)$language_id." ");
                
            }
            
            if($this->showTable('manufacturer_to_store', DB_PREFIX)){
             
                foreach ($store_id_sql as $store_id) {
            
                    $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET manufacturer_id = ".$manufacturer_id.", ".$store_id);
                
                }
                
            }
            
        }
        
        return $manufacturer_id;
        
    }
    
    public function getManufacturerNamrByManufacturerId($manufacturer_id,$language_id) {
        
        $manufacturer_name = '';
        
        $query = $this->db->query(" SELECT * FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '".(int)$manufacturer_id."' ");
        
        if($query->row){
            
            $manufacturer_name = $query->row['name'];
            
        }
        
        return $manufacturer_name;
        
    }
    
    public function getProductFilters($product_id,$sql_parts_an_param) {
        
        
        
        $product_filter_group_data = array();

        $product_filter_query = $this->db->query("SELECT a.filter_id, ad.name FROM " . DB_PREFIX . "product_filter pa LEFT JOIN " . DB_PREFIX . "filter a ON (pa.filter_id = a.filter_id) LEFT JOIN " . DB_PREFIX . "filter_description ad ON (a.filter_id = ad.filter_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.filter_group_id = '" . (int)$sql_parts_an_param['filter_group_id'] . "' AND ad.language_id = '" . (int)$sql_parts_an_param['language_id'] . "' ORDER BY a.sort_order, ad.name");

        

        foreach ($product_filter_query->rows as $product_filter_query_row) {
                $product_filter_group_data[$product_filter_query_row['filter_id']] = array(
                        'name'               => $product_filter_query_row['name']
                );
        }
        
        return $product_filter_group_data;
		
    }
    
    public function getProductAttributes($product_id,$attribute_id,$language_id) {
        
        
        
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$language_id . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

        

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
                $product_attribute_data = array();

                $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$language_id . "' AND pa.language_id = '" . (int)$language_id . "' ORDER BY a.sort_order, ad.name");

                foreach ($product_attribute_query->rows as $product_attribute) {
                        $product_attribute_data[] = array(
                                'attribute_id' => $product_attribute['attribute_id'],
                                'name'         => $product_attribute['name'],
                                'text'         => $product_attribute['text']
                        );
                }

                $product_attribute_group_data[] = array(
                        'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                        'name'               => $product_attribute_group['name'],
                        'attribute'          => $product_attribute_data
                );
        }

        if($attribute_id){
            foreach ($product_attribute_group_data as $key => $value) {
                foreach ($value['attribute'] as $key2 => $value2) {
                    
                    if($value2['attribute_id']!=$attribute_id){
                        unset($product_attribute_group_data[$key]['attribute'][$key2]);
                    }
                }
            }
            foreach ($product_attribute_group_data as $key => $value) {
                if(!$value['attribute']){
                    unset($product_attribute_group_data[$key]);
                }
            }
        }
        return $product_attribute_group_data;
		
    }
    
    public function getImageByLink($site_from_image) {
        
        $image_parts = explode('/', $site_from_image);
        
        if($image_parts && is_array($image_parts)){
            
            $check_url = array('http:'=>0,'https:'=>0);
            
            foreach ($image_parts as $key => $image_parts_check_http) {
                if(isset($check_url[$image_parts_check_http])){
                    unset($check_url[$image_parts_check_http]);
                }
                
            }
            
            if(count($check_url)>1){
                return '';
            }
            
        }
        
        $image = trim(end($image_parts));
        
        $new_file = DIR_IMAGE . $image;
        
        if(is_file( $new_file ) || !$image){
            if(!$image){
                $image = '';
            }
            return $image;
        }
        
        
        
        $b = get_headers($site_from_image);
        $imt = array('Content-Type: image/png'=>'.png',
                'Content-Type: image/jpeg'=>'.jpg',
                'Content-Type: image/jpeg'=>'.jpeg',
                'Content-Type: image/gif'=>'.gif',
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
                if(!file_exists(dirname($new_file))){
                    mkdir(dirname($new_file));
                }
                if($a){
                    file_put_contents($new_file, $a);
                    return $image;
                }
                
            }
            
        }
        return '';
    }
    
    public function getColumnIntoAbstractField($column,$type_data){
    
        $check_field = str_replace($type_data.'_', '', $column);
        
        if($check_field && $check_field=='name'){
            $column = $check_field;
        }
        
        return $column;
        
    }

    public function getIdByIdentificatorForCategory($by_name,$path_for_identification,$name_for_identification,$category_id_for_identification,$delimiter,$type_data,$table,$language_id=array(),$store_id=array()) {
        
        $result = array();
        
        $sql = '';
        
        $result['parent_id'] = 0;
        
        $result['category_id'] = 0;
        
        if($this->showTable($table, DB_PREFIX)){
            
            
            // ищем категорию с таким идентификатором и возвращаем рузальтат
            if(!$by_name){
                
                $sql = " SELECT * FROM `" . DB_PREFIX . "category` WHERE category_id = '" . (int)$category_id_for_identification . "' ";
                
                $category = $this->db->query($sql);
                
                if($category->row){
                    
                    $result['parent_id'] = $category->row['parent_id'];
                    
                    $result['category_id'] = $category->row['category_id'];
                    
                }
                
            }else{
                
                // если путь
                
                if($path_for_identification){
                    
                    //ищем категорию по пути, если не находим, то создаем путь, если нужно и выход. Категория будет создана на втором этапе
                    $path = explode($delimiter, $path_for_identification);
                    
                    if($path && is_array($path)){
                        
                        foreach ($path as $key => $category_name) {
                            
                            $category_name = trim($category_name);
                            
                            if($category_name){
                                
                                $path[$key] = $category_name;
                                
                            }else{
                                
                                unset($path[$key]);
                                
                            }
                            
                        }
                        
                        //если путь, то ищем согласно пути и создаем родителей, если не находим их
                        if(count($path)>1){
                            
                            $last_path = end($path);
                            
                            foreach ($path as $category_name) {
                                
                                //первый элемент - должен быть топовый
                                if(!isset($parent_id)){
                                    
                                    $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id[$table] . "' AND c.parent_id = 0 ";
                                    
                                    $parent_category = $this->db->query($sql_category_path);
                                    
                                    //если есть, оставляем родительский id
                                    if($parent_category->row){
                                        
                                        $parent_id = $parent_category->row['category_id'];
                                        
                                        $result['parent_id'] = $parent_id;
                    
                                        $result['category_id'] = 0;
                                        
                                    }
                                    
                                    // в противном случае, вставляем родителя и сохраняем его id
                                    else{
                                        
                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '0', `top` = '1', `column` = '1',`status` = 1, sort_order = '0', date_modified = NOW(), date_added = NOW()");
                                        
                                        $parent_id = $this->db->getLastId();
                                        
                                        $result['parent_id'] = $parent_id;
                    
                                        $result['category_id'] = 0;
                                        
                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id[$table] . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");
                                        
                                        foreach ($store_id as $store_id_value) {
                                            
                                            $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "', " . $store_id_value . " ");
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id[$table] . "' AND c.parent_id = '".$parent_id."' ";
                                    
                                    $parent_category = $this->db->query($sql_category_path);
                                    
                                    //если есть, оставляем родительский id
                                    if($parent_category->row && $category_name!=$last_path){
                                        
                                        $parent_id = $parent_category->row['category_id'];
                                        
                                        $result['parent_id'] = $parent_id;
                    
                                        $result['category_id'] = 0;
                                        
                                    }
                                    
                                    //если последний элемент есть, его заводить не нужно
                                    elseif($parent_category->row && $category_name==$last_path){
                                        
                                        $result['parent_id'] = $parent_id;
                    
                                        $result['category_id'] = $parent_category->row['category_id'];
                                        
                                    }
                                    
                                    // в противном случае, вставляем родителя и сохраняем его id
                                    else{
                                        
                                        
                                        if($category_name!=$last_path){
                                            
                                            $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '".$parent_id."', `top` = '1', `column` = '1',`status` = 1, sort_order = '0', date_modified = NOW(), date_added = NOW()");
                                        
                                            //новый родитель
                                            $parent_id = $this->db->getLastId();

                                            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id[$table] . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                            foreach ($store_id as $store_id_value) {

                                                $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "',  " . $store_id_value . " ");

                                            }

                                            $result['parent_id'] = $parent_id;

                                            $result['category_id'] = 0;
                                            
                                        }
                                        //у последнего элемента пути вставку не делаем, это будет сделано на втором эатпе
                                        else{
                                            
                                            $result['parent_id'] = $parent_id;

                                            $result['category_id'] = 0;
                                            
                                        }
                                        
                                    }
                                    
                                }

                            }
                        }
                        
                        //если путь и один элемент пути, то топовая категория, если находим возвращаем id, если нет, то выход. Будет создана на втором этапе
                        else{
                            
                            $sql = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape(end($path))."' AND cd.language_id = '" . (int)$language_id[$table] . "' ";
                            
                            $category = $this->db->query($sql);

                            if($category->row){

                                $result['parent_id'] = $category->row['parent_id'];

                                $result['category_id'] = $category->row['category_id'];

                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        $this->repair_categories = TRUE;
        
        return $result;
        
    }
    
    public function getIdByIdentificatorForCategoryImportProduct($by_name,$path_for_identification,$name_for_identification,$category_id_for_identification,$delimiter,$type_data,$table,$language_id=array(),$store_id=array(),$save_last_element_path=FALSE) {
        
        $result = array();
        
        $sql = '';
        
        $result['parent_id'] = 0;
        
        $result['category_id'] = 0;
        
        if($this->showTable($table, DB_PREFIX)){
            
            
            // ищем категорию с таким идентификатором и возвращаем рузальтат
            if(!$by_name){
                
                $sql = " SELECT * FROM `" . DB_PREFIX . "category` WHERE category_id = '" . (int)$category_id_for_identification . "' ";
                
                $category = $this->db->query($sql);
                
                if($category->row){
                    
                    $result['parent_id'] = $category->row['parent_id'];
                    
                    $result['category_id'] = $category->row['category_id'];
                    
                }
                
            }else{
                
                // если путь
                
                if($path_for_identification){
                    
                    //ищем категорию по пути, если не находим, то создаем путь, если нужно и выход. Категория будет создана на втором этапе
                    $path = explode($delimiter, $path_for_identification);
                    
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
                                    
                                    $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id[$table.'_description'] . "' AND c.parent_id = 0 ";
                                    
                                    $parent_category = $this->db->query($sql_category_path);
                                    
                                    //если есть, оставляем родительский id
                                    if($parent_category->row){
                                        
                                        $parent_id = $parent_category->row['category_id'];
                                        
                                        $result['parent_id'] = $parent_category->row['parent_id'];
                    
                                        $result['category_id'] = $parent_category->row['category_id'];
                                        
                                    }
                                    
                                    // в противном случае, вставляем родителя и сохраняем его id
                                    else{
                                        
                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '0', `top` = '1', `column` = '1',`status` = '1', sort_order = '0', date_modified = NOW(), date_added = NOW()");
                                        
                                        //если не последний елемент в path эта категория будет родителем
                                        $parent_id = $this->db->getLastId();
                                        
                                        //если последний елемент вернет значение по этой категории
                                        $result['parent_id'] = 0;
                    
                                        $result['category_id'] = $parent_id;
                                        
                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id[$table.'_description'] . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");
                                        
                                        foreach ($store_id as $store_id_value) {
                                            
                                            $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "', " . $store_id_value . " ");
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id[$table.'_description'] . "' AND c.parent_id = '".$parent_id."' ";
                                    
                                    $parent_category = $this->db->query($sql_category_path);
                                    
                                    //если есть, оставляем родительский id
                                    if($parent_category->row){
                                        
                                        $parent_id = $parent_category->row['category_id'];
                                        
                                        $result['parent_id'] = $parent_category->row['parent_id'];
                    
                                        $result['category_id'] = $parent_category->row['category_id'];
                                        
                                    }
                                    
                                    // в противном случае, вставляем родителя и сохраняем его id
                                    else{
                                        
                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET parent_id = '".$parent_id."', `top` = '1', `column` = '1', sort_order = '0', status = '1', date_modified = NOW(), date_added = NOW()");
                                        
                                        $result['parent_id'] = $parent_id;
                                        
                                        //новый родитель
                                        $parent_id = $this->db->getLastId();
                    
                                        $result['category_id'] = $parent_id;

                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id[$table.'_description'] . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                        foreach ($store_id as $store_id_value) {

                                            $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "',  " . $store_id_value . " ");

                                        }
                                        
                                    }
                                    
                                }

                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        $this->repair_categories = TRUE;
        
        return $result;
        
    }
    
    public function getIdByIdentificatorAndGroupIdentificator($by_name,$identificator_value,$type_data,$group_identificator_value, $table, $language_id=array()) {
        
        $result = FALSE;
        
        if($this->showTable($table, DB_PREFIX)){
            
            if(!$by_name){
                
                $sql = " SELECT *, (SELECT agd.name FROM `" . DB_PREFIX . $type_data . "_group_description` agd WHERE agd.".$type_data."_group_id = a.".$type_data."_group_id AND agd.language_id = '" . (int)$language_id[$table] . "') AS ".$type_data."_group FROM `" . DB_PREFIX . $type_data . "` a LEFT JOIN `" . DB_PREFIX . $type_data . "_description` ad ON (a.".$type_data."_id = ad.".$type_data."_id) WHERE ad.language_id = '" . (int)$language_id[$table] . "' ";
                
                $sql .= " AND ad.".$type_data."_id = '" . $this->db->escape($identificator_value) . "' ";
                
                $sql .= " AND a.".$type_data."_group_id = '" . $group_identificator_value . "' ";
                
            }else{
                
                $sql = " SELECT *, (SELECT agd.name FROM `" . DB_PREFIX . $type_data . "_group_description` agd WHERE agd.".$type_data."_group_id = a.".$type_data."_group_id AND agd.language_id = '" . (int)$language_id[$table] . "') AS ".$type_data."_group FROM `" . DB_PREFIX . $type_data . "` a LEFT JOIN `" . DB_PREFIX . $type_data . "_description` ad ON (a.".$type_data."_id = ad.".$type_data."_id) WHERE ad.language_id = '" . (int)$language_id[$table] . "' ";
                
                $sql .= " AND ad.name = '" . $this->db->escape($identificator_value) . "' ";
                
                $sql .= " AND a.".$type_data."_group_id = '" . $group_identificator_value . "' ";
                
            }
            
            $query = $this->db->query($sql);
            
            if(count($query->rows)==1){
                
                $result = $query->row;
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getFilterId($filter_name, $filter_group_name, $filter_group_id, $language_id) {
        
        $result = FALSE;
        
        $sql = '';
        
        if($filter_name && $filter_group_name){
            
            $sql = " SELECT * FROM `" . DB_PREFIX . "filter_group_description` WHERE name = '".$this->db->escape($filter_group_name)."' AND language_id = '" . $language_id . "' ";
            
            $query = $this->db->query($sql);
            
            if($query->row){
                
                $filter_group_id = $query->row['filter_group_id'];
                
            }
                
            $sql = " SELECT * FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (fd.filter_id = f.filter_id AND fd.language_id = '".$language_id."' ) LEFT JOIN `" . DB_PREFIX . "filter_group_description` fgd ON (f.filter_group_id = fgd.filter_group_id AND fgd.language_id = '".$language_id."' ) WHERE fd.name = '".$this->db->escape($filter_name)."' AND fgd.name = '" . $this->db->escape($filter_group_name) . "' ";

        }elseif($filter_name && $filter_group_id){
            
            $sql = " SELECT * FROM `" . DB_PREFIX . "filter_group` WHERE filter_group_id = '".$filter_group_id."' ";
            
            $query = $this->db->query($sql);
            
            if(!$query->row){
                
                $filter_group_id = 0;
                
            }
            
            $sql = " SELECT * FROM `" . DB_PREFIX . "filter` f LEFT JOIN `" . DB_PREFIX . "filter_description` fd ON (fd.filter_id = f.filter_id AND fd.language_id = '".$language_id."' ) WHERE fd.name = '".$this->db->escape($filter_name)."' AND f.filter_group_id = '" . (int)$filter_group_id . "' ";

        }
        
        if($sql && $filter_group_id){
            
            $query = $this->db->query($sql);
            
            if(count($query->rows)==1){

                $result = $query->row['filter_id'];

            }elseif(!$query->row && $filter_group_id && $filter_name){
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "filter` SET filter_group_id = '" . (int)$filter_group_id . "', sort_order = '0'");

		$filter_id = $this->db->getLastId();
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` SET filter_id = '" . (int)$filter_id . "', language_id = '" . (int)$language_id . "', filter_group_id = '" . (int)$filter_group_id . "', name = '" . $this->db->escape($filter_name) . "'");
                
                $result = $filter_id;
                
            }
            
        }elseif(!$filter_group_id && $filter_group_name && $filter_name){

            $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` SET sort_order = '0'");

            $filter_group_id = $this->db->getLastId();

            $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group_description` SET filter_group_id = '" . (int)$filter_group_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($filter_group_name) . "'");

            $this->db->query("INSERT INTO `" . DB_PREFIX . "filter` SET filter_group_id = '" . (int)$filter_group_id . "', sort_order = '0'");

            $filter_id = $this->db->getLastId();

            $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` SET filter_id = '" . (int)$filter_id . "', language_id = '" . (int)$language_id . "', filter_group_id = '" . (int)$filter_group_id . "', name = '" . $this->db->escape($filter_name) . "'");

            $result = $filter_id;

        }
        
        return $result;
        
    }

    public function getIdByIdentificator($identificator_field,$identificator_value, $table, $language_id=array(),$where_add='') {
        
        $result = FALSE;
        
        if($this->showTable($table, DB_PREFIX)){
            
            $sql = "  SELECT * FROM `".DB_PREFIX.$table."` WHERE `".$identificator_field."`= '" . $this->db->escape($identificator_value) . "' ";
            
            if(isset($language_id[$table])){
                
                $sql .= " AND language_id = '".(int)$language_id[$table]."' ";
                
            }
            
            if($where_add){
                
                $sql .= " AND ".$where_add." ";
                
            }
            
            $query = $this->db->query($sql);
            
            if(count($query->rows)==1){
                
                $result = $query->row;
                
            }
            
        }
        
        return $result;
        
    }

    public function getCurrencyByCode($currency) {
		$query = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "currency` WHERE code = '" . $this->db->escape($currency) . "'");

            return $query->row;
    }
    
    public function getCategories($SEPARATOR='&nbsp;&nbsp;&gt;&nbsp;&nbsp;',$language_id, $category_id = FALSE) {
        
            $sql = "SELECT cp.category_id AS category_id, GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '".$SEPARATOR."') AS name, c1.parent_id, c1.sort_order FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' AND cd2.language_id = '" . (int)$language_id . "'";

            $sql .= " GROUP BY cp.category_id";

            $query = $this->db->query($sql);
            
            $result = array();
            
            foreach ($query->rows as $key => $value) {
                $result[$value['category_id']] = $value;
            }
            
            if($category_id && isset($result[$category_id])){
                
                $result = array($category_id=>$result[$category_id]);
                
            }elseif($category_id && !isset($result[$category_id])){
                
                $result = array();
                
            }

            return $result;
    }
    
    public function getAllProductAttributes($language_id) {
        
        $sql = "SELECT pa.*, ad.name  FROM `" . DB_PREFIX . "product_attribute` pa LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id = '".$language_id."') GROUP by attribute_id";
        
        $query = $this->db->query($sql);
        
        $result = array();
        
        foreach ($query->rows as $key => $value) {
            
            $result[$value['attribute_id']] = $value;
            
        }

        return $result;
    }
    
    public function getAllProductFilters($language_id) {
        
        $sql = "SELECT pa.*, ad.name  FROM `" . DB_PREFIX . "product_filter` pa LEFT JOIN `" . DB_PREFIX . "filter_description` ad ON (pa.filter_id = ad.filter_id AND ad.language_id = '".$language_id."') GROUP by filter_id";
        
        $query = $this->db->query($sql);
        
        $result = array();
        
        foreach ($query->rows as $key => $value) {
            
            $result[$value['filter_id']] = $value;
            
        }

        return $result;
    }
    
    public function getProductCategories($product_id) {
        
        if($product_id){
            $sql = "SELECT *  FROM " . DB_PREFIX . "product_to_category  WHERE product_id = '" . (int)$product_id . "' ";
        }else{
            $sql = "SELECT *  FROM " . DB_PREFIX . "product_to_category ";
        }
            
            $query = $this->db->query($sql);

            return $query->rows;
    }
    
    public function getProductImages($product_id) {
        
        $sql = "SELECT *  FROM " . DB_PREFIX . "product_image  WHERE product_id = '" . (int)$product_id . "' ";
            
            $query = $this->db->query($sql);

            return $query->rows;
    }
    
    public function getLastLogData() {

        return $this->setting_version->getLastLogData();

    }
    
    public function getActionTask($task_id,$action_status_id) {
            
        return $this->setting_version->getActionTask($task_id,$action_status_id);

    }
    
    public function getSmartExchangeCheckConnect() {

        return $this->setting_version->getSmartExchangeCheckConnectStatus();

    }
    
    
    
    public function getGeneralSettingsFields(){
        
        return $this->setting_version->getGeneralSettingsFields();
        
    }
    
    public function getAbstractFields(){
        
        return $this->setting_version->getAbstractFields();
        
    }
    
    public function getExcelColumnNameByColumnNames($column_names,$odmpro_tamplate_data){
        
        return $this->setting_version->getExcelColumnNameByColumnNames($column_names,$odmpro_tamplate_data);
        
    }
    
    public function getFormulaError($e,$column_name_this,$cell_address) {

	$result = explode(' in ', $e);

	if(isset($result[0]) && str_replace('PHPExcel_Calculation_Exception: ', '', $result[0])){

	    $result = str_replace('PHPExcel_Calculation_Exception: ', '', $result[0]);

	}else{

	    $result = 'Ошибка в формуле для колонки '.$column_name_this.': '.$cell_address;

	}

	return $result;

    }
    
    
    public function getSettingVersionSettings(){
        
        $setting_version_settings = $this->setting_version->getSettingVersionSettings();
        
        $this->setting_version_settings = $setting_version_settings;
        
        if(isset($setting_version_settings['lic'])){
            $this->lic = $setting_version_settings['lic'];
        }
	
	$this->getEdistributerAdaptor();
        
        if(!defined('DIR_ANYCSV_CACHE')){

                define('DIR_ANYCSV_CACHE', DIR_SYSTEM . 'library/vendor/ocext/cache/');

        }
	
        return $this->setting_version_settings;
        
    }
    
    public function getFileByURL($url,$httpcode=FALSE,$new_file_upload=FALSE,$odmpro_tamplate_data=array()) {

        if(strstr($url,'ftp://')){
            
            $ftp_result = FALSE;
            
            $ftp_user = $odmpro_tamplate_data['ftp_login'];
            
            $ftp_password = $odmpro_tamplate_data['ftp_password'];
            
            $ftp_dir = $odmpro_tamplate_data['ftp_dir'];
            
            if($ftp_user && $ftp_dir && $ftp_password){
                
                $conn_id = ftp_connect( str_replace('ftp://', '', $url) ); 
                
                $ftp_login = ftp_login($conn_id, $ftp_user, $ftp_password);
                
                ftp_pasv($conn_id, true);
                
                if($ftp_login){
                    
                    $new_file_upload_result = md5($url).'.csv';
                    
                    $handle = fopen(DIR_DOWNLOAD.$new_file_upload_result,"w");
                    
                    $ftp_fget = ftp_fget($conn_id, $handle, $ftp_dir, FTP_ASCII, 0);
                    
                    if($ftp_fget){
                        
                        return $new_file_upload_result;
                        
                    }else{

                        return $ftp_result;

                    }
                    
                }else{
                
                    return $ftp_result;
                
                }
                
            }else{
                
                return $ftp_result;
                
            }
            
        }
        
	$new_file_upload_result = md5($url).'.csv';
		
		if(strstr($url,'.zip') && isset($this->setting_version_settings['functional']['unzip']) && $this->setting_version_settings['functional']['unzip']){
			
			$file = DIR_DOWNLOAD.md5($url).'.zip';
			
			if (copy($url, $file)) {
				
				$zip = new ZipArchive();
			
				if ($zip->open($file) === true) { 
				
					$file_name = $zip->getNameIndex(0);
					
					$zip->extractTo(DIR_DOWNLOAD);
					
					if(file_exists(DIR_DOWNLOAD.$file_name)){

						$url = DIR_DOWNLOAD.$file_name;
                                                
                                                $new_file_upload_result = $file_name;

					}else{
						
						return FALSE;
						
					}
					
					$zip->close();          
					
					return $new_file_upload_result;
					
				}
				
			}else{
				
				return FALSE;
				
			}
			
		}
	
	if(isset($odmpro_tamplate_data['ba_login']) && ($odmpro_tamplate_data['ba_login']!=='' || $odmpro_tamplate_data['ba_password']!=='') ){
	    
	    $ba_password = $odmpro_tamplate_data['ba_password'];
	    
	    $ba_login = $odmpro_tamplate_data['ba_login'];
	    
	}
        
        if($new_file_upload){
            
            $handle = fopen(DIR_DOWNLOAD.$new_file_upload_result,"w");
            
            $curl = curl_init();
	    
	    if(isset($ba_password)){
		
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		
		curl_setopt($curl, CURLOPT_USERPWD, $ba_login . ":" . $ba_password); 
		
	    }

            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 

            curl_setopt($curl, CURLOPT_URL, html_entity_decode($url));

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_FILE, $handle);

            if(!curl_exec($curl)){

                curl_close($curl);

                fclose($handle);

            }else{

                fflush($handle);

                fclose($handle);

                curl_close($curl);

            }
            
            if(!file_exists(DIR_DOWNLOAD.$new_file_upload_result)){
                
                $new_file_upload_result = '';
                
            }
            
            return $new_file_upload_result;
            
        }elseif($new_file_upload){
            
            return FALSE;
            
        }
        
        if($handle && $httpcode){
            
            return TRUE;
            
        }elseif(!$handle && $httpcode){
            
            return FALSE;
            
        } 

        return $handle;
    }
    
    public function getFileNameByURL($url,$httpcode=FALSE,$supplier_feed_source='',$odmpro_tamplate_data=array()) {
        
	if(isset($odmpro_tamplate_data['ba_login']) && ($odmpro_tamplate_data['ba_login']!=='' || $odmpro_tamplate_data['ba_password']!=='') ){
	    
	    $ba_password = $odmpro_tamplate_data['ba_password'];
	    
	    $ba_login = $odmpro_tamplate_data['ba_login'];
	    
	}
	
        $cache_file_name = md5($url);
            
        $handle = fopen(DIR_DOWNLOAD.$cache_file_name, "w+");

        $curl = curl_init();
	
	if(isset($ba_password)){

	    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

	    curl_setopt($curl, CURLOPT_USERPWD, $ba_login . ":" . $ba_password); 

	}

        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 

        curl_setopt($curl, CURLOPT_URL, html_entity_decode($url));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_FILE, $handle);

        if(!curl_exec($curl)){

            curl_close($curl);

            fclose($handle);

            unlink(DIR_DOWNLOAD.$cache_file_name);

        }else{

            fflush($handle);

            fclose($handle);

            curl_close($curl);

        }
        
        //$file_exists = @fopen($url, "r");
        
        if(!file_exists(DIR_DOWNLOAD.$cache_file_name)){
            
            return FALSE;
            
        }
		
        if(strstr($url,'.zip') && isset($this->setting_version_settings['functional']['unzip']) && $this->setting_version_settings['functional']['unzip']){

                $file = DIR_DOWNLOAD.md5($url).'.zip';

                if (copy(DIR_DOWNLOAD.$cache_file_name, $file)) {

                        $zip = new ZipArchive();

                        if ($zip->open($file) === true) { 

                                $file_name = $zip->getNameIndex(0);

                                $zip->extractTo(DIR_DOWNLOAD);

                                if(file_exists(DIR_DOWNLOAD.$file_name)){

                                        $url = DIR_DOWNLOAD.$file_name;

                                }else{

                                        return FALSE;

                                }

                                $zip->close();                   

                        }

                }else{

                        return FALSE;

                }

        }else{
            
            $url = DIR_DOWNLOAD.$cache_file_name;
            
        }
        
        
        
        $handle = fopen($url, "r");
        
        if($handle && isset($this->setting_version_settings['functional']['yml_to_dsv']) && $this->setting_version_settings['functional']['yml_to_dsv']){
            
            $yml_check = fread($handle, 2048);
            
            $xml_catalog = FALSE;
            
            if(strstr($yml_check, 'xml_catalog')){
                
                $xml_catalog = TRUE; 
                
            }
            
	    $xml_catalog_root_tag = FALSE;
            
            if(strstr($yml_check, '<catalog')){
                
                $xml_catalog_root_tag = TRUE; 
                
            }
	    
            $supplier_catalog_feed = FALSE;
            
            if(strstr($yml_check, '<supplier_catalog')){
                
                $supplier_catalog_feed = TRUE;
                
            }
            
            $avito_feed = FALSE;
            
            if(strstr($yml_check, '<Ads formatVersion')){
                
                $avito_feed = TRUE;
                
            }
            
            $gibrid_yml_feed = FALSE;
            
            if(strstr($yml_check, '<КоммерческаяИнформация')){
                
                $gibrid_yml_feed = TRUE;
                
            }
            
            
            
            $shop_feed = FALSE;
            
            if(strstr($yml_check, '<deliveries>')){
                
                $shop_feed = TRUE;
                
            }
            
            $catalog_ru_feed = FALSE;
            
            if(strstr($yml_check, '<Каталог>') && strstr($yml_check, '<Продукт>')){
                
                $catalog_ru_feed = TRUE;
                
            }
            
            $specification = $this->setting_version->getSpecifications();
            
            if($supplier_feed_source && isset($specification[$supplier_feed_source])){
                
                $specification = array($supplier_feed_source=>$specification[$supplier_feed_source]);
                
            }elseif($supplier_feed_source){
                
                $specification = array();
                
            }
            
            foreach ($specification as $specification_row) {
                
                if(isset($specification_row['encoding']) && $specification_row['encoding']!='UTF-8'){
                    
                    $yml_check = mb_convert_encoding($yml_check,'UTF-8',$specification_row['encoding']);
                    
                }
                
                if((isset($specification_row['check_valid_status']) && !$specification_row['check_valid_status']) || strstr($yml_check,$specification_row['specification_valid_text']) ){

                    $f_extension = 'xml';
                    
                    if(isset($specification_row['specification_extension']) && $specification_row['specification_extension']){
                        
                        $f_extension = $specification_row['specification_extension'];
                        
                    }
                    
                    $file_name = DIR_DOWNLOAD.md5($url).'.'.$f_extension;
                    
                    $handle2 = fopen($file_name, 'w');

                    $handle = fopen($url,'r');

                    $yml = fread($handle, filesize($url));
                    
                    if($xml_catalog){
                        
                        $yml = str_replace('xml_catalog', 'yml_catalog', $yml);
                        
                    }
                    
                    if($gibrid_yml_feed){
                        
                        $yml = str_replace('КоммерческаяИнформация', 'yml_catalog', $yml);
                        
                    }
                    
                    if($supplier_catalog_feed){
                        
                        $yml = str_replace(array('supplier_catalog','<supplier>','</supplier>'), array('yml_catalog','<shop>','</shop>'), $yml);
                        
                    }
		    
		    if($xml_catalog_root_tag){
                        
                        $yml = str_replace(array('<catalog','</catalog'), array('<yml_catalog','</yml_catalog'), $yml);
                        
                    }
                    
                    
                    
                    if($shop_feed){
                        
                        $find = array(      '<shop ',               '</shop>',                          '<properties><property','</property></properties>', '<property','</property','parentid');
                        $replace = array(   '<yml_catalog><shop ',  '</shop></yml_catalog>',            '<property',            '</property>',              '<param',   '</param','parentId');
                        $yml = str_replace($find, $replace, $yml);
                        
                    }
                    
                    if($catalog_ru_feed){
                        
                        $find = array(      '<Каталог>',                   '</Каталог>',                      '<Продукт>',  '</Продукт>', '<Артикул>',  '</Артикул>', '<Цена>',  '</Цена>', '<Описание>',  '</Описание>', '<Изображения>',  '</Изображения>', '<Изображение>',  '</Изображение>');
                        $replace = array(   '<yml_catalog><shop><offers>', '</offers></shop></yml_catalog>',  '<offer>',    '</offer>',   '<code>',  '</code>' ,   '<price>',  '</price>',   '<description>',  '</description>',   '<pictures>',  '</pictures>', '<picture>',  '</picture>' );
                        $yml = str_replace($find, $replace, $yml);
                        
                    }
                    
                    if($avito_feed){
                        
                        $find = array('<Ads ','</Ads>','<Ad>','</Ad>','target="Avito.ru">','<Images>','</Images>','Title>','Price>','Description>','Id>');
                        $replace = array('<yml_catalog ','</offers></shop></yml_catalog>','<offer>','</offer>','><shop><offers>','','','title>','price>','description>','id>');
                        $yml = str_replace($find, $replace, $yml);
                        
                    }

                    fwrite($handle2, $yml);

                    fclose($handle2);

                    $handle = $file_name;
                    
                }
            
            }
            
        }
        
        unlink($url);
        
        if($handle && $httpcode){
            
            return TRUE;
            
        }elseif(!$handle && $httpcode){
            
            return FALSE;
            
        } 

        return $handle;
    }
        
    public function getFileByFileName($file_name,$httpcode=FALSE) {
        
        $file = DIR_DOWNLOAD.$file_name;
		
		if(strstr($file,'.zip') && file_exists($file) && is_file($file)){
			
			$zip = new ZipArchive();
			
			if ($zip->open($file) === true) { 
			
				$file_name = $zip->getNameIndex(0);
				
				$zip->extractTo(DIR_DOWNLOAD);
				
				if(file_exists(DIR_DOWNLOAD.$file_name)){

					$file = DIR_DOWNLOAD.$file_name;

				}else{
					
					$file = '';
					
				}
				
				$zip->close();                   
				
			}
			
		}
        
        if($httpcode && !file_exists($file) || !is_file($file)){
            
            return FALSE;
            
        }  elseif ($httpcode && file_exists($file)) {
            
            return TRUE;
            
        }

        $handle = FALSE;
        
        if(file_exists($file) && is_file($file)){
            
            $handle = fopen($file,'r');
            
        }
        
        return $handle;
    }
    
    public function getFileNameByFileName($file_name,$httpcode=FALSE,$supplier_feed_source='') {
        
        $file = DIR_DOWNLOAD.$file_name;
		
		if(strstr($file,'.zip')){
			
			$zip = new ZipArchive();
			
			if ($zip->open($file) === true) { 
			
				$file_name = $zip->getNameIndex(0);
				
				$zip->extractTo(DIR_DOWNLOAD);
				
				if(file_exists(DIR_DOWNLOAD.$file_name)){

					$file = DIR_DOWNLOAD.$file_name;

				}else{
					
					$file = '';
					
				}
				
				$zip->close();                   
				
			}
			
		}
        
        if($httpcode && !file_exists($file)){
            
            return FALSE;
            
        }  elseif ($httpcode && file_exists($file)) {
            
            return TRUE;
            
        }

        $handle = FALSE;
        
        if(file_exists($file)){
            
            $handle = fopen($file,'r');
            
        }
        
        if($handle && isset($this->setting_version_settings['functional']['yml_to_dsv']) && $this->setting_version_settings['functional']['yml_to_dsv']){
            
            $yml_check = fread($handle, 2048);
            
            $specification = $this->setting_version->getSpecifications();
            
            if($supplier_feed_source && isset($specification[$supplier_feed_source])){
                
                $specification = array($supplier_feed_source=>$specification[$supplier_feed_source]);
                
            }elseif($supplier_feed_source){
                
                $specification = array();
                
            }
            
            foreach ($specification as $specification_row) {
                
                if(isset($specification_row['encoding']) && $specification_row['encoding']!='UTF-8'){
                    
                    $yml_check = iconv($specification_row['encoding'],'UTF-8',$yml_check);
                    
                }
                
                if((isset($specification_row['check_valid_status']) && !$specification_row['check_valid_status']) || strstr($yml_check,$specification_row['specification_valid_text']) ){

                    $f_extension = 'xml';
                    
                    if(isset($specification_row['specification_extension']) && $specification_row['specification_extension']){
                        
                        $f_extension = $specification_row['specification_extension'];
                        
                    }

                    $file_name = DIR_DOWNLOAD.md5($file).'.'.$f_extension;

                    $handle2 = fopen($file_name, 'w');

                    $handle = fopen($file,'r');

                    $yml = fread($handle, filesize($file));

                    fwrite($handle2, $yml);

                    fclose($handle2);

                    $handle = $file_name;
                    
                    return $handle;
                    
                }
            
            }
            
        }
        
        return $handle;
    }
        
    public function writeCsv($data,$first_write,$csv_delimiter,$csv_enclosure,$csv_escape,$encoding,$file_and_path,$log_data=array()) {
        
        $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
        
        $file_name_and_path = $file_and_path.'.csv';
        
        $file_name_and_path_array = explode('/', trim($file_name_and_path));
            
        $path_array = array();

        for ($i=0;$i<(count($file_name_and_path_array)-1);$i++) {

            $path_array[] = $file_name_and_path_array[$i];

        }

        $file_name = end($file_name_and_path_array);
        
        $write_path = DIR_IMAGE;
        
        if($path_array){
            
            foreach ($path_array as $dir) {
                
                $write_path .= $dir.'/';
                
                if(!file_exists($write_path)){

                    mkdir($write_path,0777);

                }
                
            }
            
        }
        
        if(!file_exists($write_path)){
            
            return;
            
        }
        
        if(!file_exists($write_path.$file_name)){
            
            $handle = fopen($write_path.$file_name, "a+"); 
            
            fclose($handle);
            
        }
        
        //Открываем
        if($first_write){
            
            $handle = fopen($write_path.$file_name, "w+");
        }else{
            $handle = fopen($write_path.$file_name, "a+");
        }
        
        
        if(!$handle){
            
            return;
        }
        
        foreach ($data as $csv_row) {
            
            $value = array();
            
            $col = 1;
            
            foreach ($csv_row as $row) {
                
                $value_this = '';
                
                if(is_array($row)){
                    
                    foreach($row as $subrow){
                        
                        $value_this = $subrow;
                        
                        if($csv_enclosure=='"'){
                            $value_this = str_replace(array("\""), "'", $value_this);
                        }
                        
                        $value_this = str_replace(array("\r\n", "\r", "\n"), " ", $value_this);
                        
                        $value[] = $value_this;
                        
                    }
                    
                }else{
                    
                    $value_this = $row;
                        
                    if($csv_enclosure=='"'){
                        $value_this = str_replace(array("\""), "'", $value_this);
                    }

                    $value_this = str_replace(array("\r\n", "\r", "\n"), " ", $value_this);

                    $value[] = $value_this;
                    
                }
                
            }
            
            $value = implode($csv_delimiter,$value);
            
            if($encoding!=='UTF-8' && $encoding && is_string($value)){

                $value = $this->convertCSVValue($value, 'UTF-8', $encoding);
                
                $csv_delimiter = $this->convertCSVValue($csv_delimiter, 'UTF-8', $encoding);
                
                $csv_enclosure = $this->convertCSVValue($csv_enclosure, 'UTF-8', $encoding);

            }
            
            fputcsv($handle, explode($csv_delimiter, $value), html_entity_decode($csv_delimiter),html_entity_decode($csv_enclosure));
            
            $log_data['__line__'] = __LINE__;

            $log_write_row = array(
                'log_data' => $log_data,
                'message' => array('success'=>$this->language->get('export_success_write_column')),
                'action'    => 'export'
            );

            $this->setLogDataRow($log_write_row,$log_data);
            
        }
        
        fclose($handle);
        
        return;
    }
        
    public function getOptionValue($where) {
            
		$result = array();
                
                $sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "option_value_description agd WHERE agd.option_value_id = a.option_value_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS option_value_name FROM " . DB_PREFIX . "option_value a LEFT JOIN " . DB_PREFIX . "option_value_description ad ON (a.option_value_id = ad.option_value_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND a.".$where['field']."='".$where['value']."' ";
                
                $sql .= " ORDER BY option_value_name, ad.name";

                $query2 = $this->db->query($sql);

                if($query2->row){

                    $result = $query2->row;

                }
                
		return $result;
    }
    
    public function getOnlyColumnsName($table,$filter_array=array()){
        
        $result = array();
        
        if($this->showTable($table, DB_PREFIX)){
                
                $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $table.'` ' );
                
                foreach ($columns->rows as $column) {
                 
                    if( ($filter_array && !in_array($column['Field'], $filter_array)) || !$filter_array){
                        
                        $result[$column['Field']] = $column['Field'];
                        
                    }
                    
                }
        
        }
        
        return $result;
        
    }
    
    public function writeLog($action,$message,$odmpro_tamplate_data,$log_data){
        
        $log_data['log_html'] = 1;
        
        if(isset ($odmpro_tamplate_data['log_html']) && $odmpro_tamplate_data['log_html']){
            
            $log_data['log_html'] = 1;
            
        }else{
            
            $log_data['log_html'] = 0;
            
        }
        
        $log_file_name = '';
        
        if(isset($odmpro_tamplate_data['log_status']) && !$odmpro_tamplate_data['log_status']){
                
            return;

        }elseif(isset ($odmpro_tamplate_data['log_file_name']) && $odmpro_tamplate_data['log_file_name']){
            
            $log_file_name = trim(ltrim($odmpro_tamplate_data['log_file_name']));
            
            if($log_data['log_html']){
                $log_file_name .= '.htm';
            }else{
                $log_file_name .= '.txt';
            }
            
        }
        
        if(isset ($odmpro_tamplate_data['log_details']) && $odmpro_tamplate_data['log_details']){
            
            $log_data['details'] = 1;
            
        }
        
        $log_data['log_update'] = 1;
        
        if(isset ($odmpro_tamplate_data['log_update']) && $odmpro_tamplate_data['log_update']){
            
            $log_data['log_update'] = 1;
            
        }else{
            
            $log_data['log_update'] = 0;
            
        }
        
        if(!isset($log_data['__line__'])){
            
            $log_data['__line__'] = 'n/a';
            
        }
        
        $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
        
        $error = '';
        
        $file_name = '';
        
        if(!$log_file_name){
            
            $error .= sprintf($this->language->get('error_log_file_name'),__LINE__);
            
        }else{
            
            $file_name_and_path_array = explode('/', trim($log_file_name));
            
            $path_array = array();
            
            for ($i=0;$i<(count($file_name_and_path_array)-1);$i++) {
                
                $path_array[] = $file_name_and_path_array[$i];
                
            }
            
            $file_name = end($file_name_and_path_array);
            
        }
        
        if(!$file_name){
            
            $error .= sprintf($this->language->get('error_log_file_name'),__LINE__);
            
        }
        
        if($error){
            
            return $error;
            
        }
        
        $write_path = DIR_IMAGE;
        
        if($path_array){
            
            foreach ($path_array as $dir) {
                
                $write_path .= $dir.'/';
                
                if(!file_exists($write_path)){

                    mkdir($write_path,0777);

                }
                
            }
            
        }
        
        if(!file_exists($write_path)){
            
            $error .= sprintf($this->language->get('error_log_file_name'),__LINE__);
            
            return $error;
            
        }
        
        $mode_fopen = 'a+';
        
        if($log_data['log_update'] && file_exists($write_path.'/'.$file_name)){
            
            $log_update_file_last_row = file($write_path.'/'.$file_name);
            
            if($log_update_file_last_row){
                
                $last_num_process = json_decode(end($log_update_file_last_row),TRUE);
                
                if($last_num_process && is_array($last_num_process) && isset($last_num_process['num_process']) && $last_num_process['num_process']!=$log_data['num_process']){
                    
                    $mode_fopen = "w+";
                    
                }
                
            }
            
        }
        
        if(!file_exists($write_path.'/'.$file_name)){
            
            $handle = fopen($write_path.'/'.$file_name, "w+");

        }elseif(file_exists($write_path.'/'.$file_name)){

            $handle = fopen($write_path.'/'.$file_name, $mode_fopen);

        }
        
        if(isset($log_data['file_url'])){
            
            $odmpro_tamplate_data['file_url'] = $log_data['file_url'];
            
        }
        
        if(isset($log_data['file_upload'])){
            
            $odmpro_tamplate_data['file_upload'] = $log_data['file_upload'];
            
        }
        
        $text = "\n"."\n".'date: '.date('Y-m-d H:i')."\n";
        $html = '<style>table { width:100%; } table tr td{ padding:1px; border:1px solid grey; font-size:12px !important; }</style><table style="" ><tr><td style="background:#444;color:white">date</td><td>'.date('Y-m-d H:i').'</td></tr>';
        
        if( !isset($log_data['log_start_position']) || (isset($log_data['log_start_position']) && !$log_data['log_start_position'])){
            $text .= 'action: '.$action."\n";
            $html .= '<tr ><td style="background:#444;color:white">action</td><td>'.$action.'</td></tr>';

            if($odmpro_tamplate_data['file_url']){
                $text .= 'file url: '.$odmpro_tamplate_data['file_url']."\n";
                $html .= '<tr style="borer:1px solid grey;"><td style="background:#444;color:white">file url</td><td>'.$odmpro_tamplate_data['file_url'].'</td></tr>';
            }elseif($odmpro_tamplate_data['file_upload']){
                $text .= 'file: '.$odmpro_tamplate_data['file_upload']."\n";
                $html .= '<tr style="borer:1px solid grey;"><td style="background:#444;color:white">file</td><td>'.$odmpro_tamplate_data['file_upload'].'</td></tr>';
            }else{
                $text .= 'file: no file'."\n";
                $html .= '<tr style="borer:1px solid grey;"><td style="background:#444;color:white">file</td><td>no file</td></tr>';
            }
            $text .= 'format: '.$log_data['format_data']."\n";
            $html .= '<tr style="borer:1px solid grey;"><td style="background:#444;color:white">format</td><td>'.$log_data['format_data'].'</td></tr>';
            $text .= 'process, №: '.$log_data['type_process']."\n";
            $html .= '<tr style="borer:1px solid grey;"><td style="background:#444;color:white">process, №</td><td>'.$log_data['type_process'].', №'.$log_data['num_process'].'</td></tr>';
            $text .= 'row: '.$log_data['start']."\n";
        }
        
        $html .'</table>';
        
        $html .= '<table><tr style="borer:1px solid grey;"><td style="background:#444;color:white">row file</td><td>'.$log_data['start'].'</td>';
        $text .= 'limit rows: '.$log_data['limit']."\n";
        $html .= '<td style="background:#444;color:white">limit rows</td><td>'.$log_data['limit'].'</td>';
        if(isset ($message['error']) && $message['error']){
            $text .= 'ERROR MESSAGE: '."\n".  strip_tags($message['error'])."\n";
            $html .= '<td style="background:#444;color:white">message error</td><td style="background:red;color:white">'.$message['error'].'</td>';
        }elseif(isset ($message['success']) && $message['success']){
            $text .= 'SUCCESS MESSAGE: '."\n".  strip_tags($message['success'])."\n";
            $html .= '<td style="background:#444;color:white">message success</td><td style="background:green;color:white">'.$message['success'].'</td>';
        }elseif(isset ($message['warning']) && $message['warning']){
            $text .= 'WARNING: '."\n".  strip_tags($message['warning'])."\n";
            $html .= '<td style="background:#444;color:white">warning</td><td style="background:orange;color:white">'.$message['warning'].'</td>';
        }else{
            $html .= '<td style="background:#444;color:white">message</td><td>no message</td>';
        }
        if(isset ($log_data['details']) && $log_data['details']){
            if(isset ($log_data['details_message']) && $log_data['details_message']){
                
                $html .= '<td style="background:#444;color:white">details</td><td>'.$log_data['details_message'].'</td>';
                $text .= 'START DETAILS --------------------- '."\n".  strip_tags($log_data['details_message']);
                $text .= 'END DETAILS --------------------- '.  "\n";
            }else{
                
                $html .= '<td style="background:#444;color:white">details</td><td>no details</td>';
                $text .= 'DETAILS: no details'.  "\n";
                
            }
        }
        
        $html .= '<td style="background:#444;color:white">__line__</td><td>'.$log_data['__line__'].'</td></tr></table><hr>'.'
';
        $text .= '__line__: '.$log_data['__line__']."\n";
        $text .= '...............................................................................................'."\n";
        
        if($log_data['num_process']){
            
            $text .= json_encode(array('num_process'=>$log_data['num_process']));
            $html .= json_encode(array('num_process'=>$log_data['num_process']));
            
        }
        
        
        
        if($handle){
            
            if($log_data['log_html']){
                
                fwrite($handle, $html);
                
            }else{
                
                fwrite($handle, $text);
                
            }
            
            fclose($handle);
            
            return '';
            
        }else{
            
            $error .= sprintf($this->language->get('error_log_file_name'),__LINE__);
            
            return $error;
            
        }
        
    }
    
    public function updateGroupDataProduct($odmpro_tamplate_data){
        
        $get = $this->request->get;;
        
        $first_step = TRUE;

            if(isset($get['num_process'])){
                $num_process = $get['num_process'];
                $history_group_data_product = $this->getSetting('history_group_data','history_group_data_product');

                if(!isset($history_group_data_product[$num_process])){
                    $set_history_group_data_product['history_group_data_product'] = $history_group_data_product;
                    $set_history_group_data_product['history_group_data_product'][$num_process] = TRUE;
                    $this->editSetting('history_group_data', $set_history_group_data_product);
                }else{
                    $first_step = FALSE;
                }
            }

            if($first_step && isset($odmpro_tamplate_data['group_id_box']['product_data'])){

                $product_data = $odmpro_tamplate_data['group_id_box']['product_data'];

                $where = $this->getWhere($product_data,'AND');

                if($where){

                    $update = array();

                    if(isset($odmpro_tamplate_data['group_id_box']['disable_quantity']) && $odmpro_tamplate_data['group_id_box']['disable_quantity']!==''){

                        $disable_quantity = (int)trim($odmpro_tamplate_data['group_id_box']['disable_quantity']);

                    }

                    if(isset($odmpro_tamplate_data['group_id_box']['disable_price']) && $odmpro_tamplate_data['group_id_box']['disable_price']!==''){

                        $disable_price = (float)trim($odmpro_tamplate_data['group_id_box']['disable_price']);

                    }

                    $sql = "UPDATE * FROM " . DB_PREFIX . "product ";

                    if(isset($disable_quantity)){

                        $update[] = " quantity = ".$disable_quantity;

                    }

                    if(isset($disable_price)){

                        $update[] = " price = ".$disable_price;

                    }

                    if(isset($odmpro_tamplate_data['group_id_box']['disable_price']) && $odmpro_tamplate_data['group_id_box']['disable_product']){

                        $update[] = " status = 0 ";

                    }

                    if($update){

                        $sql = "UPDATE " . DB_PREFIX . "product SET ".implode(",",$update)." WHERE ".$where;

                        $query = $this->db->query($sql);

                    }

                }

            }
        
    }

    public function replaceOperator($operator){

        return $this->setting_version->replaceOperator($operator);

    }
    
    public function getWhere($product_data,$sql_logic='OR') {
        
        $where = array();

        foreach ($product_data as $product_data_where){

            if($product_data_where['product_field'] && $product_data_where['operator']){

                $operator = $this->replaceOperator($product_data_where['operator']);



                $product_field = $product_data_where['product_field'];

                if($product_field=='quantity_advanced'){

                    $product_field = 'quantity';

                }elseif($product_field=='price_advanced'){

                    $product_field = 'price';

                }

                $value = $product_data_where['value'];

                $value_array = explode('///',$value);

                if(count($value_array)>1){

                    $sql = array();

                    foreach ($value_array as $value) {

                        if($operator && $product_field){

                            if($operator=='like_right'){

                                $sql[] = $product_field.' LIKE  "%'.$this->db->escape($value).'" ';

                            }elseif($operator=='like_left'){

                                $sql[] = $product_field.' LIKE  "'.$this->db->escape($value).'%" ';

                            }elseif($operator=='like'){

                                $sql[] = $product_field.' LIKE  "%'.$this->db->escape($value).'%" ';

                            }elseif($operator=='not_like_right'){

                                $sql[] = $product_field.' NOT LIKE  "%'.$this->db->escape($value).'" ';

                            }elseif($operator=='not_like_left'){

                                $sql[] = $product_field.' NOT LIKE  "'.$this->db->escape($value).'%" ';

                            }elseif($operator=='not_like'){

                                $sql[] = $product_field.' NOT LIKE  "%'.$this->db->escape($value).'%" ';

                            }else{

                                $sql[] = $product_field.' '.$operator.' "'.$this->db->escape($value).'" ';

                            }

                        }

                    }

                    if($sql){

                        $where[] = ' ( '.implode(' OR ', $sql).' ) ';

                    }

                }else{

                    if($operator && $product_field){

                        $sql = '';

                        if($operator=='like_right'){

                            $sql = $product_field.' LIKE  "%'.$this->db->escape($value).'" ';

                        }elseif($operator=='like_left'){

                            $sql = $product_field.' LIKE  "'.$this->db->escape($value).'%" ';

                        }elseif($operator=='like'){

                            $sql = $product_field.' LIKE  "%'.$this->db->escape($value).'%" ';

                        }elseif($operator=='not_like_right'){

                            $sql = $product_field.' NOT LIKE  "%'.$this->db->escape($value).'" ';

                        }elseif($operator=='not_like_left'){

                            $sql = $product_field.' NOT LIKE  "'.$this->db->escape($value).'%" ';

                        }elseif($operator=='not_like'){

                            $sql = $product_field.' NOT LIKE  "%'.$this->db->escape($value).'%" ';

                        }else{

                            $sql = $product_field.' '.$operator.' "'.$this->db->escape($value).'" ';

                        }

                        $where[] = $sql;

                    }

                }

            }

        }

        return implode(' '.$sql_logic.' ', $where);

    }
    
    public function getParsFromCollection($collection,$dsv_row) {
        
        $result = $this->setting_version->getParsFromCollection($collection,$dsv_row);
        
        return $result;

    }

    public function importCSV($odmpro_tamplate_data,$import_steps,$import_data,$log_data){
        
        if(!$this->lic){
            return;
        }
        
        $left_id_prefix = '';
        
        $right_id_prefix = '';
        
        if(isset($odmpro_tamplate_data['group_id_box']['right_prefix'])){
            
            $right_id_prefix = trim($odmpro_tamplate_data['group_id_box']['right_prefix']);
            
        }
        
        $process_log_file = DIR_CACHE.'anycsv_process_log_'.$odmpro_tamplate_data['id'].'-'.$log_data['num_process'].'.json';
        
        $process_log_data = array();
        
        if(file_exists($process_log_file)){
            
            $process_log_data = file_get_contents($process_log_file);
            
            $process_log_data = json_decode($process_log_data,TRUE);
            
            if(!$process_log_data){
                
                $process_log_data = array();
                
            }
            
        }
        
        $start = $log_data['start'];
        
        $this->temp['get_result_demo'] = NULL;
        
        $this->temp['demo_mode'] = FALSE;
        
        if(isset($odmpro_tamplate_data['get_result_demo']) && $odmpro_tamplate_data['get_result_demo']){
            
            $this->temp['demo_mode'] = TRUE;
            
            $this->temp['get_result_demo'] = array('results'=>array(),'param'=>array('this_demo_id'=>2147483700));
            
            if(file_exists(DIR_ANYCSV_CACHE.'result_demo.json')){
                
                $this->temp['get_result_demo'] = file_get_contents(DIR_ANYCSV_CACHE.'result_demo.json');
                
            }
            
        }
        
        $this->temp['process_log_data'] = $process_log_data;
        
        if(isset($odmpro_tamplate_data['group_id_box']['left_prefix'])){
            
            $left_id_prefix = trim($odmpro_tamplate_data['group_id_box']['left_prefix']);
            
        }
        
        $store_id['value_array'] = $odmpro_tamplate_data['store_id'];
        
        $store_id['sql'] = array();
        
        foreach ($store_id['value_array'] as $store_id_selected) {
            
            $store_id['sql'][$store_id_selected] = " store_id = '".$store_id_selected."' ";
            
        }
        
        if(isset($odmpro_tamplate_data['group_id_box'])){
            
            $this->updateGroupDataProduct($odmpro_tamplate_data);
            
        }
	
	$quick_stock_update_data = array();
        
        $log_write_rows = array();
        
        $no_store_id_tables = array_flip(array('attribute','attribute_group','option_value','option','filter_group','filter'));
        
        $language_id['value_string'] = $odmpro_tamplate_data['language_id'];
        
        $language_id['sql']['language_id'] = " language_id = '".$odmpro_tamplate_data['language_id']."' ";
        
        $currency = $this->getCurrencyByCode($odmpro_tamplate_data['currency_code']);
        
        if(isset($odmpro_tamplate_data['currency_code']) && $odmpro_tamplate_data['currency_code'] && isset($odmpro_tamplate_data['currency_code_to']) && $odmpro_tamplate_data['currency_code_to']){
            
            $this->convert_currency = array('from'=>$odmpro_tamplate_data['currency_code'],'to'=>$odmpro_tamplate_data['currency_code_to']);
            
        }
        
        $currency_code = $odmpro_tamplate_data['currency_code'];
        
        $currency_id = $currency['currency_id'];
        
        $type_change = $odmpro_tamplate_data['type_change'];
        
        $abstract_fields = $this->getAbstractFields();
        
        $this->odmpro_tamplate_data = $odmpro_tamplate_data;
            
        $start_row_by_add_first_row = 1;

        if(isset($odmpro_tamplate_data['add_first_row']) && $odmpro_tamplate_data['add_first_row']){

                $start_row_by_add_first_row = 0;

        }
        
        $this_num_row = $start-$start_row_by_add_first_row;
        
        if(isset($odmpro_tamplate_data['php_after_import']) && $odmpro_tamplate_data['php_after_import']!==''){
            
            $odmpro_tamplate_data['php_after_import'] = html_entity_decode($odmpro_tamplate_data['php_after_import']);
            
            $odmpro_tamplate_data['php_after_import'] = str_replace('"', "'", $odmpro_tamplate_data['php_after_import']);
            
            eval($odmpro_tamplate_data['php_after_import']);
            
        }
        
        $self_column = array();
        
        if(isset($odmpro_tamplate_data['self_column'])){
            
            $self_column = $odmpro_tamplate_data['self_column'];
            
        }
        
        $filter_import = array();
        
        if(isset($odmpro_tamplate_data['filter_import']) && $odmpro_tamplate_data['filter_import']){
            
            foreach ($odmpro_tamplate_data['filter_import'] as $filter_import_cond) {
                
                if($filter_import_cond['field']!=='' && $filter_import_cond['operator'] && $filter_import_cond['action']){
                    
                    if(!isset($filter_import[$filter_import_cond['field']])){
                        
                        $filter_import[$filter_import_cond['field']] = array();
                        
                    }
                    
                    $filter_import_cond['value'] = str_replace('\\|', 'EOCEXT', $filter_import_cond['value']);
                    
                    $filter_values = explode('|',$filter_import_cond['value']);
                    
                    foreach ($filter_values as $filter_value_key => $filter_value_value) {
                        
                        $filter_values[$filter_value_key] = str_replace('EOCEXT','|', $filter_value_value);
                        
                    }
                    
                    $filter_import_cond['value'] = $filter_values;
                    
                    $filter_import[$filter_import_cond['field']][] = $filter_import_cond;
                    
                }
                
            }
            
        }
        
        foreach ($import_steps as $type_data => $step) {
            
            $general_setting = $step['general_settings'];
            
            $this->general_setting[$type_data] = $general_setting;
            
            if(isset($general_setting['image_upload_curl']) && $general_setting['image_upload_curl']){
            
                $this->image_upload_curl = TRUE;

            }
            
            $column_settings = $step['column_settings'];
            
            $identificator = array();
            
            if(isset($step['identificator'])){
                
                $identificator = $step['identificator'];
                
            }
            
            $this->setMaxMemoryUsage();
            
            foreach ($import_data['data'] as  $csv_row_num_part => $csv_row_whis_data) {

                $csv_data = array();
                
                $skip = array();
                
                $skip_by_filter = array();
                
                $last_data_to_db = array();
            
                $new_data_for_db = array();
                
                $log_data['start'] = $csv_row_num_part + $start + $start_row_by_add_first_row;
                
                foreach ($csv_row_whis_data as $csv_position => $csv_value) {

                    if(isset($import_data['field_position']) && isset($import_data['field_position'][$csv_position])){
                        
                        if($start_row_by_add_first_row){
                        
                            $field = $import_data['field_position'][$csv_position];

                        }else{

                            $field = $csv_position;

                        }
                        
                        $csv_value = $this->prepareValue($csv_value);

                        if(isset($column_settings[$field]) && $csv_value==='' && $column_settings[$field]['additinal_settings']['column_request']==1){

                            $skip[$field] = $field;

                        }
                        
                        if(!$skip && $filter_import && isset($filter_import[$field]) && $filter_import[$field]){
                            
                            foreach ($filter_import[$field] as $filter_import_cond) {
                                
                                foreach ($filter_import_cond['value'] as $filter_import_cond_value) {
                                    
                                    if($filter_import_cond['action']=='save' && !$this->checkValueByOperatorAndFieldValue($csv_value, $filter_import_cond_value, $filter_import_cond['operator']) ){
                                        
                                        $skip_by_filter[$field] = TRUE;
                                        
                                    }
                                    elseif($filter_import_cond['action']=='save' && $this->checkValueByOperatorAndFieldValue($csv_value, $filter_import_cond_value, $filter_import_cond['operator']) ){
                                        
                                        $skip_by_filter[$field] = FALSE;
                                        
                                    }
                                    elseif($filter_import_cond['action']=='skip' && $this->checkValueByOperatorAndFieldValue($csv_value, $filter_import_cond_value, $filter_import_cond_value['operator']) ){
                                        
                                        $skip_by_filter[$field] = TRUE;
                                        
                                    }
                                    elseif($filter_import_cond['action']=='skip' && !$this->checkValueByOperatorAndFieldValue($csv_value, $filter_import_cond_value, $filter_import_cond_value['operator']) ){
                                        
                                        $skip_by_filter[$field] = FALSE;
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                    }

                }
                
                if(!$skip && $skip_by_filter){
                    
                    $skip_by_filter_this = TRUE;
                    
                    foreach ($skip_by_filter as $filter_field => $filter_value) {
                    
                        if(!$filter_value){
                            
                            $skip_by_filter_this = FALSE;
                            
                        }
                        
                    }
                    
                    $skip = $skip_by_filter_this;
                    
                }
                
                $skip_by_filter = array();
                
                if($skip){
                
                    $log_data['__line__'] = __LINE__; 
                    
                    $log_write_row = array(
                        'log_data' => $log_data,
                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_request_empty_value'),  implode(', ', $skip))),
                        
                        'action'    => $log_data['type_process']
                    );
                    
                    $this->setLogDataRow($log_write_row,$log_data);
                    
                }
                
                else{
                    
                    foreach ($csv_row_whis_data as $csv_position => $csv_value) {

                        if(isset($import_data['field_position'][$csv_position])){
                            
                            if($start_row_by_add_first_row){
                            
                                $field = $import_data['field_position'][$csv_position];

                            }else{

                                $field = $csv_position;

                            }

                            $csv_value = $this->prepareValue($csv_value);

                            $csv_data[ $field ] = $this->prepareValue($csv_value);
                            
                        }
                        
                    }
                    
                    foreach ($column_settings as $column_name_this => $column_setting_this) {
			
			if(isset($column_setting_this['additinal_settings']) && isset($column_setting_this['additinal_settings']['column_replace_from']) && $column_setting_this['additinal_settings']['column_replace_from']!=='' && isset($csv_data[$column_name_this])){
			    
			    $column_replace_from = $column_setting_this['additinal_settings']['column_replace_from'];
			    
                            $column_replace_to = $column_setting_this['additinal_settings']['column_replace_to'];
                            
			    $new_csv_value_this = $this->getFindReplace($column_replace_from, $column_replace_to, $csv_data[$column_name_this]);
			    
			    $csv_data[ $column_name_this ] = $new_csv_value_this;
			    
			}
			
		    }
		    
		    foreach ($column_settings as $column_name_this => $column_setting_this) {
			
			if(isset($column_setting_this['additinal_settings']) && isset($column_setting_this['additinal_settings']['column_transformation']) && $column_setting_this['additinal_settings']['column_transformation']!==''){
			    
			    $column_transformation_value = $column_setting_this['additinal_settings']['column_transformation'];
			    
			    $new_csv_value_this = $this->getParsFromCollection($column_transformation_value, $csv_data);
			    
			    $csv_data[ $column_name_this ] = $new_csv_value_this;
			    
			}
			
		    }
		    
		    foreach ($column_settings as $column_name_this => $column_setting_this) {
			
			if(isset($column_setting_this['additinal_settings']) && isset($column_setting_this['additinal_settings']['column_excel_transformation']) && $column_setting_this['additinal_settings']['column_excel_transformation']!==''){
			    
			    $column_excel_transformation_value = html_entity_decode($column_setting_this['additinal_settings']['column_excel_transformation']);
			    
			    $objPHPExcel = new PHPExcel();
			    
			    $sheet = $objPHPExcel->createSheet();
			    
			    $excel_cells = array();
			    
			    foreach($odmpro_tamplate_data['excel_column_name_by_column_name'] as $column_name_this2 => $column_excel_addr_this){
				
				if(isset($csv_data[$column_name_this2])){
				    
				    $excel_cells[$column_excel_addr_this] = $csv_data[$column_name_this2];
				    
				}
				
			    }
			    
			    if($excel_cells){
				
				foreach ($excel_cells as $cell_name => $cell_value) {

				    $sheet  ->setCellValueExplicit($cell_name, $cell_value);

				}
				
			    }
			    
			    $needle_excel_cell_temp = 'ZZ1';
			    
			    $sheet  ->setCellValueExplicit($needle_excel_cell_temp, $column_excel_transformation_value,PHPExcel_Cell_DataType::TYPE_FORMULA);
			    
			    try {
		    
				$new_csv_value_this = $sheet->getCell($needle_excel_cell_temp)->getCalculatedValue();

			    }catch (exception $e) { 

				$new_csv_value_this = $this->getFormulaError($e, $column_name_this ,$column_excel_transformation_value);

			    }
			    
			    $csv_data[ $column_name_this ] = $new_csv_value_this;
			    
			}
			
		    }
                    
                    if($self_column){
                        
                        foreach ($self_column as $self_column_field => $self_column_setting) {
                            
                            $result_php = array();
                            
                            if(isset($column_settings[$self_column_field])){
                                
                                $column_settings[$self_column_field]['column_settings'] = $self_column_setting;
                                
                                if(isset($self_column_setting['string']) && $self_column_setting['string']!==''){
                                    
                                    $csv_data[ $self_column_field ] = $self_column_setting['string'];
                                    
                                }
                                
                                if(isset($self_column_setting['rule'])){
                                    
                                    $text_rule_empty = array('','skip_this_part','','skip_this_part','','skip_this_part','');
                                    
                                    foreach ($self_column_setting['rule'] as $rule) {

                                        if($rule['operator'] && $rule['field']!=='' && isset($csv_data[$rule['field']])){
                                            
                                            $rule_result = $this->checkValueByOperatorAndFieldValue($csv_data[$rule['field']],$rule['value'],$rule['operator']);
                                         
                                            if($rule_result){
                                                
                                                $csv_data[ $self_column_field ] = $this->getValueByRuleText($csv_data,$rule['text']);
                                                
                                            }
                                            
                                        }elseif($text_rule_empty!==$rule['text'] && (!$rule['operator'] || $rule['field']==='')){
                                            
                                            $csv_data[ $self_column_field ] = $this->getValueByRuleText($csv_data,$rule['text']);
                                            
                                        }
                                        
                                    }
                                    
                                    
                                }
                                
                                $x_path_file = '';
                                
                                if(isset($self_column_setting['xpath_url_as_string']) && $self_column_setting['xpath_url_as_string']!==''){
                                    
                                    $x_path_file = $this->getCacheFileByURL($self_column_setting['xpath_url_as_string'],$odmpro_tamplate_data);
                                    
                                }
                                
                                if(!$x_path_file && isset($self_column_setting['xpath_url_from_column']) && $self_column_setting['xpath_url_from_column']!='skip_this_name' && isset($csv_data[$self_column_setting['xpath_url_from_column']])){
                                    
                                    $x_path_file = $this->getCacheFileByURL($csv_data[$self_column_setting['xpath_url_from_column']],$odmpro_tamplate_data);
                                    
                                }
                                
                                if($x_path_file){
                                    
                                    $x_path_params['xpath_stags'] = $self_column_setting['xpath_stags'];
                                    
                                    $result_xpath = $this->getXPathResult($x_path_file,$self_column_setting['xpath'],$x_path_params);
                                    
                                    $csv_data[ $self_column_field ] = $result_xpath;
                                    
                                }
                                
                                if(isset($self_column_setting['php']) && $self_column_setting['php']!==''){
                                    
                                    if(!isset($result_xpath)){
                                        
                                        $result_xpath = '';
                                        
                                    }
                                    
                                    $result_php = $this->getPhpResult($csv_data,$self_column_setting['php'], ($log_data['start']-$start_row_by_add_first_row),$result_xpath );
                                    
                                }
                                
                                if(isset($result_php['result'])){
                                    
                                    $csv_data[ $self_column_field ] = $result_php['result'];
                                    
                                }
                                
                                if(isset($result_php['csv_row']) && $result_php['csv_row']){
                                    
                                    foreach($result_php['csv_row'] as $php_csv_row_field_this => $php_csv_row_vaule_this){
                                        
                                        $csv_data[$php_csv_row_field_this] = $php_csv_row_vaule_this;
                                        
                                    }
                                    
                                }
                                
                            }
                            /*
                            if(!isset($csv_data[ $self_column_field ]) || $csv_data[ $self_column_field ]===''){
                                
                                //unset($column_settings[$self_column_field]);
                                //$skip = TRUE;
                                
                            }else*/
                            if(isset($csv_data[ $self_column_field ]) && $csv_data[ $self_column_field ]==='' && $column_settings[$self_column_field]['additinal_settings']['column_request']==1){
                                
                                $skip = TRUE;
                                
                            }
                            
                            if(!$skip && isset($csv_data[ $self_column_field ]) && $filter_import && isset($filter_import['self_column___'.$self_column_field]) && $filter_import['self_column___'.$self_column_field]){
                            
                                foreach ($filter_import['self_column___'.$self_column_field] as $filter_import_cond) {

                                    foreach ($filter_import_cond['value'] as $filter_import_cond_value) {
                                        
                                        if($filter_import_cond['action']=='save' && !$this->checkValueByOperatorAndFieldValue($csv_data[ $self_column_field ], $filter_import_cond_value, $filter_import_cond['operator']) ){
                                        
                                            $skip_by_filter['self_column___'.$self_column_field] = TRUE;

                                        }
                                        elseif($filter_import_cond['action']=='save' && $this->checkValueByOperatorAndFieldValue($csv_data[ $self_column_field ], $filter_import_cond_value, $filter_import_cond['operator']) ){

                                            $skip_by_filter['self_column___'.$self_column_field] = FALSE;

                                        }
                                        elseif($filter_import_cond['action']=='skip' && $this->checkValueByOperatorAndFieldValue($csv_data[ $self_column_field ], $filter_import_cond_value, $filter_import_cond_value['operator']) ){

                                            $skip_by_filter['self_column___'.$self_column_field] = TRUE;

                                        }
                                        elseif($filter_import_cond['action']=='skip' && !$this->checkValueByOperatorAndFieldValue($csv_data[ $self_column_field ], $filter_import_cond_value, $filter_import_cond_value['operator']) ){

                                            $skip_by_filter['self_column___'.$self_column_field] = FALSE;

                                        }

                                    }

                                }

                            }
                            
                        }
                        
                    }
                    
                }
                
                if(!$skip && $skip_by_filter){

                    $skip_by_filter_this = TRUE;

                    foreach ($skip_by_filter as $filter_field => $filter_value) {

                        if(!$filter_value){

                            $skip_by_filter_this = FALSE;

                        }

                    }

                    $skip = $skip_by_filter_this;

                }
		
		$this->temp['csv_data_row'] = $csv_data;
                
                if($type_data=='product' && !$skip && $csv_data){
                    
                    $product_id = 0;
                    
                    $identificator_field_name = '';
                    
                    $identificator_insert = array();
		    
		    $quick_stock_update_mode = FALSE;
			
		    if(isset($this->general_setting['product']['quick_stock_update']) && $this->general_setting['product']['quick_stock_update']){

			$quick_stock_update_mode = TRUE;

		    }
                
                    if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                     
                        foreach ($identificator as $identificator_param) {
                            
                            if($identificator_param['identificator_type'] == 'name'){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $left_id_prefix.$csv_data[$identificator_param['field']].$right_id_prefix;
                                
                                $identificator_table = 'product_description';
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table, $language_id['value_string']);
                                
                            }elseif($identificator_param['identificator_type'] == 'aid'){
                                
                                $identificator_field_name = 'product_id';
                    
                                $identificator_value = $left_id_prefix.$csv_data[$identificator_param['field']].$right_id_prefix;
                                
                                $identificator_table = 'product';
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type']){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $left_id_prefix.$csv_data[$identificator_param['field']].$right_id_prefix;
                                
                                $identificator_table = 'product';
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }
                            
                            if(!$product_id && isset($last_data_to_db['product_id'])){
                                
                                $product_id = $last_data_to_db['product_id'];
                                
                            }
                            /*
                             * Уже найден ранее, должен совпадать, иначе ошибка, что колонки идентификатора дают разные строки из базы
                             */
                            elseif($product_id && isset($last_data_to_db['product_id']) && $product_id!=$last_data_to_db['product_id']){
                                
                                $skip[$product_id] = $product_id;
                                
                                $skip[$last_data_to_db['product_id']] = $last_data_to_db['product_id'];
                
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_identificators'),  $type_data,  implode(', ', $skip),  $type_data)),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                
                                $this->setLogDataRow($log_write_row,$log_data);
                                
                            }
                            
                            if($identificator_param['additinal_settings']['identificator_insert']){
                                    
                                $identificator_insert[$identificator_table][$identificator_field_name] = $identificator_value;

                            }
                            
                        }
                        
                        if($type_change=='only_update_data' && !$product_id && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_req_data_to_db'),  $type_data,  $type_data)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }elseif($type_change=='only_new_data' && ($product_id || isset($last_data_to_db['dublicates_count'])) && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_req_data_to_db'),  'product_id',  $product_id)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }
                        
                    }
		    
                    $this->setMaxMemoryUsage();
		    
                    if(!$skip){
                            
                        /*
                         * Сначала обновляем или создаем с нуля основные данные
                         */
                        foreach ($column_settings as $field => $setting) {
                            
                            $additinal_settings = array();
                            
                            if(isset($setting['additinal_settings']) && $setting['additinal_settings']){
                                
                                $additinal_settings = $setting['additinal_settings'];
                                
                            }
                            
                            $data_action = 'add_values';
                            
                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==2){
                                
                                $data_action = 'delete_values';
                                
                            }elseif(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==3){
                                
                                $data_action = 'delete_last_data_after_add';
                                
                            }
                            
                            /*
                             * Если идентификатора нет, значит уже не обновление, а добавление. Удалять ничего не нужно - все данные новые
                             */
                            
                            if(!$product_id && ($type_change=='new_data' || $type_change=='update_data')){
                                
                                $data_action = 'add_values';
                                
                            }

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);
                            
                            /*
                             * Колонки совпадающие с названием таблицы и колонки в этой таблтце добавляем, как есть 
                             */
                            /*
                             * В некоторые таблицы значения добавляются, как массив, там где прямой - row
                             */
                            if(isset($columns[$db_column]) && isset($csv_data[$field])){
                                
                                $new_data_for_db['data'][$db_table][$data_action]['row'][$db_column] = $csv_data[$field];
                                
                            }
                            
                            /*
                             * Расширенные колонки
                             */
                            
                            elseif(isset($csv_data[$field])){
                                
                                if(!$quick_stock_update_mode && $db_column=='category_whis_path'){
                                    
                                    $yandex_market_category_sich = array();
                                    
                                    $categories = array();
                                    
                                    if(isset($odmpro_tamplate_data['yandex_market_category_sich'])){
                                        
                                        $yandex_market_category_sich = $odmpro_tamplate_data['yandex_market_category_sich'];
                                        
                                        $yandex_market_category_sich_category_id = md5($csv_data[$field]);
                                        
                                        if(isset($yandex_market_category_sich[$yandex_market_category_sich_category_id]) && $yandex_market_category_sich[$yandex_market_category_sich_category_id]){
                                            
                                            $categories = $yandex_market_category_sich[$yandex_market_category_sich_category_id];
                                            
                                        }
                                        
                                    }
                                    
                                    //var_dump($additinal_settings);exit();
                                    
                                    
                                    //var_dump($additinal_settings);exit();
                                    
                                    
                                    
                                    if(!isset($categories) || !$categories){
                                        
                                        $categories = array();
                                        
                                        /*
                                        * Категории в товарах всегда, только добавляются. Чистка категорий возможна при импорте категорий - той же колонки, например, но настройки импорт категолрий
                                        * В этом случае delete_values в последнем аргументе
                                        */
                                       //$categories = $this->getCategoriesIdByPath($csv_data[$field],$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, 'add_values', $log_data);
                                        
                                        $delimiter_2 = '';

                                        if(isset($additinal_settings['delimeter_2']) && $additinal_settings['delimeter_2']){

                                            $delimiter_2 = $additinal_settings['delimeter_2'];

                                        }

                                        if($delimiter_2!==''){

                                            $categories_parts = explode($delimiter_2,$csv_data[$field]);
                                            
                                            $categories['pathes'] = array();

                                            foreach($categories_parts as $categories_part){

                                                $categories_part = trim($categories_part);

                                                if($categories_part!==''){

                                                    $categories['pathes'][] = $this->getCategoriesIdByPath($categories_part,$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, $data_action, $log_data);

                                                }

                                            }

                                        }else{

                                            $categories = $this->getCategoriesIdByPath($csv_data[$field],$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, $data_action, $log_data);

                                        }
                                        
                                       
                                    }
                                    
                                    if($categories && !isset($categories['pathes'])){
                                        
                                        $c = 1;
                                        
                                        foreach ($categories as $category_id) {
                                            
                                            $product_to_category = array();
                                            
                                            /*
                                             * Продукт в во всех категория
                                             */
                                            if(isset($additinal_settings['all_product_category']) && $additinal_settings['all_product_category']){
                                                
                                                if($c == count($categories) && $this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){
                                            
                                                    $product_to_category['main_category'] = 1;
                                                
                                                }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                    $product_to_category['main_category'] = 0;

                                                }

                                                $product_to_category['category_id'] = $category_id;

                                                $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                            }elseif($c == count($categories)){
                                                
                                                if($this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){
                                            
                                                    $product_to_category['main_category'] = 1;
                                                
                                                }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                    $product_to_category['main_category'] = 0;

                                                }
                                                
                                                $product_to_category['category_id'] = $category_id;

                                                $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;
                                                
                                            }
                                            
                                            $c++;
                                            
                                        }
                                        
                                        unset($categories);
                                        
                                    }elseif($categories && isset($categories['pathes'])){
                                        
                                        $categories_pathes = $categories['pathes'];
                                        
                                        foreach($categories_pathes as $categories){
                                            
                                            $c = 1;
                                        
                                            foreach ($categories as $category_id) {

                                                $product_to_category = array();

                                                /*
                                                 * Продукт в во всех категория
                                                 */
                                                if(isset($additinal_settings['all_product_category']) && $additinal_settings['all_product_category']){

                                                    if($c == count($categories) && $this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }elseif($c == count($categories)){

                                                    if($this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }

                                                $c++;

                                            }

                                            unset($categories);
                                            
                                        }
                                        
                                    }else{
                
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_catogory'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='category_name_and_parent_level'){
                                    
                                    /*
                                     * Ищем другие части пути
                                     */
                                    
                                    $other_category_name_and_parent_level = array();
                                    
                                    foreach($column_settings as $field_tmp => $setting_tmp){
                                        
                                        $db_table___db_column_tmp = explode('___', $setting_tmp['db_table___db_column']);

                                        $db_column_tmp = $db_table___db_column_tmp[1];
                                        
                                        if($db_column_tmp=='category_name_and_parent_level' && $field != $field_tmp){
                                            
                                            if(isset($setting_tmp['additinal_settings']) && isset($csv_data[$field_tmp]) && $setting_tmp['additinal_settings']['parent_level']!==''){
                                                
                                                $other_category_name_and_parent_level[] = array(
                                                    'additinal_settings'    => $setting_tmp['additinal_settings'],
                                                    'value' => trim(ltrim($csv_data[$field_tmp]))
                                                ); 
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    $category_name_and_parent_level = array();
                                    
                                    if(isset($csv_data[$field]) && $additinal_settings['parent_level']!==''){
                                                
                                        $other_category_name_and_parent_level[] = array(
                                            'additinal_settings'    => $additinal_settings,
                                            'value' => trim(ltrim($csv_data[$field]))
                                        );

                                    }
                                    
                                    if($other_category_name_and_parent_level){
                                        
                                        foreach ($other_category_name_and_parent_level as $other_category_name_and_parent_level_row) {
                                            
                                            $category_name_and_parent_level[$other_category_name_and_parent_level_row['additinal_settings']['parent_level']] = $other_category_name_and_parent_level_row;
                                            
                                        }
                                        
                                        ksort($category_name_and_parent_level);
                                        
                                    }
                                    
                                    $category_names_whise_parents = array();
                                    
                                    $category_names = array();
                                    
                                    if($category_name_and_parent_level){
                                        
                                        for($c=0;$c<count($category_name_and_parent_level);$c++){
                                            
                                            if(isset($category_name_and_parent_level[$c]) && $category_name_and_parent_level[$c]['value']){
                                                
                                                $category_names[] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['category_name'] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['additinal_settings'] = $category_name_and_parent_level[$c]['additinal_settings'];
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    $delimiter_tmp = '/';
                                    
                                    $path_whis_categories_name = '';
                                    
                                    if($category_names_whise_parents /* && count($category_names_whise_parents) == count($category_name_and_parent_level)*/){
                                        
                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);
                                        
                                    }
                                    /*
                                     * Если одна и парент нуль, то одна категория
                                     */
                                    elseif($category_names_whise_parents && count($category_names_whise_parents) == 1 && isset($category_names_whise_parents[0])){
                                        
                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);
                                        
                                    }
                                    
                                    $path_whis_parent_categories = '';
                                    
                                    if($additinal_settings['parent_category_id']){
                                        
                                        $parent_categories = $this->getCategories($delimiter_tmp,$language_id['value_string'],$additinal_settings['parent_category_id']);
                                        
                                        if($parent_categories){
                                            
                                            $path_whis_parent_categories = $parent_categories[$additinal_settings['parent_category_id']]['name'].$delimiter_tmp;
                                            
                                        }
                                        
                                    }
                                    
                                    if($path_whis_categories_name){
                                        
                                        $path_whis_categories_name = $path_whis_parent_categories.$path_whis_categories_name;
                                        
                                    }
                                    
                                    if(!$path_whis_categories_name){
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_path_whis_categories_name'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }else{
                                        $additinal_settings['delimeter']=$delimiter_tmp;
                                        /*
                                        * Категории в товарах всегда, только добавляются. Чистка категорий возможна при импорте категорий - той же колонки, например, но настройки импорт категолрий
                                        * В этом случае delete_values в последнем аргументе
                                        */
                                       $categories = $this->getCategoriesIdByPath($path_whis_categories_name,$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, 'add_values', $log_data);

                                       if($categories){

                                           $c = 1;

                                           foreach ($categories as $category_id) {

                                                $product_to_category = array();

                                                /*
                                                 * Продукт в во всех категория
                                                 */
                                                if(isset($additinal_settings['all_product_category']) && $additinal_settings['all_product_category']){

                                                    if($c == count($categories) && $this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }elseif($c == count($categories)){

                                                    if($this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){

                                                        $product_to_category['main_category'] = 1;

                                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                                        $product_to_category['main_category'] = 0;

                                                    }

                                                    $product_to_category['category_id'] = $category_id;

                                                    $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;

                                                }

                                                $c++;

                                           }

                                       }else{

                                           $log_data['__line__'] = __LINE__; 

                                           $log_write_row = array(
                                               'log_data' => $log_data,
                                               'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_catogory'),  $field)),
                                               
                                               'action'    => $log_data['type_process']
                                           );
                                           
                                           $this->setLogDataRow($log_write_row,$log_data);

                                       }
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='category_id'){
                                    
                                    $category_id = (int)trim($csv_data[$field]);
                                    
                                    $product_to_category = array();

                                    if($this->checkColumnTable('product_to_category', 'main_category') && isset($additinal_settings['main_category']) && $additinal_settings['main_category']){
                                            
                                        $product_to_category['main_category'] = 1;

                                    }elseif($this->checkColumnTable('product_to_category', 'main_category')){

                                        $product_to_category['main_category'] = 0;

                                    }
                                    
                                    if($category_id){
                                        
                                        $product_to_category['category_id'] = $category_id;

                                        $new_data_for_db['data']['product_to_category'][$data_action]['rows'][$category_id] = $product_to_category;
                                        
                                    }else{
                
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_catogory'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && ($db_column=='image_advanced' || $db_column=='images') ){
                                    
                                    $images = $this->getImages($csv_data[$field],$general_setting,$additinal_settings, 'add_values',$log_data);
                                    
                                    $first_image_main = '';
                                    
                                    if($images && $db_column=='image_advanced'){
                                        
                                        $new_data_for_db['data'][$db_table][$data_action]['row']['image'] = current($images);
                                        
                                        if(isset($general_setting['skip_by_no_image']) && $general_setting['skip_by_no_image'] && (!$new_data_for_db['data'][$db_table][$data_action]['row']['image'] || strstr($new_data_for_db['data'][$db_table][$data_action]['row']['image'], 'no_image') || strstr($new_data_for_db['data'][$db_table][$data_action]['row']['image'], 'no-image')   || !file_exists(DIR_IMAGE.$new_data_for_db['data'][$db_table][$data_action]['row']['image'])) ){
                                            
                                            $skip = TRUE;
                                            
                                        }
                                        
                                    }elseif($images){
                                        
                                        if(isset($additinal_settings['first_image_main']) && $additinal_settings['first_image_main']){
                                            
                                            $new_data_for_db['data']['product'][$data_action]['row']['image'] = current($images);
                                            
                                            $first_image_main = $new_data_for_db['data']['product'][$data_action]['row']['image'];
											
                                            if(isset($general_setting['skip_by_no_image']) && $general_setting['skip_by_no_image'] && (!$first_image_main || strstr($first_image_main, 'no_image') || strstr($first_image_main, 'no-image') || !file_exists(DIR_IMAGE.$first_image_main)) ){
                                            
                                                $skip = TRUE;

                                            }
                                            
                                        }
                                        
                                        foreach($images as $image){
                                            
                                            if(!$first_image_main || ($first_image_main && $first_image_main!=$image)){
                                                
                                                $new_data_for_db['data']['product_image'][$data_action]['rows'][$image]['image'] = $image;
                                                
                                                if(isset($general_setting['skip_by_no_image']) && $general_setting['skip_by_no_image'] && (!$image || strstr($image, 'no_image') || strstr($image, 'no-image')  || !file_exists(DIR_IMAGE.$image)) ){
                                            
                                                    $skip = TRUE;

                                                }elseif(!$image || strstr($image, 'no_image') || strstr($image, 'no-image')){
                                                    
                                                    unset($new_data_for_db['data']['product_image'][$data_action]['rows'][$image]['image']);
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }elseif(!$images && isset($general_setting['skip_by_no_image']) && $general_setting['skip_by_no_image']){
										
										$skip = TRUE;
										
                                    }else{
                
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_image'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='manufacturer_name'){
                                    
                                    $manufacturer_id = $this->getManufacturerIdAndSaveByName($csv_data[$field],$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings,'add_values');
                                    
                                    $new_data_for_db['data']['product'][$data_action]['row']['manufacturer_id'] = $manufacturer_id;
                                    
                                }
                                
                                elseif($db_column=='price_advanced'){
                                    
                                    $price = $this->getPriceBySettings($csv_data[$field],$additinal_settings);
                                    /*
                                    $price = $this->getFloat($csv_data[$field]);
                                    
                                    if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                        $price *= $this->getFloat($additinal_settings['price_rate']);

                                    }

                                    if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                        $price *= $this->getFloat($additinal_settings['price_delta']);

                                    }

                                    if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                        $price = round($price,0);

                                    }
                                     * 
                                     */
                                    
                                    if(isset($general_setting['stock_status_id_by_price']) && $general_setting['stock_status_id_by_price'] && $price==0.0){
                                        
                                        $new_data_for_db['data'][$db_table][$data_action]['row']['stock_status_id'] = $general_setting['stock_status_id_by_price'];
                                        
                                    }
                                    
                                    $new_data_for_db['data'][$db_table][$data_action]['row']['price'] = $price;
                                    
                                }
                                
                                elseif($db_column=='quantity_advanced'){
                                    
                                    $quantity = (int)trim($csv_data[$field]);

                                    if(isset($additinal_settings['quantity_update']) && $additinal_settings['quantity_update'] && !$quantity){

                                        $quantity = (int)$additinal_settings['quantity_update'];

                                    }
                                    
                                    if(isset($additinal_settings['quantity_group']) && $additinal_settings['quantity_group']){

                                        foreach($additinal_settings['quantity_group'] as $g => $quantity_group){
                                            
                                            $check_quantity_field = trim($csv_data[$field]);
                                            
                                            if(html_entity_decode($quantity_group['field']) ==  html_entity_decode($check_quantity_field) && $check_quantity_field!=''){
                                                
                                                $quantity = (int)$quantity_group['value'];
                                                
                                            }
                                            
                                        }

                                    }
                                    
                                    if(isset($general_setting['stock_status_id_by_quantity']) && $general_setting['stock_status_id_by_quantity'] && !$quantity){
                                        
                                        $new_data_for_db['data'][$db_table][$data_action]['row']['stock_status_id'] = $general_setting['stock_status_id_by_quantity'];
                                        
                                    }
                                    
                                    $new_data_for_db['data'][$db_table][$data_action]['row']['quantity'] = $quantity;
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='seo_url'){
                                    
                                    $seo_url = trim($csv_data[$field]);
                                    
                                    if($seo_url){
                                        
                                        $new_data_for_db['data'][$this->table_seo_url][$data_action]['row']['keyword'] = $seo_url;
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_seo_url'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode &&  ($db_column=='attribute_value' || $db_column=='attribute_values') ){
                                    
                                    if(isset($additinal_settings['attribute_group_id___attribute_id']) && $additinal_settings['attribute_group_id___attribute_id']){
                                        
                                        $attribute_values = array();
                                        
                                        $attribute_group_id___attribute_id = explode('___', $additinal_settings['attribute_group_id___attribute_id']);
                                        
                                        if($db_column=='attribute_values'){
                                            
                                            $delimeter = '';
                                            
                                            if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){
                                                
                                                $delimeter = $additinal_settings['delimeter'];
                                                
                                            }
                                            
                                            if(!$delimeter){
                                                
                                                $delimeter = '|';
                                                
                                            }
                                            
                                            $attribute_values = explode($delimeter, $csv_data[$field]);
                                            
                                        }else{
                                            
                                            $attribute_values[] = trim($csv_data[$field]);
                                            
                                        }
                                        
                                        if($attribute_values && $attribute_group_id___attribute_id[1]){
                                            
                                            foreach ($attribute_values as $text) {
                                                
                                                $text = ltrim(trim($text));
                                                
                                                if($text && isset($attribute_group_id___attribute_id[1])){
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['text'] = $text;
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['language_id'] = $language_id['value_string'];
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['attribute_id'] = $attribute_group_id___attribute_id[1];
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    elseif(isset($additinal_settings['attribute_group_id']) && $additinal_settings['attribute_group_id']){
                                        
                                        $type_attribute_group_id = explode('___', $additinal_settings['attribute_group_id']);
                                        
                                        $attribute_group_id = 0;

                                        $attribute_group_name = '';
										
                                        $attribute_values = array();

                                        $attribute_id = 0;

                                        $attribute_name = '';

                                        $delimeter = '';

                                        if(isset($type_attribute_group_id[0]) && $type_attribute_group_id[0]=='field_this_file' && isset($csv_data[$type_attribute_group_id[1]]) && $csv_data[$type_attribute_group_id[1]]){

                                            $attribute_group_name = trim(ltrim($csv_data[$type_attribute_group_id[1]]));

                                        }else{

                                            $attribute_group_id = (int)$additinal_settings['attribute_group_id'];

                                        }
                                        
                                        if($attribute_group_id || $attribute_group_name){
                                            
                                            $attribute_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName($attribute_group_id, $attribute_group_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                            
                                        }
                                        
                                        if($attribute_group_id){
                                            
                                            $attribute_name_field = explode('___', $additinal_settings['attribute_name_field']);

                                            if(isset($attribute_name_field[0]) && $attribute_name_field[0]=='field_this_file' && isset($attribute_name_field[1]) && isset($csv_data[$attribute_name_field[1]]) && $csv_data[$attribute_name_field[1]]){

                                                $attribute_name = trim(ltrim($attribute_name_field[1]));

                                            }
                                            
                                            if($attribute_id || $attribute_name){
                                                
                                                $attribute_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($attribute_group_id,$attribute_id, $attribute_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                
                                            }
                                            
                                        }
                                        
                                        if($db_column=='attribute_values'){
                                            
                                            if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){
                                                
                                                $delimeter = $additinal_settings['delimeter'];
                                                
                                            }
                                            
                                            if($delimeter===''){
                                                
                                                $delimeter = '|';
                                                
                                            }
                                            
                                            $attribute_values = explode($delimeter, $csv_data[$field]);
                                            
                                        }else{
                                            
                                            $attribute_values[] = trim($csv_data[$field]);
                                            
                                        }
                                        
										if($attribute_id && $attribute_values){
											
											foreach ($attribute_values as $text) {
                                                
                                                $text = ltrim(trim($text));
												
												//echo $text.'-'.$attribute_id.'<br>';
												
												//var_dump($text);
												//var_dump($attribute_id);
                                                
                                                if($text){
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['text'] = $text;
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['language_id'] = $language_id['value_string'];
                                                    
                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['attribute_id'] = $attribute_id;
                                                    
                                                }
                                                
                                            }
                                            
                                        }else{
                                        
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id___attribute_id'),  $field)),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);

                                        }
                                        
                                    }
                                    
                                    else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id___attribute_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='attribute_name_field'){
                                    
                                    if(isset($additinal_settings['attribute_group_id']) && $additinal_settings['attribute_group_id']){
                                        
                                        
                                        
                                        if(!$attribute_group_id && !$attribute_group_name){
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }else{
                                            
                                            $atribute_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName($attribute_group_id, $attribute_group_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                            
                                            if($atribute_group_id){
                                                
                                                $attribute_name_field = explode('___', $additinal_settings['attribute_name_field']);
                                        
                                                $attribute_id = 0;

                                                $attribute_name = '';

                                                if(isset($attribute_name_field[0]) && $attribute_name_field[0]=='field_this_file' && isset($csv_data[$type_attribute_group_id[1]]) && $csv_data[$type_attribute_group_id[1]]){

                                                    $attribute_name = trim(ltrim($csv_data[$attribute_name_field[1]]));

                                                }
                                                
                                                $delimeter = '';
                                            
                                                if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                                    $delimeter = $additinal_settings['delimeter'];

                                                }

                                                if(!$delimeter){

                                                    $delimeter = '|';

                                                }

                                                $attribute_values = explode($delimeter, $csv_data[$field]);
                                                
                                                
                                                $attribute_values_whis_attribute_name = explode($delimiter_1, trim($csv_data[$field]));
                                                
                                                if($attribute_values_whis_attribute_name && is_array($attribute_values_whis_attribute_name)){
                                                    
                                                    foreach($attribute_values_whis_attribute_name as $attribute_value_whis_attribute_name){
                                                        
                                                        $delimiter_2 = '';
                                            
                                                        if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                                            $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                                        }

                                                        if(!$delimiter_2){

                                                            $delimiter_2 = '|';

                                                        }
                                                        
                                                        $attribute_and_value = explode($delimiter_2, trim($attribute_value_whis_attribute_name));
                                                        
                                                        if(isset($attribute_and_value[0]) && isset($attribute_and_value[1])){
                                                            
                                                            $attribute_name = trim(ltrim($attribute_and_value[0]));
                                                            
                                                            $text = trim(ltrim($attribute_and_value[1]));
                                                            
                                                            $atribute_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($atribute_group_id, 0, $attribute_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                            
                                                            if($atribute_id && $text){
                                                                
                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['text'] = $text;
                                                    
                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['language_id'] = $language_id['value_string'];

                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['attribute_id'] = $atribute_id;
                                                                
                                                            }else{
                                                                
                                                                $log_data['__line__'] = __LINE__; 

                                                                $log_write_row = array(
                                                                    'log_data' => $log_data,
                                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                                    
                                                                    'action'    => $log_data['type_process']
                                                                );
                                                                
                                                                $this->setLogDataRow($log_write_row,$log_data);
                                                                
                                                            }
                                                            
                                                        }else{
                                                            
                                                            $log_data['__line__'] = __LINE__; 

                                                            $log_write_row = array(
                                                                'log_data' => $log_data,
                                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                                
                                                                'action'    => $log_data['type_process']
                                                            );
                                                            
                                                            $this->setLogDataRow($log_write_row,$log_data);
                                                            
                                                        }
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    $log_data['__line__'] = __LINE__; 

                                                    $log_write_row = array(
                                                        'log_data' => $log_data,
                                                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                        
                                                        'action'    => $log_data['type_process']
                                                    );
                                                    
                                                    $this->setLogDataRow($log_write_row,$log_data);
                                                    
                                                }
                                                
                                            }else{
                                                
                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                                    
                                                    'action'    => $log_data['type_process']
                                                );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);
                                                
                                            }
                                            
                                        }
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='attribute_values_whis_attrubute_name'){
                                    
                                    if(isset($additinal_settings['attribute_group_id']) && $additinal_settings['attribute_group_id']){
                                        
                                        $type_attribute_group_id = explode('___', $additinal_settings['attribute_group_id']);
                                        
                                        $attribute_group_id = 0;
                                        
                                        $attribute_group_name = '';
                                        
                                        if(isset($type_attribute_group_id[0]) && $type_attribute_group_id[0]=='field_this_file' && isset($csv_data[$type_attribute_group_id[1]]) && $csv_data[$type_attribute_group_id[1]]){
                                            
                                            $attribute_group_name = trim(ltrim($csv_data[$type_attribute_group_id[1]]));
                                            
                                        }else{
                                            
                                            $attribute_group_id = (int)$additinal_settings['attribute_group_id'];
                                            
                                        }
                                        
                                        if(!$attribute_group_id && !$attribute_group_name){
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }else{
                                            
                                            $atribute_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName($attribute_group_id, $attribute_group_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                            
                                            if($atribute_group_id){
                                                
                                                $delimiter_1 = '';
                                            
                                                if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                                    $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                                }

                                                if(!$delimiter_1){

                                                    $delimiter_1 = '|';

                                                }
                                                
                                                $attribute_values_whis_attribute_name = explode($delimiter_1, trim($csv_data[$field]));
                                                
                                                if($attribute_values_whis_attribute_name && is_array($attribute_values_whis_attribute_name)){
                                                    
                                                    foreach($attribute_values_whis_attribute_name as $attribute_value_whis_attribute_name){
                                                        
                                                        $delimiter_2 = '';
                                            
                                                        if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                                            $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                                        }

                                                        if(!$delimiter_2){

                                                            $delimiter_2 = '|';

                                                        }
                                                        
                                                        $attribute_and_value = explode($delimiter_2, trim($attribute_value_whis_attribute_name));
                                                        
                                                        if(isset($attribute_and_value[0]) && isset($attribute_and_value[1])){
                                                            
                                                            $attribute_name = trim(ltrim($attribute_and_value[0]));
                                                            
                                                            $text = trim(ltrim($attribute_and_value[1]));
                                                            
                                                            $atribute_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($atribute_group_id, 0, $attribute_name,'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                            
                                                            if($atribute_id && $text){
                                                                
                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['text'] = $text;
                                                    
                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['language_id'] = $language_id['value_string'];

                                                                $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['attribute_id'] = $atribute_id;
                                                                
                                                            }else{
                                                                
                                                                $log_data['__line__'] = __LINE__; 

                                                                $log_write_row = array(
                                                                    'log_data' => $log_data,
                                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                                    
                                                                    'action'    => $log_data['type_process']
                                                                );
                                                                
                                                                $this->setLogDataRow($log_write_row,$log_data);
                                                                
                                                            }
                                                            
                                                        }else{
                                                            
                                                            $log_data['__line__'] = __LINE__; 

                                                            $log_write_row = array(
                                                                'log_data' => $log_data,
                                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                                
                                                                'action'    => $log_data['type_process']
                                                            );
                                                            
                                                            $this->setLogDataRow($log_write_row,$log_data);
                                                            
                                                        }
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    $log_data['__line__'] = __LINE__; 

                                                    $log_write_row = array(
                                                        'log_data' => $log_data,
                                                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                        
                                                        'action'    => $log_data['type_process']
                                                    );
                                                    
                                                    $this->setLogDataRow($log_write_row,$log_data);
                                                    
                                                }
                                                
                                            }else{
                                                
                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                                    
                                                    'action'    => $log_data['type_process']
                                                );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);
                                                
                                            }
                                            
                                        }
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_group_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='attribute_values_whis_attrubute_name_and_group_name'){
                                    
                                    $delimiter_1 = '';
                                            
                                    if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                        $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                    }

                                    if(!$delimiter_1){

                                        $delimiter_1 = '|';

                                    }
                                    
                                    $attribute_values_whis_attribute_name_and_group_name = explode($delimiter_1, trim($csv_data[$field]));
                                    
                                    if($attribute_values_whis_attribute_name_and_group_name && is_array($attribute_values_whis_attribute_name_and_group_name)){
                                        
                                        foreach($attribute_values_whis_attribute_name_and_group_name as $attribute_values_whis_attribute_name_and_group_name_row){
                                            
                                            $delimiter_2 = '';
                                            
                                            if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                                $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                            }

                                            if(!$delimiter_2){

                                                $delimiter_2 = '|';

                                            }
                                            
                                            $product_attribute_data = explode($delimiter_2,$attribute_values_whis_attribute_name_and_group_name_row);
                                            
                                            if(isset($product_attribute_data[0]) && isset($product_attribute_data[1]) && isset($product_attribute_data[2])){
                                                
                                                $atribute_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName(0, $product_attribute_data[0],'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                
                                                $atribute_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($atribute_group_id, 0, $product_attribute_data[1],'attribute', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                
                                                $text = trim(ltrim($product_attribute_data[2]));

                                                if($atribute_id && $text && $atribute_group_id){

                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['text'] = $text;

                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['language_id'] = $language_id['value_string'];

                                                    $new_data_for_db['data']['product_attribute'][$data_action]['rows'][$text]['attribute_id'] = $atribute_id;

                                                }else{

                                                    $log_data['__line__'] = __LINE__; 

                                                    $log_write_row = array(
                                                        'log_data' => $log_data,
                                                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                        
                                                        'action'    => $log_data['type_process']
                                                    );
                                                    
                                                    $this->setLogDataRow($log_write_row,$log_data);

                                                }
                                                
                                            }else{

                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                                    
                                                    'action'    => $log_data['type_process']
                                                );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);

                                            }
                                            
                                        }
                                        
                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_attribute_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && ($db_column=='filter_name' || $db_column=='filter_values_whis_filter_name') ){
                                    
                                    if(isset($additinal_settings['filter_group_id']) && $additinal_settings['filter_group_id']){
                                        
                                        $type_group_id = explode('___', $additinal_settings['filter_group_id']);
                                        
                                        $group_id = 0;
                                        
                                        $group_name = '';
                                        
                                        if(isset($type_group_id[0]) && $type_group_id[0]=='field_this_file' && isset($csv_data[$type_group_id[1]]) && $csv_data[$type_group_id[1]]){
                                            
                                            $group_name = trim(ltrim($csv_data[$type_group_id[1]]));
                                            
                                        }else{
                                            
                                            $group_id = (int)$additinal_settings['filter_group_id'];
                                            
                                        }
                                        
                                        if(!$group_id && !$group_name){
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }else{
                                            
                                            $filter_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName($group_id, $group_name,'filter', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                            
                                            if($filter_group_id){
                                                
                                                $delimeter = '';
                                            
                                                if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                                    $delimeter = $additinal_settings['delimeter'];

                                                }

                                                if(!$delimeter){

                                                    $delimeter = '|';

                                                }
                                                
                                                $filtres = explode($delimeter, trim($csv_data[$field]));
                                                
                                                if($filtres && is_array($filtres)){
                                                    
                                                    foreach($filtres as $filter_name){
                                                        
                                                        $filter_name = trim(ltrim($filter_name));

                                                        $filter_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($filter_group_id, 0, $filter_name,'filter', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');

                                                        if($filter_id){

                                                            $new_data_for_db['data']['product_filter'][$data_action]['rows'][$filter_id]['filter_id'] = $filter_id;

                                                        }else{
                                                    
                                                            $log_data['__line__'] = __LINE__; 

                                                            $log_write_row = array(
                                                                'log_data' => $log_data,
                                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                                
                                                                'action'    => $log_data['type_process']
                                                            );
                                                            
                                                            $this->setLogDataRow($log_write_row,$log_data);

                                                        }
                                                        
                                                    }
                                                    
                                                }else{
                                                    
                                                    $log_data['__line__'] = __LINE__; 

                                                    $log_write_row = array(
                                                        'log_data' => $log_data,
                                                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                        
                                                        'action'    => $log_data['type_process']
                                                    );
                                                    
                                                    $this->setLogDataRow($log_write_row,$log_data);
                                                    
                                                }
                                                
                                            }else{
                                                
                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                    
                                                    'action'    => $log_data['type_process']
                                                );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);
                                                
                                            }
                                            
                                        }
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='filter_values_whis_filter_name_and_group_name'){
                                    
                                    $delimiter_1 = '';
                                            
                                    if(isset($additinal_settings['delimiter_1']) && $additinal_settings['delimiter_1']){

                                        $delimiter_1 = trim(ltrim($additinal_settings['delimiter_1']));

                                    }

                                    if(!$delimiter_1){

                                        $delimiter_1 = '|';

                                    }
                                    
                                    $values_whis_attribute_name_and_group_name = explode($delimiter_1, trim($csv_data[$field]));
                                    
                                    if($values_whis_attribute_name_and_group_name && is_array($values_whis_attribute_name_and_group_name)){
                                        
                                        foreach($values_whis_attribute_name_and_group_name as $values_whis_attribute_name_and_group_name){
                                            
                                            $delimiter_2 = '';
                                            
                                            if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                                $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                            }

                                            if(!$delimiter_2){

                                                $delimiter_2 = '|';

                                            }
                                            
                                            $product_filter_data = explode($delimiter_2,$values_whis_attribute_name_and_group_name);
                                            
                                            if(isset($product_filter_data[0]) && isset($product_filter_data[1])){
                                                
                                                $filter_group_id = $this->getAttributeOrFilterGroupByGroupIdOrGroupName(0, $product_filter_data[0],'filter', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                
                                                $filter_id = $this->getAttributeOrFilterByIdOrGroupNameAndGroupId($filter_group_id, 0, $product_filter_data[1],'filter', $language_id['value_string'], $general_setting,$additinal_settings,'add_values');
                                                
                                                if($filter_id && $filter_group_id){

                                                    $new_data_for_db['data']['product_filter'][$data_action]['rows'][$filter_id]['filter_id'] = $filter_id;

                                                }else{

                                                    $log_data['__line__'] = __LINE__; 

                                                    $log_write_row = array(
                                                        'log_data' => $log_data,
                                                        'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                        
                                                        'action'    => $log_data['type_process']
                                                    );
                                                    
                                                    $this->setLogDataRow($log_write_row,$log_data);

                                                }
                                                
                                            }else{

                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                                    
                                                    'action'    => $log_data['type_process']
                                                );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);

                                            }
                                            
                                        }
                                        
                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_filter_group_id'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='relate_by_product_id'){
                                    
                                    $delimeter = '';
                                    
                                    if($additinal_settings['delimeter']!==''){
                                        
                                        $delimeter = $additinal_settings['delimeter'];
                                        
                                    }
                                    
                                    $related_product_ids = array();
                                    
                                    if($delimeter){
                                        
                                        $related_product_ids = explode($delimeter, trim(ltrim($csv_data[$field])));
                                        
                                    }else{
                                        
                                        $related_product_ids[] = (int)trim(ltrim($csv_data[$field]));
                                        
                                    }
                                    
                                    if($related_product_ids){
                                        
                                        foreach($related_product_ids as $related_product_id){
                                            
                                            $related_product_id = (int)$related_product_id;
                                            
                                            if($related_product_id){
                                                
                                                $new_data_for_db['data']['product_related'][$data_action]['rows'][$related_product_id]['related_id'] = $related_product_id;
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='relate_by_sku'){
                                    
                                    $delimeter = '';
                                    
                                    if($additinal_settings['delimeter']!==''){
                                        
                                        $delimeter = $additinal_settings['delimeter'];
                                        
                                    }
                                    
                                    $related_product_skus = array();
                                    
                                    if($delimeter){
                                        
                                        $related_product_skus = explode($delimeter, trim(ltrim($csv_data[$field])));
                                        
                                    }else{
                                        
                                        $related_product_skus[] = (int)trim(ltrim($csv_data[$field]));
                                        
                                    }
                                    
                                    if($related_product_skus){
                                        
                                        foreach($related_product_skus as $related_product_sku){
                                            
                                            $related_product_id = $this->getIdByField('sku',$related_product_sku, 'product', 'product_id');
                                            
                                            $related_product_id = (int)$related_product_id;
                                            
                                            if($related_product_id){
                                                
                                                $new_data_for_db['data']['product_related'][$data_action]['rows'][$related_product_id]['related_id'] = $related_product_id;
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='option_value_option_microdata_1'){
                                    
                                    $delimiter_2 = '';
                                    
                                    if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                        $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                    }
                                    
                                    if(!$delimiter_2){
                                        
                                        $delimiter_2 = '---';
                                        
                                    }
                                    
                                    $delimeter = '';
                                            
                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }
                                    
                                    if(!$delimeter){
                                        
                                        $delimeter = '|';
                                        
                                    }
                                    
                                    $product_option_value_code = '';
                                            
                                    if(isset($additinal_settings['column_whis_product_option_value_code']) && $additinal_settings['column_whis_product_option_value_code']){

                                        $product_option_value_code = trim(ltrim($additinal_settings['column_whis_product_option_value_code']));

                                    }
                                    
                                    $option_value_code = '';
                                            
                                    if(isset($additinal_settings['column_whis_option_value_code']) && $additinal_settings['column_whis_option_value_code']){

                                        $option_value_code = trim(ltrim($additinal_settings['column_whis_option_value_code']));

                                    }
                                    
                                    $product_option_value_parts_parts = explode($delimiter_2,trim(ltrim($csv_data[$field])));
                                    
                                    foreach($product_option_value_parts_parts as $product_option_value_parts_value){
                                        
                                        $skip_this_option_data = FALSE;
                                        
                                        $product_option_value_parts = explode($delimeter,$product_option_value_parts_value);
                                    
                                        $option_columns = array(
                                            'type',
                                            'option_name',
                                            'option_value_name',
                                            'required',
                                            'quantity',
                                            'subtract',
                                            'price_prefix',
                                            'price_whis_delta',
                                            'points_prefix',
                                            'points',
                                            'weight_prefix',
                                            'weight',
                                            'image',
                                            'option_value_code',
                                            'product_option_value_code'
                                        );

                                        $product_option_value = array();

                                        foreach ($product_option_value_parts as $sin_pisition => $product_option_value_part) {

                                            $product_option_value_part = trim(ltrim($product_option_value_part));

                                            if(isset($option_columns[$sin_pisition])){

                                                $option_column = $option_columns[$sin_pisition];

                                                $product_option_value[$option_column] = $product_option_value_part;

                                            }

                                        }

                                        if(isset($product_option_value['image']) && strstr($product_option_value['image'], '://')){

                                            $image_option_this = $this->getImageByUrlOnImage($product_option_value['image'], $additinal_settings,$general_setting,$log_data);
                                            
                                            $product_option_value['image'] = '';
                                            
                                            if(file_exists(DIR_IMAGE.$image_option_this)){
                                                $product_option_value['image'] = $image_option_this;
                                            }
                                            //file_exists(DIR_IMAGE.$image)
                                            

                                        }
                                        
                                        if(isset($product_option_value['option_value_code']) && $product_option_value['option_value_code']!=='' && $option_value_code===''){
                                            
                                            $skip_this_option_data = TRUE;
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_warning_skip_column_option_value_option_empty')),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }elseif(isset($product_option_value['option_value_code'])){
                                            
                                            $product_option_value['option_value_code'] = array(
                                                'column_name'   => $option_value_code,
                                                'value' => $product_option_value['option_value_code']
                                            );
                                            
                                            
                                            
                                        }
                                        
                                        $product_option_value_code_value = '';
                                        
                                        if(isset($product_option_value['product_option_value_code']) && $product_option_value['product_option_value_code']!=='' && $product_option_value_code===''){
                                            
                                            $skip_this_option_data = TRUE;
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_warning_skip_column_option_value_code_option_empty')),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }elseif(isset($product_option_value['product_option_value_code'])){
                                            
                                            $product_option_value['product_option_value_code'] = array(
                                                'column_name'   => $product_option_value_code,
                                                'value' => $product_option_value['product_option_value_code']
                                            );
                                            
                                            $this->checkColumnTable('product_option_value', $product_option_value_code,'text');
                                            
                                            $product_option_value_code_value = $product_option_value['product_option_value_code']['value'];
                                            
                                        }

                                        if(count($product_option_value)>=3 && !$skip_this_option_data){

                                            $option_id = $this->getOptionIdByNameOrOptionId(0,$product_option_value['option_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                            unset($product_option_value['type']);

                                            unset($product_option_value['option_name']);
                                            
                                            if($option_id){

                                                $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$product_option_value['option_value_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                                if(isset($product_option_value['option_value_code'])){
                                                    unset($product_option_value['option_value_code']);
                                                }
                                                
                                                unset($product_option_value['option_value_name']);

                                                unset($product_option_value['image']);

                                            }

                                            $product_option_value['price_whis_delta'] = $this->getPriceBySettings($product_option_value['price_whis_delta'],$additinal_settings);

                                            if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){

                                                $product_option_value['price'] = $product_option_value['price_whis_delta'];

                                                unset($product_option_value['price_whis_delta']);

                                            }

                                            if($option_id && $option_value_id && !$skip_this_option_data){
                                                
                                                foreach ($product_option_value as $product_option_value_column => $product_option_value_value) {

                                                    /*
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row'][$product_option_value_column] = $product_option_value_value;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_id'] = $option_id;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_value_id'] = $option_value_id;
                                                    */
                                                    
                                                    if($product_option_value_column=='product_option_value_code'){
                                                        
                                                        $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_value['column_name']] = $product_option_value_value;
                                                        
                                                    }else{
                                                    
                                                        $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_column] = $product_option_value_value;
                                                        
                                                    }
                                                    
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value]['option_id'] = $option_id;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value]['option_value_id'] = $option_value_id;

                                                }

                                            }else{

                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),

                                                    'action'    => $log_data['type_process']
                                                );

                                                $this->setLogDataRow($log_write_row,$log_data);

                                            }

                                        }else{

                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_microdata'),  $field)),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);

                                        }
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='option_value_option_microdata_5'){
                                    
                                    $delimeter_1 = '';
                                            
                                    if(isset($additinal_settings['delimeter_1']) && $additinal_settings['delimeter_1']){

                                        $delimeter_1 = trim(ltrim($additinal_settings['delimeter_1']));

                                    }

                                    if($delimeter_1===''){

                                        $delimeter_1 = ';';

                                    }
                                    
                                    $delimeter_2 = '';
                                            
                                    if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                        $delimeter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                    }

                                    if($delimeter_2===''){

                                        $delimeter_2 = '-';

                                    }
                                    
                                    $product_option_value_parts = explode($delimeter_1,trim(ltrim($csv_data[$field])));
                                    
                                    $option_id = 0;
                                    
                                    if(isset($additinal_settings['option_id']) && $additinal_settings['option_id']){

                                        $option_id = $additinal_settings['option_id'];

                                    }
                                    
                                    if($option_id && $product_option_value_parts){
                                        
                                        $product_option_value = array(
                                            'quantity'=>0,
                                            'required'=>0,
                                            'subtract'=>0,
                                            'price_whis_delta'=>0
                                        );
                                        
                                        foreach ($product_option_value_parts as $product_option_value_part) {
                                            
                                            $product_option_value_part_parts = explode($delimeter_2,trim(ltrim($product_option_value_part)));
                                            
                                            $option_value_name = '';
                                            
                                            if(isset($product_option_value_part_parts[0]) && $product_option_value_part_parts[0]){
                                                
                                                $option_value_name = trim(ltrim($product_option_value_part_parts[0]));
                                                
                                            }
                                            
                                            $quantity = 0;
                                            
                                            if(isset($product_option_value_part_parts[1]) && $product_option_value_part_parts[1]){
                                                
                                                $quantity = (int)trim(ltrim($product_option_value_part_parts[1]));
                                                
                                            }
                                            
                                            $subtract = 0;
                                            
                                            if(isset($product_option_value_part_parts[4]) && $product_option_value_part_parts[4]!==''){
                                                
                                                $subtract = (int)trim(ltrim($product_option_value_part_parts[4]));
                                                
                                            }
                                            
                                            if(isset($product_option_value_part_parts[2]) && $product_option_value_part_parts[2]!==''){
                                                
                                                $product_option_value['price_whis_delta'] = $this->getPriceBySettings($product_option_value_part_parts[2],$additinal_settings);
                                                
                                            }
                                            
                                            if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta'] && !isset($product_option_value['price'])){
                                                    
                                                $product_option_value['price'] = $product_option_value['price_whis_delta'];

                                                unset($product_option_value['price_whis_delta']);

                                            }
                                            
                                            $required = 0;
                                            
                                            if(isset($product_option_value_part_parts[3]) && $product_option_value_part_parts[3]!==''){
                                                
                                                $required = (int)trim(ltrim($product_option_value_part_parts[3]));
                                                
                                            }
                                            
                                            
                                            if($option_value_name){
                                                
                                                $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$option_value_name,array('image'=>''),$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                                
                                                foreach($product_option_value as $product_option_value_column => $product_option_value_value){
                                                    
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id][$product_option_value_column] = $product_option_value_value;
                                                    
                                                }
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_id'] = $option_id;
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_value_id'] = $option_value_id;
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['quantity'] = $quantity;
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['required'] = $required;
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['subtract'] = $subtract;
                                                
                                                
                                            }
                                            
                                        }
                                        
                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>"Не получено значение опции, или не задана опция в ".$field),

                                            'action'    => $log_data['type_process']
                                        );

                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='option_value_option_microdata_2'){
                                    
                                    $delimiter_2 = '';
                                    
                                    if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                        $delimiter_2 = trim(ltrim($additinal_settings['delimiter_2']));

                                    }
                                    
                                    if(!$delimiter_2){
                                        
                                        $delimiter_2 = '---';
                                        
                                    }
                                    
                                    $delimeter = '';
                                            
                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }
                                    
                                    if(!$delimeter){
                                        
                                        $delimeter = '|';
                                        
                                    }
                                    
                                    $product_option_value_code = '';
                                            
                                    if(isset($additinal_settings['column_whis_product_option_value_code']) && $additinal_settings['column_whis_product_option_value_code']){

                                        $product_option_value_code = trim(ltrim($additinal_settings['column_whis_product_option_value_code']));

                                    }
                                    
                                    $option_value_code = '';
                                            
                                    if(isset($additinal_settings['column_whis_option_value_code']) && $additinal_settings['column_whis_option_value_code']){

                                        $option_value_code = trim(ltrim($additinal_settings['column_whis_option_value_code']));

                                    }
                                    
                                    $product_option_value_parts_parts = explode($delimiter_2,trim(ltrim($csv_data[$field])));
                                    
                                    foreach($product_option_value_parts_parts as $product_option_value_parts_value){
                                        
                                        $product_option_value_parts = explode($delimeter,$product_option_value_parts_value);
                                        
                                        $option_columns = array(
                                            'option_name',
                                            'option_value_name',
                                            'price_prefix',
                                            'price_whis_delta',
                                            'quantity',
                                            'image',
                                            'type',
                                            'option_value_code',
                                            'product_option_value_code'
                                        );

                                        $product_option_value = array();
                                        
                                        

                                        foreach ($product_option_value_parts as $sin_pisition => $product_option_value_part) {

                                            $product_option_value_part = trim(ltrim($product_option_value_part));

                                            if(isset($option_columns[$sin_pisition])){
                                                $option_column = $option_columns[$sin_pisition];
                                                $product_option_value[$option_column] = $product_option_value_part;
                                            }

                                        }
                                        
                                        
                                        
                                        if(isset($product_option_value['option_value_code']) && $option_value_code===''){
                                            
                                            $skip_this_option_data = TRUE;
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_warning_skip_column_option_value_option_empty')),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }elseif(isset($product_option_value['option_value_code'])){
                                            
                                            $product_option_value['option_value_code'] = array(
                                                'column_name'   => $option_value_code,
                                                'value' => $product_option_value['option_value_code']
                                            );
                                            
                                            
                                            
                                        }
                                        
                                        $product_option_value_code_value = '';
                                        
                                        if(isset($product_option_value['product_option_value_code']) && $product_option_value_code===''){
                                            
                                            $skip_this_option_data = TRUE;
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_warning_skip_column_option_value_code_option_empty')),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }elseif(isset($product_option_value['product_option_value_code'])){
                                            
                                            $product_option_value['product_option_value_code'] = array(
                                                'column_name'   => $product_option_value_code,
                                                'value' => $product_option_value['product_option_value_code']
                                            );
                                            
                                            $this->checkColumnTable('product_option_value', $product_option_value_code,'text');
                                            
                                            $product_option_value_code_value = $product_option_value['product_option_value_code']['value'];
                                            
                                        }

                                        if(count($product_option_value)>=3){

                                            if(!isset($product_option_value['type'])){

                                                $product_option_value['type'] = 'select';

                                            }

                                            $option_id = $this->getOptionIdByNameOrOptionId(0,$product_option_value['option_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                            unset($product_option_value['type']);

                                            unset($product_option_value['option_name']);

                                            if($option_id){

                                                if(!isset($product_option_value['image'])){

                                                    $product_option_value['image'] = '';

                                                }elseif(isset($product_option_value['image']) && strstr($product_option_value['image'], '://')){

                                                    $image_option_this = $this->getImageByUrlOnImage($product_option_value['image'], $additinal_settings,$general_setting,$log_data);
                                                    
                                                    $product_option_value['image'] = '';
                                            
                                                    if(file_exists(DIR_IMAGE.$image_option_this)){
                                                        $product_option_value['image'] = $image_option_this;
                                                    }
                                                    
                                                    

                                                }

                                                $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$product_option_value['option_value_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                                if(isset($product_option_value['option_value_code'])){
                                                    unset($product_option_value['option_value_code']);
                                                }
                                                
                                                unset($product_option_value['option_value_name']);

                                                unset($product_option_value['image']);

                                            }

                                            if(!isset($product_option_value['price_whis_delta'])){

                                                    $product_option_value['price_whis_delta'] = '';

                                            }
                                            
                                            $product_option_value['price_whis_delta'] = $this->getPriceBySettings($product_option_value['price_whis_delta'],$additinal_settings);

                                            if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){

                                                $product_option_value['price'] = $product_option_value['price_whis_delta'];

                                                unset($product_option_value['price_whis_delta']);

                                            }

                                            if($option_id && $option_value_id){

                                                foreach ($product_option_value as $product_option_value_column => $product_option_value_value) {
                                                    /*
                                                     * 
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row'][$product_option_value_column] = $product_option_value_value;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_id'] = $option_id;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_value_id'] = $option_value_id;
                                                     * 
                                                     */
                                                    if($product_option_value_column=='product_option_value_code'){
                                                        
                                                        $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_value['column_name']] = $product_option_value_value;
                                                        
                                                    }else{
                                                    
                                                        $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_column] = $product_option_value_value;
                                                        
                                                    }
                                                    
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value]['option_id'] = $option_id;
                                                    $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value]['option_value_id'] = $option_value_id;

                                                }

                                                $option_columns = array(
                                                    'required'=>0,
                                                    'quantity'=>100,
                                                    'subtract'=>0,
                                                    'points_prefix'=>'+',
                                                    'points'=>0,
                                                    'weight_prefix'=>'+',
                                                    'weight'=>0
                                                );

                                                foreach ($option_columns as $product_option_value_column => $product_option_value_default_value) {
                                                    /*
                                                    if(!isset($new_data_for_db['data']['product_option_value'][$data_action]['row'][$product_option_value_column])){

                                                        $new_data_for_db['data']['product_option_value'][$data_action]['row'][$product_option_value_column] = $product_option_value_default_value;

                                                    }
                                                     * 
                                                     */
                                                    if(!isset($new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_column])){

                                                        $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id.'-'.$product_option_value_code_value][$product_option_value_column] = $product_option_value_default_value;

                                                    }

                                                }

                                            }else{

                                                $log_data['__line__'] = __LINE__; 

                                                $log_write_row = array(
                                                    'log_data' => $log_data,
                                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),

                                                    'action'    => $log_data['type_process']
                                                );

                                                $this->setLogDataRow($log_write_row,$log_data);

                                            }

                                        }else{

                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_microdata'),  $field)),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);

                                        }
                                        
                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='option_value_option_value_name'){
                                    
                                    $product_option_value = array(
                                        'required'=>0,
                                        'quantity'=>0,
                                        'subtract'=>0,
                                        'price_prefix'=>'+',
                                        'price_whis_delta'=>0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0,
                                        'image'=>'',
                                    );
                                    
                                    $product_option_value['option_value_name'] = trim(ltrim($csv_data[$field]));
                                    
                                    $type_option_id = explode('___', $additinal_settings['option_id']);
                                        
                                        $option_id = 0;
                                        
                                        $option_value_id = 0;
                                        
                                        $option_name = '';
                                        
                                        if(isset($type_option_id[0]) && $type_option_id[0]=='field_this_file' && isset($csv_data[$type_option_id[1]]) && $csv_data[$type_option_id[1]]){
                                            
                                            $option_name = trim(ltrim($csv_data[$type_option_id[1]]));
                                            
                                        }else{
                                            
                                            $option_id = (int)$additinal_settings['option_id'];
                                            
                                        }
                                    
                                    $option_id = $this->getOptionIdByNameOrOptionId($option_id,$option_name,array(),$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                    
                                    if($option_id && $product_option_value['option_value_name']){
                                        
                                        if(isset($product_option_value['image']) && strstr($product_option_value['image'], '://')){
                                        
                                            $image_option_this = $this->getImageByUrlOnImage($product_option_value['image'], $additinal_settings,$general_setting,$log_data);
                                                    
                                            $product_option_value['image'] = '';

                                            if(file_exists(DIR_IMAGE.$image_option_this)){
                                                $product_option_value['image'] = $image_option_this;
                                            }
                                            

                                        }

                                        $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$product_option_value['option_value_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                        unset($product_option_value['option_value_name']);

                                        unset($product_option_value['image']);

                                    }
                                    
                                    if($option_id && $option_value_id){
                                        
                                        if($additinal_settings['quantity_default'] && $additinal_settings['quantity_default']){
                                            
                                            if(stristr($additinal_settings['quantity_default'], 'field_this_file___')){
                                                
                                                $quantity_default_parts = explode('___', $additinal_settings['quantity_default']);
                                                
                                                if(isset($quantity_default_parts[0]) && $quantity_default_parts[0]=='field_this_file' && isset($csv_data[$quantity_default_parts[1]])){

                                                    $product_option_value['quantity'] = trim(ltrim($csv_data[$quantity_default_parts[1]]));

                                                }
                                                
                                            }else{
                                                
                                                $product_option_value['quantity'] = (int)$additinal_settings['quantity_default'];
                                                
                                            }
                                            
                                        }
                                        
                                        if($additinal_settings['price_default'] && $additinal_settings['price_default']){
                                            
                                            if(stristr($additinal_settings['price_default'], 'field_this_file___')){
                                                
                                                $price_default_parts = explode('___', $additinal_settings['price_default']);
                                                
                                                if(isset($price_default_parts[0]) && $price_default_parts[0]=='field_this_file' && isset($csv_data[$price_default_parts[1]])){

                                                    $product_option_value['price_whis_delta'] = $this->getFloat(trim(ltrim($csv_data[$price_default_parts[1]])));

                                                }
                                                
                                            }else{
                                                
                                                $product_option_value['price_whis_delta'] = (float)$additinal_settings['price_default'];
                                                
                                            }
                                            
                                        }
                                    
                                        $product_option_value['price_whis_delta'] = $this->getPriceBySettings($product_option_value['price_whis_delta'],$additinal_settings);
                                        /*
                                        if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                            $product_option_value['price_whis_delta'] *= $this->getFloat($additinal_settings['price_rate']);

                                        }

                                        if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                            $product_option_value['price_whis_delta'] *= $this->getFloat($additinal_settings['price_delta']);

                                        }

                                        if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                            $product_option_value['price_whis_delta'] = round($product_option_value['price_whis_delta'],0);

                                        }
                                        */
                                        /*
                                         * Если стоимость только разнице
                                         */
                                        if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){
                                            
                                            $product_option_value['price'] = $product_option_value['price_whis_delta'];
                                            
                                            unset($product_option_value['price_whis_delta']);

                                        }
                                        
                                        $product_option_value['required'] = (int)$additinal_settings['required_default'];
                                        
                                        $product_option_value['subtract'] = (int)$additinal_settings['subtract_default'];
                                        
                                        foreach ($product_option_value as $product_option_value_column => $product_option_value_value) {

                                            //$new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id][$product_option_value_column] = $product_option_value_value;
                                            
                                            
                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id][$product_option_value_column] = $product_option_value_value;
                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_id'] = $option_id;
                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_value_id'] = $option_value_id;

                                        }

                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='assortiment_values_by_article'){
                                    
                                    $product_option_value = array(
                                        'option_name'=>'',
                                        'option_value_name'=>'',
                                        'required'=>0,
                                        'quantity'=>0,
                                        'subtract'=>0,
                                        'price_prefix'=>'+',
                                        'price_whis_delta'=>0.0,
                                        'price'=>0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0,
                                        'image'=>'',
                                    );
                                    
                                    $product_assortiment = array(
                                        'product_assortiment_id'=>0,
                                        'ean'=>'',
                                        'model'=>'',
                                        'jan'=>'',
                                        'upc'=>'',
                                        'isbn'=>'',
                                        'mpn'=>'',
                                        'sku'=>'',
                                        'quantity'=>0,
                                        'required'=>0,
                                        'subtract'=>0,
                                        //'recommended_price'=>'',
                                        //'purchase_price'=>''
                                    );
                                    
                                    $product_assortiment_value = array(
                                        'option_value_id'=>0,
                                        'product_assortiment_value_id'=>0,
                                        'product_option_value_id'=>0,
                                        'product_option_id'=>0,
                                        'option_id'=>0,
                                        'price_prefix'=>'+',
                                        'price'=>-0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0.0
                                    );
                                    
                                    $product_assortiment_name_article = $additinal_settings['product_assortiment_name_article'];
                                    
                                    if(!$product_assortiment_name_article){
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_product_assortiment_name_article_empty'),  $field)),
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                    $product_assortiment_this = array();
                                    
                                    for($i=1;$i<6;$i++){
                                        
                                        $product_assortiment_value_this = array();
                                        
                                        $option_id_for_field = $additinal_settings['option_id_for_field_'.$i];

                                        $option_id = 0;

                                        $option_name = '';
                                        
                                        $type_option_id = explode('___', $option_id_for_field);

                                        if(isset($type_option_id[0]) && $type_option_id[0]=='field_this_file' && isset($csv_data[$type_option_id[1]]) && $csv_data[$type_option_id[1]]){

                                            $option_name = trim(ltrim($csv_data[$type_option_id[1]]));

                                        }else{

                                            $option_id = (int)$option_id_for_field;

                                        }
                                        
                                        $option_value_id = 0;
                                        
                                        $option_value_name_or_microdata_3 = '';
                                        
                                        $option_value_name_field = $additinal_settings['option_value_name_field_'.$i];
                                        
                                        $type_option_value_name = explode('___', $option_value_name_field);

                                        if(isset($type_option_value_name[0]) && $type_option_value_name[0]=='field_this_file' && isset($csv_data[$type_option_value_name[1]]) && $csv_data[$type_option_value_name[1]]){

                                            $option_value_name_or_microdata_3 = trim(ltrim($csv_data[$type_option_value_name[1]]));

                                        }
                                        
                                        /*
                                         * Проверяем на микроразметку, если нет то берем, как название
                                         */

                                        $delimeter = '|';

                                        $product_option_value_parts = explode($delimeter,$option_value_name_or_microdata_3);
                                         
                                        if($product_option_value_parts && count($product_option_value_parts)>3){
                                            
                                            $option_columns = array(
                                                'option_name',
                                                'option_value_name',
                                                'image',
                                                'type'
                                            );

                                            foreach ($product_option_value_parts as $sin_pisition => $product_option_value_part) {

                                                $product_option_value_part = trim(ltrim($product_option_value_part));

                                                $option_column = $option_columns[$sin_pisition];

                                                $product_option_value[$option_column] = $product_option_value_part;
                                                
                                                if($option_column=='image'){
                                                    
                                                    $image_option = $this->getImages($product_option_value_part,$general_setting,$additinal_settings, 'add_values',$log_data);
                                                    
                                                    $product_option_value[$option_column] = end($image_option);
                                                    
                                                }

                                            }
                                            
                                            $option_id = $this->getOptionIdByNameOrOptionId(0,$product_option_value['option_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                            
                                            
                                            $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$product_option_value['option_value_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                            
                                        }
                                        
                                        
                                        
                                        elseif($option_id && $option_value_name_or_microdata_3){
                                            $product_option_value['image'] = '';
                                            $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$option_value_name_or_microdata_3,$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                            
                                        }
                                        
                                        if(!$option_value_id || !$option_id){
                                            
                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        }else{
                                            
                                            $product_assortiment_value_this['option_id'] = $option_id;
                                            
                                            $product_assortiment_value_this['option_value_id'] = $option_value_id;
                                            
                                            if($additinal_settings['quantity_default'] && $additinal_settings['quantity_default']){
                                            
                                                if(stristr($additinal_settings['quantity_default'], 'field_this_file___')){

                                                    $quantity_default_parts = explode('___', $additinal_settings['quantity_default']);

                                                    if(isset($quantity_default_parts[0]) && $quantity_default_parts[0]=='field_this_file' && isset($csv_data[$quantity_default_parts[1]])){

                                                        $product_assortiment_this['quantity'] = trim(ltrim($csv_data[$quantity_default_parts[1]]));

                                                    }

                                                }else{

                                                    $product_assortiment_this['quantity'] = (int)$additinal_settings['quantity_default'];

                                                }

                                            }else{

                                                    $product_assortiment_this['quantity'] = (int)$additinal_settings['quantity_default'];

                                            }
                                            
                                            if(isset($additinal_settings['price_purchase_price'])){
                                                
                                                $product_assortiment_value_this['purchase_price'] = (float)$additinal_settings['price_purchase_price'];
                                                
                                            }
                                            
                                            if(isset($additinal_settings['price_rrp'])){
                                                
                                                $product_assortiment_value_this['recommended_price'] = (float)$additinal_settings['price_rrp'];
                                                
                                            }
                                            
                                            /*
                                             * Цену пока только у первой
                                             */
                                            
                                            if($i==1){
                                            
                                                        if($additinal_settings['price_default'] && $additinal_settings['price_default']){

                                                            if(stristr($additinal_settings['price_default'], 'field_this_file___')){

                                                                $price_default_parts = explode('___', $additinal_settings['price_default']);

                                                                if(isset($price_default_parts[0]) && $price_default_parts[0]=='field_this_file' && isset($csv_data[$price_default_parts[1]])){

                                                                    $product_assortiment_value_this['price_whis_delta'] = $this->getFloat(trim(ltrim($csv_data[$price_default_parts[1]])));

                                                                }

                                                            }else{

                                                                $product_assortiment_value_this['price_whis_delta'] = (float)$additinal_settings['price_default'];

                                                            }

                                                        }else{

                                                            $product_assortiment_value_this['price_whis_delta'] = (float)$additinal_settings['price_default'];

                                                        }

                                                        $product_assortiment_value_this['price_whis_delta'] = $this->getPriceBySettings($product_assortiment_value_this['price_whis_delta'],$additinal_settings);
                                                    /*    
                                                        if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                                            $product_assortiment_value_this['price_whis_delta'] *= $this->getFloat($additinal_settings['price_rate']);

                                                        }

                                                        if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                                            $product_assortiment_value_this['price_whis_delta'] *= $this->getFloat($additinal_settings['price_delta']);

                                                        }

                                                        if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                                            $product_assortiment_value_this['price_whis_delta'] = round($product_assortiment_value_this['price_whis_delta'],0);

                                                        }
*/

                                                        /*
                                                         * Если стоимость только разнице
                                                         */
                                                        if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){

                                                            $product_assortiment_value_this['price'] = $product_assortiment_value_this['price_whis_delta'];

                                                            unset($product_assortiment_value_this['price_whis_delta']);

                                                        }
                                            
                                            }
                                            
                                            $product_assortiment_this['required'] = (int)$additinal_settings['required_default'];

                                            $product_assortiment_this['subtract'] = (int)$additinal_settings['subtract_default'];
                                            
                                            foreach ($product_assortiment_value as $product_assortiment_value_column => $product_assortiment_value_default_value) {
                                                
                                                if(!isset($product_assortiment_value_this[$product_assortiment_value_column])){
                                                    
                                                    $product_assortiment_value_this[$product_assortiment_value_column] = $product_assortiment_value_default_value;
                                                    
                                                }
                                                
                                            }
                                            
                                            foreach ($product_assortiment as $product_assortiment_column => $product_assortiment_default_value) {
                                                
                                                if($product_assortiment_column==$product_assortiment_name_article){
                                                    
                                                    $product_assortiment_this[$product_assortiment_column] = trim(ltrim($csv_data[$field]));
                                                    
                                                }elseif(!isset($product_assortiment_this[$product_assortiment_column])){
                                                    
                                                    $product_assortiment_this[$product_assortiment_column] = $product_assortiment_default_value;
                                                    
                                                }
                                                
                                            }
                                            
                                        }
                                        
                                        if($product_assortiment_value_this){
                                            
                                            $product_assortiment_this['product_assortiment_value'][] = $product_assortiment_value_this;
                                            
                                        }
                                        
                                    }
                                    
                                    if($product_assortiment_this){
                                        
                                        $new_data_for_db['data']['product_assortiment_value'][$data_action]['row'] = $product_assortiment_this;

                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='assortiment_price_and_quantity_by_article'){
                                    
                                    $product_option_value = array(
                                        'option_name'=>'',
                                        'option_value_name'=>'',
                                        'required'=>0,
                                        'quantity'=>0,
                                        'subtract'=>0,
                                        'price_prefix'=>'+',
                                        'price_whis_delta'=>0.0,
                                        'price'=>0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0,
                                        'image'=>'',
                                    );
                                    
                                    $product_assortiment = array(
                                        'product_assortiment_id'=>0,
                                        'ean'=>'',
                                        'model'=>'',
                                        'jan'=>'',
                                        'upc'=>'',
                                        'isbn'=>'',
                                        'mpn'=>'',
                                        'sku'=>'',
                                        'quantity'=>0,
                                        'required'=>0,
                                        'subtract'=>0,
                                        //'recommended_price'=>'',
                                        //'purchase_price'=>''
                                    );
                                    
                                    $product_assortiment_value = array(
                                        'option_value_id'=>0,
                                        'product_assortiment_value_id'=>0,
                                        'product_option_value_id'=>0,
                                        'product_option_id'=>0,
                                        'option_id'=>0,
                                        'price_prefix'=>'+',
                                        'price'=>-0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0.0
                                    );
                                    
                                    $product_assortiment_this = array();
                                    
                                    $product_assortiment_name_article = $additinal_settings['product_assortiment_name_article'];
                                    
                                    if(!$product_assortiment_name_article){
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_product_assortiment_name_article_empty'),  $field)),
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }else{
                                        
                                        $product_assortiment_value_this = array();

                                        $option_value_id = 0;

                                        $option_value_name = '';

                                        $option_value_name_field = $additinal_settings['option_value_name_field_1'];

                                        $type_option_value_name = explode('___', $option_value_name_field);

                                        if(isset($type_option_value_name[0]) && $type_option_value_name[0]=='field_this_file' && isset($csv_data[$type_option_value_name[1]]) && $csv_data[$type_option_value_name[1]]){

                                            $option_value_name = trim(ltrim($csv_data[$type_option_value_name[1]]));

                                        }

                                        if($option_value_name){
                                            
                                            $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$option_value_name_or_microdata_3,$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');

                                        }

                                        if(!$option_value_id || !$option_id){

                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),

                                                'action'    => $log_data['type_process']
                                            );

                                            $this->setLogDataRow($log_write_row,$log_data);

                                        }else{

                                            $product_assortiment_value_this['option_id'] = $option_id;

                                            $product_assortiment_value_this['option_value_id'] = $option_value_id;

                                            if($additinal_settings['quantity_default'] && $additinal_settings['quantity_default']){

                                                if(stristr($additinal_settings['quantity_default'], 'field_this_file___')){

                                                    $quantity_default_parts = explode('___', $additinal_settings['quantity_default']);

                                                    if(isset($quantity_default_parts[0]) && $quantity_default_parts[0]=='field_this_file' && isset($csv_data[$quantity_default_parts[1]])){

                                                        $product_assortiment_this['quantity'] = trim(ltrim($csv_data[$quantity_default_parts[1]]));

                                                    }

                                                }else{

                                                    $product_assortiment_this['quantity'] = (int)$additinal_settings['quantity_default'];

                                                }

                                            }else{

                                                    $product_assortiment_this['quantity'] = (int)$additinal_settings['quantity_default'];

                                            }

                                            if(isset($additinal_settings['price_purchase_price'])){

                                                $product_assortiment_value_this['purchase_price'] = (float)$additinal_settings['price_purchase_price'];

                                            }

                                            if(isset($additinal_settings['price_rrp'])){

                                                $product_assortiment_value_this['recommended_price'] = (float)$additinal_settings['price_rrp'];

                                            }

                                            /*
                                             * Цену пока только у первой
                                             */

                                            if($i==1){

                                                        if($additinal_settings['price_default'] && $additinal_settings['price_default']){

                                                            if(stristr($additinal_settings['price_default'], 'field_this_file___')){

                                                                $price_default_parts = explode('___', $additinal_settings['price_default']);

                                                                if(isset($price_default_parts[0]) && $price_default_parts[0]=='field_this_file' && isset($csv_data[$price_default_parts[1]])){

                                                                    $product_assortiment_value_this['price_whis_delta'] = $this->getFloat(trim(ltrim($csv_data[$price_default_parts[1]])));

                                                                }

                                                            }else{

                                                                $product_assortiment_value_this['price_whis_delta'] = (float)$additinal_settings['price_default'];

                                                            }

                                                        }else{

                                                            $product_assortiment_value_this['price_whis_delta'] = (float)$additinal_settings['price_default'];

                                                        }

                                                        $product_assortiment_value_this['price_whis_delta'] = $this->getPriceBySettings($product_assortiment_value_this['price_whis_delta'],$additinal_settings);
                                                    /*    
                                                        if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                                            $product_assortiment_value_this['price_whis_delta'] *= $this->getFloat($additinal_settings['price_rate']);

                                                        }

                                                        if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                                            $product_assortiment_value_this['price_whis_delta'] *= $this->getFloat($additinal_settings['price_delta']);

                                                        }

                                                        if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                                            $product_assortiment_value_this['price_whis_delta'] = round($product_assortiment_value_this['price_whis_delta'],0);

                                                        }
*/

                                                        /*
                                                         * Если стоимость только разнице
                                                         */
                                                        if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){

                                                            $product_assortiment_value_this['price'] = $product_assortiment_value_this['price_whis_delta'];

                                                            unset($product_assortiment_value_this['price_whis_delta']);

                                                        }

                                            }

                                            $product_assortiment_this['required'] = (int)$additinal_settings['required_default'];

                                            $product_assortiment_this['subtract'] = (int)$additinal_settings['subtract_default'];

                                            foreach ($product_assortiment_value as $product_assortiment_value_column => $product_assortiment_value_default_value) {

                                                if(!isset($product_assortiment_value_this[$product_assortiment_value_column])){

                                                    $product_assortiment_value_this[$product_assortiment_value_column] = $product_assortiment_value_default_value;

                                                }

                                            }

                                            foreach ($product_assortiment as $product_assortiment_column => $product_assortiment_default_value) {

                                                if($product_assortiment_column==$product_assortiment_name_article){

                                                    $product_assortiment_this[$product_assortiment_column] = trim(ltrim($csv_data[$field]));

                                                }elseif(!isset($product_assortiment_this[$product_assortiment_column])){

                                                    $product_assortiment_this[$product_assortiment_column] = $product_assortiment_default_value;

                                                }

                                            }

                                        }

                                        if($product_assortiment_value_this){

                                            $product_assortiment_this['product_assortiment_value'][] = $product_assortiment_value_this;

                                        }



                                    
                                        
                                    }
                                    
                                    if($product_assortiment_this){
                                        
                                        $new_data_for_db['data']['product_assortiment_value'][$data_action]['row'] = $product_assortiment_this;

                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                }
                                
                                elseif(!$quick_stock_update_mode && $db_column=='option_value_option_microdata_4'){
                                    
                                    $option_id___option_value_id = array();
                                    
                                    if(isset($additinal_settings['option_id___option_value_id']) && $additinal_settings['option_id___option_value_id']){

                                        $option_id___option_value_id = explode('___',trim(ltrim($additinal_settings['option_id___option_value_id'])));

                                    }
                                    
                                    if(isset($option_id___option_value_id[0]) && $option_id___option_value_id[0] && isset($option_id___option_value_id[1]) && $option_id___option_value_id[1]){
                                        
                                        $option_id = (int)$option_id___option_value_id[0];
                                        
                                        $option_value_id = (int)$option_id___option_value_id[1];
                                        
                                    }
                                    
                                    $product_option_value = array(
                                        'required'=>0,
                                        'quantity'=>0,
                                        'subtract'=>0,
                                        'price_prefix'=>'+',
                                        'price_whis_delta'=>0.0,
                                        'points_prefix'=>'+',
                                        'points'=>0,
                                        'weight_prefix'=>'+',
                                        'weight'=>0
                                    );
                                    
                                    $delimeter = '';
                                            
                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '|';

                                    }
                                    
                                    $product_option_parice_and_quantity = explode($delimeter,trim(ltrim($csv_data[$field])));
                                    
                                    if(isset($product_option_parice_and_quantity[0])){
                                        
                                        $product_option_value['price_whis_delta'] = $this->getFloat($product_option_parice_and_quantity[0]);
                                        
                                    }
                                    
                                    if(isset($product_option_parice_and_quantity[1])){
                                        
                                        $product_option_value['quantity'] = (int)$product_option_parice_and_quantity[1];
                                        
                                    }
                                    
                                    if($option_id && $option_value_id){
                                        
                                        if($additinal_settings['quantity_default'] && $additinal_settings['quantity_default']){
                                            
                                            if(stristr($additinal_settings['quantity_default'], 'field_this_file___')){
                                                
                                                $quantity_default_parts = explode('___', $additinal_settings['quantity_default']);
                                                
                                                if(isset($quantity_default_parts[0]) && $quantity_default_parts[0]=='field_this_file' && isset($csv_data[$quantity_default_parts[1]])){

                                                    $product_option_value['quantity'] = trim(ltrim($csv_data[$quantity_default_parts[1]]));

                                                }
                                                
                                            }else{
                                                
                                                $product_option_value['quantity'] = (int)$additinal_settings['quantity_default'];
                                                
                                            }
                                            
                                        }
                                        
                                        $product_option_value['price_whis_delta'] = $this->getPriceBySettings($product_option_value['price_whis_delta'],$additinal_settings);
                                    /*
                                        if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

                                            $product_option_value['price_whis_delta'] *= $this->getFloat($additinal_settings['price_rate']);

                                        }

                                        if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

                                            $product_option_value['price_whis_delta'] *= $this->getFloat($additinal_settings['price_delta']);

                                        }

                                        if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

                                            $product_option_value['price_whis_delta'] = round($product_option_value['price_whis_delta'],0);

                                        }
                                        */
                                        /*
                                         * Если стоимость только разнице
                                         */
                                        if(isset($additinal_settings['price_whis_delta']) && $additinal_settings['price_whis_delta']){
                                            
                                            $product_option_value['price'] = $product_option_value['price_whis_delta'];
                                            
                                            unset($product_option_value['price_whis_delta']);

                                        }
                                        
                                        $product_option_value['required'] = (int)$additinal_settings['required_default'];
                                        
                                        $product_option_value['subtract'] = (int)$additinal_settings['subtract_default'];
                                        
                                        foreach ($product_option_value as $product_option_value_column => $product_option_value_value) {

                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id][$product_option_value_column] = $product_option_value_value;
                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_id'] = $option_id;
                                            $new_data_for_db['data']['product_option_value'][$data_action]['rows'][$option_id.'-'.$option_value_id]['option_value_id'] = $option_value_id;

                                        }

                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                    /*
                                    
                                    $delimeter = '';
                                            
                                    if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){

                                        $delimeter = $additinal_settings['delimeter'];

                                    }

                                    if(!$delimeter){

                                        $delimeter = '|';

                                    }
                                    
                                    $product_option_value_parts = explode($delimeter,trim(ltrim($csv_data[$field])));
                                    
                                    $option_columns = array(
                                        'type',
                                        'option_name',
                                        'option_value_name',
                                        'required',
                                        'quantity',
                                        'subtract',
                                        'price_prefix',
                                        'price',
                                        'points_prefix',
                                        'points',
                                        'weight_prefix',
                                        'weight',
                                        'image',
                                    );
                                    
                                    $product_option_value = array();
                                    
                                    foreach ($product_option_value_parts as $sin_pisition => $product_option_value_part) {
                                        
                                        $product_option_value_part = trim(ltrim($product_option_value_part));
                                        
                                        $option_column = $option_columns[$sin_pisition];
                                            
                                        $product_option_value[$option_column] = $product_option_value_part;
                                        
                                    }
                                    
                                    if(count($product_option_value)==count($option_columns)){
                                        
                                        $option_id = $this->getOptionIdByNameOrOptionId(0,$product_option_value['option_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                        
                                        unset($product_option_value['type']);
                                        
                                        unset($product_option_value['option_name']);
                                        
                                        if($option_id){
                                            
                                            $option_value_id = $this->getOptionValueIdByNameOrOptionValueId($option_id,0,$product_option_value['option_value_name'],$product_option_value,$language_id['value_string'],$general_setting,$additinal_settings,'add_values');
                                            
                                            unset($product_option_value['option_value_name']);
                                            
                                            unset($product_option_value['image']);
                                            
                                        }
                                        
                                        if($option_id && $option_value_id){
                                            
                                            foreach ($product_option_value as $product_option_value_column => $product_option_value_value) {
                                                
                                                $new_data_for_db['data']['product_option_value'][$data_action]['row'][$product_option_value_column] = $product_option_value_value;
                                                $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_id'] = $option_id;
                                                $new_data_for_db['data']['product_option_value'][$data_action]['row']['option_value_id'] = $option_value_id;
                                                
                                            }
                                            
                                        }else{

                                            $log_data['__line__'] = __LINE__; 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_empty'),  $field)),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        }
                                        
                                    }else{

                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_option_value_option_microdata'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                    }
                                    
                                    */
                                    
                                }
                                
                            }
                            
                            else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_isset_csv_row_field'),  $field)),

                                    'action'    => $log_data['type_process']
                                );

                                $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                            /*
                             * Если нужно вставить идентификатор, которого возможно не было
                             */
                            if(!$product_id && isset($identificator_insert[$db_table]) && $identificator_insert[$db_table] && isset($columns[key($identificator_insert[$db_table])])){

                                $new_data_for_db['data'][$db_table]['add_values']['row'][key($identificator_insert[$db_table])] = current($identificator_insert[$db_table]);

                            }elseif(!$product_id && isset($identificator_insert['product_description']) && $identificator_insert['product_description']){

                                $new_data_for_db['data']['product_description']['add_values']['row']['name'] = $identificator_insert['product_description']['name'];

                            }

                        }

                    }
                    
                    $this->setMaxMemoryUsage();
		    
                    if(!$skip && isset($new_data_for_db['data']) && $new_data_for_db['data']){
                        
                        /*
                         * Обмен данными
                         */
                        
                        ksort($new_data_for_db['data']);
                        
                        $id_name = 'product_id';
                        
                        $main_table = 'product';
                        
                        /*
                         * Некоторые колонки относятся к другим таблицам, но идут вместе с дочерней. Чтобы не вырезать жэти колонки из не своих таблиц, делается исключение
                         * Например, в product_option_value нет required, которое создается вручную т.к. добавление опций продукта без этого значения не имеет смысла
                         */
                        $columns_exception = array(
                            'product_option_value___required'=>'required',
                            'product_option_value___price_whis_delta'=>'price_whis_delta',
                            'product_assortiment_value___quantity'=>'quantity',
                            'product_assortiment_value___product_assortiment_value'=>'product_assortiment_value',
                            'product_assortiment_value___required'=>'required',
                            'product_assortiment_value___subtract'=>'subtract',
                            'product_assortiment_value___ean'=>'ean',
                            'product_assortiment_value___model'=>'model',
                            'product_assortiment_value___jan'=>'jan',
                            'product_assortiment_value___upc'=>'upc',
                            'product_assortiment_value___isbn'=>'isbn',
                            'product_assortiment_value___mpn'=>'mpn',
                            'product_assortiment_value___sku'=>'sku'
                        );
                        
                        $result_new_data_log = array();
                        
                        /*
                         * $new_data - если нет идентификатора
                         */
                        $new_data = array();
                        /*
                         * $update_data - если есть основной идентификатор данных
                         * Обновление данных и вставка без удаления аналогичных в релевантных таблицах
                         */
                        $update_data = array();
                        /*
                         * $delete_data - если есть основной идентификатор данных, только в этом случае может что-то удаляться
                         */
			
                        $delete_data = array();
                        
                        $delete_last_data_after_add = array();
                        
                        $quantity_default = '';

                        if(isset($general_setting['quantity_default']) && $general_setting['quantity_default']!==''){

                            $quantity_default = (int)$general_setting['quantity_default'];

                        }
                        
                        $status_enable = '';
                        
                        if(isset($general_setting['status_enable']) && $general_setting['status_enable']!=2){

                            $status_enable = (int)$general_setting['status_enable'];

                        }
                        
                        $seo_url_generator = 0;
                        
                        if(isset($general_setting['seo_url_generator']) && $general_setting['seo_url_generator']){

                            $seo_url_generator = 1;

                        }
                        
                        $dis_by_quan = '';
                        
                        if(isset($general_setting['dis_by_quan']) && $general_setting['dis_by_quan']!==''){

                            $dis_by_quan = (int)$general_setting['dis_by_quan'];

                        }
                        
                        $skip_by_quan = '';
                        
                        if(isset($general_setting['skip_by_quan']) && $general_setting['skip_by_quan']!==''){

                            $skip_by_quan = (int)$general_setting['skip_by_quan'];

                        }
			
			$quick_stock_update_data_this = array();
			
			if($quick_stock_update_mode){
			    
			    if($quantity_default!==''){
				
				$quick_stock_update_data_this['product']['quantity'] = $quantity_default;
				
			    }
			    elseif(isset($new_data_for_db['data']['product']['add_values']['row']['quantity'])){
				
				$quick_stock_update_data_this['product']['quantity'] = $new_data_for_db['data']['product']['add_values']['row']['quantity'];
				
			    }
			    
			    if($status_enable!==''){
				
				$quick_stock_update_data_this['product']['status'] = $status_enable;
				
			    }
			    
			    if(isset($quick_stock_update_data_this['product']['quantity']) && $dis_by_quan!==''){
				
				if($quick_stock_update_data_this['product']['quantity']>=$dis_by_quan){
				    
				    $quick_stock_update_data_this['product']['status'] = 1;
				    
				}else{
				    
				    $quick_stock_update_data_this['product']['status'] = 0;
				    
				}
				
			    }
			    
			    if(isset($new_data_for_db['data']['product']['add_values']['row']['price'])){
				
				$quick_stock_update_data_this['product']['price'] = $new_data_for_db['data']['product']['add_values']['row']['price'];
				
			    }
			    
			    if(isset($new_data_for_db['data']['product_discount']['add_values']['row']['price'])){
				
				$quick_stock_update_data_this['product_discount']['price'] = $new_data_for_db['data']['product_discount']['add_values']['row']['price'];
				
			    }
			    
			    if(isset($new_data_for_db['data']['product_special']['add_values']['row']['price'])){
				
				$quick_stock_update_data_this['product_special']['price'] = $new_data_for_db['data']['product_special']['add_values']['row']['price'];
				
			    }
			    
			    if(${$id_name} && $quick_stock_update_data_this){
				
				$quick_stock_update_data['product_data'][${$id_name}]['where'] = array('product_id'=>${$id_name});// = $quick_stock_update_data_this;
				
				$quick_stock_update_data['product_data'][${$id_name}]['update_data'] = $quick_stock_update_data_this;// = $quick_stock_update_data_this;
				
			    }
			    
			}else{
			    
			    foreach ($new_data_for_db['data'] as $db_table => $data_for_db) {
                            
				/*
				* Только добавляем
				*/

				if(!${$id_name}){

				    if(isset($data_for_db['add_values']['row'])){

					$insert_data_row = array();

					foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {

					    if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						$insert_data_row[$data_for_db_column] = $data_for_db_column_value;

					    }

					}

					if($db_table=='product' && $quantity_default!==''){

					    $insert_data_row['quantity'] = $quantity_default;

					}

					if($db_table=='product' && $status_enable!==''){

					    $insert_data_row['status'] = $status_enable;

					}

					if($db_table=='product' && isset($insert_data_row['quantity']) && $dis_by_quan!=='' && $insert_data_row['quantity'] >= $dis_by_quan){

					    $insert_data_row['status'] = 1;

					}elseif($db_table=='product' && isset($insert_data_row['quantity']) && $dis_by_quan!=='' && $insert_data_row['quantity'] < $dis_by_quan){

					    $insert_data_row['status'] = 0;

					}

					if($db_table=='product' && isset($general_setting['stock_status_id_by_quantity']) && $general_setting['stock_status_id_by_quantity']!=='' && isset($insert_data_row['quantity']) && !$insert_data_row['quantity']){

					    $insert_data_row['stock_status_id'] = $general_setting['stock_status_id_by_quantity'];

					}

					if($db_table=='product' && isset($insert_data_row['quantity']) && isset($skip_by_quan) && $skip_by_quan!=='' && $insert_data_row['quantity'] >= $skip_by_quan){

					    $insert_data_row = array();

					}

					if($insert_data_row){

					    $new_data[$db_table][] = $insert_data_row;

					}

				    }

				    if(isset($data_for_db['add_values']['rows'])){

					foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {

					    $insert_data_row = array();

					    foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {

						if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						    $insert_data_row[$data_for_db_column] = $data_for_db_column_value;

						}

					    }

					    if($insert_data_row){

						$new_data[$db_table][] = $insert_data_row;

					    }

					}

				    }

				}

				else{

				    if(isset($data_for_db['add_values']['row'])){

					$update_data_row = array();

					foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {

					    if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						$update_data_row[$data_for_db_column] = $data_for_db_column_value;

					    }

					}

					if($db_table=='product' && $quantity_default!==''){

					    $update_data_row['quantity'] = $quantity_default;

					}

					if($db_table=='product' && $status_enable!==''){

					    $update_data_row['status'] = $status_enable;

					}

					if($db_table=='product' && isset($update_data_row['quantity']) && $dis_by_quan!=='' && $update_data_row['quantity'] >= $dis_by_quan){

					    $update_data_row['status'] = 1;

					}elseif($db_table=='product' && isset($update_data_row['quantity']) && $dis_by_quan!=='' && $update_data_row['quantity'] < $dis_by_quan){

					    $update_data_row['status'] = 0;

					}

					if($db_table=='product' && isset($general_setting['stock_status_id_by_quantity']) && $general_setting['stock_status_id_by_quantity']!=='' && isset($update_data_row['quantity']) && !$update_data_row['quantity']){

					    $update_data_row['stock_status_id'] = $general_setting['stock_status_id_by_quantity'];

					}

					if($db_table=='product' && isset($update_data_row['quantity']) && isset($skip_by_quan) && $skip_by_quan!=='' && $update_data_row['quantity'] >= $skip_by_quan){

					    $update_data_row = array();

					}

					if($update_data_row){

					    $update_data[$db_table][] = $update_data_row;

					}

				    }

				    if(isset($data_for_db['add_values']['rows'])){

					foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {

					    $update_data_row = array();

					    foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {

						if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						    $update_data_row[$data_for_db_column] = $data_for_db_column_value;

						}

					    }

					    if($update_data_row){

						$update_data[$db_table][] = $update_data_row;

					    }

					}

				    }

				    if(isset($data_for_db['delete_values']['row'])){

					$delete_data_row = array();

					foreach ($data_for_db['delete_values']['row'] as $data_for_db_column => $data_for_db_column_value) {

					    if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						$delete_data_row[$data_for_db_column] = $data_for_db_column_value;

					    }

					}

					if($delete_data_row){

					    $delete_data[$db_table][] = $delete_data_row;

					}

				    }

				    if(isset($data_for_db['delete_values']['rows'])){

					foreach ($data_for_db['delete_values']['rows'] as $data_for_db_column_and_value) {

					    $delete_data_row = array();

					    foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {

						if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						    $delete_data_row[$data_for_db_column] = $data_for_db_column_value;

						}

					    }

					    if($delete_data_row){

						$delete_data[$db_table][] = $delete_data_row;

					    }

					}

				    }

				    if(isset($data_for_db['delete_last_data_after_add']['row'])){

					$delete_last_data_after_add_row = array();

					foreach ($data_for_db['delete_last_data_after_add']['row'] as $data_for_db_column => $data_for_db_column_value) {

					    if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						$delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;

					    }

					}

					if($delete_last_data_after_add_row){

					    $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;

					}

				    }

				    if(isset($data_for_db['delete_last_data_after_add']['rows'])){

					foreach ($data_for_db['delete_last_data_after_add']['rows'] as $data_for_db_column_and_value) {

					    $delete_last_data_after_add_row = array();

					    foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {

						if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){

						    $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;

						}

					    }

					    if($delete_last_data_after_add_row){

						$delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;

					    }

					}

				    }

				}

			    }
			    
			}
                        
                        if($delete_data){
                            
                            $result_new_data = array();

                            $result_new_data_log = array();

                            foreach($delete_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator,TRUE);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name}, TRUE);

                                   }elseif(stristr($db_table, '_related')){

                                        $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                        $this->deleteDataToTable($db_table,'related_id',${$id_name});

                                   }elseif(stristr($db_table, '_attribute')){

                                       $result_new_data[$db_table] = $this->insertProductAttribute($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name}, TRUE);

                                   }elseif(stristr($db_table, '_option_value')){

                                       $result_new_data[$db_table] = $this->insertProductOptionValue($data_for_db,$id_name,${$id_name}, TRUE);

                                   }elseif(stristr($db_table, '_discount')){

                                       $additional_id = array();

                                       if(isset($data_for_db['product_discount_id'])){

                                           $additional_id = array('product_discount_id'=>$data_for_db['product_discount_id']);

                                       }

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id, FALSE, TRUE);

                                   }elseif(stristr($db_table, '_special')){

                                       $additional_id = array();

                                       if(isset($data_for_db['product_special_id'])){

                                           $additional_id = array('product_special_id'=>$data_for_db['product_special_id']);

                                       }

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id, FALSE, TRUE);

                                   }elseif(stristr($db_table, '_to_category')){

                                       $additional_id = array();

                                       if(isset($data_for_db['category_id'])){

                                           $additional_id = array('category_id'=>$data_for_db['category_id']);

                                       }

                                       $result_new_data[$db_table] = $this->deleteDataToTable($db_table, $id_name,${$id_name}, $additional_id);

                                   }elseif(stristr($db_table, '_image')){

                                       $additional_id = array();

                                       if(isset($data_for_db['product_image_id'])){

                                           $additional_id = array('product_image_id'=>$data_for_db['product_image_id']);

                                       }

                                       if(isset($data_for_db['image'])){

                                           $additional_id = array('image'=>$data_for_db['image']);

                                       }

                                       $result_new_data[$db_table] = $this->deleteDataToTable($db_table, $id_name,${$id_name}, $additional_id);

                                   }elseif(stristr($db_table, '_filter')){

                                        $additional_id = array();

                                        if(isset($data_for_db['filter_id'])){

                                            $additional_id = array('filter_id'=>$data_for_db['filter_id']);

                                        }

                                        $result_new_data[$db_table] = $this->deleteDataToTable($db_table, $id_name,${$id_name}, $additional_id);

                                   }else{

                                        $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array(),array(),TRUE);

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row[] = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                        $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                            }

                        }
                        
                        if($delete_last_data_after_add){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($delete_last_data_after_add as $db_table => $data_for_db_rows){
                                
                                /*
                                 * Удаляем аналогичные
                                 */
                                
                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_related')){

                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});
                                       
                                       $this->deleteDataToTable($db_table,'related_id',${$id_name});

                                   }elseif(stristr($db_table, '_attribute')){

                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_option_value')){

                                       $this->deleteDataToTable($main_table.'_option',$id_name,${$id_name});
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});
                                       

                                   }elseif(stristr($db_table, '_assortiment_value')){

                                       $this->deleteDataToTable($main_table.'_assortiment',$id_name,${$id_name});
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});
                                       

                                   }elseif(stristr($db_table, '_discount')){
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_special')){
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_to_category')){
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_image')){
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_filter')){
                                       
                                       $this->deleteDataToTable($db_table,$id_name,${$id_name});

                                   }else{

                                       

                                   }

                                }

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_related')){
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array('related_id'=>$data_for_db['related_id']));

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,array('related_id'=>${$id_name}),$id_name,$data_for_db['related_id'],array('related_id'=>${$id_name}));

                                   }elseif(stristr($db_table, '_attribute')){

                                       $result_new_data[$db_table] = $this->insertProductAttribute($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_option_value')){

                                       $result_new_data[$db_table] = $this->insertProductOptionValue($data_for_db,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_assortiment_value')){

                                       $result_new_data[$db_table] = $this->insertProductAssortimentOptionValue($data_for_db,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_discount')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_discount_id'])){
                                           
                                           $additional_id = array('product_discount_id'=>$data_for_db['product_discount_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_special')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_special_id'])){
                                           
                                           $additional_id = array('product_special_id'=>$data_for_db['product_special_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_to_category')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['category_id'])){
                                           
                                           $additional_id = array('category_id'=>$data_for_db['category_id']);
                                           
                                       }
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_image')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_image_id'])){
                                           
                                           $additional_id = array('product_image_id'=>$data_for_db['product_image_id']);
                                           
                                       }
                                       
                                       if(isset($data_for_db['image'])){
                                           
                                           $additional_id = array('image'=>$data_for_db['image']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_filter')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['filter_id'])){
                                           
                                           $additional_id = array('filter_id'=>$data_for_db['filter_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }else{

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name});

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                        $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                        $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                        }
                        
                        if($new_data){
                            
                            /*
                             * идентификатор появится после первой вставки
                             */
                            
                            $first_table = $main_table;
                            
                            /*
                             * Если данные новые, но не содержат ничего из главной таблицы, то заводится пустышка в главной, для получения id
                             */
                            
                            if(!isset($new_data[$first_table])){
                                
                                $new_data[$first_table][0] = array('price'=>0.0);
                                
                                if(isset($identificator_insert[$first_table])){
                                    
                                    $new_data[$first_table][0][key($identificator_insert[$first_table])] = current($identificator_insert[$first_table]);
                                    
                                }
                                
                                $new_data[$first_table][0]['quantity'] = $quantity_default;
                                
                                if($quantity_default >= $dis_by_quan){
                                        
                                    $new_data[$first_table][0]['status'] = 1;

                                }elseif($quantity_default < $dis_by_quan){

                                    $new_data[$first_table][0]['status'] = 0;

                                }else{
                                    
                                    $new_data[$first_table][0]['status'] = (int)$status_enable;
                                    
                                }
                                
                                ksort($new_data);
                                
                            }
                            
                            if(isset($new_data[$first_table]) && isset($new_data[$first_table][0]['quantity']) && isset($skip_by_quan) && $skip_by_quan!=='' && $new_data[$first_table][0]['quantity'] < $skip_by_quan){

                                $new_data[$first_table][0] = array();

                            }
                            
                            
                            ${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                            
                            if(${$id_name}){
                                           
                                $result_new_data_log['success'][] = $this->getStringFromArray(current($new_data[$first_table]),$first_table.': ');

                           }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                $this->setLogDataRow($log_write_row,$log_data);

                           }
                            /*
                             * Если не появился, дальше ничего не добавить у новых данных
                             */
                            if(${$id_name}){
                                
                                unset($new_data[$first_table]);
                                
                                $result_new_data = array();
                            
                                $result_new_data_log = array();
                                
                                foreach($new_data as $db_table => $data_for_db_rows){

                                    foreach($data_for_db_rows as $data_for_db){

                                        if(stristr($db_table, '_description')){
                                           
                                           $result_new_data[$db_table] = $this->insertDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                       }elseif(stristr($db_table, $this->table_seo_url)){
                                           
                                           $result_new_data[$db_table] = $this->insertUrlAlias($data_for_db,$id_name,${$id_name});

                                       }elseif(stristr($db_table, '_related')){
                                           
                                           $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name});
                                           
                                           $result_new_data[$db_table] = $this->insertDataToTable($db_table,array('related_id'=>${$id_name}),$id_name,$data_for_db['related_id']);

                                       }elseif(stristr($db_table, '_attribute')){
                                           
                                           $result_new_data[$db_table] = $this->insertProductAttribute($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name});

                                       }elseif(stristr($db_table, '_option_value')){
                                           
                                           $result_new_data[$db_table] = $this->insertProductOptionValue($data_for_db,$id_name,${$id_name});

                                       }elseif(stristr($db_table, '_assortiment_value')){
                                           
                                           $result_new_data[$db_table] = $this->insertProductAssortimentOptionValue($data_for_db,$id_name,${$id_name});

                                       }else{
                                           
                                           $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name});
                                           
                                       }
                                       
                                       if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){
                                           
                                            $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }else{
                                           
                                            $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }

                                    }

                                }
                                
                                if($result_new_data_log){
                                    
                                    foreach ($result_new_data_log as $message_result_status => $message_result) {
                                        
                                        if($message_result_status=='success'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('success'=>$this->language->get('import_success_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                                
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                            unset($log_data['details_message']);
                                            
                                        }elseif($message_result_status=='warning'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_error_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                                
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                            unset($log_data['details_message']);
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $log_data['__line__'] = __LINE__; 

                                    $log_write_row = array(
                                        'log_data' => $log_data,
                                        'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                        
                                        'action'    => $log_data['type_process']
                                    );
                                        
                                    $this->setLogDataRow($log_write_row,$log_data);
                                    
                                }
                                
                            }else{
                        
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                        }
                        
                        if($update_data){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
			    
			    if(isset($this->general_setting['product']['delete_attributes_before_import']) && $this->general_setting['product']['delete_attributes_before_import']){

				$this->deleteRelatedDataBefore(array('product_attribute'),array('product_id'=>${$id_name}));

			    }

			    if(isset($this->general_setting['product']['delete_options_before_import']) && $this->general_setting['product']['delete_options_before_import']){

				$this->deleteRelatedDataBefore(array('product_option','product_option_value'),array('product_id'=>${$id_name}));

			    }

			    if(isset($this->general_setting['product']['delete_specials_before_import']) && $this->general_setting['product']['delete_specials_before_import']){

				$this->deleteRelatedDataBefore(array('product_special'),array('product_id'=>${$id_name}));

			    }

			    if(isset($this->general_setting['product']['delete_discounts_before_import']) && $this->general_setting['product']['delete_discounts_before_import']){

				$this->deleteRelatedDataBefore(array('product_discount'),array('product_id'=>${$id_name}));

			    }
			    
			    if(isset($this->general_setting['product']['delete_categories_before_import']) && $this->general_setting['product']['delete_categories_before_import']){

				$this->deleteRelatedDataBefore(array('product_to_category'),array('product_id'=>${$id_name}));

			    }
                                
                            foreach($update_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_related')){

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array('related_id'=>$data_for_db['related_id']));

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,array('related_id'=>${$id_name}),$id_name,$data_for_db['related_id'],array('related_id'=>${$id_name}));

                                   }elseif(stristr($db_table, '_attribute')){

                                       $result_new_data[$db_table] = $this->insertProductAttribute($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_option_value')){

                                       $result_new_data[$db_table] = $this->insertProductOptionValue($data_for_db,$id_name,${$id_name});

                                   }elseif(stristr($db_table, '_assortiment_value')){
                                           
                                           $result_new_data[$db_table] = $this->insertProductAssortimentOptionValue($data_for_db,$id_name,${$id_name});

                                       }elseif(stristr($db_table, '_discount')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_discount_id'])){
                                           
                                           $additional_id = array('product_discount_id'=>$data_for_db['product_discount_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_special')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_special_id'])){
                                           
                                           $additional_id = array('product_special_id'=>$data_for_db['product_special_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_to_category')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['category_id'])){
                                           
                                           $additional_id = array('category_id'=>$data_for_db['category_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_image')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['product_image_id'])){
                                           
                                           $additional_id = array('product_image_id'=>$data_for_db['product_image_id']);
                                           
                                       }
                                       
                                       if(isset($data_for_db['image'])){
                                           
                                           $additional_id = array('image'=>$data_for_db['image']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }elseif(stristr($db_table, '_filter')){
                                       
                                       $additional_id = array();
                                       
                                       if(isset($data_for_db['filter_id'])){
                                           
                                           $additional_id = array('filter_id'=>$data_for_db['filter_id']);
                                           
                                       }
                                       
                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},$additional_id);

                                   }else{

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name});

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                        }
                        
                        if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){
                            
                            $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});
                            
                        }
                        
                        if(isset($general_setting['seller_id']) && $this->showTable('ms_product', DB_PREFIX)){

                            $seller_id = (int)$general_setting['seller_id'];

                            if($seller_id){

                                    $this->db->query(' REPLACE INTO '. DB_PREFIX .'ms_product SET product_id = '.${$id_name}.', seller_id = '.$seller_id.', product_status = 1, product_approved = 1 ');

                            }else{

                                    $this->db->query(' DELETE FROM '. DB_PREFIX .'ms_product WHERE product_id = '.${$id_name}.' AND seller_id = '.$seller_id);

                            }

                        }
                        
                        $this->dataToStore($main_table,$id_name,${$id_name},$store_id['value_array']);
                        
                    }else{
                        
                        $log_data['__line__'] = __LINE__; 

                        $log_write_row = array(
                            'log_data' => $log_data,
                            'message' => array('error'=>$this->language->get('import_error_total_import')),
                            
                            'action'    => $log_data['type_process']
                        );
                        
                        $this->setLogDataRow($log_write_row,$log_data);
                        
                    }
                    $this->setMaxMemoryUsage();
                
                }
                
                elseif($type_data=='category' && !$skip && $csv_data){
                    
                    $id_name = 'category_id';
                        
                    $main_table = 'category';
                    
                    ${$id_name} = 0;
                    
                    $identificator_field_name = '';
                    
                    $identificator_insert = array();
                
                    if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                     
                        foreach ($identificator as $identificator_param) {
                            
                            if($identificator_param['identificator_type'] == 'name'){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table.'_description';
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table, $language_id['value_string']);
                                
                            }elseif($identificator_param['identificator_type'] == 'aid'){
                                
                                $identificator_field_name = $id_name;
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type']){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }
                            
                            if(!${$id_name} && isset($last_data_to_db[$id_name])){
                                
                                ${$id_name} = $last_data_to_db[$id_name];
                                
                            }
                            /*
                             * Уже найден ранее, должен совпадать, иначе ошибка, что колонки идентификатора дают разные строки из базы
                             */
                            elseif(${$id_name} && isset($last_data_to_db[$id_name]) && ${$id_name}!=$last_data_to_db[$id_name]){
                                
                                $skip[${$id_name}] = ${$id_name};
                                
                                $skip[$last_data_to_db[$id_name]] = $last_data_to_db[$id_name];
                
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_identificators'),  $type_data,  implode(', ', $skip),  $type_data)),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                
                                $this->setLogDataRow($log_write_row,$log_data);
                                
                            }
                            
                            if($identificator_param['additinal_settings']['identificator_insert']){
                                    
                                $identificator_insert[$identificator_table][$identificator_field_name] = $identificator_value;

                            }
                            
                        }
                        
                        if($type_change=='only_update_data' && !${$id_name} && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_req_data_to_db'),  $type_data,  $type_data)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }elseif($type_change=='only_new_data' && (${$id_name} || isset($last_data_to_db['dublicates_count'])) && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_req_data_to_db'),  $type_data,  $type_data)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }
                        
                    }
                    
                    if(!$skip){
                            
                        /*
                         * Сначала обновляем или создаем с нуля основные данные
                         */
                        foreach ($column_settings as $field => $setting) {
                            
                            $additinal_settings = array();
                            
                            if($setting['additinal_settings']){
                                
                                $additinal_settings = $setting['additinal_settings'];
                                
                            }
                            
                            $data_action = 'add_values';
                            
                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==2){
                                
                                $data_action = 'delete_values';
                                
                            }elseif(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==3){
                                
                                $data_action = 'delete_last_data_after_add';
                                
                            }
                            
                            /*
                             * Если идентификатора нет, значит уже не обновление, а добавление. Удалять ничего не нужно - все данные новые
                             */
                            
                            if(!${$id_name} && ($type_change=='new_data' || $type_change=='update_data')){
                                
                                $data_action = 'add_values';
                                
                            }

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);
                            
                            /*
                             * Колонки совпадающие с названием таблицы и колонки в этой таблтце добавляем, как есть 
                             */
                            /*
                             * В некоторые таблицы значения добавляются, как массив, там где прямой - row
                             */
                            if(isset($columns[$db_column]) && isset($csv_data[$field])){
                                
                                $new_data_for_db['data'][$db_table][$data_action]['row'][$db_column] = $csv_data[$field];
                                
                            }
                            
                            /*
                             * Расширенные колонки
                             */
                            
                            else{
                                
                                if($db_column=='category_whis_path'){
                                
                                    $delimiter_2 = '';

                                    if(isset($additinal_settings['delimiter_2']) && $additinal_settings['delimiter_2']){

                                        $delimiter_2 = $additinal_settings['delimiter_2'];

                                    }
                                    
                                    if($delimiter_2!==''){
                                        
                                        $categories_parts = explode($delimiter_2,$csv_data[$field]);
                                        
                                        foreach($categories_parts as $categories_part){
                                            
                                            $categories_part = trim($categories_part);
                                            
                                            if($categories_part!==''){
                                                
                                                $categories = $this->getCategoriesIdByPath($categories_part,$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, $data_action, $log_data);
                                                
                                            }
                                            
                                        }
                                        
                                    }else{
                                        
                                        $categories = $this->getCategoriesIdByPath($csv_data[$field],$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, $data_action, $log_data);
                                        
                                    }
                                    
                                    
                                }
                                
                                elseif($db_column=='category_name_and_parent_level'){
                                    
                                    /*
                                     * Ищем другие части пути
                                     */
                                    
                                    $other_category_name_and_parent_level = array();
                                    
                                    foreach($column_settings as $field_tmp => $setting_tmp){
                                        
                                        $db_table___db_column_tmp = explode('___', $setting_tmp['db_table___db_column']);

                                        $db_column_tmp = $db_table___db_column_tmp[1];
                                        
                                        if($db_column_tmp=='category_name_and_parent_level' && $field != $field_tmp){
                                            
                                            if(isset($setting_tmp['additinal_settings']) && isset($csv_data[$field_tmp]) && $setting_tmp['additinal_settings']['parent_level']!==''){
                                                
                                                $other_category_name_and_parent_level[] = array(
                                                    'additinal_settings'    => $setting_tmp['additinal_settings'],
                                                    'value' => trim(ltrim($csv_data[$field_tmp]))
                                                ); 
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    $category_name_and_parent_level = array();
                                    
                                    if(isset($csv_data[$field]) && $additinal_settings['parent_level']!==''){
                                                
                                        $other_category_name_and_parent_level[] = array(
                                            'additinal_settings'    => $additinal_settings,
                                            'value' => trim(ltrim($csv_data[$field]))
                                        );

                                    }
                                    
                                    if($other_category_name_and_parent_level){
                                        
                                        foreach ($other_category_name_and_parent_level as $other_category_name_and_parent_level_row) {
                                            
                                            $category_name_and_parent_level[$other_category_name_and_parent_level_row['additinal_settings']['parent_level']] = $other_category_name_and_parent_level_row;
                                            
                                        }
                                        
                                        ksort($category_name_and_parent_level);
                                        
                                    }
                                    
                                    $category_names_whise_parents = array();
                                    
                                    $category_names = array();
                                    
                                    if($category_name_and_parent_level){
                                        
                                        for($c=0;$c<count($category_name_and_parent_level);$c++){
                                            
                                            if(isset($category_name_and_parent_level[$c]) && $category_name_and_parent_level[$c]['value']){
                                                
                                                $category_names[] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['category_name'] = $category_name_and_parent_level[$c]['value'];
                                                $category_names_whise_parents[$c]['additinal_settings'] = $category_name_and_parent_level[$c]['additinal_settings'];
                                                
                                            }
                                            
                                        }
                                        
                                    }
                                    
                                    $delimiter_tmp = '/';
                                    
                                    $path_whis_categories_name = '';
                                    
                                    if($category_names_whise_parents && count($category_names_whise_parents) == count($category_name_and_parent_level)){
                                        
                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);
                                        
                                    }
                                    /*
                                     * Если одна и парент нуль, то одна категория
                                     */
                                    elseif($category_names_whise_parents && count($category_names_whise_parents) == 1 && isset($category_names_whise_parents[0])){
                                        
                                        $path_whis_categories_name = implode($delimiter_tmp,$category_names);
                                        
                                    }
                                    
                                    $path_whis_parent_categories = '';
                                    
                                    if($additinal_settings['parent_category_id']){
                                        
                                        $parent_categories = $this->getCategories($delimiter_tmp,$language_id['value_string'],$additinal_settings['parent_category_id']);
                                        
                                        if($parent_categories){
                                            
                                            $path_whis_parent_categories = $parent_categories[$additinal_settings['parent_category_id']]['name'].$delimiter_tmp;
                                            
                                        }
                                        
                                    }
                                    
                                    if($path_whis_categories_name){
                                        
                                        $path_whis_categories_name = $path_whis_parent_categories.$path_whis_categories_name;
                                        
                                    }
                                    
                                    if(!$path_whis_categories_name){
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_path_whis_categories_name'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }else{
                                        $additinal_settings['delimeter']=$delimiter_tmp;
                                        
                                        $categories = $this->getCategoriesIdByPath($path_whis_categories_name,$language_id['value_string'],$store_id['value_array'],$general_setting,$additinal_settings, $data_action, $log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif($db_column=='image_advanced' || $db_column=='images'){
                                    
                                    $images = $this->getImages($csv_data[$field],$general_setting,$additinal_settings, 'add_values',$log_data);
                                    
                                    if($images && $db_column=='image_advanced'){
                                        
                                        $new_data_for_db['data'][$db_table][$data_action]['row']['image'] = current($images);
                                        
                                    }elseif($images){
                                        
                                        foreach($images as $image){
                                            
                                            $new_data_for_db['data']['product_image'][$data_action]['rows'][$image]['image'] = $image;
                                            
                                        }
                                        
                                    }else{
                
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_image'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif($db_column=='seo_url'){
                                    
                                    $seo_url = trim($csv_data[$field]);
                                    
                                    if($seo_url){
                                        
                                        $new_data_for_db['data'][$this->table_seo_url][$data_action]['row']['keyword'] = $seo_url;
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_seo_url'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                            }
                            /*
                             * Если нужно вставить идентификатор, которого возможно не было
                             */
                            if(!${$id_name} && isset($identificator_insert[$db_table]) && $identificator_insert[$db_table] && isset($columns[key($identificator_insert[$db_table])])){

                                $new_data_for_db['data'][$db_table]['add_values']['row'][key($identificator_insert[$db_table])] = current($identificator_insert[$db_table]);

                            }

                        }

                    }
                    
                    if(!$skip && isset($new_data_for_db['data']) && $new_data_for_db['data']){
                        
                        /*
                         * Обмен данными
                         */
                        
                        ksort($new_data_for_db['data']);
                        
                        /*
                         * Некоторые колонки относятся к другим таблицам, но идут вместе с дочерней. Чтобы не вырезать жэти колонки из не своих таблиц, делается исключение
                         * Например, в product_option_value нет required, которое создается вручную т.к. добавление опций продукта без этого значения не имеет смысла
                         */
                        $columns_exception = array();
                        
                        $result_new_data_log = array();
                        
                        /*
                         * $new_data - если нет идентификатора
                         */
                        $new_data = array();
                        /*
                         * $update_data - если есть основной идентификатор данных
                         * Обновление данных и вставка без удаления аналогичных в релевантных таблицах
                         */
                        $update_data = array();
                        
                        /*
                         * $delete_data - если есть основной идентификатор данных, только в этом случае может что-то удаляться
                         */
                        $delete_data = array();
                        
                        $delete_last_data_after_add = array();
                        
                        $status_enable = FALSE;
                        
                        if(isset($general_setting['status_enable']) && $general_setting['status_enable']!=2){

                            $status_enable = (int)$general_setting['status_enable'];

                        }
                        
                        $seo_url_generator = 0;
                        
                        if(isset($general_setting['seo_url_generator']) && $general_setting['seo_url_generator']){

                            $seo_url_generator = 1;

                        }
                        
                        foreach ($new_data_for_db['data'] as $db_table => $data_for_db) {
                            
                            /*
                            * Только добавляем
                            */
                            
                            if(!${$id_name}){
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $insert_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $insert_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($insert_data_row){
                                        
                                        $new_data[$db_table][] = $insert_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $insert_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $insert_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($insert_data_row){

                                            $new_data[$db_table][] = $insert_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            else{
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $update_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $update_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($update_data_row){
                                        
                                        $update_data[$db_table][] = $update_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $update_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $update_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($update_data_row){

                                            $update_data[$db_table][] = $update_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['row'])){
                                    
                                    $delete_data_row = array();
                                    
                                    foreach ($data_for_db['delete_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_data_row){
                                        
                                        $delete_data[$db_table][] = $delete_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['rows'])){
                                    
                                    foreach ($data_for_db['delete_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_data_row){

                                            $delete_data[$db_table][] = $delete_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['row'])){
                                    
                                    $delete_last_data_after_add_row = array();
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_last_data_after_add_row){
                                        
                                        $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['rows'])){
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_last_data_after_add_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_last_data_after_add_row){

                                            $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                        if($delete_data){
                            
                            $result_new_data = array();

                            $result_new_data_log = array();

                            foreach($delete_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator,TRUE);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name}, TRUE);

                                   }else{

                                        $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array(),array(),TRUE);

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );

                                            $this->setLogDataRow($log_write_row,$log_data);
                                            
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                            }

                        }
                        
                        if($delete_last_data_after_add){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($delete_last_data_after_add as $db_table => $data_for_db_rows){
                                
                                /*
                                 * Удаляем аналогичные
                                 */
                                
                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_related')){

                                       

                                   }else{

                                       

                                   }

                                }

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                        }
                        
                        if($new_data){
                            
                            /*
                             * идентификатор появится после первой вставки
                             */
                            
                            $first_table = $main_table;
                            
                            /*
                             * Если данные новые, но не содержат ничего из главной таблицы, то заводится пустышка в главной, для получения id
                             */
                            
                            if(!isset($new_data[$first_table])){
                                
                                $new_data[$first_table][0] = array('sort_order'=>0);
                                
                                if(isset($identificator_insert[$first_table])){
                                    
                                    $new_data[$first_table][0][key($identificator_insert[$first_table])] = current($identificator_insert[$first_table]);
                                    
                                }
                                
                                $new_data[$first_table][0]['status'] = (int)$status_enable;
                                
                                ksort($new_data);
                                
                            }
                            
                            ${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                                
                            //${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                            
                            if(${$id_name}){
                                           
                                $result_new_data_log['success'][] = $this->getStringFromArray($new_data[$first_table],$first_table.': ');

                           }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                           }
                            
                            /*
                             * Если не появился, дальше ничего не добавить у новых данных
                             */
                            if(${$id_name}){
                                
                                unset($new_data[$first_table]);
                                
                                $result_new_data = array();
                            
                                $result_new_data_log = array();
                                
                                foreach($new_data as $db_table => $data_for_db_rows){

                                    foreach($data_for_db_rows as $data_for_db){

                                        /*
                                        * Для таблиц описаний
                                        */
                                       
                                       if(stristr($db_table, '_description')){

                                           $result_new_data[$db_table] = $this->insertDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                       }elseif(stristr($db_table, $this->table_seo_url)){
                                           
                                           $result_new_data[$db_table] = $this->insertUrlAlias($data_for_db,$id_name,${$id_name});

                                       }else{
                                           
                                           $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name});
                                           
                                       }
                                       
                                       if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){
                                           
                                            $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }else{
                                           
                                            $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }

                                    }

                                }
                                
                                if($result_new_data_log){
                                    
                                    foreach ($result_new_data_log as $message_result_status => $message_result) {
                                        
                                        if($message_result_status=='success'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('success'=>$this->language->get('import_success_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                                
                                                $this->setLogDataRow($log_write_row,$log_data);
                                            
                                            unset($log_data['details_message']);
                                            
                                        }elseif($message_result_status=='warning'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_error_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            
                                                $this->setLogDataRow($log_write_row,$log_data);
                                            unset($log_data['details_message']);
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $log_data['__line__'] = __LINE__; 

                                    $log_write_row = array(
                                        'log_data' => $log_data,
                                        'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                        
                                        'action'    => $log_data['type_process']
                                    );
                                    $this->setLogDataRow($log_write_row,$log_data);
                                }
                                
                            }else{
                        
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
$this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if($update_data){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($update_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name});

                                   }else{

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name});

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            
                                            $this->setLogDataRow($log_write_row,$log_data);

                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                    $this->setLogDataRow($log_write_row,$log_data);

                            }
                            
                        }
                        
                        if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){
                            
                            $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});
                            
                        }
                        
                        $this->dataToStore($main_table,$id_name,${$id_name},$store_id['value_array']);
                        
                    }else{
                        
                        $log_data['__line__'] = __LINE__; 

                        $log_write_row = array(
                            'log_data' => $log_data,
                            'message' => array('error'=>$this->language->get('import_error_total_import')),
                            
                            'action'    => $log_data['type_process']
                        );
                        
                        $this->setLogDataRow($log_write_row,$log_data);
                        
                    }
                
                }
                
                elseif($type_data=='manufacturer' && !$skip && $csv_data){
                    
                    $id_name = 'manufacturer_id';
                        
                    $main_table = 'manufacturer';
                    
                    ${$id_name} = 0;
                    
                    $identificator_field_name = '';
                    
                    $identificator_insert = array();
                
                    if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                     
                        foreach ($identificator as $identificator_param) {
                            
                            if($identificator_param['identificator_type'] == 'name'){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type'] == 'aid'){
                                
                                $identificator_field_name = $id_name;
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type']){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }
                            
                            if(!${$id_name} && isset($last_data_to_db[$id_name])){
                                
                                ${$id_name} = $last_data_to_db[$id_name];
                                
                            }
                            /*
                             * Уже найден ранее, должен совпадать, иначе ошибка, что колонки идентификатора дают разные строки из базы
                             */
                            elseif(${$id_name} && isset($last_data_to_db[$id_name]) && ${$id_name}!=$last_data_to_db[$id_name]){
                                
                                $skip[${$id_name}] = ${$id_name};
                                
                                $skip[$last_data_to_db[$id_name]] = $last_data_to_db[$id_name];
                
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_identificators'),  $type_data,  implode(', ', $skip),  $type_data)),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                
                                $this->setLogDataRow($log_write_row,$log_data);
                                
                            }
                            
                            if($identificator_param['additinal_settings']['identificator_insert']){
                                    
                                $identificator_insert[$identificator_table][$identificator_field_name] = $identificator_value;

                            }
                            
                        }
                        
                        if($type_change=='only_update_data' && !${$id_name} && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_req_data_to_db'),  $type_data,  $type_data)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }elseif($type_change=='only_new_data' && (${$id_name} || isset($last_data_to_db['dublicates_count'])) && !$skip){
                            
                                $skip = TRUE;

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_req_data_to_db'),  $type_data,  $type_data)),

                                    'action'    => $log_data['type_process']
                                );

                                $this->setLogDataRow($log_write_row,$log_data);

                            }
                        
                    }
                    
                    if(!$skip){
                            
                        /*
                         * Сначала обновляем или создаем с нуля основные данные
                         */
                        foreach ($column_settings as $field => $setting) {
                            
                            $additinal_settings = array();
                            
                            if($setting['additinal_settings']){
                                
                                $additinal_settings = $setting['additinal_settings'];
                                
                            }
                            
                            $data_action = 'add_values';
                            
                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==2){
                                
                                $data_action = 'delete_values';
                                
                            }elseif(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==3){
                                
                                $data_action = 'delete_last_data_after_add';
                                
                            }
                            
                            /*
                             * Если идентификатора нет, значит уже не обновление, а добавление. Удалять ничего не нужно - все данные новые
                             */
                            
                            if(!${$id_name} && ($type_change=='new_data' || $type_change=='update_data')){
                                
                                $data_action = 'add_values';
                                
                            }

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);
                            
                            /*
                             * Колонки совпадающие с названием таблицы и колонки в этой таблтце добавляем, как есть 
                             */
                            /*
                             * В некоторые таблицы значения добавляются, как массив, там где прямой - row
                             */
                            if(isset($columns[$db_column]) && isset($csv_data[$field])){
                                
                                $new_data_for_db['data'][$db_table][$data_action]['row'][$db_column] = $csv_data[$field];
                                
                            }
                            
                            /*
                             * Расширенные колонки
                             */
                            
                            else{
                                
                                if($db_column=='image_advanced' || $db_column=='images'){
                                    
                                    $images = $this->getImages($csv_data[$field],$general_setting,$additinal_settings, 'add_values',$log_data);
                                    
                                    if($images && $db_column=='image_advanced'){
                                        
                                        $new_data_for_db['data'][$db_table][$data_action]['row']['image'] = current($images);
                                        
                                    }elseif($images){
                                        
                                        foreach($images as $image){
                                            
                                            $new_data_for_db['data']['product_image'][$data_action]['rows'][$image]['image'] = $image;
                                            
                                        }
                                        
                                    }else{
                
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_image'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        
                                    }
                                    
                                }
                                
                                elseif($db_column=='seo_url'){
                                    
                                    $seo_url = trim($csv_data[$field]);
                                    
                                    if($seo_url){
                                        
                                        $new_data_for_db['data'][$this->table_seo_url][$data_action]['row']['keyword'] = $seo_url;
                                        
                                    }else{
                                        
                                        $log_data['__line__'] = __LINE__; 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_seo_url'),  $field)),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        $this->setLogDataRow($log_write_row,$log_data);
                                    }
                                    
                                }
                                
                            }
                            /*
                             * Если нужно вставить идентификатор, которого возможно не было
                             */
                            if(!${$id_name} && isset($identificator_insert[$db_table]) && $identificator_insert[$db_table] && isset($columns[key($identificator_insert[$db_table])])){

                                $new_data_for_db['data'][$db_table]['add_values']['row'][key($identificator_insert[$db_table])] = current($identificator_insert[$db_table]);

                            }

                        }

                    }
                    
                    if(!$skip  && isset($new_data_for_db['data'])  && $new_data_for_db['data'] ){
                        
                        /*
                         * Обмен данными
                         */
                        
                        ksort($new_data_for_db['data']);
                        
                        /*
                         * Некоторые колонки относятся к другим таблицам, но идут вместе с дочерней. Чтобы не вырезать жэти колонки из не своих таблиц, делается исключение
                         * Например, в product_option_value нет required, которое создается вручную т.к. добавление опций продукта без этого значения не имеет смысла
                         */
                        $columns_exception = array();
                        
                        $result_new_data_log = array();
                        
                        /*
                         * $new_data - если нет идентификатора
                         */
                        $new_data = array();
                        /*
                         * $update_data - если есть основной идентификатор данных
                         * Обновление данных и вставка без удаления аналогичных в релевантных таблицах
                         */
                        $update_data = array();
                        
                        /*
                         * $delete_data - если есть основной идентификатор данных, только в этом случае может что-то удаляться
                         */
                        $delete_data = array();
                        
                        $delete_last_data_after_add = array();
                        
                        $status_enable = FALSE;
                        
                        if(isset($general_setting['status_enable']) && $general_setting['status_enable']!=2){

                            $status_enable = (int)$general_setting['status_enable'];

                        }
                        
                        $seo_url_generator = 0;
                        
                        if(isset($general_setting['seo_url_generator']) && $general_setting['seo_url_generator']){

                            $seo_url_generator = 1;

                        }
                        
                        foreach ($new_data_for_db['data'] as $db_table => $data_for_db) {
                            
                            /*
                            * Только добавляем
                            */
                            
                            if(!${$id_name}){
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $insert_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $insert_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($insert_data_row){
                                        
                                        $new_data[$db_table][] = $insert_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $insert_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $insert_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($insert_data_row){

                                            $new_data[$db_table][] = $insert_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            else{
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $update_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $update_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($update_data_row){
                                        
                                        $update_data[$db_table][] = $update_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $update_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $update_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($update_data_row){

                                            $update_data[$db_table][] = $update_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['row'])){
                                    
                                    $delete_data_row = array();
                                    
                                    foreach ($data_for_db['delete_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_data_row){
                                        
                                        $delete_data[$db_table][] = $delete_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['rows'])){
                                    
                                    foreach ($data_for_db['delete_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_data_row){

                                            $delete_data[$db_table][] = $delete_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['row'])){
                                    
                                    $delete_last_data_after_add_row = array();
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_last_data_after_add_row){
                                        
                                        $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['rows'])){
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_last_data_after_add_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_last_data_after_add_row){

                                            $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                        if($delete_data){
                            
                            $result_new_data = array();

                            $result_new_data_log = array();

                            foreach($delete_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator,TRUE);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name}, TRUE);

                                   }else{

                                        $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array(),array(),TRUE);

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row= array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
$this->setLogDataRow($log_write_row,$log_data);
                            }

                        }
                        
                        if($delete_last_data_after_add){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($delete_last_data_after_add as $db_table => $data_for_db_rows){
                                
                                /*
                                 * Удаляем аналогичные
                                 */
                                
                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_related')){

                                       

                                   }else{

                                       

                                   }

                                }

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
$this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if($new_data){
                            
                            $first_table = $main_table;
                            
                            /*
                             * Если данные новые, но не содержат ничего из главной таблицы, то заводится пустышка в главной, для получения id
                             */
                            
                            if(!isset($new_data[$first_table])){
                                
                                $new_data[$first_table][0] = array('sort_order'=>0);
                                
                                if(isset($identificator_insert[$first_table])){
                                    
                                    $new_data[$first_table][0][key($identificator_insert[$first_table])] = current($identificator_insert[$first_table]);
                                    
                                }
                                
                                $new_data[$first_table][0]['status'] = (int)$status_enable;
                                
                                ksort($new_data);
                                
                            }
                            
                            ${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                            
                            //${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                            
                            if(${$id_name}){
                                           
                                $result_new_data_log['success'][] = $this->getStringFromArray($new_data[$first_table],$first_table.': ');

                           }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
$this->setLogDataRow($log_write_row,$log_data);
                           }
                            
                            /*
                             * Если не появился, дальше ничего не добавить у новых данных
                             */
                            if(${$id_name}){
                                
                                unset($new_data[$first_table]);
                                
                                $result_new_data = array();
                            
                                $result_new_data_log = array();
                                
                                foreach($new_data as $db_table => $data_for_db_rows){

                                    foreach($data_for_db_rows as $data_for_db){

                                        /*
                                        * Для таблиц описаний
                                        */
                                       
                                       if(stristr($db_table, '_description')){

                                           $result_new_data[$db_table] = $this->insertDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                       }elseif(stristr($db_table, $this->table_seo_url)){
                                           
                                           $result_new_data[$db_table] = $this->insertUrlAlias($data_for_db,$id_name,${$id_name});

                                       }else{
                                           
                                           $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name});
                                           
                                       }
                                       
                                       if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){
                                           
                                            $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }else{
                                           
                                            $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }

                                    }

                                }
                                
                                if($result_new_data_log){
                                    
                                    foreach ($result_new_data_log as $message_result_status => $message_result) {
                                        
                                        if($message_result_status=='success'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('success'=>$this->language->get('import_success_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            unset($log_data['details_message']);
                                            
                                        }elseif($message_result_status=='warning'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_error_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            unset($log_data['details_message']);
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $log_data['__line__'] = __LINE__; 

                                    $log_write_row = array(
                                        'log_data' => $log_data,
                                        'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                        
                                        'action'    => $log_data['type_process']
                                    );
                                    $this->setLogDataRow($log_write_row,$log_data);
                                }
                                
                            }else{
                        
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
$this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if($update_data){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($update_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_description')){

                                       $result_new_data[$db_table] = $this->updateDataToDescriptionTable($db_table,$data_for_db,$language_id['value_string'],$id_name,${$id_name},$seo_url_generator);

                                   }elseif(stristr($db_table, $this->table_seo_url)){

                                       $result_new_data[$db_table] = $this->updateUrlAlias($data_for_db,$id_name,${$id_name});

                                   }else{

                                       $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name});

                                   }

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                $this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){
                            
                            $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});
                            
                        }
                        
                        $this->dataToStore($main_table,$id_name,${$id_name},$store_id['value_array']);
                        
                    }else{
                        
                        $log_data['__line__'] = __LINE__; 

                        $log_write_row = array(
                            'log_data' => $log_data,
                            'message' => array('error'=>$this->language->get('import_error_total_import')),
                            
                            'action'    => $log_data['type_process']
                        );
                        $this->setLogDataRow($log_write_row,$log_data);
                    }
                
                }
                
                elseif($type_data=='review' && !$skip && $csv_data){
                    
                    $id_name = 'review_id';
                        
                    $main_table = 'review';
                    
                    ${$id_name} = 0;
                    
                    $identificator_field_name = '';
                    
                    $identificator_insert = array();
                
                    if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                     
                        foreach ($identificator as $identificator_param) {
                            
                            if($identificator_param['identificator_type'] == 'name'){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type'] == 'aid'){
                                
                                $identificator_field_name = $id_name;
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }elseif($identificator_param['identificator_type']){
                                
                                $identificator_field_name = $identificator_param['identificator_type'];
                    
                                $identificator_value = $csv_data[$identificator_param['field']];
                                
                                $identificator_table = $main_table;
                                
                                $last_data_to_db = $this->getIdByTableAndIdField($identificator_field_name,$identificator_value, $identificator_table);
                                
                            }
                            
                            if(!${$id_name} && isset($last_data_to_db[$id_name])){
                                
                                ${$id_name} = $last_data_to_db[$id_name];
                                
                            }
                            /*
                             * Уже найден ранее, должен совпадать, иначе ошибка, что колонки идентификатора дают разные строки из базы
                             */
                            elseif(${$id_name} && isset($last_data_to_db[$id_name]) && ${$id_name}!=$last_data_to_db[$id_name]){
                                
                                $skip[${$id_name}] = ${$id_name};
                                
                                $skip[$last_data_to_db[$id_name]] = $last_data_to_db[$id_name];
                
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_identificators'),  $type_data,  implode(', ', $skip),  $type_data)),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                
                                $this->setLogDataRow($log_write_row,$log_data);
                                
                            }
                            
                            if($identificator_param['additinal_settings']['identificator_insert']){
                                    
                                $identificator_insert[$identificator_table][$identificator_field_name] = $identificator_value;

                            }
                            
                        }
                        
                        if($type_change=='only_update_data' && !${$id_name} && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_no_req_data_to_db'),  $type_data,  $type_data)),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }elseif($type_change=='only_new_data' && (${$id_name} || isset($last_data_to_db['dublicates_count'])) && !$skip){
                            
                            $skip = TRUE;
                
                            $log_data['__line__'] = __LINE__; 

                            $log_write_row = array(
                                'log_data' => $log_data,
                                'message' => array('warning'=>sprintf($this->language->get('import_warning_skip_column_req_data_to_db'),  $id_name,  ${$id_name})),
                                
                                'action'    => $log_data['type_process']
                            );
                            
                            $this->setLogDataRow($log_write_row,$log_data);
                            
                        }
                        
                    }
                    
                    if(!$skip){
                            
                        /*
                         * Сначала обновляем или создаем с нуля основные данные
                         */
                        foreach ($column_settings as $field => $setting) {
                            
                            $additinal_settings = array();
                            
                            if($setting['additinal_settings']){
                                
                                $additinal_settings = $setting['additinal_settings'];
                                
                            }
                            
                            $data_action = 'add_values';
                            
                            if(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==2){
                                
                                $data_action = 'delete_values';
                                
                            }elseif(isset($additinal_settings['column_request']) && $additinal_settings['column_request']==3){
                                
                                $data_action = 'delete_last_data_after_add';
                                
                            }
                            
                            /*
                             * Если идентификатора нет, значит уже не обновление, а добавление. Удалять ничего не нужно - все данные новые
                             */
                            
                            if(!${$id_name} && ($type_change=='new_data' || $type_change=='update_data')){
                                
                                $data_action = 'add_values';
                                
                            }

                            $db_table___db_column = explode('___', $setting['db_table___db_column']);

                            $db_table = $db_table___db_column[0];

                            $db_column = $db_table___db_column[1];

                            $columns = $this->getColumnsByTable($db_table);
                            
                            /*
                             * Колонки совпадающие с названием таблицы и колонки в этой таблтце добавляем, как есть 
                             */
                            /*
                             * В некоторые таблицы значения добавляются, как массив, там где прямой - row
                             */
                            if(isset($columns[$db_column]) && isset($csv_data[$field])){
                                
                                $new_data_for_db['data'][$db_table][$data_action]['row'][$db_column] = $csv_data[$field];
                                
                            }
                            
                            /*
                             * Расширенные колонки
                             */
                            
                            else{
                                
                                
                                
                            }
                            /*
                             * Если нужно вставить идентификатор, которого возможно не было
                             */
                            if(!${$id_name} && isset($identificator_insert[$db_table]) && $identificator_insert[$db_table] && isset($columns[key($identificator_insert[$db_table])])){

                                $new_data_for_db['data'][$db_table]['add_values']['row'][key($identificator_insert[$db_table])] = current($identificator_insert[$db_table]);

                            }

                        }

                    }
                    
                    
                    
                    if(!$skip  && isset($new_data_for_db['data'])  && $new_data_for_db['data'] ){
                        
                        /*
                         * Обмен данными
                         */
                        
                        ksort($new_data_for_db['data']);
                        
                        /*
                         * Некоторые колонки относятся к другим таблицам, но идут вместе с дочерней. Чтобы не вырезать жэти колонки из не своих таблиц, делается исключение
                         * Например, в product_option_value нет required, которое создается вручную т.к. добавление опций продукта без этого значения не имеет смысла
                         */
                        $columns_exception = array();
                        
                        $result_new_data_log = array();
                        
                        /*
                         * $new_data - если нет идентификатора
                         */
                        $new_data = array();
                        /*
                         * $update_data - если есть основной идентификатор данных
                         * Обновление данных и вставка без удаления аналогичных в релевантных таблицах
                         */
                        $update_data = array();
                        
                        /*
                         * $delete_data - если есть основной идентификатор данных, только в этом случае может что-то удаляться
                         */
                        $delete_data = array();
                        
                        $delete_last_data_after_add = array();
                        
                        $status_enable = FALSE;
                        
                        if(isset($general_setting['status_enable']) && $general_setting['status_enable']!=2){

                            $status_enable = (int)$general_setting['status_enable'];

                        }
                        
                        foreach ($new_data_for_db['data'] as $db_table => $data_for_db) {
                            
                            /*
                            * Только добавляем
                            */
                            
                            if(!${$id_name}){
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $insert_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $insert_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($insert_data_row){
                                        
                                        $new_data[$db_table][] = $insert_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $insert_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $insert_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($insert_data_row){

                                            $new_data[$db_table][] = $insert_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            else{
                                
                                if(isset($data_for_db['add_values']['row'])){
                                    
                                    $update_data_row = array();
                                    
                                    foreach ($data_for_db['add_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $update_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($update_data_row){
                                        
                                        $update_data[$db_table][] = $update_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['add_values']['rows'])){
                                    
                                    foreach ($data_for_db['add_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $update_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $update_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($update_data_row){

                                            $update_data[$db_table][] = $update_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['row'])){
                                    
                                    $delete_data_row = array();
                                    
                                    foreach ($data_for_db['delete_values']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_data_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_data_row){
                                        
                                        $delete_data[$db_table][] = $delete_data_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_values']['rows'])){
                                    
                                    foreach ($data_for_db['delete_values']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_data_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_data_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_data_row){

                                            $delete_data[$db_table][] = $delete_data_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['row'])){
                                    
                                    $delete_last_data_after_add_row = array();
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['row'] as $data_for_db_column => $data_for_db_column_value) {
                                        
                                        if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                            $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;
                                            
                                        }
                                        
                                    }
                                    
                                    if($delete_last_data_after_add_row){
                                        
                                        $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;
                                        
                                    }
                                    
                                }
                                
                                if(isset($data_for_db['delete_last_data_after_add']['rows'])){
                                    
                                    foreach ($data_for_db['delete_last_data_after_add']['rows'] as $data_for_db_column_and_value) {
                                        
                                        $delete_last_data_after_add_row = array();
                                        
                                        foreach ($data_for_db_column_and_value as $data_for_db_column => $data_for_db_column_value) {
                                            
                                            if($this->checkColumnTable($db_table, $data_for_db_column) || (isset($columns_exception[$db_table.'___'.$data_for_db_column]) && $columns_exception[$db_table.'___'.$data_for_db_column]==$data_for_db_column)){
                                            
                                                $delete_last_data_after_add_row[$data_for_db_column] = $data_for_db_column_value;

                                            }
                                            
                                        }
                                        
                                        if($delete_last_data_after_add_row){

                                            $delete_last_data_after_add[$db_table][] = $delete_last_data_after_add_row;

                                        }
                                        
                                    }
                                    
                                }
                                
                            }
                            
                        }
                        
                        if($delete_data){
                            
                            $result_new_data = array();

                            $result_new_data_log = array();

                            foreach($delete_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */
                                    
                                    $data_for_db['status'] = $status_enable;

                                    $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name},array(),array(),TRUE);

                                    if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                         $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                    }else{

                                         $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                    }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row= array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', WAS CLEARED '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    
                                $this->setLogDataRow($log_write_row,$log_data);
                                
                            }

                        }
                        
                        if($delete_last_data_after_add){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($delete_last_data_after_add as $db_table => $data_for_db_rows){
                                
                                /*
                                 * Удаляем аналогичные
                                 */
                                
                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(stristr($db_table, '_related')){

                                       

                                   }else{

                                       

                                   }

                                }

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                   if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                        $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }else{

                                        $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                   }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                        $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE AFTER DELETE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                            $this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if($new_data){
                            
                            /*
                             * идентификатор появится после первой вставки
                             */
                            
                            $first_table = $main_table;
                            
                            /*
                             * Если данные новые, но не содержат ничего из главной таблицы, то заводится пустышка в главной, для получения id
                             */
                            
                            if(!isset($new_data[$first_table])){
                                
                                $new_data[$first_table][0] = array('author'=>0);
                                
                                if(isset($identificator_insert[$first_table])){
                                    
                                    $new_data[$first_table][0][key($identificator_insert[$first_table])] = current($identificator_insert[$first_table]);
                                    
                                }
                                
                                $new_data[$first_table][0]['status'] = (int)$status_enable;
                                
                                ksort($new_data);
                                
                            }
                            
                            
                            ${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                                
                            //${$id_name} = $this->insertNewDataToMainTable($first_table,current($new_data[$first_table]));
                            
                            if(${$id_name}){
                                           
                                $result_new_data_log['success'][] = $this->getStringFromArray(current($new_data[$first_table]),$first_table.': ');

                           }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                $this->setLogDataRow($log_write_row,$log_data);
                           }
                            
                            /*
                             * Если не появился, дальше ничего не добавить у новых данных
                             */
                            if(${$id_name}){
                                
                                unset($new_data[$first_table]);
                                
                                $result_new_data = array();
                            
                                $result_new_data_log = array();
                                
                                foreach($new_data as $db_table => $data_for_db_rows){

                                    foreach($data_for_db_rows as $data_for_db){

                                        $data_for_db['status'] = $status_enable;
                                        
                                        $result_new_data[$db_table] = $this->insertDataToTable($db_table,$data_for_db,$id_name,${$id_name});
                                       
                                       if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){
                                           
                                            $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }else{
                                           
                                            $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');
                                           
                                       }

                                    }

                                }
                                
                                if($result_new_data_log){
                                    
                                    foreach ($result_new_data_log as $message_result_status => $message_result) {
                                        
                                        if($message_result_status=='success'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('success'=>$this->language->get('import_success_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            unset($log_data['details_message']);
                                            
                                        }elseif($message_result_status=='warning'){
                                            
                                            $log_data['__line__'] = __LINE__; 
                                            
                                            $log_data['details_message'] = implode("<br>", $message_result); 

                                            $log_write_row = array(
                                                'log_data' => $log_data,
                                                'message' => array('warning'=>$this->language->get('import_error_total_import').', INSERT AS NEW '.$id_name.'='.${$id_name}."<br>"),
                                                
                                                'action'    => $log_data['type_process']
                                            );
                                            $this->setLogDataRow($log_write_row,$log_data);
                                            unset($log_data['details_message']);
                                            
                                        }
                                        
                                    }
                                    
                                }else{
                                    
                                    $log_data['__line__'] = __LINE__; 

                                    $log_write_row = array(
                                        'log_data' => $log_data,
                                        'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                        
                                        'action'    => $log_data['type_process']
                                    );
                                    $this->setLogDataRow($log_write_row,$log_data);
                                }
                                
                            }else{
                        
                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                    $this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if($update_data){
                            
                            $result_new_data = array();
                            
                            $result_new_data_log = array();
                                
                            foreach($update_data as $db_table => $data_for_db_rows){

                                foreach($data_for_db_rows as $data_for_db){

                                    /*
                                    * Для таблиц описаний
                                    */

                                    $data_for_db['status'] = $status_enable;
                                    
                                    $result_new_data[$db_table] = $this->updateDataToTable($db_table,$data_for_db,$id_name,${$id_name});

                                    if(isset($result_new_data[$db_table]) && $result_new_data[$db_table]){

                                         $result_new_data_log['success'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                    }else{

                                         $result_new_data_log['warning'][] = $this->getStringFromArray($data_for_db,$db_table.': ');

                                    }

                                }

                            }

                            if($result_new_data_log){

                                foreach ($result_new_data_log as $message_result_status => $message_result) {

                                    if($message_result_status=='success'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('success'=>$this->language->get('import_success_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }elseif($message_result_status=='warning'){

                                        $log_data['__line__'] = __LINE__; 

                                        $log_data['details_message'] = implode("<br>", $message_result); 

                                        $log_write_row = array(
                                            'log_data' => $log_data,
                                            'message' => array('warning'=>$this->language->get('import_error_total_import').', UPDATE '.$id_name.'='.${$id_name}."<br>"),
                                            
                                            'action'    => $log_data['type_process']
                                        );
$this->setLogDataRow($log_write_row,$log_data);
                                        unset($log_data['details_message']);

                                    }

                                }

                            }else{

                                $log_data['__line__'] = __LINE__; 

                                $log_write_row = array(
                                    'log_data' => $log_data,
                                    'message' => array('error'=>$this->language->get('import_error_total_import').', '.$id_name.'='.${$id_name}."<br>"),
                                    
                                    'action'    => $log_data['type_process']
                                );
                                $this->setLogDataRow($log_write_row,$log_data);
                            }
                            
                        }
                        
                        if(isset($odmpro_tamplate_data['php_before_row_import']) && $odmpro_tamplate_data['php_before_row_import']!==''){
                            
                            $this->evalPhp($odmpro_tamplate_data['php_before_row_import'],${$id_name});
                            
                        }
                        
                        $this->dataToStore($main_table,$id_name,${$id_name},$store_id['value_array']);
                        
                    }else{
                        
                        $log_data['__line__'] = __LINE__; 

                        $log_write_row = array(
                            'log_data' => $log_data,
                            'message' => array('error'=>$this->language->get('import_error_total_import')),
                            
                            'action'    => $log_data['type_process']
                        );
                        $this->setLogDataRow($log_write_row,$log_data);
                    }
                
                }
                
            }
            
        }
        
        if(isset($this->general_setting) && isset($this->general_setting['ro_enable_status']) && $this->general_setting['ro_enable_status'] && $this->ro && file_exists(DIR_APPLICATION.'model/extension/module/related_options.php') ){

            $loader = $this->registry->get('load');

            $loader->model('extension/module/related_options');

            $model = $this->registry->get('model_extension_module_related_options');

            foreach($this->ro as $product_id_ro => $ro){
                
                if($product_id_ro){
                
                    $model->removeRelatedOptions($product_id_ro);
                    
                    foreach($ro['ro_data'][0]['ro'] as $ro_key => $ro_row){
                        
                        $relatedoptions_id = $product_id_ro.'000'.implode('000', $ro_row['options']);
                        
                        $ro['ro_data'][0]['ro'][$ro_key]['relatedoptions_id'] = (int)$relatedoptions_id;
                        
                    }
                    
                    $model->setROData($product_id_ro,$ro);
                    
                }

            }

        }
        
	if($quick_stock_update_data){
	    
	    $sql_quick_update = array();
	    
	    foreach ($quick_stock_update_data as $entity_type => $entity_data) {
		
		foreach ($entity_data as $id => $values_by_table) {
		    
		    $where = " WHERE `".key($values_by_table['where'])."` = '".  current($values_by_table['where'])."'";
		    
		    $insert_where = ", `".key($values_by_table['where'])."` = '".  current($values_by_table['where'])."'";
		    
		    if($entity_type=='product_data' && isset($this->general_setting['product']['delete_specials_before_import']) && $this->general_setting['product']['delete_specials_before_import']){

			$sql_quick_update[] = "DELETE FROM `" . DB_PREFIX . "product_special` ".$where.";";

		    }

		    if($entity_type=='product_data' && isset($this->general_setting['product']['delete_discounts_before_import']) && $this->general_setting['product']['delete_discounts_before_import']){
			
			$sql_quick_update[] = "DELETE FROM `" . DB_PREFIX . "product_discount` ".$where.";";

		    }
		    
		    foreach($values_by_table['update_data'] as $table => $values){
		
			$sql_quick_update_this = array();
			
			foreach ($values as $column => $value) {
			    
			    $sql_quick_update_this[] = " `".$column."` = '".$this->db->escape($value)."' ";
			    
			}
			
			if($sql_quick_update_this && $table!=='product_discount' && $table!=='product_special'){
			    
			    $sql_quick_update[] = "UPDATE `" . DB_PREFIX .$table."` SET ".implode(',',$sql_quick_update_this).$where.";";
			    
			}
			elseif($sql_quick_update_this && ($table=='product_discount' || $table=='product_special')){
			    
			    $sql_quick_update[] = "INSERT INTO `" . DB_PREFIX .$table."` SET ".implode(',',$sql_quick_update_this).$insert_where.";";
			    
			}
			
		    }
		    
		}
		
	    }
	    
	    if($sql_quick_update){
		
		foreach($sql_quick_update as $sql_quick_update_query){
		    
		    $this->db->query($sql_quick_update_query);
		    
		}
		
	    }
	    
	}
	
        $this->repairCategories();
        
        $this->writeLogDataRows();
        
        $result['max_memory_usage'] = $this->max_memory_usage;
        
        if(isset($this->temp['process_log_data']) && $this->temp['process_log_data']){
            
            $h = fopen($process_log_file, 'w+');
            
            fwrite($h, json_encode($this->temp['process_log_data']));
            
            fclose($h);
            
        }
        
        $result['result_demo'] = '';
        
        if(isset($this->temp['get_result_demo']) && !is_null($this->temp['get_result_demo'])){
            
            if(!$this->temp['get_result_demo']){
                
                $result['result_demo'] = "Данные для импорта не были получены";
                
            }
            else{
                
                $result['result_demo'] = $this->temp['get_result_demo'];
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getIdByTableAndIdField($field, $value, $table, $language_id=0, $where='', $return_value='') {
        
        $result = FALSE;

        if($this->showTable($table, DB_PREFIX)){

            $sql = "SELECT * FROM `".DB_PREFIX.$table."` WHERE `".$field."`= '" . $this->db->escape($value) . "' ";

            if($language_id){

                $sql .= " AND language_id = '".(int)$language_id."' ";

            }

            if($where){

                $sql .= " AND ".$where." ";

            }

            $query = $this->db->query($sql);

            if(count($query->rows)==1 && !$return_value){

                $result = $query->row;

            }elseif($return_value && isset ($query->row[$return_value]) && count($query->rows)==1 ){
                
                $result = $query->row[$return_value];
                
            }
            
            if(count($query->rows)>1){
                $result['dublicates_count'] = count($query->rows);
            }

        }

        return $result;

    }
    
    public function showTable($table,$prefix) {
        
        if(isset($this->temp['showTable'][$table][$prefix])){
            
            return $this->temp['showTable'][$table][$prefix];
            
        }
        
        $query = $query = $this->db->query('SHOW TABLES from `'.DB_DATABASE.'` like "'.$prefix.$table.'" ');
        
        if($query->num_rows){
            
            $this->temp['showTable'][$table][$prefix] = TRUE;
            
            return TRUE;
            
        }else{
            
            $this->temp['showTable'][$table][$prefix] = FALSE;
            
            return FALSE;
            
        }
        
    }
    
    public function getColumnsByTable($table) {
        
	if(isset($this->temp['getColumnsByTable'][$table])){
            
            return $this->temp['getColumnsByTable'][$table];
            
        }
	
	$cache_file_name = 'anyCSV-getColumnsByTable-'.$table.'.txt';
	
	$getColumnsByTable = $this->getAnyCSVActionsCache($cache_file_name);
	
	if(!is_null($getColumnsByTable)){
	    
	    $this->temp['getColumnsByTable'][$table] = $getColumnsByTable;
	    
	    return $this->temp['getColumnsByTable'][$table];
	    
	}
        
        $result = array();
        
        if($this->showTable($table, DB_PREFIX)){
            
            $columns = $this->db->query('SHOW COLUMNS FROM `' . DB_PREFIX . $table."` " );
            
            foreach ($columns->rows as $column) {
                
                $result[$column['Field']] = $column;
                
            }
            
        }
        
        $this->temp['getColumnsByTable'][$table] = $result;
	
	$this->writeAnyCSVActionsCache($cache_file_name,$result);
        
        return $result;
        
    }
    
    public function repairCategories($parent_id = 0) {
        
            if(!$this->repair_categories){

                return;

            }
        
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
        
        $language_id = "";
        
        if($this->table_seo_url=='seo_url'){
            
            $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];
            
        }
        
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->table_seo_url. "` WHERE query = '".$seo_query."' ".$language_id;
        
        //var_dump($sql).'<br>';
        
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
            
            $language_id = "";
        
            if($this->table_seo_url=='seo_url'){

                $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];

            }
            
            $where .= $language_id;
            
            $sql = "SELECT * FROM `" . DB_PREFIX . $this->table_seo_url. "` ".$where;
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
        $find = array('№','«', '»','"', '&', '>', '<','`','&acute;','!', '^','*','$','\'','@','"', '±',' ','&','#',';','%','?',':','(',')','-','_','=','+','[',']',',','.','/','\\');
        $replace = array('','','','','','','','','','','','','','','','','','-','','','','','','','','','-','-','-','-','','','-','','-','-');
        $str = str_replace($find, $replace, $str);
        $str = trim(mb_strtolower($str,'utf-8'));
        if($only_to_latin){
            $find = array('а','б','в','г','д','е', 'ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','ц','ч','ш','щ','у','ф','х','ъ','ь','ы','э','ю','я');
            $replace = array('a','b','v','g','d','e','yo','zh','z','i','j','k','l','m','n','o','p','r','s','t','ts','ch','sh','sch','u','f','kh','','','y','e','yu','ya');
            $str = str_replace($find, $replace, $str);
        }
        
        $str = $this->cleanDubleT($str);
        
        return $str;
    }
    
    public function cleanDubleT($t){

        if(strstr($t, '--')){
            $t = str_replace(array('---','--','-—-','-–-'), '-', $t);
            if(strstr($t, '--')){
                return $this->cleanDubleT($t);
            }
        }

        return $t;

    }

    public function getDublicates($query_part,$seos){
        $result = array();
        if($seos){
            foreach ($seos as $id => $keyword) {
                $keyword = trim($keyword);
                if($keyword){

                    $where = " WHERE keyword='".$keyword."' AND query!='".$this->db->escape($query_part).'='.(int)$id."' ";

                    $language_id = "";
        
                    if($this->table_seo_url=='seo_url'){

                        $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];

                    }
                    
                    $where .= $language_id;
                    
                    $sql = "SELECT * FROM `" . DB_PREFIX . $this->table_seo_url. "` ".$where;
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
                
                $language_id = "";
                
                $language_id_ins = "";
        
                if($this->table_seo_url=='seo_url'){

                    $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];
                    
                    $language_id_ins = ", language_id = ".$this->odmpro_tamplate_data['language_id'];

                }
                
                $sql = "SELECT * FROM `" . DB_PREFIX . $this->table_seo_url. "` WHERE ".$where.$language_id;
                $query = $this->db->query($sql);
                if(!$query->row){
                    
                    if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                        
                        $this->getResultDemo($query_part,$id,$this->table_seo_url,array('keyword='.$keyword),array($query_part." = ".$id),array(),'insert');
                    
                    }
                    else{

                        $sql = "INSERT INTO `" . DB_PREFIX . $this->table_seo_url. "` SET ".$where. ', keyword = '."'".$this->db->escape($keyword)."' ".$language_id_ins;
                        $query = $this->db->query($sql);

                    }
                    
                    
                }
                $result = 1;
            }
        }
        
        return $result;

    }

    private function checkColumnTable($table,$column,$add_this_column_type_data = '') {
        
        if(isset($this->temp['checkColumnTable'][$table][$column]) && !$add_this_column_type_data){
            
            return $this->temp['checkColumnTable'][$table][$column];
            
        }

        if($this->showTable($table, DB_PREFIX)){
            
            $check = $this->db->query(" SHOW columns FROM `".DB_PREFIX.$table."` WHERE `Field` = '".$column."'  ");
            
            if(!$check->num_rows && !$add_this_column_type_data){
                
                $this->temp['checkColumnTable'][$table][$column] = FALSE;
                return FALSE;
                
            }elseif(!$check->num_rows && $add_this_column_type_data){
                
                $this->db->query(" ALTER TABLE `".DB_PREFIX.$table."` ADD COLUMN `".$column."` ".$add_this_column_type_data." ");
                $this->temp['checkColumnTable'][$table][$column] = TRUE;
                return TRUE;
                
            }else{
                
                $this->temp['checkColumnTable'][$table][$column] = TRUE;
                return TRUE;
                
            }
            
        }else{
            $this->temp['checkColumnTable'][$table][$column] = FALSE;
            return FALSE;
            
        }

    }
    
    public function getImageByUrlOnImage($site_from_image,$additinal_settings,$general_setting,$log_data) {
        
            $start_download = time();
            
            if(!isset($this->max_memory_usage['image_download_interval'])){
                
                $this->max_memory_usage['image_download_interval'] = 0;
                
            }
        
            
            $new_image_name=FALSE;
            if(isset($additinal_settings['image_new_name']) && $additinal_settings['image_new_name']){
                $new_image_name=TRUE;
            }

            $image_new_path_parts=array();
            if(isset($additinal_settings['image_new_path']) && $additinal_settings['image_new_path']!==''){
                $image_new_path =  trim($additinal_settings['image_new_path']);
		
		
		if(strstr($image_new_path,'[[') && isset($this->temp['csv_data_row']) && isset($image_new_path) && $image_new_path){
		    
		    $image_new_path = $this->getParsFromCollection($image_new_path, $this->temp['csv_data_row']);
		    
		}
		
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

                $check_url = array('http:'=>0,'https:'=>0,'ftp:'=>0);

                foreach ($image_parts as $key => $image_parts_check_http) {
                    if(isset($check_url[$image_parts_check_http])){
                        unset($check_url[$image_parts_check_http]);
                    }

                }
                
                if(count($check_url)>2){
                    return '';
                }else{
                    unset($image_parts[0]);
                    unset($image_parts[1]);
                    unset($image_parts[2]);
                }

            }
            
            $image_new_dir = 0;
            
            $all_img_ext = array('.jpg','.png','.gif','.jpeg');
            
            if(isset($general_setting['image_new_dir']) && $general_setting['image_new_dir']){
                
                $image_new_dir = (int)$general_setting['image_new_dir'];
                
            }

            if($image_new_path_parts){
                foreach ($image_new_path_parts as $url_part) {
                    $path_whis_path_array[] = $url_part;
                }
            }

            if($image_parts){
                foreach ($image_parts as $key => $url_part) {
                    
                    $url_part = rawurldecode($url_part);
                    
                    $url_part = str_replace(array('.JPG','.PNG','.GIF','.JPEG'),$all_img_ext,$url_part);
                    
                    if(isset($image_parts[$key+1])){
                        $url_part = str_replace(array('.',',','(',')'),'-',$url_part);
                    }else{
                        $url_part_ext = '';
                        if(strstr($url_part, '.jpg')){
                            $url_part_ext = '.jpg';
                            $url_part = str_replace($url_part_ext,'',$url_part);
                        }elseif(strstr($url_part, '.gif')){
                            $url_part_ext = '.gif';
                            $url_part = str_replace($url_part_ext,'',$url_part);
                        }elseif(strstr($url_part, '.png')){
                            $url_part_ext = '.png';
                            $url_part = str_replace($url_part_ext,'',$url_part);
                        }elseif(strstr($url_part, '.jpeg')){
                            $url_part_ext = '.jpeg';
                            $url_part = str_replace($url_part_ext,'',$url_part);
                        }
                        $url_part = str_replace(array('.','?',',','(',')','=','+',';',':','~','`','@','#','&','$','*','!'),'-',$url_part).$url_part_ext;
                    }
                    
                    if($image_new_dir && isset($image_parts[$key+1])){
                        $url_part = md5($url_part);
                    }
                    $path_whis_path_array[] = $url_part;
                }
            }

            if(!$path_whis_path_array){
                return '';
            }

            $image_name = rawurldecode($path_whis_path_array[count($path_whis_path_array)-1]);
            
            unset($path_whis_path_array[count($path_whis_path_array)-1]);
            $image_path = '';
            if($path_whis_path_array){
                $image_path = implode('/', $path_whis_path_array).'/';
            }
            
            
            if($new_image_name){

                $image_name_parts = explode('.',$image_name);
               
                $image_name = md5($site_from_image).'.'.strtolower(end($image_name_parts));

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

            }elseif (file_exists($server_path_and_image) && is_file($server_path_and_image) && is_readable($server_path_and_image) && filesize($server_path_and_image) > 0) {
				
                $info = getimagesize($server_path_and_image);

                $imt = array('image/png'=>'.png',
                        'image/jpeg'=>'.jpg',
                        'image/gif'=>'.gif',
                        'image/vnd.wap.wbmp'=>'.bmp'
                );

                if(isset($info['mime']) && isset($imt[$info['mime']]) && (strstr($server_path_and_image,$imt[$info['mime']]) || strstr($server_path_and_image,'.jpeg') )){

                    if(!isset($general_setting['image_no_update_last_image']) || (isset($general_setting['image_no_update_last_image']) && !$general_setting['image_no_update_last_image'])){
                        $image = $this->resizeImg($image , $image,$general_setting,$log_data);
                        if(isset($general_setting['image_only_new']) && !$general_setting['image_only_new']){
                            $image = $this->resizeImg($image , $image,$general_setting,$log_data);
                        }
                        return $image;
                    }

                }

            }else{
				
                $server_path_and_image_parts = explode('.',$server_path_and_image);

                $image_ext = strtolower(end($server_path_and_image_parts));

                if(!strstr($server_path_and_image,'.'.$image_ext)){

                        return '';

                }

            }
            
            //var_dump($site_from_image);exit();
            
            $site_from_image = rawurldecode($site_from_image);
            $site_from_image = rawurldecode($site_from_image);
            $site_from_image = str_replace(array('%3A','%2F',' '),array(':','/','%20'),$site_from_image);
		  
            if($this->image_upload_curl){
                
                $file = fopen($server_path_and_image, "w");
				
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1); 
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 1500); 
                curl_setopt($curl, CURLOPT_URL, $site_from_image);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_FILE, $file);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                if(!curl_exec($curl)){
					curl_close($curl);
                    fclose($file);
                    unlink($server_path_and_image);
                    return '';
                }
                fflush($file);
                fclose($file);
                curl_close($curl);
                $mime_content_type = mime_content_type($server_path_and_image);
                if($mime_content_type=='text/html'){
                    unlink($server_path_and_image);
                    return '';
                }
                
                $image = $this->resizeImg($image , $image,$general_setting,$log_data);
                
                $finish_download = time();
                
                $image_download_interval = $finish_download - $start_download;
                
                if($image_download_interval>$this->max_memory_usage['image_download_interval']){
                    
                    $this->max_memory_usage['image_download_interval'] = $image_download_interval;
                    
                }
                
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
						
                        $mime_content_type = mime_content_type($server_path_and_image);
                        if($mime_content_type=='text/html'){
                                unlink($server_path_and_image);
                                return '';
                        }
                        
                        $image = $this->resizeImg($image , $image,$general_setting,$log_data);
                        
                        $finish_download = time();

                        $image_download_interval = $finish_download - $start_download;

                        if($image_download_interval>$this->max_memory_usage['image_download_interval']){

                            $this->max_memory_usage['image_download_interval'] = $image_download_interval;

                        }
						
                        return $image;
                    }

                }

            }
        return '';
    }
    
    public  function resizeImg($source_file_image,$new_file_image,$general_setting,$log_data){

        if(isset($general_setting['image_crop']) && !$general_setting['image_crop']){
            return $source_file_image;
        }
        
        if(file_exists(DIR_IMAGE.$source_file_image) && is_file(DIR_IMAGE.$source_file_image)){
            
            $info = getimagesize(DIR_IMAGE.$source_file_image);

            $mime   = $info['mime'];

            if(substr($mime, 0, 6) != 'image/'){
                    $log_data['__line__'] = __LINE__; 
                    $log_write_row = array(
                        'log_data' => $log_data,
                        'message' => array('warning'=>'Error: requested file is not an accepted type: ' .$source_file_image),
                        'action'    => $log_data['type_process']
                    );
                    $this->setLogDataRow($log_write_row,$log_data);
                    return 'Error: requested file is not an accepted type: ' .$source_file_image;
            }
            
            $w_source = $info[0];
            
            $w = '';
            
            $h_source = $info[1];
            
            $h = '';
            
            if(isset($general_setting['image_max_width']) && $general_setting['image_max_width']!==''){
                
                $w = (float)$general_setting['image_max_width'];
                
            }
            
            if(isset($general_setting['image_max_height']) && $general_setting['image_max_height']!==''){
                
                $h = (float)$general_setting['image_max_height'];
                
            }
            
            $top = 0;
            
            $left = 0;
            
            $right = 0;
            
            $botton = 0;
            
            if(isset($general_setting['image_crop_left']) && $general_setting['image_crop_left']!==''){
                
                $left = (float)$general_setting['image_crop_left'];
                
            }
            
            if(isset($general_setting['image_crop_top']) && $general_setting['image_crop_top']!==''){
                
                $top = (float)$general_setting['image_crop_top'];
                
            }
            
            if(isset($general_setting['image_crop_right']) && $general_setting['image_crop_right']!==''){
                
                $right = (float)$general_setting['image_crop_right'];
                
            }
            
            if(isset($general_setting['image_crop_bottom']) && $general_setting['image_crop_bottom']!==''){
                
                $botton = (float)$general_setting['image_crop_bottom'];
                
            }
            
            $w_source_ratio = $w_source-($left+$right);
            
            $h_source_ratio = $h_source-($top+$botton);
            
            if(!$w || !$h){
                
                if($w && !$h){
                    
                    $h = $h_source_ratio * $w/$w_source_ratio;
                    
                }
                
                elseif(!$w && $h){
                    
                    $w = $w_source_ratio * $h/$h_source_ratio;
                    
                }
                
                else{
                    
                    $w = $w_source_ratio;
                    
                    $h = $h_source_ratio;
                    
                }
                
            }else{
                
                if($w_source_ratio > $h_source_ratio && $w > $h){
                    
                    $h = $h_source_ratio * $w_source_ratio/$w;
                    
                }elseif($w_source_ratio < $h_source_ratio && $w < $h){
                    
                    $w = $w_source_ratio * $h_source_ratio/$h;
                    
                }elseif($w_source_ratio < $h_source_ratio && $w > $h){
                    
                    $h = $h_source_ratio * $w_source_ratio/$w;
                    
                }elseif($w_source_ratio > $h_source_ratio && $w < $h){
                    
                    $w = $w_source_ratio * $h_source_ratio/$h;
                    
                }elseif($w_source_ratio == $h_source_ratio && $w < $h){
                    
                    $h = $h*$w/$h;
                    
                }elseif($w_source_ratio == $h_source_ratio && $w > $h){
                    
                    $w = $w*$h/$w;
                    
                }else{
                    
                    $w = $w_source_ratio * $h/$h_source_ratio;
                    
                    $h = $h_source_ratio * $w/$w_source_ratio;
                    
                }
                
            }
            
            if($h<0 || !$h || $w<0 || !$w){
                
                return $new_file_image;
                
            }
            
            $extension = image_type_to_extension($info[2]);
            if(strtolower($extension) == '.png'){
                $img = $this->resizeImagePng(DIR_IMAGE.$source_file_image,$w, $h , $top, $botton, $left, $right);
                imagepng($img,DIR_IMAGE.$new_file_image);
                imagedestroy($img);
            }elseif(strtolower($extension) == '.jpeg'){
                $img = $this->resizeImageJpeg(DIR_IMAGE.$source_file_image, $w, $h, $top, $botton, $left, $right);
                imagejpeg($img, DIR_IMAGE.$new_file_image);
                imagedestroy($img);
            }elseif(strtolower($extension == '.gif')){
                $img = $this->resizeImageGif(DIR_IMAGE.$source_file_image, $w, $h, $top, $botton, $left, $right);
                imagegif($img,DIR_IMAGE.$new_file_image);
                imagedestroy($img);
            }
            
        }
        
        return $new_file_image;

    }
    
    private function resizeImagePng($file, $w, $h, $top, $botton, $left, $right) {
       list($width, $height) = getimagesize($file);
       $src = imagecreatefrompng($file);
       $dst = imagecreatetruecolor($w, $h);
       imagecopyresampled($dst, $src, 0, 0, $left, $top, $w, $h, $width-$right-$left, $height-$botton-$top);
       return $dst;
    }
    private function resizeImageJpeg($file, $w, $h, $top, $botton, $left, $right) {
        
       list($width, $height) = getimagesize($file);
       $src = imagecreatefromjpeg($file);
       $dst = imagecreatetruecolor($w, $h);
       imagecopyresampled($dst, $src, 0, 0, $left, $top, $w, $h, $width-$right-$left, $height-$botton-$top);
       return $dst;
    }
     
    private function resizeImageGif($file, $w, $h, $top, $botton, $left, $right) {
       list($width, $height) = getimagesize($file);
       $src = imagecreatefromgif($file);
       $dst = imagecreatetruecolor($w, $h);
       imagecopyresampled($dst, $src, 0, 0, $left, $top, $w, $h, $width-$right-$left, $height-$botton-$top);
       return $dst;
    }
    
    public function getImages($images,$general_setting,$additinal_settings, $data_action = 'add_values',$log_data) {
        
        $delimeter = '';
        
        if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']!==''){
            
            $delimeter = $additinal_settings['delimeter'];
            
        }
        
        $images_array = array();
        
        if($delimeter!=='' && $images){
            
            $images_array = explode($delimeter, $images);
            
        }elseif($images){
            
            $images_array[] = $images;
            
        }
        
        $result = array();
        
        if($images_array){
            
            foreach ($images_array as $image_url) {
             
                $image_url = trim(ltrim($image_url));
                
                if($image_url && isset($additinal_settings['image_upload']) && $additinal_settings['image_upload']){
                    
                    $image = $this->getImageByUrlOnImage($image_url,$additinal_settings,$general_setting,$log_data);
                    
                    
                    if($image && file_exists(DIR_IMAGE.$image)){
                        
                        $result[$image] = $image;
                        
                    }
                    
                }elseif($image_url){
                    
                    $image_new_path = '';
                    
                    if(isset($additinal_settings['image_new_path']) && $additinal_settings['image_new_path']!==''){
                        
                        $image_new_path = trim($additinal_settings['image_new_path']);
                        
                        if($image_new_path && substr($image_new_path, -1)!=='/'){
                            
                            $image_new_path .= '/';
                            
                        }
                        
                    }
                    
                    $result[$image_url] = $image_new_path.$image_url;
                    
                }
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getManufacturerIdAndSaveByName($manufacturer_name,$language_id,$store_id,$general_setting,$additinal_settings,$data_action = 'add_values') {
        
        $manufacturer_id = 0;
        
        $query = $this->db->query(" SELECT * FROM `" . DB_PREFIX . "manufacturer` WHERE name = '".$this->db->escape($manufacturer_name)."' ");
        
        if($query->row){
            
            $manufacturer_id = $query->row['manufacturer_id'];
            
        }else{
            
            $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer` SET image = '', sort_order = 0, name = '".$this->db->escape($manufacturer_name)."' ");
            
            $manufacturer_id = $this->db->getLastId();
            
            if($this->showTable('manufacturer_description', DB_PREFIX) && $this->checkColumnTable('manufacturer_description', 'name')){
             
                $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_description` SET manufacturer_id = ".$manufacturer_id.", name = '".$this->db->escape($manufacturer_name)."', language_id = ".(int)$language_id." ");
                
            }
            
            if($this->showTable('manufacturer_to_store', DB_PREFIX)){
             
                $this->db->query(" DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = ".$manufacturer_id." ");
                
                foreach ($store_id as $store_id_value) {
                    
                    $this->db->query(" INSERT INTO `" . DB_PREFIX . "manufacturer_to_store` SET manufacturer_id = ".$manufacturer_id.", store_id = ".$store_id_value);
                
                }
                
            }
            
        }
        
        if($data_action=='delete_values' && $manufacturer_id){

            $this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ");
                
            $this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_to_store` WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ");

            if($this->showTable('manufacturer_description', DB_PREFIX)){
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "manufacturer_description` WHERE manufacturer_id = '" . (int)$manufacturer_id . "' ");
                
            }
            
            $manufacturer_id = 0;
            
        }
        
        return $manufacturer_id;
        
    }
    
    private function getFloat($string,$return_empty_string=FALSE){
            
        $find = array(',',' ','`',"'");

        $replace = array('.','','','');
        
        $result = trim(str_replace($find, $replace, $string));
        
        if(!$return_empty_string){
            $result = (float)$result;
        }

        return $result;
    }
    
    public function getAttributeOrFilterGroupByGroupIdOrGroupName($group_id, $group_name,$table, $language_id, $general_setting,$additinal_settings,$data_action = 'add_values') {
        
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
        
        if($data_action=='delete_values' && $group_id_result){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $table."_group` WHERE ".$table."_group_id = '" . (int)$group_id_result . "' ");

            if($this->showTable($table."_group_description", DB_PREFIX)){
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . $table."_group_description` WHERE ".$table."_group_id = '" . (int)$group_id_result . "' ");
                
            }
            
            $group_id_result = 0;
            
        }
        
        return $group_id_result;
        
    }
    
    public function getAttributeOrFilterByIdOrGroupNameAndGroupId($group_id, $id, $name,$table, $language_id, $general_setting,$additinal_settings,$data_action = 'add_values') {
        
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
                
                $sql .= " AND ad.name = '" . $this->db->escape($name) . "'";
                
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
        
        if($data_action=='delete_values' && $id_result){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $table."` WHERE ".$table."_id = '" . (int)$id_result . "' ");

            if($this->showTable($table."_description", DB_PREFIX)){
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . $table."_description` WHERE ".$table."_id = '" . (int)$id_result . "' ");
                
            }
            
            $id_result = 0;
            
        }
        
        return $id_result;
        
    }
    
    public function getCategoriesIdByPath($path_whis_categories_name,$language_id,$store_id,$general_setting,$additinal_settings,$data_action='add_values',$log_data = array()) {
        
        $delimiter = '';
        
        if(isset($additinal_settings['delimeter']) && $additinal_settings['delimeter']){
            
            $delimiter = $additinal_settings['delimeter'];
            
        }
        
        $status = 0;
        
        if(isset($general_setting['status_enable']) && $general_setting['status_enable']){
            
            $status = 1;
            
        }
        
        $seo_url_generator = 1;
        
        if(isset($general_setting['seo_url_generator']) && $general_setting['seo_url_generator']){
            
            $seo_url_generator = 1;
            
        }
        
        $result = array();
        
        $result_all_categories = array();
        
        $sql = '';
        
        $result['parent_id'] = 0;
        
        $result['category_id'] = 0;
        
        $table = 'category';
        
        if($this->showTable($table, DB_PREFIX)){
            
            //ищем категорию по пути, если не находим, то создаем путь, если нужно и выход. Категория будет создана на втором этапе
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
                        
                        $category_name_parts = explode('_-C-_',$category_name);
						
                        $image = '';

                        $set_additinal_data = array();

                        if(count($category_name_parts)>1){

                                $category_name = trim($category_name_parts[0]);

                                $images = str_replace('_-LS-_','/',$category_name_parts[1]);

                                $images_parts = explode(',',$images);

                                foreach($images_parts as $images_part){

                                        $image = trim($images_part);

                                        if($image){

                                            $image = $this->getImageByUrlOnImage($image,$additinal_settings,$general_setting,$log_data);

                                            $set_additinal_data[] = " image = '".$this->db->escape($image)."' ";

                                        }

                                }

                        }
                        
                        if($set_additinal_data){
                            
                            $set_additinal_data = implode(' , ',$set_additinal_data).", ";
                            
			}else{
			    
			    $set_additinal_data = '';
			    
			}

                        //первый элемент - должен быть топовый
                        if(!isset($parent_id)){

                            $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id . "' AND c.parent_id = 0 ";

                            $parent_category = $this->db->query($sql_category_path);

                            //если есть, оставляем родительский id
                            if($parent_category->row){

                                $parent_id = $parent_category->row['category_id'];

                                $result['parent_id'] = $parent_category->row['parent_id'];

                                $result['category_id'] = $parent_category->row['category_id'];
                                
                                if($set_additinal_data){

                                    $this->db->query("UPDATE `" . DB_PREFIX . "category` SET ".$set_additinal_data." parent_id = ".$result['parent_id']." WHERE category_id = ".$parent_category->row['category_id']);

                                }
                                
                                $result_all_categories[$result['category_id']] = $result['category_id'];
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_category->row['category_id']=>$category_name));
                                    
                                }

                            }

                            // в противном случае, вставляем родителя и сохраняем его id
                            else{

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET ".$set_additinal_data."  parent_id = '0', `top` = '1', `column` = '1',`status` = '".$status."', sort_order = '0', date_modified = NOW(), date_added = NOW()");

                                //если не последний елемент в path эта категория будет родителем
                                $parent_id = $this->db->getLastId();

                                //если последний елемент вернет значение по этой категории
                                $result['parent_id'] = 0;

                                $result['category_id'] = $parent_id;
                                
                                $result_all_categories[$parent_id] = $parent_id;

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$parent_id . "' ");
                                
                                foreach ($store_id as $store_id_value) {

                                    $this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "', store_id = " . $store_id_value . " ");

                                }
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_id=>$category_name));
                                    
                                }

                            }

                        }else{

                            $sql_category_path = " SELECT * FROM `" . DB_PREFIX . "category` c LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id ) WHERE cd.name = '".$this->db->escape($category_name)."' AND cd.language_id = '" . (int)$language_id . "' AND c.parent_id = '".$parent_id."' ";

                            $parent_category = $this->db->query($sql_category_path);

                            //если есть, оставляем родительский id
                            if($parent_category->row){

                                $parent_id = $parent_category->row['category_id'];

                                $result['parent_id'] = $parent_category->row['parent_id'];

                                $result['category_id'] = $parent_category->row['category_id'];
                                
                                if($set_additinal_data){

                                    $this->db->query("UPDATE `" . DB_PREFIX . "category` SET ".$set_additinal_data." parent_id = ".$result['parent_id']." WHERE category_id = ".$parent_category->row['category_id']);

                                }
                                
                                $result_all_categories[$result['category_id']] = $result['category_id'];
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_category->row['category_id']=>$category_name));
                                    
                                }

                            }

                            // в противном случае, вставляем родителя и сохраняем его id
                            else{

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category` SET ".$set_additinal_data." parent_id = '".$parent_id."', `top` = '1', `column` = '1', sort_order = '0', status = '".$status."', date_modified = NOW(), date_added = NOW()");

                                $result['parent_id'] = $parent_id;

                                //новый родитель
                                $parent_id = $this->db->getLastId();

                                $result['category_id'] = $parent_id;
                                
                                $result_all_categories[$parent_id] = $parent_id;

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` SET category_id = '" . (int)$parent_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($category_name) . "', description = '', meta_title = '" . $this->db->escape($category_name) . "', meta_description = '', meta_keyword = ''");

                                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$parent_id . "' ");
                                
                                foreach ($store_id as $store_id_value) {
                                    
                                    $this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` SET category_id = '" . (int)$parent_id . "',  store_id = " . $store_id_value . " ");

                                }
                                
                                if($seo_url_generator){
                                    
                                    $this->seoUrlGenerateAndSave('category_id',array($parent_id=>$category_name));
                                    
                                }

                            }

                        }

                    }

                }
                
                $log_data['__line__'] = __LINE__; 

                $log_data['details_message'] = $this->getStringFromArray($path,$table.': '); 

                $log_write_row = array(
                    'log_data' => $log_data,
                    'message' => array('success'=>$this->language->get('import_success_category_path')),
                    'action'    => $log_data['type_process']
                );
                
                $this->setLogDataRow($log_write_row,$log_data);

            }else{
                
                $log_data['__line__'] = __LINE__; 

                $log_data['details_message'] = ''; 

                $log_write_row = array(
                    'log_data' => $log_data,
                    'message' => array('warning'=>$this->language->get('import_warning_category_path')),
                    'action'    => $log_data['type_process']
                );
                
                $this->setLogDataRow($log_write_row,$log_data);
                
            }
            
        }
                            
        if($data_action=='delete_values' && $result_all_categories){

            foreach ($result_all_categories as $category_id) {
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_to_store` WHERE category_id = '" . (int)$category_id . "' ");
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "category` WHERE category_id = '" . (int)$category_id . "' ");
                
                $this->db->query("DELETE FROM `" . DB_PREFIX . "category_description` WHERE category_id = '" . (int)$category_id . "' ");
                
                $table = 'category_to_layout';
                
                if($this->showTable($table, DB_PREFIX)){
                    
                    $this->db->query("DELETE FROM `" . DB_PREFIX .$table. "` WHERE category_id = '" . (int)$category_id . "' ");
                    
                }
                
                $table = 'category_filter';
                
                if($this->showTable($table, DB_PREFIX)){
                    
                    $this->db->query("DELETE INTO `" . DB_PREFIX .$table. "` WHERE category_id = '" . (int)$category_id . "' ");
                    
                }
                
                $table = 'category_path';
                
                if($this->showTable($table, DB_PREFIX)){
                    
                    $this->db->query("DELETE INTO `" . DB_PREFIX .$table. "` WHERE category_id = '" . (int)$category_id . "' ");
                    
                }
                
            }
            
            $log_data['__line__'] = __LINE__; 

            $log_data['details_message'] = $this->getStringFromArray($result_all_categories,$table.': '); 

            $log_write_row = array(
                'log_data' => $log_data,
                'message' => array('success'=>$this->language->get('import_success_category_delete')),
                'action'    => $log_data['type_process']
            );

            $this->setLogDataRow($log_write_row,$log_data);
            
            $result_all_categories = TRUE;

        }
        
        $this->repair_categories = TRUE;
        
        return $result_all_categories;
        
    }
    
    public function getOptionIdByNameOrOptionId($option_id,$option_name,$option_columns,$language_id,$general_setting,$additinal_settings,$data_action='add_values'){
        
        $option_id_result = 0;
        
        if($option_id){
            
            $option = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE option_id = '".$this->db->escape($option_id)."' ");
            
        }elseif($option_name){
            
            $option = $this->db->query("SELECT * FROM " . DB_PREFIX . "option_description WHERE language_id = ".$language_id ." AND name = '".$this->db->escape($option_name)."' ");
            
        }
        
        $types = array('select','checkbox','radio','image');

        if( isset($option->row) && !$option->row && isset($option_columns['type']) && $option_columns['type'] && in_array($option_columns['type'],$types)){

            if(FALSE && isset($this->temp['demo_mode']) && $this->temp['demo_mode']){

                $option_id_result = $this->temp['get_result_demo']['param']['this_demo_id'];
                
                $this->temp['get_result_demo']['param']['this_demo_id']+=1;
                
                $this->getResultDemo('option_id',$option_id_result,"option",array("sort_order = 0","type = '".$option_columns['type']."'"),array(),array(),'insert');
                
                $this->getResultDemo('option_id',$option_id_result,"option_description",array("language_id = ".$language_id,"name = '".$this->db->escape($option_name)."'"),array(),array(),'insert');
                
                $option_id_result = 0;
                
            }
            else{
                
                $this->db->query("INSERT INTO " . DB_PREFIX . "option SET sort_order = 0, type = '".$option_columns['type']."' ");

                $option_id_result = $this->db->getLastId();

                $this->db->query("INSERT INTO " . DB_PREFIX . "option_description SET option_id = ".$option_id_result.", name = '".$this->db->escape($option_name)."',  language_id = ".$language_id." ");
                
            }

        }elseif(isset ($option->row['option_id']) && $option->row['option_id']){
            
            $option_id_result = $option->row['option_id'];

        }
        
        if($data_action=='delete_values' && $option_id_result){
                
            $this->db->query("DELETE FROM `" . DB_PREFIX . "option` WHERE option_id = '" . (int)$option_id_result . "' ");

            $this->db->query("DELETE FROM `" . DB_PREFIX . "option_description` WHERE option_id = '" . (int)$option_id_result . "' ");
            
            $option_id_result = 0;

        }
        
        return $option_id_result;
        
    }
    
    public function getOptionValueIdByNameOrOptionValueId($option_id,$option_value_id,$option_value_name,$option_value_columns,$language_id,$general_setting,$additinal_settings,$data_action='add_values'){
        
        $option_value_id_result = 0;
        
        $sql_option_code = '';
        
        $sql_option_code2 = '';
        
        $sql_option_code_set = '';
        
        $sql_option_code_set_array = array();
        
        if(isset($option_value_columns['option_value_code']['column_name']) && $option_value_columns['option_value_code']['column_name'] && $this->checkColumnTable('option_value', $option_value_columns['option_value_code']['column_name'], 'text')){
            
            $sql_option_code .= " AND ov.".$option_value_columns['option_value_code']['column_name']." = '".$this->db->escape($option_value_columns['option_value_code']['value'])."' ";
            
            $sql_option_code2 .= " AND ".$option_value_columns['option_value_code']['column_name']." = '".$this->db->escape($option_value_columns['option_value_code']['value'])."' ";
            
            $sql_option_code_set .= ", `".$option_value_columns['option_value_code']['column_name']."` = '".$this->db->escape($option_value_columns['option_value_code']['value'])."' ";
            
            $sql_option_code_set_array[$option_value_columns['option_value_code']['column_name']] = $option_value_columns['option_value_code']['column_name']."` = '".$this->db->escape($option_value_columns['option_value_code']['value'])."' ";
            
        }
        
        if($option_value_id && $option_id){

            $option_value = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value_description` ovd LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (ovd.option_value_id = ov.option_value_id) WHERE  ovd.option_id = " . $option_id . " AND ovd.language_id = ".$language_id." AND ovd.option_value_id = '".$option_value_id."' ".$sql_option_code);

        }elseif($option_value_name && $option_id){

            $option_value = $this->db->query("SELECT * FROM `" . DB_PREFIX . "option_value_description` ovd LEFT JOIN `" . DB_PREFIX . "option_value` ov ON (ovd.option_value_id = ov.option_value_id) WHERE  ovd.option_id = " . $option_id . " AND language_id = ".$language_id." AND ovd.name = '".$this->db->escape($option_value_name)."' ".$sql_option_code);
            
        }
        
        if(!isset($option_value_columns['image'])){
            
            $option_value_columns['image'] = '';
            
        }
        
        if( (!isset($option_value->row) || !$option_value->row) && $option_value_name){
            
            if(FALSE && isset($this->temp['demo_mode']) && $this->temp['demo_mode']){

                $option_value_id_result = $this->temp['get_result_demo']['param']['this_demo_id'];
                
                $this->temp['get_result_demo']['param']['this_demo_id']+=1;
                
                $sql_option_code_set_array['image'] = "image = ".$option_value_columns['image'];
                
                $sql_option_code_set_array['sort_order'] = "sort_order = ".$option_value_columns['sort_order'];
                
                $this->getResultDemo('option_value_id',$option_value_id_result,"option_value",$sql_option_code_set_array,array(),array(),'insert');
                
                $this->getResultDemo('option_value_id',$option_value_id_result,"option_value_description",array("language_id = ".$language_id,"name = '".$this->db->escape($option_value_name)."'"),array(),array(),'insert');
                
                $option_value_id_result = 0;
                
            }
            else{
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "option_value` SET option_id = '" . (int)$option_id . "' , image = '".$option_value_columns['image']."',  sort_order = 0 ".$sql_option_code_set);

                $option_value_id_result = $this->db->getLastId();

                $this->db->query("INSERT INTO `" . DB_PREFIX . "option_value_description` SET option_value_id = ".$option_value_id_result.", option_id = '" . (int)$option_id . "', name = '".$this->db->escape($option_value_name)."',  language_id = ".$language_id." ");
                
            }
            
        }elseif(isset($option_value->row['option_value_id']) && $option_value->row['option_value_id']){
            
            $option_value_id_result = $option_value->row['option_value_id'];
            
            $this->db->query("UPDATE `" . DB_PREFIX . "option_value` SET image = '".$option_value_columns['image']."' WHERE option_id = '" . (int)$option_id . "' AND option_value_id = ".$option_value_id_result." ".$sql_option_code2);
            
        }
        
        if($data_action=='delete_values' && $option_value_id_result){
                
            $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value` WHERE option_value_id = '" . (int)$option_value_id_result . "' ".$sql_option_code);

            $this->db->query("DELETE FROM `" . DB_PREFIX . "option_value_description` WHERE option_value_id = '" . (int)$option_value_id_result . "' ");
            
            $option_value_id_result = 0;

        }
        
        return $option_value_id_result;
        
    }
    
    public function getIdByField($field,$value, $table, $column=0 , $language_id=0, $where='') {
        
        $result = 0;
        
        if($this->showTable($table, DB_PREFIX) && $this->checkColumnTable($table, $field)){
            
            $sql = "  SELECT * FROM `".DB_PREFIX.$table."` WHERE `".$field."`= '" . $this->db->escape($value) . "' ";
            
            if($language_id){
                
                $sql .= " AND language_id = '".(int)$language_id."' ";
                
            }
            
            if($where){
                
                $sql .= " AND ".$where_add." ";
                
            }
            
            $query = $this->db->query($sql);
            
            if($query->row){
                
                if($column && isset($query->row[$column])){
                    $result = $query->row[$column];
                }else{
                    $result = $query->row;
                }
                
            }
            
        }
        
        return $result;
        
    }
    
    public function insertNewDataToMainTable($table,$new_data){
        
        $id_resutl = 0;
        
        if($this->showTable($table, DB_PREFIX) && $new_data){
            
            $set = array();
            
            $columns_table = array();
            
            foreach($new_data as $column => $data){
                
                $set[] = " `".$column."` = '".$this->db->escape($data)."' ";
                
                $columns_table[$column] = $column;
                
            }
            
            if($table=='product'){
		
                    if(!isset($columns_table['date_available'])){
                        
                        $set['date_available'] = " `date_available` = NOW() ";
                        
                    }
                
                    if(!isset($columns_table['date_modified'])){
                        
                        $set['date_modified'] = " `date_modified` = NOW() ";
                        
                    }
                    
                    if(!isset($columns_table['date_added'])){
                        
                        $set['date_added'] = " `date_added` = NOW() ";
                        
                    }

            }
            elseif($table=='product_special' || $table=='product_discount'){
                
                if(!isset($columns_table['date_start'])){
                    
                    $set['date_start'] = " `date_start` = NOW() ";
                    
                }
                
                if(isset($new_data['price']) && ($new_data['price']==='' || $new_data['price']===0.0 || !$new_data['price'])){

                    return 1;

                }
                
                if(!isset($new_data['customer_group_id'])){
                    
                    $set['customer_group_id'] = " `customer_group_id` = ".(int)$this->config->get('config_customer_group_id')." ";
                    
                }
                
                if(!isset($new_data['quantity']) && $table=='product_discount'){
                    
                    $set['quantity'] = " `quantity` = 100 ";
                    
                }
                
            }
            
            $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set);
            
            if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                $id_resutl = $this->temp['get_result_demo']['param']['this_demo_id'];
                
                $this->getResultDemo($table.'_id',$id_resutl,$table,$set,array(),array(),'insert');
                
                $this->temp['get_result_demo']['param']['this_demo_id'] += 1;

            }
            else{

                $this->db->query($sql);
            
                $id_resutl = $this->db->getLastId();
                
            }
            
        }
        
        return $id_resutl;
        
    }
    
    public function insertUrlAlias($new_data,$id_name,$id){
        
        $result = 0;
        
        $language_id = "";

        if($this->table_seo_url=='seo_url'){

            $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];

        }
        
        if(isset($new_data['keyword']) && $new_data['keyword'] && $id){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $this->table_seo_url. "` WHERE query = '".$id_name."=".$id."' ".$language_id);
            
            $result = $this->seoUrlSave($id_name,array($id=>$new_data['keyword']));
            
        }
            
        return $result;
        
    }
    
    public function updateUrlAlias($new_data,$id_name,$id,$delete_data=FALSE){
        
        $result = 0;
        
        $language_id = "";

        if($this->table_seo_url=='seo_url'){

            $language_id = " AND language_id = ".$this->odmpro_tamplate_data['language_id'];

        }
        
        if(isset($new_data['keyword']) && $id && $delete_data){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $this->table_seo_url. "` WHERE query = '".$id_name."=".$id."' ".$language_id);
            
            return 1;

        }
        
        if(isset($new_data['keyword']) && $new_data['keyword'] && $id){
            
            $this->db->query("DELETE FROM `" . DB_PREFIX . $this->table_seo_url. "` WHERE query = '".$id_name."=".$id."' ".$language_id);
            
            $result = $this->seoUrlSave($id_name,array($id=>$new_data['keyword']));
            
        }
            
        return $result;
        
    }
    
    public function deleteDataToTable($table,$id_name,$id,$aditional_id=array()){
        
        $result = 0;
        
        if($this->showTable($table, DB_PREFIX) && $id){
            
            $set_where = array();
            
            $process_log_data_key = array($id_name,$id,$table);
            
            if($aditional_id){
                
                foreach($aditional_id as $aditional_id_column=>$aditional_id_value){
                    
                    $set_where[$aditional_id_column] = $aditional_id_column." = '".$aditional_id_value."' ";
                    
                    $process_log_data_key[] = $aditional_id_column;
                    
                    $process_log_data_key[] = $aditional_id_value;
                    
                }
                
            }
            
            if(isset($this->temp['process_log_data']) && isset($this->temp['process_log_data'][ implode('-', $process_log_data_key) ])){
                
                return 1;
                
            }
            
            $set_where[$id_name] = " ".$id_name." = ".$id." ";
            
            if($set_where){
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                    $this->getResultDemo($id_name,$id,$table,array(),$set_where,array(),'delete');

                }
                else{
                    
                    $this->db->query(" DELETE FROM `".DB_PREFIX.$table."` WHERE  ".  implode(' AND ',$set_where));

                    $this->temp['process_log_data'][ implode('-', $process_log_data_key) ] = TRUE;
                    
                }
                
                $result = 1;
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getLastAndNewDBDataFromSQLSetPartAndLastDBData($set_sql,$where_sql,$last_db_data) {
        
        $result = array();
        
        $find = array('`',"'");
        
        $replace = array('',"");
        
        if($set_sql){
            
            foreach ($set_sql as $sql_set_part) {
                
                $sql_set_part = explode('=',$sql_set_part);
                
                if(count($sql_set_part)>1){
                    
                    $column = trim(str_replace($find, $replace, $sql_set_part[0]));
                    
                    unset($sql_set_part[0]);
                    
                    $new_value = trim(implode('=', $sql_set_part));
                    
                    if(mb_strcut($new_value, 0, 1)==="'"){
                        
                        //$new_value = trim(mb_strcut($new_value, 1, mb_strlen($new_value)-2));
                        
                    }
                    
                    $last_value = '';
                    
                    if(isset($last_db_data[$column])){
                        
                        $last_value = $last_db_data[$column];
                        
                    }
                    
                    foreach ($where_sql as $key => $value) {
                        $value = explode('=',$value);
                        if(strstr($value[0],'_id') && isset($value[1]) && trim($value[1])>=2147483700){
                            $where_sql[$key] = $value[0].'='."'Пока неизвестен'";
                        }
                    }
                    
                    $result[$column] = array(
                        'new_value' => $new_value,
                        'last_value' => $last_value,
                        'column' => $column,
                        'where' => implode(', ', $where_sql),
                    );
                    
                }
                
            }
            
        }
        elseif($where_sql){
            
            foreach ($where_sql as $key => $value) {
                $value = explode('=',$value);
                if(strstr($value[0],'_id') && isset($value[1]) && trim($value[1])>=2147483700){
                    $where_sql[$key] = $value[0].'='."'Пока неизвестен'";
                }
            }

            $result[] = array(
                'new_value' => '-',
                'last_value' => '-',
                'column' => '-',
                'where' => implode(', ', $where_sql),
            );
            
        }
        
        return $result;
        
    }
    
    public function getResultDemo($id_name,$id,$table,$set_sql,$where_sql,$last_db_data,$operation) {
        
        $demo_mode = FALSE;
        
        if(isset($this->temp['get_result_demo']) && !is_null($this->temp['get_result_demo'])){
            
            if(!isset($this->temp['get_result_demo']['results'][$id_name][$id][$table][$operation])){
                
                $this->temp['get_result_demo']['results'][$id_name][$id][$table][$operation] = array();
                
            }
            
            $this->temp['get_result_demo']['results'][$id_name][$id][$table][$operation] = $this->getLastAndNewDBDataFromSQLSetPartAndLastDBData($set_sql,$where_sql,$last_db_data);

            $demo_mode = TRUE;
            
        }
        
        return $demo_mode;
        
    }
    
    public function updateDataToTable($table,$new_data,$id_name,$id,$aditional_id=array(),$after_delete=FALSE,$delete_data=FALSE){
        
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
            
            $last_data = $this->db->query(" SELECT * FROM `".DB_PREFIX.$table."` WHERE  ".  implode(' AND ',$set_where));
            
            if($after_delete && $set_where){
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,array(),$set_where,$last_data->row,'delete');
                    
                }
                else{
                    
                    $this->db->query(" DELETE FROM `".DB_PREFIX.$table."` WHERE  ".  implode(' AND ',$set_where));
                    
                }
                
                $last_data->row = array();
                
            }
            
            $set = array();
        
            $columns_table = array();
            
            foreach($new_data as $column => $data){
                
                if($column!=$id_name && !isset($aditional_id[$column])){
                    
                    if(!$delete_data){
                        
                        $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";
                        
                    }elseif($delete_data){
                        
                        $set[$column] = " `".$column."` = '' ";
                        
                    }
                    
                    $columns_table[$column] = $column;
                    
                }
                
            }
            
            if($table=='product'){
		
                    if(!isset($columns_table['date_available'])){
                        
                        $set['date_available'] = " `date_available` = NOW() ";
                        
                    }
                
                    if(!isset($columns_table['date_modified'])){
                        
                        $set['date_modified'] = " `date_modified` = NOW() ";
                        
                    }
                    
                    if(!isset($columns_table['date_added'])){
                        
                        //$set['date_added'] = " `date_added` = NOW() ";
                        
                    }

            }
            elseif($table=='product_special' || $table=='product_discount'){
                
                if(!isset($columns_table['date_start'])){
                    
                    $set['date_start'] = " `date_start` = NOW() ";
                    
                }
                
                if(isset($new_data['price']) && ($new_data['price']==='' || $new_data['price']===0.0 || !$new_data['price'])){

                    return 1;

                }
                
                if(!isset($new_data['customer_group_id'])){
                    
                    $set['customer_group_id'] = " `customer_group_id` = ".(int)$this->config->get('config_customer_group_id')." ";
                    
                }
                
                if(!isset($new_data['quantity']) && $table=='product_discount'){
                    
                    $set['quantity'] = " `quantity` = 100 ";
                    
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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $set_this = array_merge($set,$set_where);
                    
                    $this->getResultDemo($id_name,$id,$table,$set_this,array(),array(),'insert');
                    
                }
                else{
                    
                    $this->db->query($sql);
                    
                }

                $result = 1;
                
                
                
            }
            elseif($set && $last_data->row){
                
                $result = 1;
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,$set_where,$last_data->row,'update');
                    
                }
                else{
                    
                    $sql = " UPDATE `".DB_PREFIX.$table."` SET ".implode(',', $set)." WHERE ".implode(' AND ',$set_where);
            
                    $this->db->query($sql);
                    
                }
                
            }
            
        }
        
        return $result;
        
    }
    
    public function insertDataToTable($table,$new_data,$id_name,$id,$aditional_id=array(),$after_delete=FALSE){
        
        $result = 0;
        
        $columns_table = array();
        
        if($this->showTable($table, DB_PREFIX) && $new_data && $id){
            
            $set = array();
            
            foreach($new_data as $column => $data){
                
                if($column!=$id_name && !isset($aditional_id[$column])){
                    
                    $set[] = " `".$column."` = '".$this->db->escape($data)."' ";
                    
                    $columns_table[$column] = $column;
                    
                }
                
            }
            
            if($table=='product'){
		
                    if(!isset($columns_table['date_available'])){
                        
                        $set['date_available'] = " `date_available` = NOW() ";
                        
                    }
                
                    if(!isset($columns_table['date_modified'])){
                        
                        $set['date_modified'] = " `date_modified` = NOW() ";
                        
                    }
                    
                    if(!isset($columns_table['date_added'])){
                        
                        $set['date_added'] = " `date_added` = NOW() ";
                        
                    }

            }
            elseif($table=='product_special' || $table=='product_discount'){
                
                if(!isset($columns_table['date_start'])){
                    
                    $set['date_start'] = " `date_start` = NOW() ";
                    
                }
                
                if(isset($new_data['price']) && ($new_data['price']==='' || $new_data['price']===0.0 || !$new_data['price'])){

                    return 1;

                }
                
                if(!isset($new_data['customer_group_id'])){
                    
                    $set['customer_group_id'] = " `customer_group_id` = ".(int)$this->config->get('config_customer_group_id')." ";
                    
                }
                
                if(!isset($new_data['quantity']) && $table=='product_discount'){
                    
                    $set['quantity'] = " `quantity` = 100 ";
                    
                }
                
            }
            
            if($set){
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id),array(),'insert');
                    
                }
                else{
                    
                    $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set).", ".$id_name." = ".$id;
            
                    $this->db->query($sql);
                    
                }

                $result = 1;
                
            }
            
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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id,"language_id = ".$language_id),array(),'insert');
                    
                }
                else{
                    
                    $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set).", ".$id_name." = ".$id.", language_id = ".$language_id;

                    $this->db->query($sql);
                    
                }

                $result = 1;
                
            }elseif($set && $last_data->row){
                
                $result = 1;
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id,"language_id = ".$language_id),$last_data->row,'update');
                    
                }
                else{
                    
                    $sql = " UPDATE `".DB_PREFIX.$table."` SET ".implode(',', $set)." WHERE ".$id_name." = ".$id." AND language_id = ".$language_id;
            
                    $this->db->query($sql);
                    
                }
                
                
                
            }
            
            if($seo_url_generator && $this->checkColumnTable($table, 'name') && $result){

                $data = $this->db->query(" SELECT name FROM `".DB_PREFIX.$table."` WHERE  ".$id_name." = ".$id." AND language_id = ".$language_id);

                if(isset($data->row['name']) && $data->row['name']){

                    $this->seoUrlGenerateAndSave($id_name,array($id=>$data->row['name']));

                }

            }
            
        }
        
        return $result;
        
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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id,"language_id = ".$language_id),array(),'insert');
                    
                }
                else{
                    
                    $sql = " INSERT INTO `".DB_PREFIX.$table."` SET ".implode(',', $set).", ".$id_name." = ".$id.", language_id = ".$language_id;
            
                    $this->db->query($sql);
                    
                }
                
                

                $result = 1;

                if($seo_url_generator && $this->checkColumnTable($table, 'name')){

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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id,"language_id = ".$language_id,"attribute_id = ".$new_data['attribute_id']),array(),'insert');
                    
                }
                else{
                    
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $table . "` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "' ");
                    
                }
                
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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                    
                    $this->getResultDemo($id_name,$id,$table,$set,array($id_name." = ".$id,"language_id = ".$language_id,"attribute_id = ".$new_data['attribute_id']),$product_attribute->row,'update');
                    
                }
                else{
                    
                    $this->db->query("UPDATE `" . DB_PREFIX . $table . "` SET ".implode(',', $set)." WHERE ".$id_name." = '" . (int)$id . "' AND language_id = ".$language_id." AND attribute_id = ".$new_data['attribute_id']." ");
                    
                }
                
                $result = 1;
                
            }
            
        }
        
        return $result;
        
    }
    
    
    
    public function insertProductAssortimentOptionValue($new_data,$id_name,$id, $delete_data = FALSE) {
        
        $result = 0;
        
        $product_assortiment_ids = array(
            'ean'=>'',
            'model'=>'',
            'jan'=>'',
            'upc'=>'',
            'isbn'=>'',
            'mpn'=>'',
            'sku'=>'',
            'product_assortiment_id'=>''
        );
        
        $where_array = array();
        
        foreach ($product_assortiment_ids as $product_assortiment_id => $tmp) {
            
            if(isset($new_data[$product_assortiment_id]) && $new_data[$product_assortiment_id]){
                
                $where_array[] = $product_assortiment_id." = '".$new_data[$product_assortiment_id]."' ";
                
            }
            
        }
        
        if($where_array){
            
            $product_assortiment = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_assortiment` WHERE ".$id_name." = '" . (int)$id . "' AND ".  implode(' AND ', $where_array));
            
        }
        
        //var_dump("SELECT * FROM `" . DB_PREFIX  . "product_assortiment` WHERE ".$id_name." = '" . (int)$id . "' AND ".  implode(' AND ', $where_array));exit();
        
        /*
         * Если позиция не новое - сверка по артикулу
         */
        
        if(isset($product_assortiment->row) && $product_assortiment->row){
            
            $new_data['product_assortiment_id'] = $product_assortiment->row['product_assortiment_id'];
            
        }
        
        /*
         * Если данные в принципе есть по позиции
         */
        
        if($new_data['product_assortiment_value']){

            foreach ($new_data['product_assortiment_value'] as $product_assortiment_value_key => $product_assortiment_value_value) {

                $product_assortiment_value_last = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_assortiment_value` WHERE product_assortiment_id = '" . (int)$new_data['product_assortiment_id'] . "' AND  option_value_id = ".$product_assortiment_value_value['option_value_id']);

                /*
                * Проверяем есть ли опция, связанная с PAI, если да, то дописываем POI, POVI и PAVI
                */
                
                if(isset($product_assortiment_value_last->row) && $product_assortiment_value_last->row){

                    $new_data['product_assortiment_value'][$product_assortiment_value_key]['product_option_value_id'] = $product_assortiment_value_last->row['product_option_value_id'];

                    $new_data['product_assortiment_value'][$product_assortiment_value_key]['product_option_id'] = $product_assortiment_value_last->row['product_option_id'];

                    $new_data['product_assortiment_value'][$product_assortiment_value_key]['product_assortiment_value_id'] = $product_assortiment_value_last->row['product_assortiment_value_id'];

                }

                if(isset($product_assortiment_value_value['price_whis_delta'])){

                    $product_price = $this->db->query("SELECT price FROM `" . DB_PREFIX  . "product` WHERE ".$id_name." = '" . (int)$id . "' ");

                    if($product_price->row && isset($product_price->row['price'])){

                        $product_assortiment_value_value['price'] = $product_assortiment_value_value['price_whis_delta'] - (float)$product_price->row['price'];

                        if($product_assortiment_value_value['price']>=0){

                            $new_data['product_assortiment_value'][$product_assortiment_value_key]['price_prefix'] = '+';

                        }else{

                            $new_data['product_assortiment_value'][$product_assortiment_value_key]['price_prefix'] = '-';

                        }

                        $new_data['product_assortiment_value'][$product_assortiment_value_key]['price'] = abs($product_assortiment_value_value['price']);

                    }

                    unset($new_data['product_assortiment_value'][$product_assortiment_value_key]['price_whis_delta']);

                }

            }

        }else{
            
            $new_data['product_assortiment_value'] = array();
            
        }
        
        $product_assortiment = array();
        
        if($new_data['product_assortiment_value']){
            
            $product_assortiment['product_assortiment'][] = $new_data;
            
        }
        
        /*
         * Пишем старые, для метода базового, который сразу все создает
         */
        
        $product_assortiment_last = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_assortiment` WHERE ".$id_name." = '" . (int)$id . "' AND product_assortiment_id != ".  $new_data['product_assortiment_id']);
        
        if($product_assortiment_last->rows){
            
            /*
            * Строки ассортиртиментной позиции
            */
            
            foreach ($product_assortiment_last->rows as $key => $value) {
                
                
                $product_assortiment_last_row = array();
                
                foreach($value as $key_key => $value_value){
                    
                    $product_assortiment_last_row[$key_key] = $value_value;
                    
                }
                
                $product_assortiment_value_last = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_assortiment_value` WHERE ".$id_name." = '" . (int)$id . "' AND product_assortiment_id = ".  $value['product_assortiment_id']);
                
                /*
                * Опции ассортиментной позиции
                */
                
                foreach ($product_assortiment_value_last->rows as $key_key_key => $value_value_value) {
                    
                    $product_assortiment_value_row = array();
                    
                    $last_product_option_value = $this->db->query("SELECT po.required, pov.* FROM `" . DB_PREFIX  . "product_option` po LEFT JOIN " . DB_PREFIX . "product_option_value pov ON (po.product_option_id = pov.product_option_id) WHERE pov.product_option_id = ".  $value_value_value['product_option_id']." AND pov.product_option_value_id = ".  $value_value_value['product_option_value_id']." ");
                        
                    $product_assortiment_last_row['required'] = $last_product_option_value->row['required'];

                    $product_assortiment_last_row['subtract'] = $last_product_option_value->row['subtract'];

                    $product_assortiment_last_row['quantity'] = $last_product_option_value->row['quantity'];
                    
                    foreach($value_value_value as $key_key_key_key => $value_value_value_value){
                    
                        $product_assortiment_value_row[$key_key_key_key] = $value_value_value_value;

                    }
                    
                    unset($product_assortiment_value_row['product_assortiment_id']);
                    
                    $product_assortiment_value_row['price_prefix'] = $last_product_option_value->row['price_prefix'];
                    
                    $product_assortiment_value_row['price'] = $last_product_option_value->row['price'];
                    
                    $product_assortiment_value_row['points_prefix'] = $last_product_option_value->row['points_prefix'];
                    
                    $product_assortiment_value_row['points'] = $last_product_option_value->row['points'];
                    
                    $product_assortiment_value_row['weight_prefix'] = $last_product_option_value->row['weight_prefix'];
                    
                    $product_assortiment_value_row['weight'] = $last_product_option_value->row['weight'];
                    
                    $product_assortiment_last_row['product_assortiment_value'][] = $product_assortiment_value_row;
                    
                }
                
                $product_assortiment['product_assortiment'][] = $product_assortiment_last_row;
                
            }
            
        }
        $this->model('tool/distributer_tools_extecom');
        
        $this->ocext_model_tool_distributer_tools_extecom->editAssortiment($product_assortiment,$id);
                
        return;
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /*
         * Новая позиция
         */
        if(!isset($product_assortiment->row) || !$product_assortiment->row){
            
            /*
             * Сначало заведение опции в товаре, если еще нет
             */
            
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
            /*
             * Заведение значения опции - у новой позиции всегда новое значение
             */
                            
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

                            $product_option_columns = $this->getOnlyColumnsName('product_option_value');

                            $set = array();

                            foreach($new_data as $column => $data){

                                if(isset($product_option_columns[$column]) && $column!=$id_name && $column!='option_value_id' && $column!='option_id'){

                                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                                }

                            }

                            $product_option_value_id = 0;
                            
                            if($set){

                                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "', option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", option_value_id = ".$new_data['option_value_id']." ");

                                $product_option_value_id = $this->db->getLastId();
                                
                            }
                            
            /*
             * Заведение ассортиментной позиции
             */
                            if($product_option_value_id && $product_option_id){
                                
                                $set = array();
        
                                foreach ($product_assortiment_ids as $product_assortiment_id => $tmp) {

                                    if(isset($new_data[$product_assortiment_id]) && $new_data[$product_assortiment_id]){

                                        $set[] = $product_assortiment_id." = '".$new_data[$product_assortiment_id]."' ";

                                    }

                                }
                                
                                $set[] = $id_name.' = '.$id;
                                
                                $set[] = ' product_option_id = '.$product_option_id;
                                
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_assortiment` SET ".implode(' , ', $set));
                                
                                $product_assortiment_id = $this->db->getLastId();
                                
                                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_assortiment_value` SET product_assortiment_id = ".$product_assortiment_id.", product_option_value_id = ".$product_option_value_id.", option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", purchase_price = ".$new_data['purchase_price'].", recommended_price = ".$new_data['recommended_price'].", option_value_id = ".$new_data['option_value_id'].", ".$id_name.' = '.$id);
                                
                                $result = 1;
                                
                            }
            
        }
        
        else{
            
            /*
             * Сначало заведение опции в товаре, если еще нет
             */
                            $product_assortiment_id = $product_assortiment->row['product_assortiment_id'];
            
                            $product_option = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_option` WHERE ".$id_name." = '" . (int)$id . "' AND product_option_id = ".$product_assortiment->row['product_option_id']." ");

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
                                
                                $product_option_columns = $this->getOnlyColumnsName('product_option_value');
                                
                                $product_option_id = $new_data['option_value_id'];
                            
                                $product_assortiment_value = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_assortiment_value` WHERE product_assortiment_id = ".$product_assortiment_id." AND option_value_id = ".$product_option_id." ");
                                
                                if($product_assortiment_value->row){
                                    
                                    $product_option_value_id = $product_assortiment_value->row['product_option_value_id'];
                                    
                                    $set = array();

                                    foreach($new_data as $column => $data){

                                        if(isset($product_option_columns[$column])  && $column!=$id_name && $column!='option_value_id' && $column!='option_id' && !$delete_data){

                                            $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                                        }elseif(isset($product_option_columns[$column])  && $column!=$id_name && $column!='option_value_id' && $column!='option_id' && $delete_data){

                                            $set[$column] = " `".$column."` = '' ";

                                        }

                                    }

                                    if($set){

                                        $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set)." WHERE product_option_value_id = ".$product_assortiment_value->row['product_option_value_id']);

                                    }
                                    
                                    
                                }else{
                                    
                                    $product_option_columns = $this->getOnlyColumnsName('product_option_value');

                                    $set = array();

                                    foreach($new_data as $column => $data){

                                        if(isset($product_option_columns[$column]) && $column!=$id_name && $column!='option_value_id' && $column!='option_id'){

                                            $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                                        }

                                    }

                                    $product_option_value_id = 0;

                                    if($set){

                                        $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "', option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", option_value_id = ".$new_data['option_value_id']." ");

                                        $product_option_value_id = $this->db->getLastId();

                                    }
                                    
                                    /*
                            * Заведение ассортиментной позиции
                            */
                                   if($product_option_value_id && $product_option_id  && $product_assortiment_id){

                                       $this->db->query("REPLACE INTO `" . DB_PREFIX . "product_assortiment_value` SET product_assortiment_id = ".$product_assortiment_id.", product_option_value_id = ".$product_option_value_id.", option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", purchase_price = ".$new_data['purchase_price'].", recommended_price = ".$new_data['recommended_price'].", option_value_id = ".$new_data['option_value_id'].", ".$id_name.' = '.$id);

                                       $result = 1;

                                   }
                                    
                                }
            
            
        }
        
        return $result;
        
    }
    
    public function insertProductOptionValue($new_data,$id_name,$id, $delete_data = FALSE) {
        
        $result = 0;
        
        $product_option = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_option` WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." ");
        
        $ro = array();
        
        $result_demo = array();
        
        if(!$product_option->row){
            
            if(!isset($new_data['required'])){
                
                $new_data['required'] = 0;

            }
            
            if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                $product_option_id = $this->temp['get_result_demo']['param']['this_demo_id'];
                
                $this->temp['get_result_demo']['param']['this_demo_id'] += 1;
                
                $result_demo['product_option']['set'] = array("option_id = ".(int)$new_data['option_id'],"required = ".(int)$new_data['required'],$id_name." = '" . (int)$id."' ");
                
                $result_demo['product_option']['last_data'] = array();
                
                $result_demo['product_option']['where'] = array();
                
                $result_demo['product_option']['operation'] = 'insert';

            }else{
                
                $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option` SET option_id = ".(int)$new_data['option_id'].", required = ".(int)$new_data['required'].", ".$id_name." = '" . (int)$id . "' ");
            
                $product_option_id = $this->db->getLastId();
                
            }
            
        }else{
            
            $product_option_id = $product_option->row['product_option_id'];
            
            if(!isset($new_data['required'])){
                
                $new_data['required'] = $product_option->row['required'];

            }
            
            if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                $result_demo['product_option']['set'] = array("option_id = ".(int)$new_data['option_id'],"required = ".(int)$new_data['required'],$id_name." = '" . (int)$id."' ");
                
                $result_demo['product_option']['last_data'] = $product_option->row;
                
                $result_demo['product_option']['where'] = array("product_option_id = ".$product_option_id);
                
                $result_demo['product_option']['operation'] = 'update';

            }else{
                
                if($delete_data){
                
                    $this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET required = 0 WHERE product_option_id = ".$product_option_id." ");

                }else{

                    $this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET required = ".(int)$new_data['required']." WHERE product_option_id = ".$product_option_id." ");

                }
                
            }
            
        }
        
        $product_option_value_code_sql_where = '';
        
        foreach ($new_data as $c => $v) {
            
            if(is_array($v)){
                
                $product_option_value_code_sql_where .= " AND  `".$v['column_name']."` = '".$this->db->escape($v['value'])."' ";
                
                $new_data[$c] = $this->db->escape($v['value']);
                
            }
            
        }
        
        if( (!isset($this->temp['demo_mode']) || !$this->temp['demo_mode']) && isset($this->general_setting) && isset($this->general_setting['ro_enable_status']) && $this->general_setting['ro_enable_status'] && file_exists(DIR_APPLICATION.'model/extension/module/related_options.php') ){
            
            $ro = array(
                'ro_data_included' => 1,
                'ro_data' => array(),
            );
            
            $loader = $this->registry->get('load');
            
            $loader->model('extension/module/related_options');
            
            $model = $this->registry->get('model_extension_module_related_options');
            
            $ro_data = $model->getROData($id);
            
            $sku_variants = array('model','sku','upc','ean','location');
            
            $sku_variant_avail = FALSE;
            
            foreach ($sku_variants as $sku_variant) {
                
                if(isset($new_data[$sku_variant]) && $new_data[$sku_variant] && isset($this->ro[$id]) ){
                    
                    $ro = $this->ro[$id];
                    
                }
                
                if(isset($new_data[$sku_variant]) && $new_data[$sku_variant]){
                    
                    $sku_variant_avail = TRUE;
                    
                }
                
            }
            
            if(!$ro['ro_data']){
                
                $ro['ro_data'][0] = array(
                    'rovp_id' => '',
                    'use' => TRUE,
                    'rov_id' => '',
                    'ro' => array()
                );
                
            }
            
        }
        
        $product_option_value = $this->db->query("SELECT * FROM `" . DB_PREFIX  . "product_option_value` WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." AND product_option_id = ".$product_option_id." AND option_value_id = ".$new_data['option_value_id']." ".$product_option_value_code_sql_where);
        
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
            
            $product_option_value = array(
                'quantity'=>0,
                'points_prefix'=>'+',
                'points'=>0,
                'weight_prefix'=>'+',
                'weight'=>0
            );
            
            if(!isset($new_data['points_prefix'])){
                
                $new_data['points_prefix'] = '+';

            }
            
            if(!isset($new_data['weight_prefix'])){
                
                $new_data['weight_prefix'] = '+';

            }
            
            if(!isset($new_data['subtract'])){
                
                $new_data['subtract'] = 0;

            }
            
            $set = array();
            
            foreach($new_data as $column => $data){

                if($column!=$id_name && $column!='required' && $column!='value' && $column!='option_value_id' && $column!='option_id'){

                    $set[$column] = " `".$column."` = '".$this->db->escape($data)."' ";

                }

            }
            
            if($set){
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                    $result_demo['product_option_value']['set'] = $set;

                    $result_demo['product_option_value']['last_data'] = array();

                    $result_demo['product_option_value']['where'] = array();

                    $result_demo['product_option_value']['operation'] = 'insert';

                }
                else{

                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set).", ".$id_name." = '" . (int)$id . "', option_id = ".$new_data['option_id'].", product_option_id = ".$product_option_id.", option_value_id = ".$new_data['option_value_id']." ");

                }
                
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
                
                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){
                
                    $result_demo['product_option_value']['set'] = $set;

                    $result_demo['product_option_value']['last_data'] = $product_option_value->row;

                    $result_demo['product_option_value']['where'] = array("product_option_id = ".$product_option_id,"option_id = ".$new_data['option_id'],$id_name." = '" . (int)$id . "'","option_value_id = ".$new_data['option_value_id']);

                    $result_demo['product_option_value']['operation'] = 'update';

                }
                else{
                    
                    $this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET ".implode(',', $set)." WHERE ".$id_name." = '" . (int)$id . "' AND option_id = ".$new_data['option_id']." AND product_option_id = ".$product_option_id." AND option_value_id = ".$new_data['option_value_id']." ".$product_option_value_code_sql_where);
                    
                }
                
                $result = 1;
                
            }
            
        }
        
        if( (!isset($this->temp['demo_mode']) || !$this->temp['demo_mode']) && isset($this->general_setting) && isset($this->general_setting['ro_enable_status']) && $this->general_setting['ro_enable_status'] &&  file_exists(DIR_APPLICATION.'model/extension/module/related_options.php') && $sku_variant_avail ){
            
            foreach ($sku_variants as $sku_variant) {
                
                if(isset($new_data[$sku_variant]) && $ro && $new_data[$sku_variant]){
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['options'][ $new_data['option_id'] ] = $new_data['option_value_id'];
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['quantity'] = $new_data['quantity'];
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['stock_status_id'] = 0;
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['weight_prefix'] = '=';
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['weight'] = $new_data['weight'];
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['price'] = $new_data['price'];
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['relatedoptions_id'] = 0;
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]]['points_prefix'] = '=';
                    
                    foreach ($sku_variants as $sku_variant2) {
                        
                        $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]][$sku_variant2] = '';
                        
                    }
                    
                    $ro['ro_data'][0]['ro'][$sku_variant.'__'.$new_data[$sku_variant]][$sku_variant] = $new_data[$sku_variant];
                    
                    $this->ro[$id] = $ro;
                    
                }
                
            }
            
        }
        
        if(isset($this->temp['demo_mode']) && $this->temp['demo_mode'] && isset($result_demo['product_option']) && isset($result_demo['product_option_value'])){

            $this->getResultDemo($id_name,$id,'product_option_value',$result_demo['product_option_value']['set'],$result_demo['product_option_value']['where'],$result_demo['product_option_value']['last_data'],$result_demo['product_option_value']['operation']);
            
            $this->getResultDemo($id_name,$id,'product_option',$result_demo['product_option']['set'],$result_demo['product_option']['where'],$result_demo['product_option']['last_data'],$result_demo['product_option']['operation']);

        }
        
        return $result;
        
    }
    
    public function dataToStore($main_table,$id_name,$id,$store_id){
        
        if($this->showTable($main_table."_to_store", DB_PREFIX)){
            
            foreach ($store_id as $store_id_value) {

                if(isset($this->temp['demo_mode']) && $this->temp['demo_mode']){

                    if($id){
                        
                        $this->getResultDemo($id_name,$id,$main_table."_to_store",array("store_id = '" . (int)$store_id_value."' "),array($id_name." = ".$id),array(),'insert');
                        
                    }

                }
                else{
                    
                    $this->db->query("DELETE FROM `" . DB_PREFIX . $main_table."_to_store` WHERE store_id = '" . (int)$store_id_value . "' AND ".$id_name." = '" . (int)$id . "' ");
                
                    $this->db->query("INSERT INTO `" . DB_PREFIX . $main_table."_to_store` SET store_id = '" . (int)$store_id_value . "', ".$id_name." = '" . (int)$id . "' ");
                    
                }

            }
            
        }
        
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
    
    public function getDataToDB($main_table,$odmpro_tamplate_data,$log_data,$language_id,$store_id,$start,$limit=0,$general_setting,$total = FALSE, $join_main_table=''){
        
        $tables = array(
            'product'   => array(
                'attribute',
                'description',
                'discount',
                'filter',
                'option',
                'option_value',
                'related',
                'special',
                'to_category',
                'to_store',
                'image'
            ),
            'order_product'   => array(
                'option'
            )
        );
        
        $data_to_db = array();
        
        $main_data = array();
        
        $where_store = array();
        
        foreach ($store_id as $store_id_value) {
            
            $where_store[] = "  d2s.store_id = '".$store_id_value."' ";
            
        }
        
        if($main_table=='product'){
            
            $categories_filter = "";
            
            $categories_filter_where = '';
            
            if(isset($general_setting['categories_filter']) && $general_setting['categories_filter']){
                
                $categories_filter .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (d.product_id = p2c.product_id) ";
                
                if(is_array($general_setting['categories_filter'])){
                    
                    $categories_filter_sql = array();
                    
                    foreach ($general_setting['categories_filter'] as $filter_category_id) {
                        
                        if($filter_category_id){
                            
                            $categories_filter_sql[] = " p2c.category_id = '".$filter_category_id."' ";
                            
                        }
                        
                    }
                    
                    if($categories_filter_sql){
                        
                        $categories_filter_where = " AND (". implode(' OR ', $categories_filter_sql).") ";
                        
                    }
                    
                    
                }else{
                
                    $categories_filter_where = " AND p2c.category_id = ".$general_setting['categories_filter']." ";
                    
                }
                
            }
            
            $manufacturer_filter = '';
            
            if(isset($general_setting['manufacturer_filter']) && $general_setting['manufacturer_filter']){
                
                if(is_array($general_setting['manufacturer_filter'])){
                    
                    $manufacturer_filter_sql = array();
                    
                    foreach ($general_setting['manufacturer_filter'] as $filter_manufacturer_id) {
                        
                        if($filter_manufacturer_id){
                            
                            $manufacturer_filter_sql[] = " d.manufacturer_id = '".$filter_manufacturer_id."' ";
                            
                        }
                        
                    }
                    
                    if($manufacturer_filter_sql){
                        
                        $manufacturer_filter = " AND (". implode(' OR ', $manufacturer_filter_sql).") ";
                        
                    }
                    
                }
                else{
                    
                    $manufacturer_filter = " AND d.manufacturer_id = ".(int)$general_setting['manufacturer_filter'];
                    
                }
                
            }
            
            $prodict_id_from_filter = '';
            
            if(isset($general_setting['prodict_id_from_filter']) && $general_setting['prodict_id_from_filter']){
                
                $prodict_id_from_filter = " AND d.product_id >= ".(int)$general_setting['prodict_id_from_filter'];
                
            }
            
            $prodict_id_to_filter = '';
            
            if(isset($general_setting['prodict_id_to_filter']) && $general_setting['prodict_id_to_filter']){
                
                $prodict_id_to_filter = " AND d.product_id <= ".(int)$general_setting['prodict_id_to_filter'];
                
            }
            
            $id_name = $main_table.'_id';
            
            $select = " d.* "; 
            
            $limit_sql = " LIMIT ".$start.", ".$limit;
            
            if($total){
                
                $select = " COUNT(DISTINCT d.".$id_name.") AS total "; 
                
                $limit_sql = " ";
                
            }
            
            if(isset($odmpro_tamplate_data['type_data_general_settings']['product']['where_rules'])){
                
                $prodict_id_to_filter_where_rules = $this->getWhereExport($odmpro_tamplate_data['type_data_general_settings']['product']['where_rules']);
                
                if($prodict_id_to_filter_where_rules){
                    
                    $prodict_id_to_filter .= " AND ( ".$prodict_id_to_filter_where_rules.") ";
                    
                }
                 
            }
            
            $sql = "SELECT ".$select."  FROM " . DB_PREFIX . $main_table." d ".$categories_filter." LEFT JOIN " . DB_PREFIX . $main_table. "_to_store d2s ON (d.".$id_name." = d2s.".$id_name.") WHERE " . implode(' OR ',$where_store).$manufacturer_filter.$categories_filter_where.$prodict_id_to_filter.$prodict_id_from_filter.$limit_sql;
            
            $main_data = $this->db->query($sql);
            
            if($total){
                
                return $main_data;
                
            }
            
            if($main_data->rows){
                
                foreach($main_data->rows as $data){
                    
                    $id = $data[$id_name];
                    
                    $data_to_db[$id][$main_table]['row'] = $data;
                    
                    $data_to_db[$id][$main_table]['columns'] = $this->getColumnsByTable($main_table);
                    
                    foreach ($tables[$main_table] as $related_table) {
                        
                        if($this->showTable($main_table.'_'.$related_table, DB_PREFIX)){
                            
                            if(stristr($main_table.'_'.$related_table, '_description') || stristr($main_table.'_'.$related_table, '_attribute')){
                                
                                $sql = "SELECT * FROM " . DB_PREFIX . $main_table.'_'.$related_table. " WHERE " . $id_name." = ".$id." AND language_id = ".$language_id;
                                
                            }else{
                                
                                $sql = "SELECT * FROM " . DB_PREFIX . $main_table.'_'.$related_table. " WHERE " . $id_name." = ".$id." ";
                                
                            }
                            
                            $related_data = $this->db->query($sql);
                            
                            if(stristr($main_table.'_'.$related_table, '_description')){
                                
                                foreach($related_data->rows as $row_key => $row_value){
                                    
                                    if(isset($row_value['description'])){
                                        
                                        $related_data->rows[$row_key]['description'] = html_entity_decode($row_value['description']);
                                        
                                    }
                                    
                                }
                                
                            }
                            
                            $data_to_db[$id][$main_table.'_'.$related_table]['rows'] = $related_data->rows;
                            
                            $data_to_db[$id][$main_table.'_'.$related_table]['columns'] = $this->getColumnsByTable($main_table.'_'.$related_table);
                            
                        }
                        
                    }
                    
                }
                
                
            }else{
                
                $log_data['__line__'] = __LINE__; 

                $log_data['details_message'] = $log_data['details_message'] = $this->getStringFromArray($store_id,$main_table.'_to_store'.': '); ; 

                $log_write_row = array(
                    'log_data' => $log_data,
                    'message' => array('warning'=>sprintf($this->language->get('import_warning_empty_main_data'),$main_table,$start,$limit)),
                    'action'    => $log_data['type_process']
                );
                
                $this->setLogDataRow($log_write_row,$log_data);
                
            }
            
        }
        
        elseif($main_table=='order_product'){
            
            $where = array();
            
            if(isset($general_setting['order_id_from_filter']) && $general_setting['order_id_from_filter']){
                
                $where[] = " d.order_id >= ".(int)$general_setting['order_id_from_filter'];
                
            }
            
            if(isset($general_setting['order_id_to_filter']) && $general_setting['order_id_to_filter']){
                
                $where[] = " d.order_id <= ".(int)$general_setting['order_id_to_filter'];
                
            }
            
            if(isset($general_setting['date_from_filter']) && $general_setting['date_from_filter']){
                
                $where[] = " DATE(d2.date_added) >= DATE('".$general_setting['date_from_filter']."') ";
                
            }
            
            if(isset($general_setting['date_to_filter']) && $general_setting['date_to_filter']){
                
                $where[] = " DATE(d2.date_added) <= DATE('".$general_setting['date_to_filter']."') ";
                
            }
            
            if(isset($general_setting['today_filter']) && $general_setting['today_filter']){
                
                $date = date("Y-m-d");
                
                if($general_setting['today_filter']==1){
                    
                    $where[] = " DATE(d2.date_added) = DATE('".$date."') ";
                    
                }elseif($general_setting['today_filter']==2){
                    
                    $time = time()-(24*60*60);
                    
                    $date = date("Y-m-d",$time);
                    
                    $where[] = " DATE(d2.date_added) = DATE('".$date."') ";
                    
                }elseif($general_setting['today_filter']==3){
                    
                    $date_to = date("Y-m-d");
                    
                    $time = time()-(7*24*60*60);
                    
                    $date_from = date("Y-m-d",$time);
                    
                    $where[] = " DATE(d2.date_added) >= DATE('".$date_from."') ";
                    
                    $where[] = " DATE(d2.date_added) <= DATE('".$date_to."') ";
                    
                }
                
            }
            
            $id_name = $main_table.'_id';
            
            $select = " d.*, d2.*  "; 
            
            $limit_sql = " LIMIT ".$start.", ".$limit;
            
            if($total){
                
                $select = " COUNT(DISTINCT d.".$id_name.") AS total "; 
                
                $limit_sql = " ";
                
            }
            
            if(isset($odmpro_tamplate_data['type_data_general_settings']['order_product']['where_rules'])){
                
                $order_to_filter_where_rules = $this->getWhereExport($odmpro_tamplate_data['type_data_general_settings']['order_product']['where_rules']);
                
                if($order_to_filter_where_rules){
                    
                    $where[] = " ( ".$order_to_filter_where_rules.") ";
                    
                }
                 
            }
            
            if($where){
                
                $where = ' WHERE '.implode(' AND ',$where);
                
            }else{
                
                $where = '';
                
            }
            
            $join_main_table = '';
                        
            $where_joined_main_table = '';

            if(isset($odmpro_tamplate_data['type_data_general_settings']['product']['where_rules'])){

                $order_to_filter_where_rules = $this->getWhereExport($odmpro_tamplate_data['type_data_general_settings']['product']['where_rules'],'p');

                if($order_to_filter_where_rules){

                    $join_main_table = " LEFT JOIN " . DB_PREFIX . "product p ON (d.product_id = p.product_id) ";

                    $where_joined_main_table = " ( ".$order_to_filter_where_rules.") ";

                }

            }

            if(isset($odmpro_tamplate_data['type_data_general_settings']['order']['where_rules'])){

                $order_to_filter_where_rules = $this->getWhereExport($odmpro_tamplate_data['type_data_general_settings']['order']['where_rules'],'d2');

                if($order_to_filter_where_rules){
                    
                    if($where_joined_main_table){
                        
                        $where_joined_main_table .= " AND ( ".$order_to_filter_where_rules.") ";
                        
                    }else{
                        
                        $where_joined_main_table = " ( ".$order_to_filter_where_rules.") ";
                        
                    }

                }

            }
            
            if(!$where && $where_joined_main_table){
                
                $where = " WHERE ".$where_joined_main_table;
                
            }elseif($where && $where_joined_main_table){
                
                $where .= " AND ".$where_joined_main_table;
                
            }
            
            $sql = "SELECT ".$select."  FROM `" . DB_PREFIX . $main_table."` d LEFT JOIN `" . DB_PREFIX ."order` d2 ON (d.order_id = d2.order_id)" . ' '.$join_main_table.' '.$where.' '.' GROUP BY d.order_product_id  '.$limit_sql.' ';
            
            $main_data = $this->db->query($sql);
            
            if($total){
                
                return $main_data;
                
            }
            
            if($main_data->rows){
                
                foreach($main_data->rows as $data){
                    
                    $id = $data[$id_name];
                    
                    $data_to_db[$id][$main_table]['row'] = $data;
                    
                    $data_to_db[$id][$main_table]['columns'] = $this->getColumnsByTable($main_table);
                    
                    foreach ($tables[$main_table] as $related_table) {
                        
                        if($related_table=='option'){
                            
                            $sql = "SELECT * FROM " . DB_PREFIX . "order_option "." WHERE order_product_id = ".$data['order_product_id'];
                               
                            $related_data = $this->db->query($sql);
                                 
                            $data_to_db[$id]['order_option']['rows'] = $related_data->rows;

                            $data_to_db[$id]['order_option']['columns'] = $this->getColumnsByTable('order_option');
                            
                            $sql = "SELECT * FROM " . DB_PREFIX . "product "." WHERE product_id = ".$data['product_id'];
                               
                            $related_data = $this->db->query($sql);
                                 
                            $data_to_db[$id]['product_main_data']['row'] = $related_data->row;

                            $data_to_db[$id]['product_main_data']['columns'] = $this->getColumnsByTable('product');
                            
                        }
                        
                    }
                    
                }
                
                
            }else{
                
                $log_data['__line__'] = __LINE__; 

                $log_data['details_message'] = $log_data['details_message'] = $this->getStringFromArray($store_id,$main_table.'_to_store'.': '); ; 

                $log_write_row = array(
                    'log_data' => $log_data,
                    'message' => array('warning'=>sprintf($this->language->get('import_warning_empty_main_data'),$main_table,$start,$limit)),
                    'action'    => $log_data['type_process']
                );
                
                $this->setLogDataRow($log_write_row,$log_data);
                
            }
            
        }
        
        return $data_to_db;
        
    }
    
    public function getDataByGroupIdAndProducID($language_id, $table , $product_id=array()){
        
        $group_data = array();
        
        foreach($product_id as $product_id_row){
            
            $product_group_query = $this->db->query("SELECT ag.".$table."_group_id, agd.name FROM " . DB_PREFIX . "product_".$table." pa LEFT JOIN " . DB_PREFIX . "".$table." a ON (pa.".$table."_id = a.".$table."_id) LEFT JOIN " . DB_PREFIX . "".$table."_group ag ON (a.".$table."_group_id = ag.".$table."_group_id) LEFT JOIN " . DB_PREFIX . "".$table."_group_description agd ON (ag.".$table."_group_id = agd.".$table."_group_id) WHERE pa.product_id = '" . (int)$product_id_row['product_id'] . "' AND agd.language_id = '" . (int)$language_id . "' GROUP BY ag.".$table."_group_id ORDER BY ag.sort_order, agd.name");

            foreach ($product_group_query->rows as $product_group) {
                
                    $product_data = array();

                    $patext = "";
                    
                    $palanguege_id = "";
                    
                    if($table == 'attribute'){
                        
                        $patext = ", pa.text ";
                        
                        $palanguege_id = " AND pa.language_id = '" . (int)$language_id . "'";
                        
                    }
                    
                    
                    $product_query = $this->db->query("SELECT a.".$table."_id, ad.name".$patext."  FROM " . DB_PREFIX . "product_".$table." pa LEFT JOIN " . DB_PREFIX . "".$table." a ON (pa.".$table."_id = a.".$table."_id) LEFT JOIN " . DB_PREFIX . "".$table."_description ad ON (a.".$table."_id = ad.".$table."_id) WHERE pa.product_id = '" . (int)$product_id_row['product_id'] . "' AND a.".$table."_group_id = '" . (int)$product_group[$table.'_group_id'] . "' AND ad.language_id = '" . (int)$language_id . "' ".$palanguege_id." ORDER BY a.sort_order, ad.name");

                    foreach ($product_query->rows as $product_data_row) {
                        if($table == 'attribute'){
                            
                            $product_data[] = array(
                                    $table.'_id' => $product_data_row[$table.'_id'],
                                    'name'         => $product_data_row['name'],
                                    'text'         => $product_data_row['text']
                            );
                            
                        }else{
                            
                            $product_data[] = array(
                                    $table.'_id' => $product_data_row[$table.'_id'],
                                    'name'         => $product_data_row['name']
                            );
                            
                        }
                            
                    }

                    $group_data[] = array(
                            $table.'_group_id' => $product_group[$table.'_group_id'],
                            'name'               => $product_group['name'],
                            $table          => $product_data
                    );
            }
            
        }
        
        return $group_data;
        
    }
    
    public function getValueFromDB($table , $column, $where = array(), $language_id = 0){
        
        if($where){
            
            foreach($where as $where_column => $where_value){
                
                $set_where[] = $where_column." = '".$where_value."' ";
                
            }
            
        }
        
        $sql = " SELECT  ".$column." FROM ".DB_PREFIX.$table." ";
        
        if($set_where){
            
            $sql .= " WHERE ".implode(" AND ",$set_where);
            
        }
        
        $result = $this->db->query($sql);
        
        
        
        if(isset($result->row[$column])){
            
            return $result->row[$column];
            
        }else{
            
            return '';
            
        }
        
    }
    
    
    public function getProductOptions($product_id, $language_id) {
		$product_option_data = array();

		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$language_id . "' ORDER BY o.sort_order");

		foreach ($product_option_query->rows as $product_option) {
			$product_option_value_data = array();

			$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$language_id . "' ORDER BY ov.sort_order");

			foreach ($product_option_value_query->rows as $product_option_value) {
				$product_option_value_data[] = array(
					'product_option_value_id' => $product_option_value['product_option_value_id'],
					'option_value_id'         => $product_option_value['option_value_id'],
					'name'                    => $product_option_value['name'],
					'image'                   => $product_option_value['image'],
					'quantity'                => $product_option_value['quantity'],
					'subtract'                => $product_option_value['subtract'],
					'price'                   => $product_option_value['price'],
					'price_prefix'            => $product_option_value['price_prefix'],
					'weight'                  => $product_option_value['weight'],
					'weight_prefix'           => $product_option_value['weight_prefix'],
                                        'points'                  => $product_option_value['points'],
					'points_prefix'           => $product_option_value['points_prefix']
				);
			}

			$product_option_data[] = array(
				'product_option_id'    => $product_option['product_option_id'],
				'product_option_value' => $product_option_value_data,
				'option_id'            => $product_option['option_id'],
				'name'                 => $product_option['name'],
				'type'                 => $product_option['type'],
				'value'                => $product_option['value'],
				'required'             => $product_option['required']
			);
		}

		return $product_option_data;
	}

public function getProcessHistoryStatus($odmpro_tamplate_data_id,$supplier_name){

    $result = array();
    
    $result['error'] = "Ошибка загрузки истории операций модуля поставщика ".$supplier_name;
    
    if($this->showTable('anycsv_sinch_supplier', DB_PREFIX) && $odmpro_tamplate_data_id && $supplier_name && file_exists(DIR_APPLICATION.'model/tool/'.$supplier_name.'_plugin.php')){
        
        $this->model('tool/'.$supplier_name.'_plugin');
        
        $result = $this->{'ocext_model_tool_'.$supplier_name.'_plugin'}->getProcessHistoryStatus($odmpro_tamplate_data_id);
        
    }
    
    return $result;

}

public function getOcextDmproStepOneSettingsSinchSupplier($tamplate_data_selected,$supplier_name){
    
    $ocext_dmpro_step_one_settings_sinch_supplier = '';
    
    if($this->showTable('anycsv_sinch_supplier', DB_PREFIX) && $supplier_name && file_exists(DIR_APPLICATION.'model/tool/'.$supplier_name.'_plugin.php')){
        
        $this->model('tool/'.$supplier_name.'_plugin');
        
        $ocext_dmpro_step_one_settings_sinch_supplier = $this->{'ocext_model_tool_'.$supplier_name.'_plugin'}->getOcextDmproStepOneSettingsSinchSupplier($tamplate_data_selected);
        
    }
    
    return $ocext_dmpro_step_one_settings_sinch_supplier;
    
}


public function getAnycsvSinchSupplier(){
    
    $results = array('supplier_setting'=>array(),'process_history'=>array());
    
    if($this->showTable('anycsv_sinch_supplier', DB_PREFIX) && $this->lic){
        
        $anycsv_sinch_supplier = $this->db->query("SELECT * FROM " . DB_PREFIX . "anycsv_sinch_supplier ");
        
        if($anycsv_sinch_supplier->rows){
            
            foreach ($anycsv_sinch_supplier->rows as $anycsv_sinch_supplier_row) {
                
                $this->model('tool/'.$anycsv_sinch_supplier_row['supplier_name'].'_plugin');
                
                $setting = $this->{'ocext_model_tool_'.$anycsv_sinch_supplier_row['supplier_name'].'_plugin'}->getSettings('supplier_setting');
                
                $results['process_history'][$anycsv_sinch_supplier_row['supplier_name']] = $this->{'ocext_model_tool_'.$anycsv_sinch_supplier_row['supplier_name'].'_plugin'}->getProcessHistory();
                
                $results['anycsv_sinch_supplier_tamplate_data_add_column'][$anycsv_sinch_supplier_row['supplier_name']] = $this->{'ocext_model_tool_'.$anycsv_sinch_supplier_row['supplier_name'].'_plugin'}->getSupplierAddColumn();
                
                if(isset($setting['supplier_setting'])){
                    
                    foreach($setting['supplier_setting'] as $supplier_setting){
                        
                        if(isset($supplier_setting['value']['status']) && $supplier_setting['value']['status']){
                            
                            $results['supplier_setting'][] = array(
                                'setting_id' => $supplier_setting['setting_id'],
                                'title' => mb_strtoupper($anycsv_sinch_supplier_row['supplier_name']).': '.$supplier_setting['value']['title'],
                                'supplier_name' => $anycsv_sinch_supplier_row['supplier_name']
                            );
                            
                        }
                        
                    }
                    
                }
                
            } 
            
        }
        
    }
    
    return $results;
    
}

public function getPriceBySettings($base_value,$additinal_settings){
    
    $price = $this->getFloat($base_value);
    
    $base_value = $this->getFloat($base_value);
                                        
    if(isset($additinal_settings['price_rate']) && $additinal_settings['price_rate']!==''){

        $price *= $this->getFloat($additinal_settings['price_rate']);

    }

    if(isset($additinal_settings['price_delta']) && $additinal_settings['price_delta']!==''){

        $price *= $this->getFloat($additinal_settings['price_delta']);

    }
    
    if(isset($additinal_settings['price_range']) && $additinal_settings['price_range']){
        
        $price_range = $additinal_settings['price_range'];
        
        for($r=0;$r<count($price_range);$r++){
            
            $from = $this->getFloat($price_range[$r]['from']);
            
            $to = $this->getFloat($price_range[$r]['to']);
            
            $multiply = $this->getFloat($price_range[$r]['multiply']);
            
            $plus = $this->getFloat($price_range[$r]['plus'],TRUE);
            
            if($multiply>0 || $plus!==''){
                
                if($base_value>=$from && $base_value<$to){
                    
                    if($multiply){
                        
                        $price *= $multiply;
                        
                    }
                    
                    if($plus!==''){
                        
                        $price += $plus;
                        
                    }
                    
                }
                
            }
            
        }
        
    }
    
    if($this->convert_currency){
        
        $price = $this->currency->convert($price,$this->convert_currency['from'],$this->convert_currency['to']);
        
    }
    
    if(isset($additinal_settings['price_around']) && !$additinal_settings['price_around']){

        $price = round($price,0);

    }
    
    return $price;
    
}
        
                /*
*This method takes stream file ->$file
*and encoding settings ->$type_encoding
* 
 * @return string(utf-8)  
 *  
 */
    public function fileEncodingUTF8($file, $type_encoding){
        if($type_encoding=="UTF-8"){
            return $file;
        }else{
            $file_utf8 = mb_convert_encoding($file, "UTF-8", $type_encoding);
            return $file_utf8;
        }
    }
    
    public function getLincenceStatus() {
        
        $lic = $this->setting_version->getLincenceStatus();
        $return = array('error'=>'','success'=>'','status'=>FALSE);
        if(isset($lic['lic'])){
            
            $this->lic = $lic['lic'];
            $return['status'] = $this->lic;
        }
        
        if(isset($lic['lic_error']) && $lic['lic_error']){
            $return['error'] = $lic['lic_error'];
        }
        elseif(isset($lic['lic_success']) && $lic['lic_success']){
            $return['success'] = $lic['lic_success'];
        }
        
        return $return;
        
    }
    
    public function model($model, $data = array(),$c=false,$admin='admin') {
        
        
	$module = "csv_ocext_dmpro_7";
	if(file_exists($_SERVER["DOCUMENT_ROOT"]."/system/library/vendor/ocext/".str_replace('_7','',$module).".php")){
	    require $_SERVER["DOCUMENT_ROOT"]."/system/library/vendor/ocext/".str_replace('_7','',$module).".php";
	}
        
        
    }
    
    public function getAnyXMLStatus() {

        $dir = dirname(dirname(__DIR__)).'/';
        
        $status = FALSE;
        
        if($this->lic && file_exists($dir.'controller/'.$this->path_oc_version.'/anyxml_ocext_plugin.php')){

            $this->model('tool/anyxml_ocext_plugin');

            $status = $this->ocext_model_tool_anyxml_ocext_plugin->getLicense();

        }
        
        return $status;

    }
        
    public function getAnyXLStatus() {
        
        $dir = dirname(dirname(__DIR__)).'/';
        
        $status = FALSE;
        
        if($this->lic && file_exists($dir.'model/tool/anyxls_ocext_plugin.php')){

            $this->model('tool/anyxls_ocext_plugin');

            $status = $this->ocext_model_tool_anyxls_ocext_plugin->getLicense();

        }
        
        return $status;

    }
    
    public function getTypesDataSelfColumns($odmpro_tamplate_data,$type_process,$type_data,$columns,$self_column_id,$export_columns){
        
        return $this->setting_version->getTypesDataSelfColumns($odmpro_tamplate_data,$type_process,$type_data,$columns,$self_column_id,$export_columns);
        
    }
    
    public function getSelfColumn($odmpro_tamplate_data,$types_data,$type_process){
        
        return $this->setting_version->getSelfColumn($odmpro_tamplate_data,$types_data,$type_process);
        
    }
    
    public function getAnyYMLResult($odmpro_tamplate_data) {
        
        $result = array('error'=>'','file_upload'=>'');
        
        $yml_file_name = '';
        
        $supplier_feed_source = '';
        
        if(isset($odmpro_tamplate_data['supplier_feed_source']) && $odmpro_tamplate_data['supplier_feed_source']){
            
            $supplier_feed_source = $odmpro_tamplate_data['supplier_feed_source'];
            
        }
        
        if($odmpro_tamplate_data['file_url']){
            
            $this->odmpro_tamplate_data = $odmpro_tamplate_data;
            
            $yml_file_name = $this->getFileNameByURL($odmpro_tamplate_data['file_url'],FALSE,$supplier_feed_source,$odmpro_tamplate_data);
            
        }elseif($odmpro_tamplate_data['file_upload']){
            
            $yml_file_name = $this->getFileNameByFileName($odmpro_tamplate_data['file_upload'],FALSE,$supplier_feed_source);
            
        }
        
        if(is_string($yml_file_name) && $yml_file_name && isset($this->setting_version_settings['functional']['yml_to_dsv']) && $this->setting_version_settings['functional']['yml_to_dsv']){
            
            $result = $this->setting_version->ymlToDSV($yml_file_name,$odmpro_tamplate_data);
            
        }else{
            
            $result['error'] = "Не удалось получить YML файл";
            
        }
        
        return $result;
        
    }
    
    
    public function getAnyXLSResult($odmpro_tamplate_data){
            
        $this->model('tool/anyxls_ocext_plugin');

        $result = $this->ocext_model_tool_anyxls_ocext_plugin->getAnyXLSResult($odmpro_tamplate_data);

        return $result;

    }

    public function getAnyXMLResult($odmpro_tamplate_data){

        $this->model('tool/anyxml_ocext_plugin');

        $result = $this->ocext_model_tool_anyxml_ocext_plugin->getAnyXMLResult($odmpro_tamplate_data);

        return $result;

    }

    public function getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation=0) {

        $anycsv_sinch_supplier_setting_id_parts = explode('___',$odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']);

        $supplier_name = $anycsv_sinch_supplier_setting_id_parts[0];

        $result['error'] = "Модуль работы с данными поставщика ".$supplier_name." не найден";
        
        $dir = dirname(dirname(__DIR__)).'/';

        if($this->lic && file_exists($dir.'model/tool/'.$supplier_name.'_plugin.php')){

            $this->model('tool/'.$supplier_name.'_plugin');

            $result = $this->{'ocext_model_tool_'.$supplier_name.'_plugin'}->getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation);

        }

        return $result;

    }
    
    public function updateSmartExchange($post){
        
        $this->setting_version->updateSmartExchange($post);
        
    }
    
    public function getAdvancedSettings($additinal_column,$odmpro_tamplate_data,$type_process,$type_data){
        
        $result = $this->setting_version->getAdvancedSettings($additinal_column,$odmpro_tamplate_data,$type_process,$type_data);
        
        return $result;
        
    }
    
    public function getEdistributierAdaptorVeiw($additinal_column,$odmpro_tamplate_data,$type_process,$type_data){
        
        $result = $this->edistributier_adaptor->get_edistributier_adaptor_veiw($additinal_column,$odmpro_tamplate_data,$type_process,$type_data);
        
        return $result;
        
    }
    
    public function getSettingVersionDataByKey($key) {
        
        $value = $this->setting_version->get($key);
        
        return $value;
        
    }
    
    public function getIConvCodes($array_flip=FALSE){
        $codes = $this->setting_version->getIConvCodes();
        if($array_flip){
            $codes = array_flip($codes);
        }
        return $codes;
    }
    
    public function getSqlWhereOperators() {
        $operators = $this->setting_version->getSqlWhereOperators();
        return $operators;
    }
    
    public function convertCSVValue($value,$from='UTF-8',$to='UTF-8'){
        
        $match = $this->getIConvCodes();
        
        $from = $match[$from];
        
        $to = $match[$to];
        
        if($from==$to){
            
            return $value;
            
        }
        
        $value = (string)$value;
        
        $value = mb_convert_encoding($value,$to,$from);
        
        return $value;
        
    }
    
    public function getLanguages($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "language";

			$sort_data = array(
				'name',
				'code',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY sort_order, name";
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
		} else {
			$language_data = $this->cache->get('language');

			if (!$language_data) {
				$language_data = array();

				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "language ORDER BY sort_order, name");

				foreach ($query->rows as $result) {
					$language_data[$result['code']] = array(
						'language_id' => $result['language_id'],
						'name'        => $result['name'],
						'code'        => $result['code'],
						'locale'      => $result['locale'],
						'image'       => $result['image'],
						'directory'   => $result['directory'],
						'sort_order'  => $result['sort_order'],
						'status'      => $result['status']
					);
				}

				$this->cache->set('language', $language_data);
			}

			return $language_data;
		}
	}
        
        public function getPhpResult($csv_row,$php,$num_row=0,$result_xpath='',$entity_id=null) {
            
            $result_array = array();
            
            $csv_row_result = $csv_row;
            
            foreach ($csv_row as $field => $value) {
                
                if(is_array($value) && $value['value']){
                    
                    $value = $value['value'];
                    
                }elseif(is_array($value) && $value['values'] && count($value['values'])==1){
                    
                    $value = $value['values'][0];
                    
                }
                
                ${$field} = $value;
                
            }
            
            $php = html_entity_decode($php);
            
            $php = str_replace('"', "'", $php);
            
            eval($php);
            
            if(isset($result)){
                
                $result_array['result'] = $result;
                
            }
            
            foreach ($csv_row_result as $field => $value) {
                
                if(isset(${$field}) /*&& $value!==${$field}*/ && !is_array($value)){
                    
                    $csv_row_result[$field] = ${$field};
                    
                }elseif(isset(${$field}) && !is_array($value) && $value['value']!==${$field}){
                    
                    $csv_row_result[$field]['value'] = ${$field};
                    
                }elseif(isset(${$field}) && is_array($value) && $value['values'] && count($value['values'])==1 && $value['values'][0]!==${$field}){
                    
                    $csv_row_result[$field]['values'][0] = ${$field};
                    
                }else{
                    
                    unset($csv_row_result[$field]);
                    
                }
                
            }
            
            
            
            if(isset($csv_row_result) && $csv_row_result){
                
                $result_array['csv_row'] = $csv_row_result;
                
            }
            
            return $result_array;
            
        }
        
        
    public function checkValueByOperatorAndFieldValue($field_value,$value,$operator) {

        $result = FALSE;
            
        $operator = $this->replaceOperator($operator);

        if($operator){

            if($operator=='like' && strstr($field_value, $value)){

                $result = TRUE;

            }elseif($operator=='not_like' &&  !strstr($field_value, $value)){

                $result = TRUE;

            }else{

                if($operator=='<' &&  $field_value < $value){

                    $result = TRUE;

                }elseif($operator=='<='  && $field_value <= $value){

                    $result = TRUE;

                }elseif($operator=='=' &&  $field_value == $value){

                    $result = TRUE;

                }elseif($operator=='>=' &&  $field_value >= $value){

                    $result = TRUE;

                }elseif($operator=='>' &&  $field_value > $value){

                    $result = TRUE;

                }elseif($operator=='!=' &&  $field_value != $value){

                    $result = TRUE;

                }

            }

        }
        
        return $result;

    }
    
    public function getValueByRuleText($csv_row,$text){
        
        $result = array();
        
        foreach ($text as $key => $value) {
            
            if(isset($csv_row[$value]) && !is_array($csv_row[$value])){
                
                $result[] = $csv_row[$value];
                
            }elseif(isset($csv_row[$value]) && is_array($csv_row[$value]) && $csv_row[$value]['value']){
                
                $result[] = $csv_row[$value]['value'];
                
            }elseif(isset($csv_row[$value]) && is_array($csv_row[$value]) && count($csv_row[$value]['values'])==1){
                
                $result[] = $csv_row[$value]['values'][0];
                
            }elseif($value!=='skip_this_part' && !isset($csv_row[$value])){
                
                $result[] = $value;
                
            }
            
        }
        
        return implode('',$result);
        
    }
    
    public function getCacheFileByURL($url,$template_data=array()) {
        
        $check_url = array('http:'=>0,'https:'=>0,'ftp:'=>0);

        $url_parts = explode('/', $url);
        
        foreach ($url_parts as $key => $url_part) {
            if(isset($check_url[$url_part])){
                unset($check_url[$url_part]);
            }

        }

        if(count($check_url)>2){
            return '';
        }

        $file_exists = @fopen($url, "r");
        
        $user_agents = array("Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)","Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)","Opera/9.80 (Windows NT 5.1; U; ru) Presto/2.7.62 Version/11.01","Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1;)");
        
        if(isset($template_data['def_headers']) && $template_data['def_headers']){
            
            shuffle($user_agents); 
            
        }
        
        $user_agent = $user_agents[0];
        
        $new_file_upload_result = 'cache_xpath_anycsvxlspro.html';
        
        $handle = fopen(DIR_CACHE.$new_file_upload_result,"w");
            
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, $user_agent); 

        curl_setopt($curl, CURLOPT_URL, html_entity_decode($url));

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_FILE, $handle);

        if(!curl_exec($curl)){

            curl_close($curl);

            fclose($handle);

        }else{

            fflush($handle);

            fclose($handle);

            curl_close($curl);

        }

        if(!file_exists(DIR_CACHE.$new_file_upload_result)){

            $new_file_upload_result = '';

        }
        
        if(isset($template_data['xpath_sleep'])){
            
            $xpath_sleep = (int)trim($template_data['xpath_sleep']);
            
            if($xpath_sleep){
                
                sleep($xpath_sleep);
                
            }
            
        }

        return $new_file_upload_result; 
    }
    
    
    public function getXPathResult($html_file,$xpath,$params=array()){
        
        $result = '';
        
        $query = '//body';
        
        if($xpath!==''){
            
            $query = $xpath;
            
        }
        
        $query = html_entity_decode(trim($query));
        
        if(file_exists(DIR_CACHE.$html_file) && $query){
            
            $doc = new DOMDocument();
            
            libxml_use_internal_errors(TRUE);
            
            $doc->loadHTMLFile(DIR_CACHE.$html_file);
            
            $xpath = new DOMXpath($doc);
            
            $elements = $xpath->query($query);
            
            $result = '';
            
            if($elements){
                
                foreach($elements as $element){

                    $result .= utf8_decode($doc->saveHTML($element));

                }
                
            }
            
            if(isset($params['xpath_stags']) && $params['xpath_stags']){
                
                $result = strip_tags($result);
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getWhereExport($product_data,$table_alias='d') {

        $where = array();
        
        foreach ($product_data as $product_data_where){

            if($product_data_where['product_field'] && $product_data_where['operator']){

                $operator = $this->replaceOperator($product_data_where['operator']);

                $product_field = $product_data_where['product_field'];

                $value = trim($product_data_where['value']);
                
                $sql_operator = $product_data_where['logic'];

                if($operator && $product_field){
                    
                    $sql = '';

                    if($operator=='like_right'){

                        $sql = ' '.$table_alias.'.'.$product_field.' LIKE  "%'.$this->db->escape($value).'" ';

                    }elseif($operator=='like_left'){

                        $sql = ' '.$table_alias.'.'.$product_field.' LIKE  "'.$this->db->escape($value).'%" ';

                    }elseif($operator=='like'){

                        $sql = ' '.$table_alias.'.'.$product_field.' LIKE  "%'.$this->db->escape($value).'%" ';

                    }elseif($operator=='not_like_right'){

                        $sql = ' '.$table_alias.'.'.$product_field.' NOT LIKE  "%'.$this->db->escape($value).'" ';

                    }elseif($operator=='not_like_left'){

                        $sql = ' '.$table_alias.'.'.$product_field.' NOT LIKE  "'.$this->db->escape($value).'%" ';

                    }elseif($operator=='not_like'){

                        $sql = ' '.$table_alias.'.'.$product_field.' NOT LIKE  "%'.$this->db->escape($value).'%" ';

                    }else{

                        $sql = ' '.$table_alias.'.'.$product_field.' '.$operator.' "'.$this->db->escape($value).'" ';

                    }

                    $where[][$sql_operator] = $sql;
                    
                }
                
            }

        }
        
        $where_result = '';
        
        if($where){
            
            $count = 1;
            
            foreach ($where as $sql_where) {
                
                $sql_operator = key($sql_where);
                
                $sql_where = current($sql_where);
                
                if($count<count($where)){
                    
                    $where_result .= $sql_where.$sql_operator;
                    
                }else{
                    
                    $where_result .= $sql_where;
                    
                }
                
                $count++;
                
            }
            
        }
        
        return $where_result;

    }
    
    public function evalPhp($php,$entity_id){
        
        //$option = $this->db->query(' SELECT * FROM '.DB_PREFIX.'option_description WHERE name != "Размер" AND (name LIKE "%размер%" OR name LIKE "%Размер%") ');
        
        //var_dump($option);
        
        $php = html_entity_decode($php);
        
        eval($php);
        
    }
    
    public function getAnyCSVActionsCache($file_name) {
	
	$result = NULL;
	
	$max_age_per_seconds = 24*60*60;
	
	$file_name = DIR_CACHE.$file_name;
	
	if(is_file($file_name) && file_exists($file_name) && (filemtime($file_name)+$max_age_per_seconds) > time() ){

	    $result = json_decode(file_get_contents($file_name),TRUE);

	}
	elseif(is_file($file_name) && file_exists($file_name)){
	    
	    unlink($file_name);
	    
	}
	
	return $result;
	
    }
    
    public function deleteRelatedDataBefore($tables,$wheres){
	
	$where_sql = array();
	
	foreach($wheres as $column => $value){
	    
	    $where_sql[] = " `".$column."` = '".$this->db->escape($value)."' ";
	    
	}
	
	$where_sql = implode(' AND ', $where_sql);
	
	foreach($tables as $table){
	    
	    $this->db->query("DELETE FROM `" . DB_PREFIX .$table. "` WHERE ".$where_sql);
	    
	}
	
    }
    
    public function writeAnyCSVActionsCache($file_name,$data) {
	
	$file_name = DIR_CACHE.$file_name;
	
	$handle = fopen($file_name, 'w+');

	fwrite( $handle, json_encode($data,JSON_UNESCAPED_UNICODE) );

	fclose($handle);
	
	return ;
	
    }
    
    public function getFindReplace($from,$to,$string){
        
        return $this->setting_version->getFindReplace($from,$to,$string);
        
    }
    
    public function uninstall() {

        $this->setting_version->uninstall();
        
        

    }
    
    public function getSupplierSettingView($source_id,$template_data_on_step_one) {
            
        return $this->setting_version->getSupplierSettingView($source_id,$template_data_on_step_one);

    }
    
    public function getRecommendedParams($config_odmpro_template_data){
        /*
        $query = $this->db->query("SHOW VARIABLES LIKE 'max_allowed_packet'");
        
        $result = array(
            'max_allowed_packet' => 16
        );
        
        $config_size = mb_strlen(json_encode($config_odmpro_template_data), '8bit')/1024;
        
        $h = fopen(DIR_CACHE.'/anycsv_tmp','w+');
        fwrite($h, json_encode($config_odmpro_template_data));
        fclose($h);
        $config_size = filesize(DIR_CACHE.'/anycsv_tmp');
        
        $this->log->write($config_odmpro_template_data);
        
        if($query->row && isset($query->row['Variable_name']) && $query->row['Variable_name']=='max_allowed_packet'){
            
            
            
        }
        */
    }
    
}
?>