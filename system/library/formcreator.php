<?php
class Formcreator {
	private $registry;
	public function __construct($registry) {
		$this->registry = $registry;
	}
	public function __get($name) {
		return $this->registry->get($name);
	}	
	public function initFeedback($feedback_id){
		$this->load->model('setting/module');
		$setting_info = $this->model_setting_module->getModule($feedback_id); 
		if (isset($setting_info['status']) && $setting_info['status'] && isset($setting_info['custom_position'])){
			$form = $this->load->controller('extension/module/formcreator', $setting_info);
		} else {
			$form = '';			
		}
		return $form;
	}
}