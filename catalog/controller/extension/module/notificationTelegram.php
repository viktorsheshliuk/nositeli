<?php
class ControllerExtensionModuleNotificationTelegram extends Controller {
        
	public function sendOrderAlert(&$route, &$data, &$output) {

		$order_id = $data[0];
		$this->load->model('checkout/order');
		$this->load->model('extension/module/notificationTelegram');
		$order_info = $this->model_checkout_order->getOrder($order_id);

		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');

		if (isset($setting['module_notificationTelegram_order_alert'])) {
			
			$this->load->model('account/order');
			if (count($this->model_account_order->getOrderHistories($order_id)) <= 1) {
				$message = $this->replaceMessage($setting['module_notificationTelegram_meassage'],$order_info) . "\n";
				$order_products = $this->model_checkout_order->getOrderProducts($order_id);
				$products = $this->bulidProducts($order_products);
				$message .= $products;
				$this->model_extension_module_notificationTelegram->sendMessagetoTelegam($message);
			}
			
		}
		
	}
	
	public function sendAccountAlert(&$route, &$data, &$output) {
		$this->load->model('setting/setting');
		$this->load->model('extension/module/notificationTelegram');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		if (isset($setting['module_notificationTelegram_customer_alert'])) {

			$message = $this->replaceMessage($setting['module_notificationTelegram_new_account_meassage'],$data[0]);
			$this->model_extension_module_notificationTelegram->sendMessagetoTelegam($message);
			
		}
	}
	
	public function sendReturnProductAlert(&$data,&$output) {
		$this->load->model('setting/setting');
		$this->load->model('extension/module/notificationTelegram');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		if (isset($setting['module_notificationTelegram_return_alert'])) {
			
			$message = "Return request \n ";
			$this->model_extension_module_notificationTelegram->sendMessagetoTelegam($message);
		}
	}
	
	
	
	
	
	public function buldArray($arr) {
		if (is_array($arr)) {
			$dataAttributes = array_map(function ($value, $key) {
				return @"$key ---> $value  \n";
			}, array_values($arr), array_keys($arr));
			
			return $dataAttributes = implode(' ', $dataAttributes);
		}
	}
	
	public function replaceMessage($string,$arr) {
		return   $str = preg_replace_callback('/{(\w+)}/', function($match) use($arr) {
			return $arr[$match[1]];
		}, $string );
		
	}
	
	protected function  bulidProducts($products){
		$this->load->model('catalog/product');
		$pr = array();
		$i=1;
		foreach ($products as $product){
			$product_info = $this->model_catalog_product->getProduct($product['product_id']);

			$ostatok = $product_info['quantity'] + $product['quantity'];
			$product_href = HTTPS_SERVER . 'index.php?route=product/product&product_id=' .  $product['product_id'];  

			$pr[] = "$i. <a href='$product_href'>$product[name]</a>\nЦена: $product[price]\nКол-во: $product[quantity] c $ostatok";

			$i++;
		}

		return implode(" \n",$pr);
		
	}
		
}