<?php 
/*
 * @copyright Copyright (c) 2011 Shilovsky Andrej, Dmitrij (an911@ukr.net)
 */
class ControllerExtensionPaymentPrivat24 extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('extension/payment/privat24');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
                      
			$this->model_setting_setting->editSetting('payment_privat24', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_currency'] = $this->language->get('text_currency');
                
		$data['entry_currency'] = $this->language->get('entry_currency');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		
		$data['help_total'] = $this->language->get('help_total');
                
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');


 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) { 
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}
		
		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}
		
		if (isset($this->error['currency'])) {
			$data['error_currency'] = $this->error['currency'];
		} else {
			$data['error_currency'] = '';
		}
				
		if (isset($this->error['type'])) { 
			$data['error_type'] = $this->error['type'];
		} else {
			$data['error_type'] = '';
		}

		$data['breadcrumbs'] = array();
                
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/privat24', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
                		

		$data['action'] = $this->url->link('extension/payment/privat24', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL');
     
		if (isset($this->request->post['payment_privat24_merchant'])) {
			$data['privat24_merchant'] = $this->request->post['payment_privat24_merchant'];
		} else {
			$data['privat24_merchant'] = $this->config->get('payment_privat24_merchant');
		}

		if (isset($this->request->post['payment_privat24_password'])) {
			$data['privat24_password'] = $this->request->post['payment_privat24_password'];
		} else {
			$data['privat24_password'] = $this->config->get('payment_privat24_password');
		}

		if (isset($this->request->post['payment_privat24_currency'])) {
			$data['privat24_currency'] = $this->request->post['payment_privat24_currency'];
		} else {
			$data['privat24_currency'] = $this->config->get('payment_privat24_currency');
		}

		if (isset($this->request->post['payment_privat24_total'])) {
			$data['privat24_total'] = $this->request->post['payment_privat24_total'];
		} else {
			$data['privat24_total'] = $this->config->get('payment_privat24_total');
		}
				
		if (isset($this->request->post['payment_privat24_order_status_id'])) {
			$data['privat24_order_status_id'] = $this->request->post['payment_privat24_order_status_id'];
		} else {
			$data['privat24_order_status_id'] = $this->config->get('payment_privat24_order_status_id');
		} 

		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_privat24_geo_zone_id'])) {
			$data['privat24_geo_zone_id'] = $this->request->post['payment_privat24_geo_zone_id'];
		} else {
			$data['privat24_geo_zone_id'] = $this->config->get('payment_privat24_geo_zone_id');
		} 		
		
		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_privat24_status'])) {
			$data['privat24_status'] = $this->request->post['payment_privat24_status'];
		} else {
			$data['privat24_status'] = $this->config->get('payment_privat24_status');
		}

		
		if (isset($this->request->post['payment_privat24_sort_order'])) {
			$data['privat24_sort_order'] = $this->request->post['payment_privat24_sort_order'];
		} else {
			$data['privat24_sort_order'] = $this->config->get('payment_privat24_sort_order');
		}
		

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/privat24', $data));
	}

        
        
	private function validate() {
             $this->load->model('localisation/currency'); 
             
		if (!$this->user->hasPermission('modify', 'extension/payment/privat24')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['payment_privat24_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payment_privat24_password']) {
			$this->error['password'] = $this->language->get('error_password');
		}

               $currencies = array();
		$results = $this->model_localisation_currency->getCurrencies();	
                  	
		foreach ($results as $result) {
			if ($result['status']) {
   				$currencies[] = $result['code'];
			}
		}
                        
		if (!in_array((string)$this->request->post['payment_privat24_currency'], $currencies)) { 
			$this->error['currency'] = $this->language->get('error_currency_not');
		}
                
		if ($this->request->post['privat24_currency']=='0') {
			$this->error['currency'] = $this->language->get('error_currency');
		}

					
		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}	
	}
}
?>