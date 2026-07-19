<?php

class ControllerExtensionModuleFormcreator extends Controller{


	public function index($setting) {


		$this->load->language('extension/module/formcreator');



		$data['button_text'] = $this->language->get('button_text');

		$data['button_send'] = $this->language->get('button_send');

		

		$data['entry_name'] = $this->language->get('entry_name');

		$data['entry_phone'] = $this->language->get('entry_phone');

		$data['entry_email'] = $this->language->get('entry_email');

		$data['enntry_text'] = $this->language->get('enntry_text');

		

		if (isset($setting['module_id'])){

			$data['module_id'] = $setting['module_id'];

			$this->form_id = $setting['module_id'];

		}

		

		$fields = $setting['formcreator_field'];

		//$fields = $this->config->get('formcreator_field');



			if ($setting['form_name'][$this->config->get('config_language_id')]){

				$data['module_name'] = $setting['form_name'][$this->config->get('config_language_id')];

			} else {

				$data['module_name'] = $setting['name'];

			}	



			if (isset($setting['formcreator_modal'])) {

				$data['button_name'] = $setting['modal_button'][$this->config->get('config_language_id')];

			}



			$this->session->data['formcreator_email'] = $setting['formcreator_email'];



			if ($setting['form_success'][$this->config->get('config_language_id')]){

				$data['form_success'] = $setting['form_success'][$this->config->get('config_language_id')];

			} else {

				$data['form_success'] = $this->language->get('text_succes');

			}



		if (isset($fields)) {	

			foreach ($fields as $field) {

				if (!isset($field['field_status'])) {

					$field['field_status'] = 0;

				}

				if (!isset($field['required'])) {

					$field['required'] = 0;

				}

				if (!isset($field['option'])) {

					$field['option'][$this->config->get('config_language_id')] = 0;

				} else {

					$field['option'][$this->config->get('config_language_id')] = explode(':', $field['option'][$this->config->get('config_language_id')]);

				}

				$data['fields'][] = array(

					'name' => $field['name'][$this->config->get('config_language_id')],

					'type' => $field['type'],

					'field_status' => $field['field_status'],

					'required' => $field['required'],

					'option' => $field['option'][$this->config->get('config_language_id')],

				);

			}

		}

		$data['mytitle'] = $this->document->getTitle();

		$data['domain'] = $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] . "\n" ;

