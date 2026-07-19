<?php
class ControllerExtensionFeedOdmproUpdateCSVLink extends Controller {
    
        private $path_oc_version = 'extension/module';
        private $anyxml = FALSE;
        private $anyxls = FALSE;
        private $path_oc_version_feed = 'extension/feed';
        private $max_memory_usage = array('memory_usage'=>0,'memory_usage_txt'=>'');
    
        public function __construct($registry) {
            $this->registry = $registry;
            $this->getLincenceStatus();
            $this->getAnyXMLStatus();
            $this->getAnyXLStatus();
                $this->getSettingVersionSettings();
        }
        
        public function setMaxMemoryUsage() {
            
            $memory_usage = memory_get_usage();
            
            if($memory_usage>$this->max_memory_usage['memory_usage']){
                
                $this->max_memory_usage['memory_usage'] = $memory_usage;
                
                $this->max_memory_usage['memory_usage_txt'] = round(($memory_usage/1024/1024),3).'Mb';
                
            }
            
        }
        
        public function getSettingVersionSettings(){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $setting_version_settings = $this->model_tool_csv_ocext_dmpro->getSettingVersionSettings();
            
            $this->setting_version_settings = $setting_version_settings;
            
        }
        
        public function getLincenceStatus() {
            $this->load->model('tool/csv_ocext_dmpro');
            
            $lic = $this->model_tool_csv_ocext_dmpro->getLincenceStatus();
                
            if(!isset($lic['status']) || !$lic['status']){
                exit("licence key error, pls, send request to support");
            }
        }
        
        public function getAnyXMLStatus() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');
                
