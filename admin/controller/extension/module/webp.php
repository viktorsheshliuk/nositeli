<?php
class ControllerExtensionModuleWebp extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/webp');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('module_webp', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/webp', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/webp', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['module_webp_quality'])) {
			$data['module_webp_quality'] = $this->request->post['module_webp_quality'];
		} elseif($this->config->get('module_webp_quality')) {
			$data['module_webp_quality'] = $this->config->get('module_webp_quality');
		} else {
			$data['module_webp_quality'] = '80';
		}

		if (isset($this->request->post['module_webp_status'])) {
			$data['module_webp_status'] = $this->request->post['module_webp_status'];
		} else {
			$data['module_webp_status'] = $this->config->get('module_webp_status');
		}

		$webp = gd_info();

		if ($webp['WebP Support']) {
			$data['text_alert'] = $this->language->get('text_supported');
		} else {
			$data['text_alert'] = $this->language->get('text_unsupported');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/webp', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/webp')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function refresh($directory = DIR_IMAGE . 'cache/webp') {
		
		foreach(glob($directory."/*") as $file)
	    {
	        if(is_dir($file)) { 
	            $this->refresh($file);
	        } else {
	            unlink($file);
	        }
	    }
	    rmdir($directory);
	}
}