		if (isset($setting['formcreator_modal'])) {

				return $this->load->view('extension/module/formcreator_modal', $data);

		} else {

				return $this->load->view('extension/module/formcreator', $data);

		}

	}



	public function send() {

		

		$this->language->load('extension/module/formcreator');

		

		$json = array(); 

	

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {

		

			$feedback_options = array(

				'module_name' => $this->request->post['module_name'],

				'module_id' => $this->request->post['module_id'],

				'page_link'   =>$this->request->post['link_page'],  

				);

			$form_success = $this->request->post['form_success'];



			$json = $this->validate($this->request->post);
	    	

			if (!isset($json['error'])) {

			$json = $this->request->post;

			foreach ($json['form_input'] as $key_fields => $fields) {

				$json['form_input'][$key_fields] = array();



				foreach ($fields as $key_text => $text) {
					
					$key_text = str_replace("'", "&#039;", $key_text);

					$text = str_replace("'", "&#039;", $text);

					$json['form_input'][$key_fields][$key_text] = $text;

				}

			}



			$this->load->model('extension/module/formcreator');

			$this->model_extension_module_formcreator->addFeedback($json, $feedback_options);

						

				$link_page = $this->request->post['link_page']. "\n\n" ;



				$domain = $_SERVER['SERVER_NAME'];			

				$domain = str_replace( 'http://', '', $domain );			

				$domain = str_replace( 'www.', '', $domain );



				$mail = new Mail($this->config->get('config_mail_engine'));

				$mail->parameter = $this->config->get('config_mail_parameter');

				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');

				$mail->smtp_username = $this->config->get('config_mail_smtp_username');

				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');

				$mail->smtp_port = $this->config->get('config_mail_smtp_port');

				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');



				if ($this->session->data['formcreator_email']) {	

					$mail->setTo($this->session->data['formcreator_email']);

				} else {

					$mail->setTo($this->config->get('config_email'));

				}

			  	//$mail->setFrom($this->config->get('config_email'));

			  	$mail->setFrom('info@'.$domain);

			  	$mail->setSender($this->config->get('config_name'));

			  	$mail->setSubject(html_entity_decode(sprintf($this->language->get('entry_email'), 'NAMEString', ENT_QUOTES, 'UTF-8')));

			  	

				$form = $json['form_input'];

						foreach ($form as $form_box) {

							foreach ($form_box as $name => $value) {

								if ($name != 'required') {

									if (!isset($forms_tomail)) {

										if (is_array($value)){

											$forms_tomail = $name . ': '. implode(", ",$value) . "\n\n";

										} else {

											$forms_tomail = $name . ': '. $value . "\n\n";

										}

									} else {

										if (is_array($value)){

											$forms_tomail .= $name . ': '. implode(", ",$value) . "\n\n";

										} else {

											$forms_tomail .= $name . ': '. $value . "\n\n";

										}

									}

								}

							}

						}

                $title =$this->request->post['mytitle'];// $this->document->getTitle();

			  	$mail->setText(

			  		 html_entity_decode($forms_tomail)

			  		. html_entity_decode($this->language->get('text_send') . ' '.$link_page, ENT_QUOTES, 'UTF-8' )//message_link

			  		. html_entity_decode($title)

			  		);

		      	$mail->send();

$message_telegramm = html_entity_decode($forms_tomail) . html_entity_decode($this->language->get('text_send') . ' '.$link_page, ENT_QUOTES, 'UTF-8' ) . html_entity_decode($title);
$this->load->model('extension/module/notificationTelegram');
$this->model_extension_module_notificationTelegram->sendMessagetoTelegam($message_telegramm);

		      	//Send to additional alert emails

				$emails = explode(',', $this->config->get('config_mail_alert_email'));

				foreach ($emails as $email) {

					if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {

						$mail->setTo($email);

						$mail->send();

					}

				}



				$json['success'] = $form_success;

			}	





		}



		$this->response->setOutput(json_encode($json));



	}





	public function validate($results){

		$this->language->load('module/formcreator');

		$text_error_send = $this->language->get('error_send');

		$out = array();



			$form = $results['form_input'];

    		foreach ($form as $form_box) {

    			if ( isset($form_box['required']) && $form_box['required'] == 'input') {

	    			foreach ($form_box as $name => $value) {

						if ($name != 'required'){

	    					if ((utf8_strlen($value) < 3) || (utf8_strlen($value) > 32)) {

	    						$out['error'] = $text_error_send;

	    					}

	    				}

	    			}

    			}

    		}



    		$form = $results['form_input'];

    		foreach ($form as $form_box) {

    			if ( isset($form_box['required']) && $form_box['required'] == 'textarea') {

	    			foreach ($form_box as $name => $value) {

	    				

						if ($name != 'required'){

	    					if ((utf8_strlen($value) < 5) || (utf8_strlen($value) > 200)) {

	    						$out['error'] = $text_error_send;

	    					}

	    				}

    				}

    			}

    		}



    		$form = $results['form_input'];

    		foreach ($form as $form_box) {

    			if ( isset($form_box['required']) && $form_box['required'] == 'radio') {

					if (count($form_box) == 1){

    						$out['error'] = $text_error_send;

    				}

    			}

    		}



    		$form = $results['form_input'];

    		foreach ($form as $form_box) {

    			if ( isset($form_box['required']) && $form_box['required'] == 'checkbox') {

					if (count($form_box) == 1){

    						$out['error'] = $text_error_send;

    				}

    			}

    		}



    	return $out;

	}

}