            $this->anyxml = $this->ocext_model_tool_csv_ocext_dmpro->getAnyXMLStatus();
            
        }
        
        public function getAnyXLStatus() {
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');
                
            $this->anyxls = $this->ocext_model_tool_csv_ocext_dmpro->getAnyXLStatus();
            
        }
        
        public function getAnyXLSResult($odmpro_tamplate_data){
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');

            $result = $this->ocext_model_tool_csv_ocext_dmpro->getAnyXLSResult($odmpro_tamplate_data);

            return $result;

        }
        
        public function getAnyYMLResult($odmpro_tamplate_data) {
            
            if(!isset($this->setting_version)){
                
                require_once(DIR_SYSTEM . 'library/vendor/ocext/anydsvxls_setting_version.php');
                $this->registry->set('setting_version',new anyDSVXLSSettingVersion($this->registry,  $this->path_oc_version, $this->language,$this->load));
                
            }
            
            $this->load->model('tool/csv_ocext_dmpro');
        
            $result = array('error'=>'','file_upload'=>'');
            
            $supplier_feed_source = '';
        
            if(isset($odmpro_tamplate_data['supplier_feed_source']) && $odmpro_tamplate_data['supplier_feed_source']){

                $supplier_feed_source = $odmpro_tamplate_data['supplier_feed_source'];

            }

            $yml_file_name = '';

            if($odmpro_tamplate_data['file_url']){

                $yml_file_name = $this->ocext_model_tool_csv_ocext_dmpro->getFileNameByURL($odmpro_tamplate_data['file_url'],FALSE,$supplier_feed_source,$odmpro_tamplate_data);

            }elseif($odmpro_tamplate_data['file_upload']){

                $yml_file_name = $this->ocext_model_tool_csv_ocext_dmpro->getFileNameByFileName($odmpro_tamplate_data['file_upload']);

            }

            if(is_string($yml_file_name) && $yml_file_name && isset($this->setting_version_settings['functional']['yml_to_dsv']) && $this->setting_version_settings['functional']['yml_to_dsv']){
            
                $result = $this->setting_version->ymlToDSV($yml_file_name,$odmpro_tamplate_data);

            }else{

                $result['error'] = "Не удалось получить YML файл";

            }

            return $result;

        }

        public function getAnyXMLResult($odmpro_tamplate_data){

            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');

            $result = $this->ocext_model_tool_csv_ocext_dmpro->getAnyXMLResult($odmpro_tamplate_data);

            return $result;

        }
        
        public function getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation=0){

            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');

            $result = $this->ocext_model_tool_csv_ocext_dmpro->getAnyCSVSincSupplierResult($odmpro_tamplate_data,$status_continuation);

            return $result;

        }
        
        public function smartExchangeTask() {
            
            require_once(DIR_SYSTEM . 'library/vendor/ocext/anydsvxls_setting_version.php');
            $this->registry->set('setting_version',new anyDSVXLSSettingVersion($this->registry,  $this->path_oc_version, $this->language,$this->load));
            
        }
        
        public function writeCache($filename,$array,$cache_sub_dir='') {
        
            $handle = fopen(DIR_SYSTEM . 'library/vendor/ocext/cache/'.$cache_sub_dir. $filename, 'w+');

            fwrite( $handle, json_encode($array) );

            fclose($handle);

            if(file_exists(DIR_SYSTEM . 'library/vendor/ocext/cache/'.$cache_sub_dir. $filename)){

                return TRUE;

            }else{

                return FALSE;

            }

        }
    
        public function index() {
            
            
            
            if(isset($this->request->get['exchange_link_token'])){
                
                //return $this->smartExchangeTask();
                
            }
            
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $this->load->model('tool/csv_ocext_dmpro');
            
            $this->model_tool_csv_ocext_dmpro->model('tool/csv_ocext_dmpro');
            
            $config_odmpro_tamplate_data = $this->ocext_model_tool_csv_ocext_dmpro->getSetting('odmpro','odmpro_tamplate_data');
                
            $odmpro_update_csv_link = $this->ocext_model_tool_csv_ocext_dmpro->getSetting('odmpro_update_csv_link','odmpro_update_csv_link');
            
            $token = '';
            
            $json['errors'] = array();
            
            $this->setMaxMemoryUsage();
            
            $task_id = 0;
            
            $task = array();
            
            $tasks = array();
            
            $smart_exchange_log = array();
            
            if(isset($this->request->get['task_id'])){
                
                $task_id = $this->request->get['task_id'];
                
                $file_name_tasks = "smart_exchange_tasks";
        
                $tasks = $this->getCache($file_name_tasks);
                
                $file_name_smart_exchange_log = "smart_exchange_log";
        
                $smart_exchange_log = $this->getCache($file_name_smart_exchange_log);
                
                if(isset($tasks[$task_id]) && $tasks[$task_id]['action_status_id']==5){
                    
                    $tasks[$task_id]['action_status_id'] = 7;
                    
                    $tasks[$task_id]['data_mod'] = time();
                    
                    $url_task = $tasks[$task_id]['url'];
                    
                    $tasks[$task_id]['url'] = 'Не получен';
                    
                    $task = $tasks[$task_id];
                    
                    $last_log_data = date('Y-m-d G:i:s')." 2. Начинается обработка по задаче ".$tasks[$task_id]['type_process'].", URL: ".$url_task.". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);
                    
                    $this->writeCache($file_name_tasks, $tasks);
                    
                }
                
            }
            
            $this->setMaxMemoryUsage();
            
            if(isset($this->request->get['token'])){
                
                $token = trim($this->request->get['token']);
                
            }else{
                
                if(isset($tasks[$task_id])){
                    
                    $tasks[$task_id]['action_status_id'] = 3;
                    
                    $tasks[$task_id]['data_mod'] = time();
                    
                    $last_log_data = date('Y-m-d G:i:s')." Ошибка. Неверный токен в защите ссылки автозапуска по задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);
                    
                    $this->writeCache($file_name_tasks, $tasks);
                    
                    header('Smart-Exchange: 200');
                    
                }
                
                exit($this->language->get('error_no_token'));
                
            }
            
            if($odmpro_update_csv_link && $config_odmpro_tamplate_data){
                
                $update_settings = $odmpro_update_csv_link;
                
                $update_setting = array();
                
                $file_upload_this_from_plugin = FALSE;
                
                foreach ($update_settings as $setting) {
                    
                    if($setting['token']==$token){

                        $update_setting = $setting;
                        
                        if(!$update_setting['status']){
                            
                            exit($this->language->get('error_status'));
                            
                        }
                        
                        $tamplates_data = $config_odmpro_tamplate_data;
                        
                        $odmpro_tamplate_data = array();
                        
                        if(isset($tamplates_data[$update_setting['tamplate_data_id']])){
                            
                            $odmpro_tamplate_data = $tamplates_data[$update_setting['tamplate_data_id']];
                            
                        }
                        
                        $odmpro_tamplate_data['file_upload_this']='';
                        
                        if(isset($this->request->get['file_upload_this'])){

                            $odmpro_tamplate_data['file_upload_this'] = $this->request->get['file_upload_this'];

                        }
                        
                        if(!$odmpro_tamplate_data['file_upload_this'] && $odmpro_tamplate_data && !isset($this->request->get['start']) && $this->anyxml && isset($odmpro_tamplate_data['anyxml_xml_upload']) && $odmpro_tamplate_data['anyxml_xml_upload']){
                            
                            $any_XML_result = $this->getAnyXMLResult($odmpro_tamplate_data);
                            
                            $file_upload_this_from_plugin = TRUE;

                            if(isset($any_XML_result['error']) && $any_XML_result['error']){

                                $json['errors'][] = $any_XML_result['error'];

                            }else{

                                $odmpro_tamplate_data['file_url'] = '';

                                $odmpro_tamplate_data['file_upload'] = $any_XML_result['file_upload'];

                                $odmpro_tamplate_data['new_file_upload'] = '';
                                
                                $odmpro_tamplate_data['file_upload_this'] = $any_XML_result['file_upload'];

                            }
                            
                        }
                        
                        if(!$odmpro_tamplate_data['file_upload_this'] && $odmpro_tamplate_data && !isset($this->request->get['start']) && $this->anyxls && isset($odmpro_tamplate_data['anyxls_xls_upload']) && $odmpro_tamplate_data['anyxls_xls_upload']){
                            
                            $any_XLS_result = $this->getAnyXLSResult($odmpro_tamplate_data);
                            
                            $file_upload_this_from_plugin = TRUE;

                            if(isset($any_XLS_result['error']) && $any_XLS_result['error']){

                                $json['errors'][] = $any_XLS_result['error'];

                            }else{

                                $odmpro_tamplate_data['file_url'] = '';

                                $odmpro_tamplate_data['file_upload'] = $any_XLS_result['file_upload'];

                                $odmpro_tamplate_data['new_file_upload'] = '';
                                
                                $odmpro_tamplate_data['file_upload_this'] = $any_XLS_result['file_upload'];

                            }
                            
                        }
                        
                        if(!$odmpro_tamplate_data['file_upload_this'] && $odmpro_tamplate_data && !isset($this->request->get['start']) && isset($odmpro_tamplate_data['anyyml_yml_upload']) && $odmpro_tamplate_data['anyyml_yml_upload']){
                            
                            $any_YML_result = $this->getAnyYMLResult($odmpro_tamplate_data);
                            
                            $file_upload_this_from_plugin = TRUE;

                            if(isset($any_YML_result['error']) && $any_YML_result['error']){

                                $json['errors'][] = $any_YML_result['error'];

                            }else{

                                $odmpro_tamplate_data['file_url'] = '';

                                $odmpro_tamplate_data['file_upload'] = $any_YML_result['file_upload'];

                                $odmpro_tamplate_data['new_file_upload'] = '';
                                
                                $odmpro_tamplate_data['file_upload_this'] = $any_YML_result['file_upload'];

                            }
                            
                        }
                        
                        if(!$odmpro_tamplate_data['file_upload_this'] && isset($odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']) && $odmpro_tamplate_data['anycsv_sinch_supplier_setting_id']){
                    
                            $any_CSV_Sinc_Supplier_result = $this->getAnyCSVSincSupplierResult($odmpro_tamplate_data);
                            
                            $file_upload_this_from_plugin = TRUE;

                            if(isset($any_CSV_Sinc_Supplier_result['file_upload']) && $any_CSV_Sinc_Supplier_result['file_upload']){

                                $odmpro_tamplate_data['file_url'] = '';
                                
                                $odmpro_tamplate_data['file_upload'] = $any_CSV_Sinc_Supplier_result['file_upload'];
                                
                                $odmpro_tamplate_data['file_upload_this'] = $any_CSV_Sinc_Supplier_result['file_upload'];

                                $odmpro_tamplate_data['new_file_upload'] = '';

                            }elseif(isset($any_CSV_Sinc_Supplier_result['error']) && $any_CSV_Sinc_Supplier_result['error']){

                                $json['errors'][] = $any_CSV_Sinc_Supplier_result['error'];

                            }

                        }
                        
                        if(!$odmpro_tamplate_data['file_upload_this'] && !$file_upload_this_from_plugin && $odmpro_tamplate_data && $odmpro_tamplate_data['file_url'] && !isset($this->request->get['start'])){
                    
                            $new_file_upload_by_url = $this->ocext_model_tool_csv_ocext_dmpro->getFileByURL($odmpro_tamplate_data['file_url'],FALSE,TRUE);
                            
                            $file_upload_this_from_plugin = TRUE;

                            if($new_file_upload_by_url && $new_file_upload_by_url){

                                $odmpro_tamplate_data['file_upload'] = $new_file_upload_by_url;

                                $odmpro_tamplate_data['file_upload_this'] = $new_file_upload_by_url;
                                
                                $odmpro_tamplate_data['new_file_upload'] = '';

                                $odmpro_tamplate_data['file_url'] = '';

                            }elseif(!$new_file_upload_by_url){

                                $json['errors'][] = "Не могу получить файл по ссылке";

                            }

                        }
                        
                        if($json['errors']){
                            
                            if(isset($tasks[$task_id])){
                    
                                $tasks[$task_id]['action_status_id'] = 3;

                                $tasks[$task_id]['data_mod'] = time();

                                $last_log_data = date('Y-m-d G:i:s')." Ошибки: ".  $this->getStringFromArray($json['errors']).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                                $smart_exchange_log[$task_id][] = $last_log_data;
                    
                                $tasks[$task_id]['last_log_data'] = $last_log_data;

                                $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                                $this->writeCache($file_name_tasks, $tasks);
                                
                                header('Smart-Exchange: 200');

                            }
                            
                            echo json_encode($json);
                            
                            exit();
                            
                        }
                        
                        if($odmpro_tamplate_data){
                            
                            $this->request->post['odmpro_tamplate_data'] = $odmpro_tamplate_data;
                            
                            if(!isset($this->request->get['start'])){

                                $this->request->get['start'] = $odmpro_tamplate_data['start']-1;
                                
                                if($this->request->get['start']<0){
                                    $this->request->get['start'] = 0;
                                }
                                
                                $this->request->get['first_row'] = 1;
                                

                            }else{
                                
                                $this->request->get['first_row'] = 0;
                                
                            }
                            
                            if(!isset($this->request->get['num_process'])){

                                $this->request->get['num_process'] = time();
                                
                                $new_files_upload_prefix = $odmpro_tamplate_data['id'].'-file_upload_this';
                                
                                $this->unlinkFiles(array($new_files_upload_prefix),DIR_SYSTEM . 'library/vendor/ocext/cache/file_upload_cache/');

                            }
                            
                            $this->request->post['type_process'] = 'write file';
                            
                            if(isset($this->request->get['export']) && $this->request->get['export']){
                                
                                $this->startExport();
                                
                            }else{
                                
                                $this->startImport();
                                
                            }
                            
                        }else{
                            
                            exit($this->language->get('error_template_data'));
                            
                        }
                        
                    }
                    
                }
                
                $this->setMaxMemoryUsage();
                
                if(!$update_setting){
                    
                    if(isset($tasks[$task_id])){

                        $tasks[$task_id]['action_status_id'] = 3;

                        $tasks[$task_id]['data_mod'] = time();

                        $last_log_data = date('Y-m-d G:i:s')." Ошибка: ".  $this->language->get('error_no_token').". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                        $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                        $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                        $this->writeCache($file_name_tasks, $tasks);

                        header('Smart-Exchange: 200');

                    }
                    
                    exit($this->language->get('error_no_token'));
                    
                }
                
            }else{
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ".  $this->language->get('error_no_odmpro_update_csv_link').". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                exit($this->language->get('error_no_odmpro_update_csv_link'));
                
            }
            
        }
        
        private function checkCURL(){
            
            if(function_exists('curl_version')){
                
                return TRUE;
                
            }else{
                
                return FALSE;
                
            }
        }
        
        public function unlinkFiles($cache_files=array(),$other_path='') {
        
            if(!$other_path){
                
                $files = scandir(DIR_SYSTEM . 'library/vendor/ocext/cache/');
                
                $other_path = DIR_SYSTEM . 'library/vendor/ocext/cache/';
                
            }else{
                
                $files = scandir($other_path);
                
            }
            
            foreach($files as $file_name){
                
                foreach($cache_files as $cache_file){
                 
                    if(strstr($file_name, $cache_file)){
                        
                        unlink($other_path.$file_name);
                        
                    }
                    
                }
                
            }
            
        }
        
    public function getCache($filename,$unlink=FALSE,$cache_sub_dir='') {
            
        $cache = array();

        if(file_exists(DIR_SYSTEM . 'library/vendor/ocext/cache/'.$cache_sub_dir.$filename)){

            $cache = json_decode(file_get_contents(DIR_SYSTEM . 'library/vendor/ocext/cache/'.$cache_sub_dir.$filename),TRUE);

            if($unlink){
                unlink(DIR_SYSTEM . 'library/vendor/ocext/cache/'.$cache_sub_dir.$filename);
            }

        }
        
        if(!is_array($cache)){
            
            $cache = array();
            
        }

        return $cache;

    }
        
        public function startImport() {
            
            $this->setMaxMemoryUsage();
            
            $format_data = $this->request->post['odmpro_tamplate_data']['format_data'];
            
            $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
            
            $type_process = $this->request->post['type_process'];
            
            $type_change = $odmpro_tamplate_data['type_change'];
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $start = $this->request->get['start'];
            
            $limit = $this->request->post['odmpro_tamplate_data']['limit'];
            
            if( $limit < 300 && !isset($this->request->get['task_id']) ){
                
                //$limit = 300;
                
            }
            
            $task_id = 0;
            
            $task = array();
            
            $tasks = array();
            
            $smart_exchange_log = array();
            
            if(isset($this->request->get['task_id'])){
                
                $task_id = $this->request->get['task_id'];
                
                $file_name_tasks = "smart_exchange_tasks";
        
                $tasks = $this->getCache($file_name_tasks);
                
                $file_name_smart_exchange_log = "smart_exchange_log";
        
                $smart_exchange_log = $this->getCache($file_name_smart_exchange_log);
                
                if(isset($tasks[$task_id]) && $tasks[$task_id]['action_status_id']==7){
                    
                    $tasks[$task_id]['action_status_id'] = 8;
                    
                    $tasks[$task_id]['data_mod'] = time();
                    
                    $last_log_data = date('Y-m-d G:i:s')." 3. Начинается импорт start: ".$start.' limit: '.$limit.", номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);
                    
                    $this->writeCache($file_name_tasks, $tasks);
                    
                    header('Smart-Exchange: 200');
                    
                    echo 'action_status_id 8';
                    
                }
                
            }
            
            $this->setMaxMemoryUsage();
            
            $num_process = $this->request->get['num_process'];
            
            $log_data = array(
                'start' => $start,
                'limit' => $limit,
                'num_process'   => $num_process,
                'type_process'  => $type_process,
                'format_data'   => $format_data
            );
            
            $json['error'] = '';
            
            if(!isset($odmpro_tamplate_data['type_data'])){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro = writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            /*
             * проверяем есть ли колонки файла для импорта
             */
            $type_data_columns = FALSE;
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data){
                    
                    $type_data_columns = TRUE;
                    
                }
                
            }
            
            if(!$type_data_columns){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            if(!$type_change){
                
                $json['error'] .= $this->language->get('entry_type_change_error');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_type_change_error')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            $this->setMaxMemoryUsage();
            
            /*
             * Данные разбираем с учетом принадложености к основному типу: товары, к товарам, категории к категориям и т.п.
             */
            $type_data_columns_by_type_data = array();
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data && $field!=='' && isset($odmpro_tamplate_data['type_data_column'][$field]) && $odmpro_tamplate_data['type_data_column'][$field]['db_table___db_column']){
                    
                    $type_data_columns_by_type_data[$type_data]['column_settings'][$field] = $odmpro_tamplate_data['type_data_column'][$field];
                    
                    $type_data_columns_by_type_data[$type_data]['general_settings'] = array();
                    
                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data]){
                        
                        $type_data_columns_by_type_data[$type_data]['general_settings'] = $odmpro_tamplate_data['type_data_general_settings'][$type_data];
                        
                    }
                    
                }
                
            }
            
            
            if(!$type_data_columns_by_type_data){
                
                $json['error'] .= $this->language->get('import_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            /*
             * Проходим по всем типам, проверяем настройки по каждому типу на основные ошибки, без которых невозможен обмен данными
             */
            
            foreach ($type_data_columns_by_type_data as $type_data => $settings) {
                
                if($type_change=='update_data' || $type_change=='only_update_data' || $type_change=='only_new_data'){
                    
                    $identificator = FALSE;
                    
                    foreach ($settings['column_settings'] as $field => $setting) {
                        
                        $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);
                        
                        $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];
                        
                        if($db_column_or_advanced_column_name=='identificator'){
                            
                            $identificator = TRUE;
                            
                            /*
                             * Идентификаторов может быть несколько, например, ошибочно или для поиска хотя бы одного
                             */
                            
                            $type_data_columns_by_type_data[$type_data]['identificator'][$field] = array(
                                'field'=>$field,
                                'additinal_settings'=>$setting['additinal_settings'],
                                'identificator_type'=>$setting['additinal_settings']['identificator_type'],
                            );
                            
                        }
                        
                    }
                    
                    if(!$identificator){
                
                        $json['error'] .= sprintf($this->language->get('import_error_no_identificator'),$type_data,$type_data); 

                        $log_data['__line__'] = __LINE__; 

                        $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>sprintf($this->language->get('import_error_no_identificator'),$type_data,$type_data)),$odmpro_tamplate_data,$log_data);

                        if($log_error){

                            $json['error'] .= $log_error;

                        }
                        
                        if(isset($tasks[$task_id])){

                            $tasks[$task_id]['action_status_id'] = 3;

                            $tasks[$task_id]['data_mod'] = time();

                            $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                            $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                            $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                            $this->writeCache($file_name_tasks, $tasks);

                            header('Smart-Exchange: 200');

                        }

                        echo json_encode($json);

                        return;

                    }
                    
                }
                
                foreach ($settings['column_settings'] as $field => $setting) {
                        
                    $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);

                    $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];

                    if($db_column_or_advanced_column_name=='image_advanced' && isset($setting['additinal_settings']['image_upload']) && $setting['additinal_settings']['image_upload']){
                        
                        $check_curl = $this->checkCURL();
                
                        if(!$check_curl){

                            $json['error'] .= '<p>'.$this->language->get('entry_curl_exits').'</p>'; 
                            
                            $log_data['__line__'] = __LINE__; 

                            $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_curl_exits')),$odmpro_tamplate_data,$log_data);

                            if($log_error){

                                $json['error'] .= $log_error;

                            }
                            
                            if(isset($tasks[$task_id])){

                                $tasks[$task_id]['action_status_id'] = 3;

                                $tasks[$task_id]['data_mod'] = time();

                                $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                                $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                                $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                                $this->writeCache($file_name_tasks, $tasks);

                                header('Smart-Exchange: 200');

                            }

                            echo json_encode($json);

                            return;

                        }

                    }

                }
                
            }
            
            $this->setMaxMemoryUsage();
            
            if (!$odmpro_tamplate_data['csv_enclosure']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_enclosure').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_enclosure')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!$odmpro_tamplate_data['language_id']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_language_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_language_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!$odmpro_tamplate_data['csv_delimiter']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_delimiter').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_delimiter')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!isset($odmpro_tamplate_data['store_id'])) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_store_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_store_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            } 
            
            $this->setMaxMemoryUsage();
            
            $file = '';
            
            $new_files_upload_prefix = $odmpro_tamplate_data['id'].'-file_upload_this';
            
            $new_files_upload = $this->getCache($new_files_upload_prefix,FALSE,'file_upload_cache/');
            
            if(isset($odmpro_tamplate_data['file_upload_this']) && $odmpro_tamplate_data['file_upload_this'] && !is_array($odmpro_tamplate_data['file_upload_this'])){
                
                $file = $this->ocext_model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['file_upload_this']);
                
            }elseif(isset($odmpro_tamplate_data['file_upload_this']) && $odmpro_tamplate_data['file_upload_this'] && is_array($odmpro_tamplate_data['file_upload_this'])){
                
                if(!isset($this->request->get['nfu']) || $this->request->get['nfu']==='no_data'){
                    
                    $this->request->get['nfu'] = 0;
                    
                    $new_files_upload = $odmpro_tamplate_data['file_upload_this'];
                    
                    $this->writeCache($new_files_upload_prefix,$new_files_upload,'file_upload_cache/');

                }
                
                $new_files_upload = $this->getCache($new_files_upload_prefix,FALSE,'file_upload_cache/');
                
                $new_file_upload = '';
                
                if(isset($new_files_upload[$this->request->get['nfu']])){
                    
                    $new_file_upload = $new_files_upload[$this->request->get['nfu']];
                    
                }
                
                $file = $this->ocext_model_tool_csv_ocext_dmpro->getFileByFileName($new_file_upload);
                
            }elseif(isset($odmpro_tamplate_data['new_file_upload']) && $odmpro_tamplate_data['new_file_upload']){
                
                $file = $this->ocext_model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['new_file_upload']);
                
            }elseif($odmpro_tamplate_data['file_url'] && $odmpro_tamplate_data['file_url']){
                
                $file = $this->ocext_model_tool_csv_ocext_dmpro->getFileByURL($odmpro_tamplate_data['file_url']);
                
            }elseif($odmpro_tamplate_data['file_upload']){
                
                $file = $this->ocext_model_tool_csv_ocext_dmpro->getFileByFileName($odmpro_tamplate_data['file_upload']);
                
            }
            
            $this->setMaxMemoryUsage();
            
            if(!$file){
                
                $json['error'] .= '<p>'.$this->language->get('entry_file_exits').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_file_exits')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $this->getStringFromArray($json).". В задаче ".$tasks[$task_id]['type_process']." (".$tasks[$task_id]['start']."/".$tasks[$task_id]['limit']."). Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            
            $json['success'] = '';
            
            $import_result['count_rows'] = 0;
            
            $start_row_by_add_first_row = 1;
            
            if(isset($odmpro_tamplate_data['add_first_row']) && $odmpro_tamplate_data['add_first_row']){
                    
                    $start_row_by_add_first_row = 0;
                    
            }
            
            if(!$json['error']){
                
                $process_log_file = DIR_CACHE.'anycsv_process_log_'.$odmpro_tamplate_data['id'].'-'.$num_process.'.json';
                
                if(!file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                    $h = fopen($process_log_file, 'w+');
                    
                    fclose($h);
                    
                }
                
                /*
                 * +1 сдвигаемся с заголовков полей
                 */
                $import_result = $this->ocext_model_tool_csv_ocext_dmpro->getCsvRows($file,$start+$start_row_by_add_first_row,$limit,$odmpro_tamplate_data);
                
                $this->ocext_model_tool_csv_ocext_dmpro->importCSV($odmpro_tamplate_data,$type_data_columns_by_type_data,$import_result,$log_data);
                
            }
            
            $json['total'] = $import_result['count_rows'];
            
            $json['nfu'] = 'no_data';
            
            $json['new_nfu'] = 'no_data';
            
            if(isset($this->request->get['nfu'])){
                
                $json['nfu'] = $this->request->get['nfu'];
                
            }
            
            $this->setMaxMemoryUsage();
            
            if($json['nfu'] === 'no_data' && !$json['error'] && (($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0)){
                
                $json['success'] = $this->language->get('write_success_accomplished');
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog(''.$type_process,array('success'=>$this->language->get('write_success_accomplished')),$odmpro_tamplate_data,$log_data);
                
                if(file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    if(!isset($tasks[$task_id]['url'])){
                        
                        $tasks[$task_id]['url'] = "Не задан";
                        
                    }
                    
                    $url_task = $tasks[$task_id]['url'];
                    
                    $tasks[$task_id]['total'] = $json['total'];

                    $last_log_data = date('Y-m-d G:i:s')." 5. Успешное завершение импорта".$memory_time." URL:".$url_task.". В задаче ".$tasks[$task_id]['type_process'].", обработано: ".$tasks[$task_id]['total'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);
                    
                    if($tasks[$task_id]['email_notice'] && $tasks[$task_id]['finish_email_notice']){
                        
                        $notice_tasks[$tasks[$task_id]['email_notice']][] = array(
                            "sbj" => date('Y-m-d G:i:s')." Успешное завершение импорта",
                            "msg" => $last_log_data
                        );
                        
                        require_once DIR_SYSTEM.'library/mail.php';
            
                        $mail = new Mail();
                        
                        $mail->setFrom('smart-exchange-notice@'.str_replace(array('https://','http://','/'), array(''), HTTP_SERVER));
                        
                        $mail->setSender('smart-exchange-notice');


                        foreach ($notice_tasks as $email_notice => $notices) {

                            $mail->setTo('');

                            $email_notice_parts = explode(',', $email_notice);

                            $email_notice = array();

                            foreach ($email_notice_parts as $email_notice_part) {

                                $email_notice_part = trim($email_notice_part);

                                if(filter_var($email_notice_part, FILTER_VALIDATE_EMAIL)){

                                    $email_notice[] = $email_notice_part;

                                }

                            }

                            if($email_notice){

                                for($e=0;$e<count($email_notice);$e++){

                                    $mail->setTo($email_notice[$e]);

                                    for($n=0;$n<count($notices);$n++){

                                        $mail->setSubject('smart-exchange-notice: '.$notices[$n]['sbj']);

                                        $mail->setText($notices[$n]['msg']);

                                        $mail->send();

                                        sleep(5);

                                    }

                                }

                            }

                        }
                        
                    }

                    header('Smart-Exchange: 200');

                }
                
            } 
            
            elseif ( /*$json['nfu'] == 'no_data' && */ !$json['error'] && $import_result['count_rows']>0 && ($start+$limit)<=$import_result['count_rows']) {
                
                $file_name_tasks = "smart_exchange_tasks";
        
                $tasks_actual = $this->getCache($file_name_tasks);
                
                if(isset($new_files_upload[$json['nfu']])){

                    $new_file_upload = $new_files_upload[$json['nfu']];

                }else{

                    $new_file_upload = $odmpro_tamplate_data['file_upload_this'];

                }
                
                if(isset($tasks[$task_id]) && isset($tasks_actual[$task_id]) && $tasks_actual[$task_id]!=3 && $tasks_actual[$task_id]!=4){

                    $tasks[$task_id]['action_status_id'] = 2;
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    $tasks[$task_id]['url'] = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$new_file_upload.'&task_id='.$task_id.'&nfu='.$json['nfu']);
                    
                    $tasks[$task_id]['start'] = (int)($start+$limit);
                    
                    $tasks[$task_id]['limit'] = $limit;
                    
                    $tasks[$task_id]['total'] = $json['total'];
                    
                    $url_task = $tasks[$task_id]['url'];
                    
                    $nfu_text = '';
                    
                    if($json['nfu']!='no_data'){
                        
                        $nfu_text = " (порядковый номер файла: ".$json['nfu'].")";
                        
                    }

                    $last_log_data = date('Y-m-d G:i:s')." 4. Завершено ".(($start+$limit)-1)." из ".$json['total']." строк,".$memory_time.$nfu_text." NEW URL:".$url_task.". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');
                    
                    echo 'ok';

                }else{
                    
                    //$this->response->redirect($this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$new_file_upload.'&nfu='.$json['nfu']));
                    
		    $url = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$new_file_upload.'&nfu='.$json['nfu']);
		    
		    $this->requestUrl($url);
		    
                }
                
            }
            
            elseif($json['nfu'] !=='no_data' && !$json['error'] && (($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0) && !isset ($new_files_upload[ $json['nfu']+1 ])){
                
                $json['success'] = $this->language->get('write_success_accomplished');
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog(''.$type_process,array('success'=>$this->language->get('write_success_accomplished')),$odmpro_tamplate_data,$log_data);
                
                if(file_exists($process_log_file)){
                    
                    $this->unlinkProcessLog($odmpro_tamplate_data['id']);
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    if(!isset($tasks[$task_id]['url'])){
                        
                        $tasks[$task_id]['url'] = "Не задан";
                        
                    }
                    
                    $url_task = $tasks[$task_id]['url'];
                    
                    $tasks[$task_id]['total'] = $json['total'];

                    $last_log_data = date('Y-m-d G:i:s')." 5. Успешное завершение импорта".$memory_time." URL:".$url_task.". В задаче ".$tasks[$task_id]['type_process'].", обработано: ".$tasks[$task_id]['total'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);
                    
                    if($tasks[$task_id]['email_notice'] && $tasks[$task_id]['finish_email_notice']){
                        
                        $notice_tasks[$tasks[$task_id]['email_notice']][] = array(
                            "sbj" => date('Y-m-d G:i:s')." Успешное завершение импорта",
                            "msg" => $last_log_data
                        );
                        
                        require_once DIR_SYSTEM.'library/mail.php';
            
                        $mail = new Mail();
                        
                        $mail->setFrom('smart-exchange-notice@'.str_replace(array('https://','http://','/'), array(''), HTTP_SERVER));
                        
                        $mail->setSender('smart-exchange-notice');


                        foreach ($notice_tasks as $email_notice => $notices) {

                            $mail->setTo('');

                            $email_notice_parts = explode(',', $email_notice);

                            $email_notice = array();

                            foreach ($email_notice_parts as $email_notice_part) {

                                $email_notice_part = trim($email_notice_part);

                                if(filter_var($email_notice_part, FILTER_VALIDATE_EMAIL)){

                                    $email_notice[] = $email_notice_part;

                                }

                            }

                            if($email_notice){

                                for($e=0;$e<count($email_notice);$e++){

                                    $mail->setTo($email_notice[$e]);

                                    for($n=0;$n<count($notices);$n++){

                                        $mail->setSubject('smart-exchange-notice: '.$notices[$n]['sbj']);

                                        $mail->setText($notices[$n]['msg']);

                                        $mail->send();

                                        sleep(5);

                                    }

                                }

                            }

                        }
                        
                    }

                    header('Smart-Exchange: 200');

                }
                
            }
            
            elseif($json['nfu'] !=='no_data' && !$json['error'] && (($start+$limit)>$import_result['count_rows'] && $import_result['count_rows']>0) && isset ($new_files_upload[ $json['nfu']+1 ])){
                
                $file_name_tasks = "smart_exchange_tasks";
        
                $tasks_actual = $this->getCache($file_name_tasks);
                
                $json['nfu'] += 1;
                
                $json['new_nfu'] = 'new_data';
                
                $file_upload_this = $new_files_upload[ $json['nfu'] ];
                
                if(isset($tasks[$task_id]) && isset($tasks_actual[$task_id]) && $tasks_actual[$task_id]!=3 && $tasks_actual[$task_id]!=4){

                    $tasks[$task_id]['action_status_id'] = 2;
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    $tasks[$task_id]['url'] = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', '&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$file_upload_this.'&task_id='.$task_id.'&nfu='.$json['nfu']);
                    
                    $tasks[$task_id]['start'] = 0;
                    
                    $tasks[$task_id]['limit'] = $limit;
                    
                    $tasks[$task_id]['total'] = 0;
                    
                    $url_task = $tasks[$task_id]['url'];
                    
                    $nfu_text = '';
                    
                    if($json['nfu']!=='no_data'){
                        
                        $nfu_text = " (порядковый номер файла: ".$json['nfu'].")";
                        
                    }

                    $last_log_data = date('Y-m-d G:i:s')." 4. Завершено ".(($start+$limit)-1)." из ".$json['total']." строк,".$memory_time.$nfu_text." NEW URL:".$url_task.". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');
                    
                    echo 'ok';

                }else{
                    
                    //$this->response->redirect($this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', '&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$file_upload_this.'&nfu='.$json['nfu']));
		    
		    $url = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', '&num_process='.$num_process.'&token=' . $this->request->get['token'].'&file_upload_this='.$file_upload_this.'&nfu='.$json['nfu']);
		    
		    $this->requestUrl($url);
		    
                    
                }
                
            }
            
            $this->setMaxMemoryUsage();
            
            if($json['error']){
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo $json['error'];
                
            }elseif ($json['success']) {
                
                echo $json['success'];
                
            }
            
            return;
            
        }
	
	public function requestUrl($url) {
	
	    $curl = curl_init();

	    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"); 

	    curl_setopt($curl, CURLOPT_URL, html_entity_decode($url));

	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,  2); 

	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); 

	    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	    
	    curl_exec($curl);
	   
	/*    echo "Процесс успешно начат. Вы можете закрыть это окно. Процесс завершиться самостоятельно"; */

	    return;

	}
        
        
        public function startExport() {
            
            $this->setMaxMemoryUsage();
            
            $format_data = $this->request->post['odmpro_tamplate_data']['format_data'];
            
            $odmpro_tamplate_data = $this->request->post['odmpro_tamplate_data'];
            
            $type_process = $this->request->post['type_process'];
            
            $this->load->model('tool/csv_ocext_dmpro');
                
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            $start = (int)$this->request->get['start'];
            
            $limit = (int)$this->request->post['odmpro_tamplate_data']['limit'];
            
            if($limit < 200){
                
                //$limit = 200;
                
            }
            
            $task_id = 0;
            
            $task = array();
            
            $tasks = array();
            
            $smart_exchange_log = array();
            
            if(isset($this->request->get['task_id'])){
                
                $task_id = $this->request->get['task_id'];
                
                $file_name_tasks = "smart_exchange_tasks";
        
                $tasks = $this->getCache($file_name_tasks);
                
                $file_name_smart_exchange_log = "smart_exchange_log";
        
                $smart_exchange_log = $this->getCache($file_name_smart_exchange_log);
                
                if(isset($tasks[$task_id]) && $tasks[$task_id]['action_status_id']== 7){
                    
                    $tasks[$task_id]['action_status_id'] = 8;
                    
                    $tasks[$task_id]['data_mod'] = time();
                    
                    $task = $tasks[$task_id];
                    
                    $last_log_data = date('Y-m-d G:i:s')." 3. Начинается экспорт. Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);
                    
                    $this->writeCache($file_name_tasks, $tasks);
                    
                    header('Smart-Exchange: 200');
                    
                    echo 'action_status_id 5';
                    
                }
                
            }
            
            $num_process = $this->request->get['num_process'];
            
            $log_data = array(
                'start' => $start,
                'limit' => $limit,
                'num_process'   => $num_process,
                'type_process'  => $type_process,
                'format_data'   => $format_data,
                'file_url'   => '',
                'file_upload'   => $odmpro_tamplate_data['export_file_name'],
            );
            
            $json['error'] = '';
            
            if(!isset($odmpro_tamplate_data['type_data'])){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            $type_data_columns = FALSE;
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data){
                    
                    $type_data_columns = TRUE;
                    
                }
                
            }
            
            if(!$type_data_columns){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }
            
            $type_data_columns_by_type_data = array();
            
            foreach ($odmpro_tamplate_data['type_data'] as $field => $type_data) {
                
                if($type_data && $field!=='' && isset($odmpro_tamplate_data['type_data_column'][$field]) && $odmpro_tamplate_data['type_data_column'][$field]['db_table___db_column']){
                    
                    $type_data_columns_by_type_data[$type_data]['column_settings'][$field] = $odmpro_tamplate_data['type_data_column'][$field];
                    
                    $type_data_columns_by_type_data[$type_data]['general_settings'] = array();
                    
                    if(isset($odmpro_tamplate_data['type_data_general_settings'][$type_data]) && $odmpro_tamplate_data['type_data_general_settings'][$type_data]){
                        
                        $type_data_columns_by_type_data[$type_data]['general_settings'] = $odmpro_tamplate_data['type_data_general_settings'][$type_data];
                        
                    }
                    
                }
                
            }
            
            if(!$type_data_columns_by_type_data){
                
                $json['error'] .= $this->language->get($type_process.'_error_no_type_data');
                
                $log_data['__line__'] = __LINE__;
                
                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get($type_process.'_error_no_type_data')),$odmpro_tamplate_data,$log_data);
                
                if($log_error){
                    
                    $json['error'] .= $log_error;
                    
                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                echo json_encode($json);
                
                return;
                
            }else{
                
                foreach ($type_data_columns_by_type_data as $type_data => $settings) {
                    
                    foreach ($settings['column_settings'] as $field => $setting) {
                        
                        $db_column_or_advanced_column_name_parts = explode('___', $setting['db_table___db_column']);
                        
                        $db_column_or_advanced_column_name = $db_column_or_advanced_column_name_parts[1];
                        
                        if($db_column_or_advanced_column_name=='identificator'){
                            
                            $type_data_columns_by_type_data[$type_data]['identificator'][$field] = array(
                                'field'=>$field,
                                'additinal_settings'=>$setting['additinal_settings'],
                                'identificator_type'=>$setting['additinal_settings']['identificator_type'],
                            );
                            
                        }
                        
                    }
                    
                }
                
            }
            
            $this->setMaxMemoryUsage();
            
            /*
            
            $json['success'] = "Экспорт в данной версии будет доступен в обновлении от 15.01.2017 г.";

            echo json_encode($json);
            
            exit();
            
             * 
             */
            
            if (!$odmpro_tamplate_data['csv_enclosure']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_enclosure').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_enclosure')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!$odmpro_tamplate_data['language_id']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_language_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_language_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!$odmpro_tamplate_data['csv_delimiter']) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_csv_delimiter').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_csv_delimiter')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            if (!isset($odmpro_tamplate_data['store_id'])) {
                
                $json['error'] .= '<p>'.$this->language->get('import_error_no_store_id').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('import_error_no_store_id')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            } 
            
            $file = FALSE;
            
            if($odmpro_tamplate_data['export_file_name'] && $odmpro_tamplate_data['export_file_name']){
                
                $file = trim($odmpro_tamplate_data['export_file_name']);
                
            }
            
            if(!$file){
                
                $json['error'] .= '<p>'.$this->language->get('entry_file_exits_export').'</p>'; 
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog('preparation_'.$type_process,array('error'=>$this->language->get('entry_file_exits_export')),$odmpro_tamplate_data,$log_data);

                if($log_error){

                    $json['error'] .= $log_error;

                }
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }

                echo json_encode($json);

                return;
                
            }
            
            $this->setMaxMemoryUsage();
            
            $json['success'] = '';
            
            $result['count_rows'] = 0;
            
            if($format_data=='csv' && !$json['error']){
                
                $result = $this->ocext_model_tool_csv_ocext_dmpro->exportCSV($odmpro_tamplate_data,$type_data_columns_by_type_data,$log_data);
                
            }
            
            $this->setMaxMemoryUsage();
            
            $json['total'] = $result['count_rows'];
            
            if(($start+$limit)>$result['count_rows'] && $result['count_rows']>0){
                
                $json['success'] = $this->language->get('import_success_accomplished');
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog($type_process,array('success'=>$this->language->get('import_success_accomplished')),$odmpro_tamplate_data,$log_data);
                
            }elseif(!$result['count_rows']){
                
                $json['error'] = $this->language->get('export_empty_data');
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    $tasks[$task_id]['data_mod'] = time();

                    $last_log_data = date('Y-m-d G:i:s')." Ошибка: ". $json['error'].". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');

                }
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog($type_process,array('error'=>$this->language->get('export_empty_data')),$odmpro_tamplate_data,$log_data);
                
            }
            
            $this->setMaxMemoryUsage();
            
            if(!$json['error'] && (($start+$limit)>$result['count_rows'] && $result['count_rows']>0)){
                
                $json['success'] = $this->language->get('write_success_accomplished');
                
                $log_data['__line__'] = __LINE__; 

                $log_error = $this->ocext_model_tool_csv_ocext_dmpro->writeLog(''.$type_process,array('success'=>$this->language->get('write_success_accomplished')),$odmpro_tamplate_data,$log_data);
                
                if(isset($tasks[$task_id])){

                    $tasks[$task_id]['action_status_id'] = 3;

                    
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    $url_task = $tasks[$task_id]['url'];

                    $last_log_data = date('Y-m-d G:i:s')." 5. Успешное завершение экспорта".$memory_time." URL: ".$url_task.". В задаче ".$tasks[$task_id]['type_process'].", обработано: ".$tasks[$task_id]['total'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $smart_exchange_log[$task_id][] = $last_log_data;
                    
                    $tasks[$task_id]['last_log_data'] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);
                    
                    if($tasks[$task_id]['email_notice'] && $tasks[$task_id]['finish_email_notice']){
                        
                        $notice_tasks[$tasks[$task_id]['email_notice']][] = array(
                            "sbj" => date('Y-m-d G:i:s')." Успешное завершение экспорта",
                            "msg" => $last_log_data
                        );
                        
                        require_once DIR_SYSTEM.'library/mail.php';
            
                        $mail = new Mail();
                        
                        $mail->setFrom('smart-exchange-notice@'.str_replace(array('https://','http://','/'), array(''), HTTP_SERVER));
                        
                        $mail->setSender('smart-exchange-notice');


                        foreach ($notice_tasks as $email_notice => $notices) {

                            $mail->setTo('');

                            $email_notice_parts = explode(',', $email_notice);

                            $email_notice = array();

                            foreach ($email_notice_parts as $email_notice_part) {

                                $email_notice_part = trim($email_notice_part);

                                if(filter_var($email_notice_part, FILTER_VALIDATE_EMAIL)){

                                    $email_notice[] = $email_notice_part;

                                }

                            }

                            if($email_notice){

                                for($e=0;$e<count($email_notice);$e++){

                                    $mail->setTo($email_notice[$e]);

                                    for($n=0;$n<count($notices);$n++){

                                        $mail->setSubject('smart-exchange-notice: '.$notices[$n]['sbj']);

                                        $mail->setText($notices[$n]['msg']);

                                        $mail->send();

                                        sleep(5);

                                    }

                                }

                            }

                        }
                        
                    }

                    header('Smart-Exchange: 200');

                }
                
            } 
            
            elseif (!$json['error'] && $result['count_rows']>0 && ($start+$limit)<=$result['count_rows']) {
                
                $tasks_actual = $this->getCache($file_name_tasks);
                
                if(isset($tasks[$task_id]) && isset($tasks_actual[$task_id]) && $tasks_actual[$task_id]!=3 && $tasks_actual[$task_id]!=4){

                    $tasks[$task_id]['action_status_id'] = 2;
                    
                    $memory_time = ', ОЗУ: '.$this->max_memory_usage['memory_usage_txt'].', время обработки: '.(time()-$tasks[$task_id]['data_mod']).' сек., ';

                    $tasks[$task_id]['data_mod'] = time();
                    
                    $tasks[$task_id]['url'] = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&export=1&num_process='.$num_process.'&token=' . $this->request->get['token'].'&task_id='.$task_id);
                    
                    $tasks[$task_id]['start'] = (int)($start+$limit);
                    
                    $tasks[$task_id]['limit'] = $limit;
                    
                    $tasks[$task_id]['total'] = $result['count_rows'];
                    
                    $url_task = $tasks[$task_id]['url'];

                    $last_log_data = date('Y-m-d G:i:s')." 4. Завершено ".(($start+$limit))." из ".$result['count_rows']." строк".$memory_time." NEW URL: ".$url_task.". В задаче ".$tasks[$task_id]['type_process'].". Номер задачи (ЧЧ-ММ-Дн.Н-ID) ".$task_id.", номер автообновления (token): ".$tasks[$task_id]['setting_id']." (".$tasks[$task_id]['token'].") , номер профиля: ".$tasks[$task_id]['tamplate_data_id'];

                    $tasks[$task_id]['last_log_data'] = $last_log_data;
                    
                    $smart_exchange_log[$task_id][] = $last_log_data;

                    $this->writeCache($file_name_smart_exchange_log, $smart_exchange_log);

                    $this->writeCache($file_name_tasks, $tasks);

                    header('Smart-Exchange: 200');
                    
                    echo 'ok';

                }else{
                    
                    //$this->response->redirect($this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&export=1&num_process='.$num_process.'&token=' . $this->request->get['token']));
                    
		    $url = $this->url->link($this->path_oc_version_feed.'/odmpro_update_csv_link', 'start='.($start+$limit).'&export=1&num_process='.$num_process.'&token=' . $this->request->get['token']);
		    
		    $this->requestUrl($url);
		    
                }
                
            }
            
            if($json['error']){
                echo $json['error'];
            }elseif ($json['success']) {
                echo $json['success'];
            }
            
            exit();
        }
        
        
        private function getFloat($string){
            
            $find = array('-',',',' ');
            
            $replace = array('.','.','');
            
            $result = (float)str_replace($find, $replace, $string);
            
            return $result;
        }
        
        public function getAttributeOrFilterGroups($language_id,$type_data_column) {
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            if(!isset($this->request->get['token'])){
                
                exit($this->language->get('error_no_token'));
                
            }
            
            if($type_data_column=='attribute_name'){
                
                $table = 'attribute_group_description';
                
            }
            
            if($type_data_column=='filter_name'){
                
                $table = 'filter_group_description';
                
            }
            
            if(!$language_id){
                
                $language_id = (int)$this->config->get('config_language_id');
                
            }
            
            $sql = "SELECT * FROM " . DB_PREFIX . $table." WHERE language_id = '" . $language_id . "' ";

            $query = $this->db->query($sql);

            return $query->rows;
	}
        
        public function getAttributes($data = array('start'=>0,'limit'=>10000)) {
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            if(!isset($this->request->get['token'])){
                
                exit($this->language->get('error_no_token'));
                
            }
            
		$sql = "SELECT *, (SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group_name FROM " . DB_PREFIX . "attribute a LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'";
                
                $sql .= " ORDER BY attribute_group_name, ad.name";
                
		$sql .= " ASC";
                
                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
                
		$query = $this->db->query($sql);
                
                $result = array();
                
                if($query->rows){
                    
                    foreach ($query->rows as $value) {
                        
                        $result[$value['attribute_group_id'].'_'.$value['attribute_id']] = $value;
                        
                    }
                }
                
                ksort($result);
                
		return $result;
	}
        
        protected function curl_get_contents($url) {
            
            $this->load->language($this->path_oc_version.'/csv_ocext_dmpro');
            
            if(!isset($this->request->get['token'])){
                
                exit($this->language->get('error_no_token'));
                
            }
            
            if(function_exists('curl_version')){
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
                $output = curl_exec($ch);
                curl_close($ch);
                return $output;
            }else{
                $output['ru'] = 'Проверка версии недоступна. Включите php расширение - CURL на Вашем хостинге';
                $output['en'] = 'You can not check the version. Enable php extension - CURL on your hosting';
                $language_code = $this->config->get( 'config_admin_language' );
                if(isset($output[$language_code])){
                    return $output[$language_code];
                }else{
                    return $output['en'];
                }
            }
	}
        
        public function model($model, $data = array()) {
            
                $dir = dirname(__DIR__).'/';

                $file = $dir . $model . '.php';

                $class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

                if (file_exists($file)) {
                        include_once($file);

                        $this->registry->set('ocext_model_' . str_replace('/', '_', $model), new $class($this->registry));
                } else {
                        trigger_error('Error: Could not load model ' . $file . '!');
                        exit();
                }

        }
        
        public function getStringFromArray($array,$delimeter=', ') {
            
            $string = array();
            
            if(is_string($array)){
                
                return $array;
                
            }else{
                
                foreach ($array as $key => $value) {
                    
                    $string[] = $this->getStringFromArray($value);
                    
                }
                
            }
            
            return implode($delimeter, $string);
            
        }
        
        public function unlinkProcessLog($id) {
	
            if(is_dir(DIR_CACHE)){
	    
                $result = scandir(DIR_CACHE);

                foreach ($result as $file_name) {

                    if($file_name!=='.' && $file_name!=='..' && strstr($file_name,'anycsv_process_log_'.$id)){
                        
                        unlink(DIR_CACHE.$file_name);

                    }

                }

            }

            return;

        }
        
        
}
?>