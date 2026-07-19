<?php

class ControllerExtensionModuleFormcreator extends Controller {
	private $error = array();

	public function index(){
		$this->load->language('extension/module/formcreator');

		$this->document->setTitle($this->language->get('page_title'));

		$this->load->model('setting/setting');

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
	
			if (isset($this->request->get['module_id'])){
				$this->request->post['module_id'] = $this->request->get['module_id'];
			}

			if ($this->validateForm($this->request->post)) {
			
				if (!isset($this->request->get['module_id'])) {
					$this->model_setting_module->addModule('formcreator', $this->request->post);
					$last_id = $this->db->query("SELECT MAX(module_id) as module_id FROM `" . DB_PREFIX . "module`");
					$this->request->post['module_id'] = $last_id->row['module_id'];
					$this->model_setting_module->editModule($last_id->row['module_id'], $this->request->post);
				} else {
					$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
				}

				$this->model_setting_setting->editSetting('formcreator', $this->request->post);
				$this->session->data['success'] = $this->language->get('text_success');
				$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
				
			}
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('extension/module/formcreator');
		$fedbacks = $this->model_extension_module_formcreator->getFeedbackAll();

		if (isset($this->error['error_module'])) {
			$data['error_warning'] = $this->error['error_module'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('page_title'),
				'href' => $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('page_title'),
				'href' => $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}
	
		$data['feedback_list'] = $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->get['module_id'])) {
			$data['module_id'] = $this->request->get['module_id'];
		} else {
			$data['module_id'] = '';
		}

		if (isset($this->request->post['formcreator_modal'])) {
			$data['formcreator_modal'] = $this->request->post['formcreator_modal'];
		} elseif (isset($module_info['formcreator_modal'])) {
			$data['formcreator_modal'] = $module_info['formcreator_modal'];
		} else {
			$data['formcreator_modal'] = '';
		}

		if (isset($this->request->post['form_name'])) {
			$data['form_name'] = $this->request->post['form_name'];
		} elseif (isset($module_info['form_name'])) {
			$data['form_name'] = $module_info['form_name'];
		} else {
			$data['form_name'] = '';
		}

		if (isset($this->request->post['form_success'])) {
			$data['form_success'] = $this->request->post['form_success'];
		} elseif (isset($module_info['form_success'])) {
			$data['form_success'] = $module_info['form_success'];
		} else {
			$data['form_success'] = '';
		}

		if (isset($this->request->post['modal_button'])) {
			$data['modal_button'] = $this->request->post['modal_button'];
		} elseif (isset($module_info['modal_button'])) {
			$data['modal_button'] = $module_info['modal_button'];
		} else {
			$data['modal_button'] = '';
		}

		if (isset($this->request->post['custom_position'])) {
			$data['custom_position'] = $this->request->post['custom_position'];
		} elseif (isset($module_info['custom_position'])) {
			$data['custom_position'] = $module_info['custom_position'];
		} else {
			$data['custom_position'] = '';
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}

		if (isset($this->request->post['formcreator_email'])) {
			$data['formcreator_email'] = $this->request->post['formcreator_email'];
		} elseif (isset($module_info['formcreator_email'])) {
			$data['formcreator_email'] = $module_info['formcreator_email'];
		} else {
			$data['formcreator_email'] = '';
		}

		$data['fields'] = array();

		if (isset($this->request->post['formcreator_field'])) {
			$data['fields'] = $this->request->post['formcreator_field'];
		} elseif (!empty($module_info['formcreator_field'])) {  
			$data['fields'] = $module_info['formcreator_field'];
		} else {
			$data['fields'] = '';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/formcreator', $data));

	}


