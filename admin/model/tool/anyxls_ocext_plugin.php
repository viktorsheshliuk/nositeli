<?php
class ModelToolAnyXLSOcextPlugin extends Model {
	
    protected $registry;
    private $path_oc_version = 'extension/module';
    private $user_key = '';
    private $user_email = '';
    private $domen = '';
    private $host = '';
    private $curl_status = FALSE;
    private $zip_status = FALSE;
    
    private $license = FALSE;
    private $xls_as_array = array();
    private $csv_rows = array();
    private $first_row_xls = array();
    private $yandex_market_categories = array();
    private $xls_specification = 0;
    private $excel = FALSE;
    private $anyxls_count_column = 50;
    private $anyxls_count_rows = 5000;



    private $rows_xls = array();
    private $rows_xls_elements = array();
    private $rows_xls_elements_for_write_new = array();
    private $rows_xls_elements_for_end_write_new = array();
    

    public function __construct($registry) {
        $this->domen = $_SERVER["HTTP_HOST"];
        $this->registry = $registry;
        $this->setLicense();
        $this->checkCurl();
        $this->checkZip();
        $this->setExcel();
    }
    
    public function setExcel() {
        
        if(file_exists(DIR_SYSTEM.'PHPExcelOcext/Classes/PHPExcel.php')){
            if(!class_exists('PHPExcel')){
                include_once DIR_SYSTEM.'PHPExcelOcext/Classes/PHPExcel.php';
            }
            
            
            $this->excel = TRUE;
            
        }
        
    }
    
    private function setLicense(){
        
        $this->license = TRUE;
        
    }
    
    public function getLicense(){
        
        return $this->license;
        
    }
    
    private function checkZip(){
            
        if (class_exists('ZipArchive')) {

            $this->zip_status = TRUE;

        }else{

            $this->zip_status = FALSE;

        }

    }
        
    private function checkCurl() {

        if (function_exists('curl_init')) {

            $this->curl_status = TRUE;

        }else{

            $this->curl_status = FALSE;

        }

    }
    
    private function checkURL($url) {
        
        $url_parts = explode('/', trim($url));

        if($url_parts && is_array($url_parts)){
            
            $protocols = array('http:'=>0,'https:'=>0);
            
            if(isset($protocols[array_shift($url_parts)])){
                
                return TRUE;
                
            }
            
        }
        
        return FALSE;
        
    }
    
    

    public function cleanUrl($url){
            
            $url_parts = explode('/', $url);
            
            $result = '';
            
            foreach ($url_parts as $value) {
                
                $value = trim($value);
                
                if($value!='https:' && $value!='http:' && $value){
                    
                    $result = $value;
                    
                }
                
            }
            
            return $result;
            
        }
    
