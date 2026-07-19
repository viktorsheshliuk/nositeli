<?php
require_once('config.php');
ini_set('log_errors', 'On');
ini_set('error_log', DIR_SYSTEM.'library/vendor/ocext/cache/php_errors.log');
date_default_timezone_set('UTC');
if(isset($_GET['exchange_link_token'])){
    
    //$exchange_link_token = trim(htmlspecialchars($_GET['exchange_link_token'], ENT_COMPAT, 'UTF-8'));
    
    $exchange_link_token = '';
    
    require_once(DIR_SYSTEM . 'library/vendor/ocext/anydsvxls_setting_version.php');
  
    $exchange = new anyDSVXLSSettingVersion(array(),  '', '','');
    
    $exchange->smartExchangeTask($exchange_link_token);
    
}