	public function getList() {
		$this->load->language('extension/module/formcreator');
		$this->load->model('extension/module/formcreator');

		$this->document->setTitle($this->language->get('page_title'));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}
		if (isset($this->request->get['filter_text'])) {
			$filter_text = $this->request->get['filter_text'];
		} else {
			$filter_text = null;
		}
		if (isset($this->request->get['filter_noread'])) {
			$filter_noread = $this->request->get['filter_noread'];
		} else {
			$filter_noread = null;
		}
	
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date';
		}
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
	
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_noread'])) {
			$url .= '&filter_noread=' . urlencode(html_entity_decode($this->request->get['filter_noread'], ENT_QUOTES, 'UTF-8'));
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token']. '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('page_title'),
			'href' => $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
		$data['add_module'] = $this->url->link('extension/module/formcreator', 'user_token=' . $this->session->data['user_token'], true);

		$data['deleteFeedback'] = $this->url->link('extension/module/formcreator/deleteFeedback', 'user_token=' . $this->session->data['user_token'], true);
		$data['saveFeedback'] = $this->url->link('extension/module/formcreator/saveFeedback', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		$filter_data = array(
			'filter_name'   => $filter_name,
			'filter_text'   => $filter_text,
			'filter_noread'   => $filter_noread,
			'sort' 			=> $sort,
			'order'			=> $order,
			'start' 		=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' 		=> $this->config->get('config_limit_admin')
		);
		
		$feedbacks = $this->model_extension_module_formcreator->getFeedbackAll($filter_data);
		$feedback_total = $this->model_extension_module_formcreator->getTotalFeedbacks($filter_data);

		$module_options = $this->model_extension_module_formcreator->getFeedbackOptions('');
		$data['module_names'] = $this->model_extension_module_formcreator->getFeedbackOptions('module_name');

		foreach ($module_options as $module_option) {
			$data['feedback_module_options'][] = array(
				'module_id'   => $module_option['module_id'],
				'module_name' => $module_option['name'],
				'module_url'  => $this->url->link('extension/module/formcreator&module_id='.$module_option['module_id'], 'user_token=' . $this->session->data['user_token'], true),
			);
		}

		foreach ($feedbacks as $feedback ) {
				if ($feedback['status'] == 'noread'){
					$status = 'Не прочитан';
				} else {
					$status = 'Прочитан';
				}
				$data['feedbacks'][] = array(
				'fedback_id' 		=> $feedback['fedback_id'],
				'date' 				=> $feedback['date'],
				'module_name' 		=> $feedback['module_name'],
				'page_link'	   	    => $feedback['page_link'],
				'status' 		  	=> $status,
				'feedback_array'	=> unserialize($feedback['feedback_array']),
			);	
		}


		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_noread'])) {
			$url .= '&filter_noread=' . urlencode(html_entity_decode($this->request->get['filter_noread'], ENT_QUOTES, 'UTF-8'));
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . '&sort=module_name' . $url, true);
		$data['sort_date'] = $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . '&sort=date' . $url, true);
		$data['sort_id'] = $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . '&sort=fedback_id' . $url, true);

		$url = '';

	
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . urlencode(html_entity_decode($this->request->get['filter_text'], ENT_QUOTES, 'UTF-8'));
		}
		if (isset($this->request->get['filter_noread'])) {
			$url .= '&filter_noread=' . urlencode(html_entity_decode($this->request->get['filter_noread'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['path'])) {
			$url .= '&path=' . $this->request->get['path'];
		}

		$pagination = new Pagination();
		$pagination->total = $feedback_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['filter_name'] = $filter_name;
		$data['filter_text'] = $filter_text;
		$data['filter_noread'] = $filter_noread;
		
		$data['results'] = sprintf($this->language->get('text_pagination'), ($feedback_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($feedback_total - $this->config->get('config_limit_admin'))) ? $feedback_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $feedback_total, ceil($feedback_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/formcreator_list', $data));
		
	}

	public function deleteFeedback(){
		$url = '';
		$this->load->model('extension/module/formcreator');

		if ($this->request->post['selected']){

			foreach ($this->request->post['selected'] as $feedback_id) {
				$this->model_extension_module_formcreator->deleteFeedback($feedback_id);
			}
		
			$this->response->redirect($this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
		
		

	}

	public function saveFeedback(){
		$url = '';
		$this->load->model('extension/module/formcreator');

		if ($this->request->post){
			$this->model_extension_module_formcreator->saveFeedback($this->request->post);
			$this->response->redirect($this->url->link('extension/module/formcreator/getlist', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}
	}

	public function validateForm($feedback_forms){
		$this->load->model('localisation/language');

		if (utf8_strlen($feedback_forms['name']) < 2 || utf8_strlen($feedback_forms['name']) > 50){
			$this->error['error_module'] = $this->language->get('text_error_name_module');
		}

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			$language_id = $language['language_id'];
				if (isset($feedback_forms['formcreator_field'])){
					$feedback_forms = $feedback_forms['formcreator_field'];
					foreach ($feedback_forms as $feedback_form) {
					
						if (utf8_strlen($feedback_form['name'][$language_id]) < 2 || utf8_strlen($feedback_form['name'][$language_id]) > 50) {
							$this->error['error_module'] = $this->language->get('text_error_field_name');
						}

						if (isset($feedback_form['option'])) {
							if (utf8_strlen($feedback_form['option'][$language_id]) < 1 || utf8_strlen($feedback_form['option'][$language_id]) > 200) {
								$this->error['error_module'] = $this->language->get('text_error_field_option');
								
							}	
							
						}
					
					}	
				}	
			}
		return !$this->error;
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/formcreator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function getAll(){
		
	}

	public function delete() {
		
	}
	
	public function install(){
		$this->load->model('extension/module/formcreator');
		$this->model_extension_module_formcreator->install();
	}


}