    public function getXLSSpecifications(){
        
        return array();
        
        $this->load->language($this->path_oc_version.'/anyxls_ocext_plugin');
        
        $xls_specifications = array('YML_category','YML_offer','YML','XML_other','XML_other_2','YML_offer_compact_1','YML_offer_compact_2');
        
        $data['text_xls_specification_YML_category'] = $this->language->get('text_xls_specification_YML_category');
        $data['text_xls_specification_YML_offer'] = $this->language->get('text_xls_specification_YML_offer');
        $data['text_xls_specification_YML'] = $this->language->get('text_xls_specification_YML');
        $data['text_xls_specification_XML_other'] = $this->language->get('text_xls_specification_XML_other');
        $data['text_xls_specification_XML_other_2'] = $this->language->get('text_xls_specification_XML_other_2');
        
        $data['text_xls_specification_YML_offer_compact_1'] = $this->language->get('text_xls_specification_YML_offer_compact_1');
        
        $data['text_xls_specification_YML_offer_compact_2'] = $this->language->get('text_xls_specification_YML_offer_compact_2');
        
        foreach ($xls_specifications as $key => $xls_specification) {
            
            unset($xls_specifications[$key]);
            
            $xls_specifications[$xls_specification] = $data['text_xls_specification_'.$xls_specification];
            
        }
        
        
        if(file_exists(DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php')){
            
            include DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php';
            
        }
        
        
        return $xls_specifications;
        
    }
   
    protected function getCell($worksheet,$row,$col,$default_val='') {
            //$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
            $row += 1; // we use 0-based, PHPExcel uses 1-based row index
            $val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getCalculatedValue() : $default_val;
            if ($val===null) {
                    $val = $default_val;
            }
            return trim(ltrim($val));
    }
    
    public function getStartFirstRow($start_first_row_columns,$start_first_row_columns2,$excel){
        
        $reslut = array();
        
        if($excel){
            
            for ($r=0;$r<300; $r++) {
            
                for($c=0;$c<7;$c++){

                    $value = $this->getCell($excel,$r,$c,'');
					
                    $value = str_replace('  ',' ',$value);

                    $value = mb_strtolower($value,'UTF-8');

                    if(in_array($value, $start_first_row_columns)){
                        		
                        $reslut[] = '';
                        
                    }

                }
                
                if(count($reslut) == count($start_first_row_columns)){
                    
                    return $r;
                    
                }

            }
        
        }
        
        return NULL;
        
    }
    
    protected function getCellAdv($worksheet,$row,$col,$default_val='',$calc=FALSE,$hiperl=FALSE) {
            //$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
            $row += 1; // we use 0-based, PHPExcel uses 1-based row index
            if($calc){
                
                $val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getCalculatedValue() : $default_val;
                
            }elseif($hiperl){
                
                $val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getHyperlink()->getUrl() : $default_val;
                
            }else{
                
                $val = ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
                
            }
            
            if (is_null($val)) {
                    $val = $default_val;
            }
            return $val;
    }
    
    public function getRowsAndColumns($worksheet) {
        
        $count_rows = 0;
        
        $count_columns = 0;
        
        foreach ($worksheet->getRowIterator() as $row) {
            
            $count_rows++;
            
            $cell_iterator = $row->getCellIterator();
				
            $cell_iterator->setIterateOnlyExistingCells(false);

            $count_columns_this = 0;
            
            foreach ($cell_iterator as  $cell) {

                if ( !is_null($cell) ) {
                    
                    $count_columns_this++;
                    
                    if($count_columns_this>$count_columns){
                        
                        $count_columns = $count_columns_this;
                        
                    }
                    
                }
                else{
                    
                    break;
                    
                }
                                        
            }
            
        }
        
        $this->anyxls_count_rows = $count_rows;
        
        $this->anyxls_count_column = $count_columns;
        
    }
    
    public function getAnyXLSResult($odmpro_tamplate_data){
            
            $xls = FALSE;
            
            $file_type = '';
            
            $result_process = array('error'=>'','success'=>'');
            
            $xls_specification = 0;
            
            if(isset($odmpro_tamplate_data['xls_specification'])){
                
                $xls_specification = $odmpro_tamplate_data['xls_specification'];
                
            }
            
            if(!$this->excel){
                $result_process['error'] = "Загрузка XLS невозможна. Проверьте установку классов эксель, которые идут вместе с файлами модуля";
                return $result_process;
            }
            
            $this->xls_specification = $xls_specification;
            
            $this->load->language($this->path_oc_version.'/anyxls_ocext_plugin');
            
            $only_columns = array();
            
            $cut_columns = array();
            
            $only_columns = array_flip($only_columns);
            
            $cut_columns = array_flip($cut_columns);
            
            $new_file_name = '';
            
            if(strstr(mb_strtolower($odmpro_tamplate_data['file_url'],'UTF-8'), '.zip') || strstr(mb_strtolower($odmpro_tamplate_data['file_upload'],'UTF-8'), '.zip')){
                
                if(!$this->zip_status){
                    
                    $result_process['error'] = "Загрузка ZIP архива невозможна. Включите расширение php zip на хостинге";
                    
                    return $result_process;
                    
                }else{
                    
                    if($odmpro_tamplate_data['file_url']){
                        
                        $fileinfo = pathinfo($odmpro_tamplate_data['file_url']);
                        $zip_basename = $fileinfo['basename'];
                        $zip_file = file_get_contents($odmpro_tamplate_data['file_url']);
                        $handle = fopen(DIR_DOWNLOAD.$zip_basename, "w+");
                        fwrite($handle, $zip_file);
                        fclose($handle);
                        $zip_path = DIR_DOWNLOAD.$zip_basename;
                        $zip = new ZipArchive();
                        $odmpro_tamplate_data['file_upload'] = '';
                        $odmpro_tamplate_data['file_url'] = '';
                        if ($zip->open($zip_path) === true) { 
                            $file_name = $zip->getNameIndex(0);
                            $zip->extractTo(DIR_DOWNLOAD);
                            if(file_exists(DIR_DOWNLOAD.$file_name)){
                                
                                $odmpro_tamplate_data['file_upload'] = $file_name;
                                
                            }
                            $zip->close();                   
                        }
                        
                    }
                    
                    elseif($odmpro_tamplate_data['file_upload']){
                        
                        
                        $zip_path_last = $odmpro_tamplate_data['file_upload'];
                        $zip_path = DIR_DOWNLOAD.$zip_path_last;
                        $zip = new ZipArchive();
                        $odmpro_tamplate_data['file_upload'] = '';
                        $odmpro_tamplate_data['file_url'] = '';
                        if ($zip->open($zip_path) === true) { 
                            $file_name = $zip->getNameIndex(0);
                            $zip->extractTo(DIR_DOWNLOAD);
                            if(file_exists(DIR_DOWNLOAD.$file_name)){
                                
                                $odmpro_tamplate_data['file_upload'] = $file_name;
                                
                            }
                            $zip->close();                   
                        }
                        
                    }
                    
                    
                }
                
            }
            
            
            
            if($odmpro_tamplate_data['file_url']){
                    
                if($this->checkURL($odmpro_tamplate_data['file_url'])){
                    
                    $fileinfo = pathinfo($odmpro_tamplate_data['file_url']);
                    $new_file_name = $fileinfo['basename'];
                    $file = file_get_contents($odmpro_tamplate_data['file_url']);
                    $handle = fopen(DIR_DOWNLOAD.$new_file_name, "w+");
                    fwrite($handle, $file);
                    fclose($handle);
                    
                    $file_type = PHPExcel_IOFactory::identify(DIR_DOWNLOAD.$new_file_name);
                    //$xls = PHPExcel_IOFactory::load(DIR_DOWNLOAD. $new_file_name);
                    
                }else{
                    
                    $result_process['error'] = "Не могу загрузить файл по указанной ссылке. Проверьте ссылку";
                    
                    return $result_process;
                    
                }

            }
            elseif($odmpro_tamplate_data['file_upload']){

                $new_file_name = $odmpro_tamplate_data['file_upload'];
                
                $file_type = PHPExcel_IOFactory::identify(DIR_DOWNLOAD.$new_file_name);
                //$xls = PHPExcel_IOFactory::load(DIR_DOWNLOAD. $new_file_name);

            }
            
            $file_types = array('Excel2003XML','Excel2007','Excel5');
            
            if(isset($odmpro_tamplate_data['anyxls_disable_validate']) && !$odmpro_tamplate_data['anyxls_disable_validate']){
                
                if(!$file_type || !in_array($file_type, $file_types)){
                    
                    $result_process['error'] = "Данный файл, скорее всего, не является файлом, который поддерживается PHPEXCEL. Попробуйте отключить валидацию, чтобы попытаться загрузить файл без проверки формата";
                    
                }
                
            }
            
            if(isset($result_process['error']) && $result_process['error']){
                
                return $result_process;
                
            }
            
            try {
                if (!($xls = PHPExcel_IOFactory::load(DIR_DOWNLOAD. $new_file_name))) {

                    

                }
            } catch(Exception $e) {
                
                $result_process['error'] = 'Не удалось открыть файл. Формат файла определен, как: <b>'.$file_type.'</b>, критическая ошибка: <b>'.$e->getMessage().'</b>';
                
            }
            
            if(isset($result_process['error']) && $result_process['error']){
                
                return $result_process;
                
            }
            
            if(FALSE && $xls){
                
                $excel = $xls->getActiveSheet();
                
                $xls_rows = array();

                $xls_first_row = array();
                
                if(isset($odmpro_tamplate_data['anyxls_count_column']) && $odmpro_tamplate_data['anyxls_count_column']!==""){
                    
                    $this->anyxls_count_column = (int)$odmpro_tamplate_data['anyxls_count_column'];
                    
                }
                
                if(isset($odmpro_tamplate_data['anyxls_count_rows']) && $odmpro_tamplate_data['anyxls_count_rows']!==""){
                    
                    $this->anyxls_count_rows = (int)$odmpro_tamplate_data['anyxls_count_rows'];
                    
                }
                
                $this->anyxls_first_row = 1;
                
                if(isset($odmpro_tamplate_data['anyxls_first_row']) && $odmpro_tamplate_data['anyxls_first_row']!==""){
                    
                    $this->anyxls_first_row = (int)$odmpro_tamplate_data['anyxls_first_row'];
                    
                }
                
                $start_first_row_columns = array('номенклатура','наличие','артикул','розница','мелкий опт','опт','дилер');
                
                $start_first_row = $this->getStartFirstRow($start_first_row_columns,array(),$excel);
                
                if(!is_null($start_first_row)){
                    
                    $start_rows = $this->anyxls_first_row;

                    $control_index_product_row = 2;

                    $hiperlink_index_and_on = array(/*5=>TRUE*/);

                    $calc_index_and_on = array(/*6=>TRUE*/);
        
                    $category_path = '';

                    if($excel){

                            for ($r=$start_first_row;$r<$this->anyxls_count_rows; $r++) {

                                    for($c=0;$c<$this->anyxls_count_column;$c++){

                                            $hiperl = FALSE;

                                            $calc = FALSE;

                                            if(isset($hiperlink_index_and_on[$c]) && $r!==$start_first_row){

                                                    $hiperl = $hiperlink_index_and_on[$c];

                                            }

                                            if(isset($calc_index_and_on[$c]) && $r!==$start_first_row){

                                                    $calc = $calc_index_and_on[$c];

                                            }

                                            //5
                                            $value = $this->getCellAdv($excel,$r,$c,'',$calc,$hiperl);

                                            if($r===$start_first_row && $value!==''){

                                                    $xls_first_row[$c] = trim($value);

                                            }elseif(isset ($xls_first_row[$c]) && $r>=$start_rows){

                                                    $index_csv = $r-$start_rows;

                                                    if(is_string($value)){
                                                            $value = trim($value);
                                                    }else{
                                                            //$value = 'NO_STRING';
                                                    }

                                                    $xls_rows[$index_csv][$c] = $value;

                                            }

                                    }

                                    if($r===$start_first_row){

                                            $xls_first_row[] = "Категория";
                                            
                                            $cat_pos = count($xls_first_row)-1;

                                    }elseif(isset($index_csv) && $xls_rows[$index_csv][0] && isset($xls_rows[$index_csv][1]) && !$xls_rows[$index_csv][1]){

                                            $category_path = $xls_rows[$index_csv][0];

                                    }

                                    if(isset($index_csv) && isset($xls_rows[$index_csv]) && isset($xls_rows[$index_csv][$control_index_product_row]) && $xls_rows[$index_csv][$control_index_product_row]===''){

                                            unset($xls_rows[$index_csv]);

                                    }elseif(isset($index_csv)){

                                            $xls_rows[$index_csv][$cat_pos] = $category_path;

                                    }


                            }

                    }
			
		}else{
			
                    $start_first_row = 0;

                    $start_rows = $this->anyxls_first_row;

                    $control_index_product_row = 2;

                    $hiperlink_index_and_on = array(/*5=>TRUE*/);

                    $calc_index_and_on = array(/*6=>TRUE*/);

                    if($excel){

                            for ($r=$start_first_row;$r<$this->anyxls_count_rows; $r++) {

                                    for($c=0;$c<$this->anyxls_count_column;$c++){

                                            $hiperl = FALSE;

                                            $calc = FALSE;

                                            if(isset($hiperlink_index_and_on[$c]) && $r!==$start_first_row){

                                                    $hiperl = $hiperlink_index_and_on[$c];

                                            }

                                            if(isset($calc_index_and_on[$c]) && $r!==$start_first_row){

                                                    $calc = $calc_index_and_on[$c];

                                            }

                                            //5
                                            $value = trim($this->getCellAdv($excel,$r,$c,'',$calc,$hiperl));

                                            if($r===$start_first_row && $value!==''){

                                                    $xls_first_row[$c] = trim($value);

                                            }elseif(isset ($xls_first_row[$c]) && $r>=$start_rows){

                                                    $index_csv = $r-$start_rows;

                                                    if(is_string($value)){
                                                            $value = trim($value);
                                                    }else{
                                                            //$value = 'NO_STRING';
                                                    }

                                                    $xls_rows[$index_csv][$c] = $value;

                                            }


                                    }



                                    if($r===$start_first_row){

                                            $xls_first_row[] = "Категория";

                                    }elseif(isset($index_csv) && $xls_rows[$index_csv][0] && isset($xls_rows[$index_csv][1]) && isset($xls_rows[$index_csv][2]) && isset($xls_rows[$index_csv][4]) && !$xls_rows[$index_csv][1] && !$xls_rows[$index_csv][2] && !$xls_rows[$index_csv][4]){

                                            $category_path = $xls_rows[$index_csv][0];

                                    }

                                    if(isset($index_csv) && isset($xls_rows[$index_csv]) && isset($xls_rows[$index_csv][$control_index_product_row]) && $xls_rows[$index_csv][$control_index_product_row]===''){

                                            unset($xls_rows[$index_csv]);

                                    }elseif(isset($index_csv)){

                                            $xls_rows[$index_csv][7] = $category_path;

                                    }


                            }

                    }
			
                }
                
                $csv_rows = array();

                if(!$xls_first_row || !$xls_rows){

                    $result['error'] = 'Не удается прочитать XLS файл или в файле нет данных';
                    return $result;

                }
                
                $csv_rows[] = $xls_first_row;
                $csv_rows += $xls_rows;
                
                    
                foreach ($csv_rows as $key_csv => $value_csv) {

                    $no_data = TRUE;

                    foreach ($value_csv as $key_csv2 => $value_csv2) {

                        if($value_csv2){

                            $no_data = FALSE;

                        }

                    }

                    if($no_data){

                        unset($csv_rows[$key_csv]);

                    }

                }
                    
                $result_process = array('error'=>'','file_upload'=>'');

                $csv_delimiter = $odmpro_tamplate_data['csv_delimiter'];

                $csv_enclosure = $odmpro_tamplate_data['csv_enclosure'];

                $csv_escape = $odmpro_tamplate_data['csv_escape'];

                $encoding = $odmpro_tamplate_data['encoding'];

                if(file_exists(DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php')){

                    include DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php';

                }

                $file_name_result = $this->writeCsv($csv_rows,1,$csv_delimiter,$csv_enclosure,$csv_escape,$encoding,$new_file_name,array());

                if(!$file_name_result){

                    $this->load->language($this->path_oc_version.'/anyxls_ocext_plugin');

                    $result_process['error'] = $this->language->get('error_anyxls_result');

                }else{

                    $result_process['file_upload'] = $file_name_result;

                }

                return $result_process;
                
            }elseif(FALSE){

                $result_process['error'] = "Не удалось загрузить файл XLS";

            }
            
            if($xls){
                
                $all_sheets = $xls->getSheetNames();
                
                if(isset($odmpro_tamplate_data['anyxls_sheet_name']) && $odmpro_tamplate_data['anyxls_sheet_name']!=='' && !in_array($odmpro_tamplate_data['anyxls_sheet_name'], $all_sheets)){
                    
                    $result_process['error'] = "Указанный лист отсутствует в файле. Список листов этого файла: <b>". implode(', ', $all_sheets)."</b>";
                    
                    return $result_process;
                    
                }elseif(isset($odmpro_tamplate_data['anyxls_sheet_name']) && $odmpro_tamplate_data['anyxls_sheet_name']!=='' && in_array($odmpro_tamplate_data['anyxls_sheet_name'], $all_sheets)){
                    
                    $excel = $xls->getSheetByName($odmpro_tamplate_data['anyxls_sheet_name']);
                    
                }else{
                    
                    $excel = $xls->getActiveSheet();
                    
                }
        
                $xls_rows = array();

                $xls_first_row = array();
                
                if(isset($odmpro_tamplate_data['anyxls_count_rows']) && $odmpro_tamplate_data['anyxls_count_rows']!==""){
                    
                    $this->anyxls_count_rows = (int)$odmpro_tamplate_data['anyxls_count_rows'];
                    
                }
                
                else{
                    
                    $this->getRowsAndColumns($excel);
                    
                }
                
                if(isset($odmpro_tamplate_data['anyxls_count_column']) && $odmpro_tamplate_data['anyxls_count_column']!==""){
                    
                    $this->anyxls_count_column = (int)$odmpro_tamplate_data['anyxls_count_column'];
                    
                }
                
                $this->anyxls_first_row = 1;
                
                if(isset($odmpro_tamplate_data['anyxls_first_row']) && $odmpro_tamplate_data['anyxls_first_row']!==""){
                    
                    $this->anyxls_first_row = (int)$odmpro_tamplate_data['anyxls_first_row'];
                    
                }
                
                if($excel){

                    for ($r=0;$r<$this->anyxls_count_rows; $r++) {

                        for($c=0;$c<$this->anyxls_count_column;$c++){

                            $value = (string)$this->getCell($excel,$r,$c);

                            if($r===($this->anyxls_first_row-1) && $value){

                                $xls_first_row[$c] = $value;

                            }elseif(isset ($xls_first_row[$c])){

                                $xls_rows[$r][$c] = $value;

                            }


                        }

                    }

                }
                
                $csv_rows = array();

                if(!$xls_first_row || !$xls_rows){

                    $result['error'] = 'Не удается прочитать XLS файл или в файле нет данных.'." Возможно данные расположены в другом листе. Список листов этого файла: <b>". implode(', ', $all_sheets)."</b>";
                    return $result;

                }
                
                $csv_rows[] = $xls_first_row;
                $csv_rows += $xls_rows;
                
                    
                foreach ($csv_rows as $key_csv => $value_csv) {

                    $no_data = TRUE;

                    foreach ($value_csv as $key_csv2 => $value_csv2) {

                        if($value_csv2){

                            $no_data = FALSE;

                        }

                    }

                    if($no_data){

                        unset($csv_rows[$key_csv]);

                    }

                }
                    
                $result_process = array('error'=>'','file_upload'=>'');

                $csv_delimiter = $odmpro_tamplate_data['csv_delimiter'];

                $csv_enclosure = $odmpro_tamplate_data['csv_enclosure'];

                $csv_escape = $odmpro_tamplate_data['csv_escape'];

                $encoding = $odmpro_tamplate_data['encoding'];

                if(file_exists(DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php')){

                    include DIR_APPLICATION.'model/tool/anyxls_ocext_plugin_add.php';

                }

                $file_name_result = $this->writeCsv($csv_rows,1,$csv_delimiter,$csv_enclosure,$csv_escape,$encoding,$new_file_name,array());

                if(!$file_name_result){

                    $this->load->language($this->path_oc_version.'/anyxls_ocext_plugin');

                    $result_process['error'] = $this->language->get('error_anyxls_result');

                }else{

                    $result_process['file_upload'] = $file_name_result;

                }

                return $result_process;

            }else{

                $result_process['error'] = "Не удалось загрузить файл XLS";

            }
            
            
        }
        
    public function getFileByURL($url,$httpcode=FALSE) {

        $file_exists = @fopen($url, "r");

        if(!$file_exists){

            return FALSE;

        }

        $handle = fopen($url, "r");

        if($handle && $httpcode){

            return TRUE;

        }elseif(!$handle && $httpcode){

            return FALSE;

        } 

        return $handle;
    }

    public function getFileByFileName($file_name,$httpcode=FALSE) {

        $file = DIR_DOWNLOAD.$file_name;

        if($httpcode && !file_exists($file)){

            return FALSE;

        }  elseif ($httpcode && file_exists($file)) {

            return TRUE;

        }

        $handle = FALSE;

        if(file_exists($file)){

            $handle = fopen($file,'r');

        }

        return $handle;
    }

    public function writeCsv($data,$first_write,$csv_delimiter,$csv_enclosure,$csv_escape,$encoding,$file_and_path,$log_data=array()) {
        
        $file_name_and_path = $file_and_path.'.csv';
        
        $file_name_and_path_array = explode('/', trim($file_name_and_path));
            
        $path_array = array();

        for ($i=0;$i<(count($file_name_and_path_array)-1);$i++) {

            $path_array[] = $file_name_and_path_array[$i];

        }

        $file_name = end($file_name_and_path_array);
        
        $write_path = DIR_DOWNLOAD;
        
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
        
        foreach ($data as $num_row => $csv_row) {
            
            $value = '';
            
            $col = 1;
            
            foreach ($csv_row as $row) {
                
                if($col<count($csv_row)){
                    $value .= $row.$csv_delimiter;
                }else{
                    $value .= $row;
                }
                
                $value = str_replace(array("\r\n", "\r", "\n"), " ", $value);
                
                $value = str_replace(array("\""), "'", $value);
                
                $col++;
                
            }
            
            unset($data[$num_row]);
        
            fputcsv($handle, explode($csv_delimiter, $value), html_entity_decode($csv_delimiter),html_entity_decode($csv_enclosure));
            
        }
        
        fclose($handle);
        
        return $file_name;
    }
        
    private function setYandexMarketCategories($name, $category_id, $parent_id = 0) {
        
        if(!$category_id || !$name) {
            return;
        }
        if((int)$parent_id > 0) {
            $this->categories[$category_id] = array(
                    'id'=>$category_id,
                    'parentId'=>(int)$parent_id,
                    'name'=>$name
            );
        }else{
            $this->categories[$category_id] = array(
                'id'=>(int)$category_id,
                'name'=>$name
            );
        }
        
    }
    
    protected function getPathWhisCategories($category_id,$old_path = '', $delimiter='/') {
            if (isset($this->yandex_market_categories[$category_id])) {
                if (!$old_path) {
                    $new_path = $this->yandex_market_categories[$category_id]['name'];
                } else {
                    $new_path = $this->yandex_market_categories[$category_id]['name'].$delimiter.$old_path;
                }	
                if (isset($this->yandex_market_categories[$category_id]['parent_id']) && $this->yandex_market_categories[$category_id]['parent_id']) {
                    return $this->getPathWhisCategories($this->yandex_market_categories[$category_id]['parent_id'], $new_path);
                } else {
                    return $new_path;
                }
            }
    }
    
    
    
    
    
    
    
    
    public function transposeArray($array){
        
        $result = array();
        
        if(isset($array[0])){
            
            $a = $array[0];
            
            $result[$a] = array();
            
            
            if(isset($array[1])){
            
                $a1 = $array[1];

                $result[$a][$a1] = array();

            }
            
            if(isset($array[2])){
            
                $a2 = $array[2];

                $result[$a][$a1][$a2] = array();
            }
            
            if(isset($array[3])){
            
                $a3 = $array[3];

                $result[$a][$a1][$a2][$a3] = array();
                
            }
            
            if(isset($array[4])){
            
                $a4 = $array[4];

                $result[$a][$a1][$a2][$a3][$a4] = array();
                
            }
            
            if(isset($array[5])){
            
                $a5 = $array[5];

                $result[$a][$a1][$a2][$a3][$a4][$a5] = array();
                
            }
            
            if(isset($array[6])){
            
                $a6 = $array[6];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6] = array();
                
            }
            
            if(isset($array[7])){
            
                $a7 = $array[7];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7] = array();
                
            }
            
            if(isset($array[8])){
            
                $a8 = $array[8];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8] = array();
                
            }
            
            if(isset($array[9])){
            
                $a9 = $array[9];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8][$a9] = array();
                
            }
            
            if(isset($array[10])){
            
                $a10 = $array[10];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8][$a9][$a10] = array();
                
            }
            
        }
        
        return $result;
        
    }

    public function transposeArrayForXMLData($array){
        
        $result = array();
        
        $xls_indexes = array(
            '___name___',
            '___attributes___',
            '___elements___',
            '___children___',
        );
        
        if(isset($array[0])){
            
            $a = $array[0];
            
            foreach($xls_indexes as $xls_index){
                
                if($xls_index=='___name___'){
                    
                    $result[$a][$xls_index] = $a;
                    
                }else{
                    
                    $result[$a][$xls_index] = array();
                    
                }
                
            }
            
            if(isset($array[1])){
            
                $a1 = $array[1];

                foreach($xls_indexes as $xls_index){
                
                    if($xls_index=='___name___'){

                        $result[$a]['___children___'][$a1]['___name___'] = $a1;

                    }else{

                        $result[$a]['___children___'][$a1][$xls_index] = array();

                    }

                }

            }
            
            if(isset($array[2])){
            
                $a2 = $array[2];
                
                foreach($xls_indexes as $xls_index){
                
                    if($xls_index=='___name___'){

                        $result[$a]['___children___'][$a1]['___children___'][$a2] = $a2;

                    }else{

                        $result[$a]['___children___'][$a1]['___children___'][$a2][$xls_index] = array();

                    }

                }
                
            }
            
            if(isset($array[3])){
            
                $a3 = $array[3];

                foreach($xls_indexes as $xls_index){
                
                    if($xls_index=='___name___'){

                        $result[$a]['___children___'][$a1]['___children___'][$a2]['___children___'][$a3] = $a3;

                    }else{

                        $result[$a]['___children___'][$a1]['___children___'][$a2]['___children___'][$a3][$xls_index] = array();

                    }

                }
                
            }
            
            if(isset($array[4])){
            
                $a4 = $array[4];

                foreach($xls_indexes as $xls_index){
                
                    if($xls_index=='___name___'){

                        $result[$a]['___children___'][$a1]['___children___'][$a2]['___children___'][$a3]['___children___'][$a4] = $a4;

                    }else{

                        $result[$a]['___children___'][$a1]['___children___'][$a2]['___children___'][$a3]['___children___'][$a4][$xls_index] = array();

                    }

                }
                
            }
            
            if(isset($array[5])){
            
                $a5 = $array[5];

                $result[$a][$a1][$a2][$a3][$a4][$a5] = array();
                
            }
            
            if(isset($array[6])){
            
                $a6 = $array[6];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6] = array();
                
            }
            
            if(isset($array[7])){
            
                $a7 = $array[7];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7] = array();
                
            }
            
            if(isset($array[8])){
            
                $a8 = $array[8];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8] = array();
                
            }
            
            if(isset($array[9])){
            
                $a9 = $array[9];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8][$a9] = array();
                
            }
            
            if(isset($array[10])){
            
                $a10 = $array[10];

                $result[$a][$a1][$a2][$a3][$a4][$a5][$a6][$a7][$a8][$a9][$a10] = array();
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getCountXMLDuplicateElements_____last($xls){
            
            if($xls){
                
                $count = 0;
                
                $last_name = '';
                
                $first_dublicate = '';
                
                foreach ($xls as $name => $element) {
                    
                    /*
                     * элементы
                     */
                    
                    if($element->count() && $first_dublicate!=''){
                        
                        $count += 1;
                        
                    }else{
                        
                        $count += 0;
                        
                    }
                    
                    $name = (string)$name;
                    
                    if($name==$last_name){
                        
                        $count += 1;
                        
                        if($first_dublicate==''){
                            
                            $count += 1;
                            
                        }
                        
                        $first_dublicate = 1;
                        
                    }
                    
                    $last_name = $name;
                    
                    $count += $this->getCountXMLDuplicateElements($element);
                    
                }
                
                return $count;
                
            }else{
                
                return 0;
                
            }
            
        }
        
    public function getCountDuplicateNames($xls){
            
        $name_whis_dublicates = array();

        if($xls->count()){

            foreach ($xls as $name => $element) {

                $name = (string)$name;

                if(!isset($name_whis_dublicates[$name])){

                    $name_whis_dublicates[$name] = 0;

                }else{

                    $name_whis_dublicates[$name] += 1;

                }

            }

        }

        return $name_whis_dublicates;

    }
    
    public function getCountDuplicateNamesDelta($duplicate_names,$name_needle){
            
        $count = 0;
        
        foreach($duplicate_names as $name => $count_dublicates){
            
            if($name!=$name_needle){
                
                $count += $count_dublicates;
                
            }
        }

        return $count;

    } 
        
    public function getXMLDuplicateElements($xls){

        if($xls){

            $count = 0;

            $last_name = '';

            $last_name_attribute = '';

            $first_dublicate = '';

            $attributes = $xls->attributes();

            if($attributes && FALSE){

                foreach($attributes as $name_attribute => $attribute){

                    $name_attribute = (string)$name_attribute;

                    //echo $name_attribute.'<br>';

                    if($name_attribute==$last_name_attribute){

                    $count += 1;

                        if($first_dublicate==''){

                            $count += 1;

                        }

                        $first_dublicate = 1;

                    }

                    $last_name_attribute = $name_attribute;

                }

            }

            foreach ($xls as $name => $element) {

                /*
                 * элементы
                 */

                if($element->count() && $first_dublicate!=''){

                    $count += 1;

                }else{

                    $count += 0;

                }

                $name = (string)$name;

                if($name==$last_name){

                    $count += 1;

                    if($first_dublicate==''){

                        $count += 1;

                    }

                    $first_dublicate = 1;

                }

                $last_name = $name;

                $count += $this->getXMLDuplicateElements($element);

            }

            return $count;

        }else{

            return 0;

        }

    }

    public function getXMLNameDuplicate($xls){

        $name_whis_dublicates = array();

        if($xls->count()){

            foreach ($xls as $name => $element) {

                $name = (string)$name;

                if(!isset($name_whis_dublicates[$name])){

                    $name_whis_dublicates[$name] = 0;

                }else{

                    $name_whis_dublicates[$name] += 1;

                }

            }

        }

        return $name_whis_dublicates;

    }

    public function getCountXMLNameDuplicate($xls){

        $count_name_whis_dublicates = 0;

        $name_whis_dublicates = array();

        if($xls->count()){

            foreach ($xls as $name => $element) {

                $name = (string)$name;

                if(!isset($name_whis_dublicates[$name])){

                    $name_whis_dublicates[$name] = 0;

                }else{

                    $name_whis_dublicates[$name] += 1;

                }

            }

        }

        foreach ($name_whis_dublicates as $key => $value) {
            $count_name_whis_dublicates += $value;
        }

        return $count_name_whis_dublicates;

    }    
        
    public function getCountXMLElementDuplicates($xls){
            
            if($xls){
                
                $count = 0;
                
                $last_name = '';
                
                $last_name_attribute = '';
                
                $first_dublicate = '';
                
                $attributes = $xls->attributes();
                
                if($attributes && FALSE){
                    
                    foreach($attributes as $name_attribute => $attribute){
                        
                        $name_attribute = (string)$name_attribute;
                        
                        if($name_attribute==$last_name_attribute){
                        
                        $count += 1;
                        
                            if($first_dublicate==''){

                                $count += 1;

                            }

                            $first_dublicate = 1;

                        }

                        $last_name_attribute = $name_attribute;
                        
                    }
                    
                }
                
                foreach ($xls as $name => $element) {
                    
                    /*
                     * элементы
                     */
                    
                    if($element->count() && $first_dublicate!=''){
                        
                        $count += 1;
                        
                    }else{
                        
                        $count += 0;
                        
                    }
                    
                    $name = (string)$name;
                    
                    if($name==$last_name){
                        
                        $count += 1;
                        
                        if($first_dublicate==''){
                            
                            $count += 1;
                            
                        }
                        
                        $first_dublicate = 1;
                        
                    }
                    
                    $last_name = $name;
                    
                    $count += $this->getXMLDuplicateElements($element);
                    
                }
                
                return $count;
                
            }else{
                
                return 0;
                
            }
            
        }    
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
    
    
        
 
    
    
    
    
    
    public function getXMLAttributesNew($xls,$parrent_element_name='',$write_rows=FALSE,$dont_writed=0,$dublicate_writes=0,$head_element_write=FALSE){
            
            if(!$parrent_element_name && $xls){
                
                $attributes = $xls->attributes();
                
                $parrent_element_name = $xls->getName();
                
                $count_dublicates = $this->getXMLDuplicateElements($xls);
                
                $name_whis_dublicates = $this->getXMLNameDuplicate($xls);
                
                if($attributes){
                        
                    $attributes = (array)$attributes;
                    
                    $index_attributes = $parrent_element_name.'-';
                        
                    $rows_xls_elements_this_attributes = array();

                    if($write_rows){

                        $rows_xls_elements_this_attributes = $this->rows_xls_elements[$index_attributes];

                    }

                    foreach($attributes['@attributes'] as $attribute_name => $attribute_value){
                        
                        $attribute_name = (string)$attribute_name;
                            
                        $index_unique = $parrent_element_name.'-'.$attribute_name;

                        $this->first_row_xls[$index_unique] = $index_unique;
                        
                        if(!isset($this->rows_xls_elements[$index_attributes][$attribute_name]) && !$write_rows){
                                
                            $this->rows_xls_elements[$index_attributes][$attribute_name] = 1;

                        }elseif(!$write_rows){
                            
                            $this->rows_xls_elements[$index_attributes][$attribute_name] += 1;
                            
                        }
                        
                        if($write_rows && $head_element_write){
                                
                            foreach($rows_xls_elements_this_attributes as $attribute_name_this_attributes => $count_nodes_whis_data){

                                if($attribute_name_this_attributes == $attribute_name){

                                    for($i=0;$i<$count_dublicates;$i++) {

                                        $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = ltrim(trim((string)$attribute_value));

                                    }
                                    
                                    //$this->rows_xls_elements_for_end_write_new[$index_attributes.$attribute_name_this_attributes] = $dont_writed;

                                    unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);

                                }

                            }

                        }

                    }
                    
                    if($rows_xls_elements_this_attributes){
                                        
                        foreach($rows_xls_elements_this_attributes as $attribute_name_this_attributes => $count_nodes_whis_data){

                            for($i=0;$i<$count_dublicates;$i++) {

                                $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = '';
                                
                                //$this->rows_xls_elements_for_end_write_new[$index_attributes.$attribute_name_this_attributes] = $dont_writed;

                            }

                            unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);

                        }

                    }

                }
                
                
                $this->first_row_xls[$parrent_element_name] = $parrent_element_name; 
                
                $element_value = ltrim(trim((string)$xls));
                
                $last_parrent_element_name = '';
                
                if($head_element_write && $write_rows && !isset($this->first_row_xls[$parrent_element_name])){

                    for($i=0;$i<$count_dublicates;$i++) {

                        $this->rows_xls_elements_for_write_new[$parrent_element_name][] = '';

                    }


                }elseif($write_rows && $head_element_write && isset($this->first_row_xls[$parrent_element_name]) && $parrent_element_name != $last_parrent_element_name && (!isset($name_whis_dublicates[(string)$parrent_element_name]) || !$name_whis_dublicates[(string)$parrent_element_name] ) ){

                    for($i=0;$i<$count_dublicates;$i++) {

                        $this->rows_xls_elements_for_write_new[$parrent_element_name][] = $element_value;

                    }

                }elseif($write_rows && $head_element_write && isset($this->first_row_xls[$parrent_element_name]) && ($parrent_element_name != $last_parrent_element_name || (isset($name_whis_dublicates[(string)$parrent_element_name]) && $name_whis_dublicates[(string)$parrent_element_name] ) )){

                    if(isset($name_whis_dublicates[(string)$parrent_element_name]) && $name_whis_dublicates[(string)$parrent_element_name] ){

                        $this->rows_xls_elements_for_write_new[$parrent_element_name][] = $element_value;

                        //$writed_dublicate++;

                    }else{

                        $this->rows_xls_elements_for_write_new[$parrent_element_name][] = $element_value;

                    }


                }elseif($write_rows && $head_element_write && isset($this->first_row_xls[$parrent_element_name]) && $parrent_element_name == $last_parrent_element_name){

                    $this->rows_xls_elements_for_write_new[$parrent_element_name][] = $element_value;

                    //$writed_dublicate++;

                }
                
                //$this->rows_xls_elements_for_end_write_new[$parrent_element_name] = $dont_writed;
                
            }
            
            if($xls){
                
                $last_index_unique_element = '';
                
                $name_whis_dublicates = $this->getXMLNameDuplicate($xls);
                
                $count_name_whis_dublicates = $this->getCountXMLNameDuplicate($xls);
                
                $count_dublicates = $this->getXMLDuplicateElements($xls);
                
                foreach ($xls as $a => $b) {
                    
                    $attributes = $b->attributes();
                    
                    $count_dublicates_for_attributes = $this->getXMLDuplicateElements($b);
                    
                    if($attributes){
                        
                        $attributes = (array)$attributes;
                        
                        $index_attributes = $parrent_element_name.'__'.$a.'-';
                        
                        $rows_xls_elements_this_attributes = array();
                        
                        if($write_rows){
                            
                            $rows_xls_elements_this_attributes = $this->rows_xls_elements[$index_attributes];
                            
                        }
                        
                        foreach($attributes['@attributes'] as $attribute_name => $attribute_value){
                            
                            $attribute_name = (string)$attribute_name;
                            
                            $index_unique = $parrent_element_name.'__'.$a.'-'.$attribute_name;
                            
                            $this->first_row_xls[$index_unique] = $index_unique;
                            
                            if(!isset($this->rows_xls_elements[$index_attributes][$attribute_name]) && !$write_rows){
                                
                                $this->rows_xls_elements[$index_attributes][$attribute_name] = 1;
                                
                            }elseif(!$write_rows){
                                
                                $this->rows_xls_elements[$index_attributes][$attribute_name] += 1;
                                
                            }
                            
                            if($write_rows){
                                
                                foreach($rows_xls_elements_this_attributes as $attribute_name_this_attributes => $count_nodes_whis_data){
                                    
                                    //$this->rows_xls_elements_for_end_write_new[$index_attributes.$attribute_name_this_attributes] = $dont_writed;
                                    
                                    if($attribute_name_this_attributes == $attribute_name && $count_dublicates_for_attributes){
                                        
                                        for($i=0;$i<$count_dublicates_for_attributes;$i++) {

                                            $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = ltrim(trim((string)$attribute_value));
                                            
                                        }
                                        
                                        unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);
                                        
                                        
                                    }elseif($attribute_name_this_attributes == $attribute_name && !$count_dublicates_for_attributes){
                                        
                                        $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = ltrim(trim((string)$attribute_value));
                                        
                                        unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);
                                        
                                    }

                                }

                            }
                            
                        }
                        
                        if($rows_xls_elements_this_attributes){
                            
                            
                            $rows_xls_elements_this_attributes_add_spaces = $rows_xls_elements_this_attributes;
                                        
                            foreach($rows_xls_elements_this_attributes as $attribute_name_this_attributes => $count_nodes_whis_data){
                                
                                //$this->rows_xls_elements_for_end_write_new[$index_attributes.$attribute_name_this_attributes] = $dont_writed;
                                
                                if($count_dublicates_for_attributes){
                                    
                                    for($i=0;$i<$count_dublicates_for_attributes;$i++) {

                                        $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = '';

                                    }
                                    
                                    unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);
                                    
                                }elseif(!$count_dublicates_for_attributes){
                                    
                                    $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes][] = '';
                                    
                                    unset($rows_xls_elements_this_attributes[$attribute_name_this_attributes]);
                                    
                                }

                            }

                        }
                        
                    }
                    
                    $index_unique_element = $parrent_element_name.'__'.$a;
                    
                    if(!isset($this->rows_xls_elements[$parrent_element_name][$a]) && !$write_rows){
                                
                        $this->rows_xls_elements[$parrent_element_name][$a] = 1;

                    }elseif(!$write_rows){

                        $this->rows_xls_elements[$parrent_element_name][$a] += 1;

                    }
                    
                    $this->first_row_xls[$index_unique_element] = $index_unique_element;
                     
                    $element_value = ltrim(trim((string)$b));
                    
                    if($write_rows){
                        
                        //echo $index_unique_element.'-'.$count_dublicates_for_attributes.'-'.$dont_writed.'-'.$dublicate_writes.'-'.$name_whis_dublicates[(string)$a].'-'.(string)$a.'<br>';
                    }
                    
                    if($write_rows){
                        
                        if($write_rows && !isset($this->first_row_xls[$index_unique_element])){

                            for($i=0;$i<$count_dublicates;$i++) {

                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = '';

                            }


                        }elseif($write_rows && isset($this->first_row_xls[$index_unique_element]) && $index_unique_element != $last_index_unique_element && (!isset($name_whis_dublicates[(string)$a]) || !$name_whis_dublicates[(string)$a] ) ){

                            for($i=0;$i<$count_dublicates;$i++) {

                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = $element_value;

                            }

                        }elseif($write_rows && isset($this->first_row_xls[$index_unique_element]) && ($index_unique_element != $last_index_unique_element || (isset($name_whis_dublicates[(string)$a])) )){

                            if(isset($name_whis_dublicates[(string)$a]) && $name_whis_dublicates[(string)$a] ){
                                
                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = $element_value;
                                
                            }
                            elseif(isset($name_whis_dublicates[(string)$a]) && !$name_whis_dublicates[(string)$a] ){
                                
                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = $element_value;
                                
                                //$dublicate_writes++;
                                
                                
                            }else{
                                
                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = $element_value;
                                
                            }
                            

                        }elseif($write_rows && isset($this->first_row_xls[$index_unique_element]) && $index_unique_element == $last_index_unique_element){

                            $this->rows_xls_elements_for_write_new[$index_unique_element][] = $element_value;
                            
                            //$dublicate_writes++;

                        }
                        
                        //$this->rows_xls_elements_for_end_write_new[$index_unique_element] = $dont_writed;
                        
                        $last_index_unique_element = $index_unique_element;

                    }
                    
                    if($write_rows){
                        
                        
                        $all_dunlicates_on_level = 0;
                        
                        $all_dunlicates_on_level_whis_one_row = 0;
                        
                        foreach($name_whis_dublicates as $name_whis_dublicate_count){
                            
                            $all_dunlicates_on_level += $name_whis_dublicate_count;
                            
                            $all_dunlicates_on_level_whis_one_row += $name_whis_dublicate_count+1;
                            
                        }
                        
                        if($all_dunlicates_on_level_whis_one_row){
                            
                            $all_dunlicates_on_level_whis_one_row - 1;
                            
                        }
                        
                        if( $name_whis_dublicates[(string)$a] && !isset($this->rows_xls_elements_for_end_write_new[$index_unique_element]) ){

                            $this->rows_xls_elements_for_end_write_new[$index_unique_element] = 1;

                        }elseif($name_whis_dublicates[(string)$a] && $this->rows_xls_elements_for_end_write_new[$index_unique_element] == ($name_whis_dublicates[(string)$a])){
                            
                            for($r=0;$r<( ($count_dublicates - ($all_dunlicates_on_level_whis_one_row+1) ) + $all_dunlicates_on_level_whis_one_row-$name_whis_dublicates[(string)$a]   );$r++){
                                
                                $this->rows_xls_elements_for_write_new[$index_unique_element][] = '';
                                
                                if(isset($index_unique)){
                                    
                                    $this->rows_xls_elements_for_write_new[$index_unique][] = '';
                                    
                                }
                                
                            }
                            
                            /*
                             * Добписываются пробелы у атрибутов, котоых нет в принципе в этой ветке, когда повторы в ветке элементов
                             */
                            if(isset($rows_xls_elements_this_attributes_add_spaces) && $rows_xls_elements_this_attributes_add_spaces){
                                
                                foreach ($rows_xls_elements_this_attributes_add_spaces as $attribute_name_this_attributes_space => $tmp) {
                                    
                                    for($r=0;$r<( ($count_dublicates - ($all_dunlicates_on_level_whis_one_row+1) ) + $all_dunlicates_on_level_whis_one_row-$name_whis_dublicates[(string)$a]   );$r++){
                                
                                        $this->rows_xls_elements_for_write_new[$index_attributes.$attribute_name_this_attributes_space][] = '';

                                    }
                                    
                                    
                                }
                                
                            }
                            
                            unset($this->rows_xls_elements_for_end_write_new[$index_unique_element]);
                            
                        }elseif($name_whis_dublicates[(string)$a]){
                            
                            $this->rows_xls_elements_for_end_write_new[$index_unique_element] += 1;
                            
                        }
                        
                        $rows_xls_elements_this = array();
                        
                        $rows_xls_elements_this = $this->rows_xls_elements[$parrent_element_name];
                        
                        foreach ($name_whis_dublicates as $a_this => $tmp) {
                            
                            if(isset($rows_xls_elements_this[$a_this])){
                                
                                unset($rows_xls_elements_this[$a_this]);

                            }
                            
                        }
                        
                        if($rows_xls_elements_this){
                            
                            foreach ($rows_xls_elements_this as $a_this => $tmp){
                                
                                for($r=0;$r<($all_dunlicates_on_level+1);$r++){
                                    
                                    $this->rows_xls_elements_for_write_new[$parrent_element_name.'__'.$a_this][] = '';

                                }
                                
                                $name_whis_dublicates[$a_this] = 0;
                                
                            }
                              
                            
                        }
                        
                        //echo $index_unique_element.'--'.$count_name_whis_dublicates.'-'.$count_dublicates_for_attributes.'-'.$count_dublicates.'-`'.'`-'.$dont_writed.'-'.$dublicate_writes.'-'.$name_whis_dublicates[(string)$a].'-'.(string)$a.'-'.$all_dunlicates_on_level.'<br>';
                    }
                    
                    if(isset($name_whis_dublicates[(string)$a]) && $name_whis_dublicates[(string)$a] ){

                        $dublicate_writes++;

                    }
                    
                    $this->getXMLAttributesNew($b,$parrent_element_name.'__'.$a,$write_rows,$count_dublicates-$count_dublicates_for_attributes,$dublicate_writes,$head_element_write); 
                    
                }
                
            }
            return FALSE;
            
        }
    
    
    public function getXMLDataAsTable($xls,$parrent_element_name='',$dublucate_writed=0,$count_dublicate_from_first_level=0,$count_dublicate_on_this_level=0){
        
        $first_level = FALSE;
        
        if(!$parrent_element_name && $xls){

            $parrent_element_name = $xls->getName();

            $count_dublicate_from_first_level = $this->getCountXMLDuplicateElements($xls);

            $element_value = ltrim(trim((string)$xls));

            $__elements__ = $this->getElementsByColumnName($parrent_element_name);

            $__attributes__ = $this->getAttributesByColumnName($parrent_element_name);
            
            $attributes = $xls->attributes();

            $index_attributes = $parrent_element_name.'-';

            if($attributes){

                $attributes = (array)$attributes;

                foreach($attributes['@attributes'] as $attribute_name => $attribute_value){

                    $attribute_name = (string)$attribute_name;
                    
                    $attribute_value = ltrim(trim((string)$attribute_value));

                    $index_unique = $parrent_element_name.'-'.$attribute_name;

                    unset($__attributes__[$attribute_name]);

                    for($i=0;$i<$count_dublicate_from_first_level;$i++){

                        $this->csv_rows[$index_unique][] = $attribute_value;

                    }

                }

            }
            
            $first_level = TRUE;
                
        }
            
        if($xls){
            
                $__elements__ = $this->getElementsByColumnName($parrent_element_name);
                
                $duplicate_element_names = $this->getDuplicateNames($xls);
                /*
                 * Если нет дублированных на этом уровне или не финальный уровень - меняем количество дубликатов на уровне 
                 */
                
                //$count_dublicate_on_this_level = $this->getCountXMLDuplicateElements($xls);
                
                if($this->xls_specification=='XML_other_2'){
                    
                    if(($xls->count() && !$this->finNode($xls))){
                    
                        $count_dublicate_on_this_level = $this->getCountXMLDuplicateElements($xls);
                    
                    }
                    
                }else{
                    
                    $count_dublicate_on_this_level = $this->getCountXMLDuplicateElements($xls);
                    
                }
                
                //$count_dublicate_on_this_level = $this->getCountXMLDuplicateElements($xls);
                
                //echo '$duplicate_element_names='. json_encode($duplicate_element_names).'<br>';
                
                $write_name = array();
                
                foreach ($xls as $a => $b) {
                    
                    $element_value = ltrim(trim((string)$b));
                    
                    $element_name = ltrim(trim((string)$a));
                    
                    if($first_level){
                    
                        $count_dublicate_from_first_level = $this->getCountXMLDuplicateElements($b);
                        
                        $dublucate_writed = 0;
                    
                    }
                    
                    $count_internal_dublicate = $this->getCountXMLDuplicateElements($b);
                    
                    //echo '$element_name='.$element_name.', $count_dublicate_on_this_level='.$count_dublicate_on_this_level.', $count_dublicate_from_first_level='. $count_dublicate_from_first_level.', $count_internal_dublicate='.$count_internal_dublicate.', $dublucate_writed='.$dublucate_writed.', $duplicate_element_names='.  json_encode($duplicate_element_names).'<br>';
                    
                    $index_unique_element = $parrent_element_name.'__'.$element_name;
                    
                    $__attributes__ = $this->getAttributesByColumnName($index_unique_element);
                    
                    //echo '$__attributes__='.  json_encode($__attributes__).', $element_name='.$element_name.' - <br>';
                    
                    unset($__elements__[$element_name]);
                    
                    /*
                     * Дублированный элемент, с вложенными элементами - без значения - пишется по количество вложенных
                     */
                    if(isset($duplicate_element_names[$element_name]) && $duplicate_element_names[$element_name] && $count_internal_dublicate){
                       
                        for($i=0;$i<$count_internal_dublicate;$i++){

                                //$this->csv_rows[$index_unique_element][] = '___ambient_tag___';
                                $this->csv_rows[$index_unique_element][] = $element_value;

                        }
                        
                        if(!isset($write_name[$element_name])){
                            
                            $write_name[$element_name] = 1;

                        }else{

                            $write_name[$element_name] += 1;

                        }
                        
                    }
                    /*
                     * Дублированный элемент, без вложенных элементов пишется по своему количеству дублей, с двиганием на уже ранее записанные по этому уровню
                     */
                    elseif(isset($duplicate_element_names[$element_name]) && $duplicate_element_names[$element_name] && !$count_internal_dublicate){
                       
                        /*
                         * При первом входе, пишем строчки уже отписанных дублей в других колонках
                         */
                        if(!isset($write_name[$element_name])){
                            
                            for($i=0;$i<$dublucate_writed;$i++){

                                    //$this->csv_rows[$index_unique_element][] = '___before_space___';

                            }
                            
                        }
                        
                        $this->csv_rows[$index_unique_element][] = $element_value;
                        
                        $dublucate_writed += 1;
                        
                        if(!isset($write_name[$element_name])){
                            
                            $write_name[$element_name] = 1;

                        }else{

                            $write_name[$element_name] += 1;

                        }
                        
                        //echo '$element_name='.$element_name.', $write_name[$element_name]='.$write_name[$element_name].', $count_dublicate_on_this_level='.$count_dublicate_on_this_level.'<br>';
                        
                        /*
                         * При последнем, пишем строчки 
                         */
                        if(isset($write_name[$element_name]) && $write_name[$element_name] == $duplicate_element_names[$element_name]){
                            
                            for($i=0;$i<($count_dublicate_on_this_level-$write_name[$element_name]);$i++){

                                    $this->csv_rows[$index_unique_element][] = '';
                                    //$this->csv_rows[$index_unique_element][] = '___after_space___';

                            }
                            
                        }
                        
                    }
                    /*
                     * Не дублированный элемент, без вложенных элементов - какое-то значение - пишется по количеству дублированных по всему уровню, или один раз, если их нет
                     */
                    elseif( (!isset($duplicate_element_names[$element_name]) || !$duplicate_element_names[$element_name]) && !$count_internal_dublicate){
                       
                        //echo '$element_name='.$element_name.'-'.$xls->count().'-'.$count_dublicate_on_this_level.'<br>';
                        
                        if($count_dublicate_on_this_level){
                            
                            for($i=0;$i<$count_dublicate_on_this_level;$i++){

                                $this->csv_rows[$index_unique_element][] = $element_value;

                            }
                            
                            if(!isset($write_name[$element_name])){
                            
                                $write_name[$element_name] = 1;

                            }else{

                                $write_name[$element_name] += 1;

                            }
                            
                            
                        }else{
                            
                            $this->csv_rows[$index_unique_element][] = $element_value;
                            
                            if(!isset($write_name[$element_name])){
                            
                                $write_name[$element_name] = 1;

                            }else{

                                $write_name[$element_name] += 1;

                            }
                            
                        }
                        
                    }
                    
                    /*
                     * Не дублированный элемент, с вложенными элементами - не значение - пишется по количеству дублированных по всему уровню, или один раз, если их нет
                     */
                    elseif( (!isset($duplicate_element_names[$element_name]) || !$duplicate_element_names[$element_name]) && $count_internal_dublicate){
                       
                        //echo '$element_name='.$element_name.'-'.$xls->count().'-'.$count_dublicate_on_this_level.'<br>';
                        
                        for($i=0;$i<$count_dublicate_on_this_level;$i++){

                                $this->csv_rows[$index_unique_element][] = $element_value;
                                //$this->csv_rows[$index_unique_element][] = '___ambient_tag___';

                        }
                        
                        if(!isset($write_name[$element_name])){
                            
                            $write_name[$element_name] = 1;

                        }else{

                            $write_name[$element_name] += 1;

                        }
                        
                    }
                    
                    $index_attributes = $parrent_element_name.'__'.$a.'-';
                    
                    $attributes = $b->attributes();
                    
                    if($attributes){
                        
                        $attributes = (array)$attributes;
                        
                        foreach($attributes['@attributes'] as $attribute_name => $attribute_value){
                            
                            $attribute_name = (string)$attribute_name;
                            
                            $attribute_value = ltrim(trim((string)$attribute_value));
                            
                            $index_unique = $parrent_element_name.'__'.$a.'-'.$attribute_name;
                            
                            unset($__attributes__[$attribute_name]);
                            
                            if(isset($duplicate_element_names[$element_name]) && $duplicate_element_names[$element_name] && $count_internal_dublicate){
                                
                                for($i=0;$i<$count_internal_dublicate;$i++){

                                        $this->csv_rows[$index_unique][] = $attribute_value;

                                }

                                if(!isset($write_name[$index_unique])){

                                    $write_name[$index_unique] = 1;

                                }else{

                                    $write_name[$index_unique] += 1;

                                }
                                
                            }
                            /*
                            * Дублированный элемент, без вложенных элементов пишется по своему количеству дублей, с двиганием на уже ранее записанные по этому уровню
                            */
                            elseif(isset($duplicate_element_names[$element_name]) && $duplicate_element_names[$element_name] && !$count_internal_dublicate){

                               $this->csv_rows[$index_unique][] = $attribute_value;

                               if(!isset($write_name[$index_unique])){

                                   $write_name[$index_unique] = 1;

                               }else{

                                   $write_name[$index_unique] += 1;

                               }

                               /*
                                * При последнем, пишем строчки 
                                */
                               if(isset($write_name[$index_unique]) && $write_name[$index_unique] == $duplicate_element_names[$element_name]){

                                   for($i=0;$i<($count_dublicate_on_this_level-$write_name[$index_unique]);$i++){

                                            //$this->csv_rows[$index_unique][] = $attribute_value;
                                            $this->csv_rows[$index_unique][] = '';
                                            //$this->csv_rows[$index_unique][] = '___after_attr_space___';

                                   }

                               }

                           }
                           
                           /*
                            * Не дублированный элемент, с вложенными элементами - не значение - пишется по количеству дублированных по всему уровню, или один раз, если их нет
                            */
                           elseif( (!isset($duplicate_element_names[$element_name]) || !$duplicate_element_names[$element_name]) && $count_internal_dublicate){

                               //echo '$element_name='.$element_name.'-'.$xls->count().'-'.$count_dublicate_on_this_level.'<br>';

                               for($i=0;$i<$count_dublicate_on_this_level;$i++){

                                       $this->csv_rows[$index_unique][] = $attribute_value;
                                       //$this->csv_rows[$index_unique][] = '___ambient_attr_tag___';

                               }

                               if(!isset($write_name[$index_unique])){

                                   $write_name[$index_unique] = 1;

                               }else{

                                   $write_name[$index_unique] += 1;

                               }

                           }
                           /*
                            * Не дублированный элемент, без вложенных элементов - какое-то значение - пишется по количеству дублированных по всему уровню, или один раз, если их нет
                            */
                           elseif( (!isset($duplicate_element_names[$element_name]) || !$duplicate_element_names[$element_name]) && !$count_internal_dublicate){

                               //echo '$element_name='.$element_name.'-'.$xls->count().'-'.$count_dublicate_on_this_level.'<br>';

                               if($count_dublicate_on_this_level){

                                   for($i=0;$i<$count_dublicate_on_this_level;$i++){

                                       $this->csv_rows[$index_unique][] = $attribute_value;

                                   }

                                   if(!isset($write_name[$index_unique])){

                                       $write_name[$index_unique] = 1;

                                   }else{

                                       $write_name[$index_unique] += 1;

                                   }


                               }else{

                                   $this->csv_rows[$index_unique][] = $attribute_value;

                                   if(!isset($write_name[$index_unique])){

                                       $write_name[$index_unique] = 1;

                                   }else{

                                       $write_name[$index_unique] += 1;

                                   }

                               }

                           }
                           
                        }
                        
                    }
                    //echo json_encode($__attributes__).' - '.$element_name.' ----- ';
                    
                    //cho '$__attributes__='.  json_encode($__attributes__).' - <br>';
                    
                    foreach ($__attributes__ as $attribute_name) {
                        //echo '__no_etalon_attr-2__ <br>';
                        //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-2__'.json_encode($__attributes__).$index_unique_element;
                        $this->csv_rows[$index_attributes.$attribute_name][] = '';
                        
                        if($count_dublicate_on_this_level){
                            
                            if(isset($write_name[$index_attributes.$attribute_name])){

                                    for($i=0;$i<($count_dublicate_on_this_level-$write_name[$index_attributes.$attribute_name]);$i++){

                                            //$this->csv_rows[$index_attributes.$attribute_name][] = '';
                                            //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-4__';

                                    }

                            }else{

                                    for($i=0;$i<($count_dublicate_on_this_level);$i++){

                                            //$this->csv_rows[$index_attributes.$attribute_name][] = '';
                                            //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-3__';

                                    }

                            }

                    }else{

                            //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-2__';
                            //$this->csv_rows[$index_attributes.$attribute_name][] = '';

                    }
                        
                    
                    /*

                    if(isset($duplicate_element_names[$element_name]) && $duplicate_element_names[$element_name]){

                        for($i=0;$i< $duplicate_element_names[$element_name];$i++){

                            //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-1__';

                        }

                    }else{

                        //$this->csv_rows[$index_attributes.$attribute_name][] = '__no_etalon_attr-2__';

                    }

                    */
                        

                    }
                    
                    $this->getXMLDataAsTable($b,$parrent_element_name.'__'.$a,$dublucate_writed,$count_dublicate_from_first_level,$count_dublicate_on_this_level); 
                    
                }
                
                //echo json_encode($__elements__).'<br>';
                
                if($__elements__){
                    
                    foreach ($__elements__ as $element_name_key => $tmp) {
                        
                        if($count_dublicate_on_this_level){
                            
                            $__attributes__ = $this->getAttributesByColumnName($parrent_element_name.'__'.$element_name_key);

                            for($i=0;$i<$count_dublicate_on_this_level;$i++){

                                //$this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '__no_etalon_element-1__';
                                $this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '';
                                
                                foreach ($__attributes__ as $attribute_name_this => $tmp) {
                                    
                                    //$this->csv_rows[$parrent_element_name.'__'.$element_name_key.'-'.$attribute_name_this][] = '__no_etalon_attr-3__';
                                    $this->csv_rows[$parrent_element_name.'__'.$element_name_key.'-'.$attribute_name_this][] = '';
                                    
                                }
                                //$this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '';

                            }

                        }else{
                            
                            $__attributes__ = $this->getAttributesByColumnName($parrent_element_name.'__'.$element_name_key);
                            
                            //echo '$element_name_key='.$element_name_key.'<br>';
                            //echo '$element_name_key='.  json_encode($__attributes__).'<br><br>';
                            
                            $this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '';
                            //$this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '__no_etalon_element-2__';
                            
                            foreach ($__attributes__ as $attribute_name_this => $tmp) {
                                    //echo '$element_name_key='.$element_name_key.$attribute_name_this.'<br>';
                                //echo '$parrent_element_name.__.$element_name_key='.$parrent_element_name.'__'.$element_name_key.'-'.$attribute_name_this.'-'.'<br>';
                                //$this->csv_rows[$parrent_element_name.'__'.$element_name_key.'-'.$attribute_name_this][] = '__no_etalon_attr-4__';
                                $this->csv_rows[$parrent_element_name.'__'.$element_name_key.'-'.$attribute_name_this][] = '';

                            }
                            //$this->csv_rows[$parrent_element_name.'__'.$element_name_key][] = '';

                        }
                        
                    }

                }
                
            }
            
        return FALSE;
            
    }
        
    public function getElementsByColumnName($column_name){
        
        $element_whis_attribute_name = explode('-',$column_name);
                        
        $array = explode('__',$element_whis_attribute_name[0]);
        
        $elements = array();
        
        if(isset($array[0])){
                
            $elements = $this->xls_as_array[$array[0]]['___elements___'];

        }

        if(isset($array[1])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'];

        }
        
        if(isset($array[2])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'];

        }
        
        if(isset($array[3])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'];

        }
        
        if(isset($array[4])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'];

        }
        
        if(isset($array[5])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'];

        }
        
        if(isset($array[6])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'];

        }
        
        if(isset($array[7])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'];

        }
        
        if(isset($array[8])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___elements___'];

        }
        
        if(isset($array[9])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___elements___'][$array[9]]['___elements___'];

        }
        
        if(isset($array[10])){

            $elements = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___elements___'][$array[9]]['___elements___'][$array[10]]['___elements___'];

        }
        
        return $elements;
        
    }
    
    public function getAttributesByColumnName($column_name){
        
        $element_whis_attribute_name = explode('-',$column_name);
                        
        $array = explode('__',$element_whis_attribute_name[0]);
        
        $attributes = array();
        
        if(isset($array[0])){
                
            $attributes = $this->xls_as_array[$array[0]]['___attributes___'];

        }

        if(isset($array[1])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___attributes___'];

        }
        
        if(isset($array[2])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___attributes___'];

        }
        
        if(isset($array[3])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___attributes___'];

        }
        
        if(isset($array[4])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___attributes___'];

        }
        
        if(isset($array[5])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___attributes___'];

        }
        
        if(isset($array[6])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___attributes___'];

        }
        
        if(isset($array[7])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___attributes___'];

        }
        
        if(isset($array[8])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___attributes___'];

        }
        
        if(isset($array[9])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___elements___'][$array[9]]['___attributes___'];

        }
        
        if(isset($array[10])){

            $attributes = $this->xls_as_array[$array[0]]['___elements___'][$array[1]]['___elements___'][$array[2]]['___elements___'][$array[3]]['___elements___'][$array[4]]['___elements___'][$array[5]]['___elements___'][$array[6]]['___elements___'][$array[7]]['___elements___'][$array[8]]['___elements___'][$array[9]]['___elements___'][$array[10]]['___attributes___'];

        }
        
        return $attributes;
        
    }
    
    public function getDuplicateNames($xls){
            
        $dublicate_names = array();

        if($xls){

            foreach ($xls as $name => $element) {

                $name = (string)$name;

                if(!isset($dublicate_names[$name])){

                    $dublicate_names[$name] = 0;

                }elseif(isset($dublicate_names[$name])){

                    /*
                     * Считает дублированных включая, первый
                     */
                    if(!$dublicate_names[$name]){
                        
                        $dublicate_names[$name] += 1;
                        
                    }
                    
                    $dublicate_names[$name] += 1;

                }

            }

        }

        return $dublicate_names;

    }
    
    public function getDuplicateOnLevel($xls){
            
        $dublicate_names = FALSE;

        if($xls){

            foreach ($xls as $name => $element) {

                $name = (string)$name;

                if(!isset($dublicate_names[$name])){

                    

                }elseif(isset($dublicate_names[$name])){

                    $dublicate_names = FALSE;

                }

            }

        }

        return $dublicate_names;

    }
        
    public function finNode($xls){
        
        $result = TRUE;
        
        $names = array();
        
        foreach ($xls as $name => $element) {
            
            $names[$name] = $name;
            
            if($element->count()){
                
                $result = FALSE;
                
            }
            
        }
        
        return $result;
        
    }
    
    public function getCountXMLDuplicateElements($xls){
        
            $count = 0;
        
            if($xls){
                
                $last_name = '';
                
                $writed_dublicate = array();
                
                foreach ($xls as $name => $element) {
                    
                    $name = (string)$name;
                    
                    if(!$element->count()){
                        
                        if($name===$last_name){
                            
                            $count += 1;
                            
                            if(!isset($writed_dublicate[$name])){
                            
                                $count += 1;

                            }
                            
                            $writed_dublicate[$name] = TRUE;
                            
                        }
                        
                    }
                    
                    $last_name = $name;
                    
                    $count += $this->getCountXMLDuplicateElements($element);
                    
                }
                
            }
            
            return $count;
            
        }
   
        
}
?>