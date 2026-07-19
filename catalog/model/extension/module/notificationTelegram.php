<?php
class ModelExtensionModuleNotificationTelegram extends Model {
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
			'parse_mode' => 'HTML',
			'disable_web_page_preview'=> true
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
?>