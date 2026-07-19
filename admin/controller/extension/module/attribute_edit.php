<?php
class ControllerExtensionModuleAttributeEdit extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/attribute_edit');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/module/attribute_edit');

		$this->getList();
	}

	public function edit() {
		$this->load->language('catalog/attribute_edit');

		$this->load->model('extension/module/attribute_edit');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$total = $this->model_extension_module_attribute_edit->editAttribute($this->request->get['language_id'],$this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			echo $this->language->get('text_success') . ' ' . $total;
		} else {
			echo $this->error['warning'];
		}
	}

	protected function getList() {

		if (isset($this->request->get['filter_text'])) {
			$filter_text = $this->request->get['filter_text'];
		} else {
			$filter_text = null;
		}

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = null;
		}

		if (isset($this->request->get['filter_group_name'])) {
			$filter_group_name = $this->request->get['filter_group_name'];
		} else {
			$filter_group_name = null;
		}

		if (isset($this->request->get['filter_language_id'])) {
			$filter_language_id = $this->request->get['filter_language_id'];
		} else {
			$filter_language_id = $this->config->get('config_language_id');
		}
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pa.text';
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

		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . $this->request->get['filter_text'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_group_name'])) {
			$url .= '&filter_group_name=' . $this->request->get['filter_group_name'];
		}
		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . $this->request->get['filter_language_id'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/attribute_edit', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);


		$data['attributes'] = array();

		$filter_data = array(
			'filter_text' => $filter_text,
			'filter_name' => $filter_name,
			'filter_group_name' => $filter_group_name,
			'filter_language_id' => $filter_language_id,
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$attribute_total = $this->model_extension_module_attribute_edit->getTotalAttributes($filter_data);

		$results = $this->model_extension_module_attribute_edit->getAttributes($filter_data);

		foreach ($results as $result) {
			$data['attributes'][] = array(
				'text'            => $result['text'],
				'old_text'        => base64_encode($result['text']),
				'language_id'     => $result['language_id'],
				'total'           => $result['total'],
				'name'            => $result['name'],
				'group_name'      => $result['group_name'],
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_text'] = $this->language->get('column_text');
		$data['column_language'] = $this->language->get('column_language');
		$data['column_total'] = $this->language->get('column_total');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_group_name'] = $this->language->get('column_group_name');
		$data['entry_language_id'] = $this->language->get('entry_language_id');
		$data['entry_text'] = $this->language->get('entry_text');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_group_name'] = $this->language->get('entry_group_name');

		$data['button_add'] = $this->language->get('button_add');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_delete'] = $this->language->get('button_delete');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = '';

		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . $this->request->get['filter_text'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_group_name'])) {
			$url .= '&filter_group_name=' . $this->request->get['filter_group_name'];
		}
		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . $this->request->get['filter_language_id'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_text'] = $this->url->link('extension/module/attribute_edit', 'user_token=' . $this->session->data['user_token'] . '&sort=pa.text' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_text'])) {
			$url .= '&filter_text=' . $this->request->get['filter_text'];
		}
		if (isset($this->request->get['filter_group_name'])) {
			$url .= '&filter_group_name=' . $this->request->get['filter_group_name'];
		}
		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_language_id'])) {
			$url .= '&filter_language_id=' . $this->request->get['filter_language_id'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $attribute_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/attribute_edit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($attribute_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attribute_total - $this->config->get('config_limit_admin'))) ? $attribute_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attribute_total, ceil($attribute_total / $this->config->get('config_limit_admin')));

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['filter_text'] = $filter_text;
		$data['filter_name'] = $filter_name;
		$data['filter_group_name'] = $filter_group_name;
		
		$data['filter_language_id'] = $filter_language_id;
		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/attribute_edit_list', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/attribute_edit')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->user->hasPermission('modify', 'catalog/product')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
