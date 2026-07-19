<?php
class ControllerExtensionModuleOCNCallback extends Controller {
	private $errors = [];
	
	public function index() {
		$this->document->addStyle('catalog/view/style/ocn/ocn_callback.css');
		$this->document->addScript('catalog/view/javascript/ocn/ocn_callback.js');
		
		$this->load->language('extension/module/ocn_callback/ocn_callback');
		
		$data['view_modal'] = $this->getViewModal();
		$data['view_alert'] = $this->getViewAlert();

		return $this->load->view('extension/module/ocn_callback/ocn_callback', $data);
	}

	public function store() {
		$this->load->language('extension/module/ocn_callback/ocn_callback');
		$this->load->model('extension/module/ocn_callback');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$callbackData = [
				'store_id' => $this->config->get('config_store_id'),
				'language_id' => $this->config->get('config_language_id'),
				'url' => htmlspecialchars($this->request->post['url'], ENT_QUOTES),
				'name' => htmlspecialchars($this->request->post['name'], ENT_QUOTES),
				'email' => '',//htmlspecialchars($this->request->post['email'], ENT_QUOTES),
				'phone' => htmlspecialchars($this->request->post['phone'], ENT_QUOTES),
				'message' => ''//htmlspecialchars($this->request->post['message'], ENT_QUOTES)
			];
			
			$json['callback_id'] = $this->model_extension_module_ocn_callback->addCallback($callbackData);
			$json['status'] = 'success';

			$message = 'Обратный звонок'."\n";
			$message .= $this->request->post['name'] . "\n";
			$message .= $this->request->post['phone'] . "\n";

			$this->sendMessagetoTelegam($message);

		} else {
			$json['status'] = 'error';
			$json['errors'] = $this->errors;
		}
		
		$this->response->addHeader('Content-Type: application/json; charset=utf-8');
		$this->response->setOutput(json_encode($json));
	}
	
	private function getViewModal()
	{
		$this->load->language('extension/module/ocn_callback/ocn_callback_modal');
		$data['url_store'] = $this->url->link('extension/module/ocn_callback/store', '', true);
		
		return $this->load->view('extension/module/ocn_callback/ocn_callback_modal', $data);
	}
	
	private function getViewAlert()
	{
		$this->load->language('extension/module/ocn_callback/ocn_callback_alert');
		
		return $this->load->view('extension/module/ocn_callback/ocn_callback_alert');
	}
	
	private function validateForm() {
		// if (!isset($this->request->post['phone']) || (isset($this->request->post['phone']) && utf8_strlen($this->request->post['phone']) == 0)) {
		// 	$this->errors['phone'] = $this->language->get('error_phone');
		// }
		if (!isset($this->request->post['phone'])) {
		 	$this->errors['phone'] = $this->language->get('error_phone');
		}
		
		// if ( !isset($this->request->post['phone']) || ( isset($this->request->post['phone']) && strripos($this->request->post['phone'], '_') )   ) {
		//  	$this->errors['phone'] = $this->language->get('error_phone');
		// } 

		
		if (!isset($this->request->post['name']) || (isset($this->request->post['name']) && utf8_strlen($this->request->post['name']) == 0)) {
			$this->errors['name'] = $this->language->get('error_name');
		}
		
		if ($this->errors && !isset($this->errors['warning'])) {
			$this->errors['warning'] = $this->language->get('error_warning');
		}
		
		return !$this->errors;
	}

		//Send  message To notificationTelegram
	public function sendMessagetoTelegam($msg) {
		
		$this->load->model('setting/setting');
		$setting = $this->model_setting_setting->getSetting('module_notificationTelegram');
		
		//print_r($setting);
		$botToken = $setting['module_notificationTelegram_boot_token'];
		$website = "https://api.telegram.org/bot" . $botToken;
		$chatIds = $setting['module_notificationTelegram_chat_ids'];  //Receiver Chat Id
		
		if (is_array($chatIds)) {
			foreach ($chatIds as $val) {
				$this->initMessage($botToken, $val, $msg);
			}
		} else {
			$this->initMessage($botToken, $chatIds, $msg);
		}
	}
	private function initMessage($botToken, $chatID, $msg) {
		
		$website = "https://api.telegram.org/bot" . $botToken;
		
		$params = [
			'chat_id' => $chatID,
			'text' => $msg,
		];
		$ch = curl_init($website . '/sendMessage');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($ch);
		curl_close($ch);            
	}


	
}
