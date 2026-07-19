<?php
class ControllerExtensionMayAdvancedOptions extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/may/advanced_options');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/may/advanced_options');

		$this->getList();
	}

	public function add() {
		$this->load->language('extension/may/advanced_options');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/may/advanced_options');

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/may/advanced_options');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/may/advanced_options');

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/may/advanced_options');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('extension/may/advanced_options');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $option_id) {
				$this->model_extension_may_advanced_options->deleteOption($option_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

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

			$this->response->redirect($this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function save() {
		$this->load->language('extension/may/advanced_options');

		$this->load->model('extension/may/advanced_options');

		if (isset($this->request->post['option_values']) && 
			//isset($this->request->post['option_children']) && 
			isset($this->request->post['advanced_option_name']) && 
			$this->validateForm()) {

			$data = array();

			foreach ($this->request->post['option_values'] as $option_id => $option_values) {
				foreach ($option_values as $option_value) {
					if (!isset($data[$option_id])) {
						$data[$option_id] = array();
					}
					if (isset($this->request->post['option_children'][$option_id])) {
						$data[$option_id][$option_value] = $this->request->post['option_children'][$option_id][$option_value];
					} else {
						$data[$option_id][$option_value] = array();
					}
				}
			}

			$this->request->post['option_values'] = $data;

			if (!isset($this->request->get['option_id'])) {
				$this->model_extension_may_advanced_options->addOption($this->request->post);
			} else {
				$this->model_extension_may_advanced_options->editOption($this->request->get['option_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');
		}
	}

	protected function getList() {
		$this->load->model('catalog/option');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'option_name';
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/may/advanced_options/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/may/advanced_options/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['options'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$option_total = $this->model_extension_may_advanced_options->getTotalOptions();

		$results = $this->model_extension_may_advanced_options->getOptions($filter_data);

		foreach ($results as $result) {
			$children = array();
			foreach (explode(",", $result['children']) as $child_id) {
				$child_names = $this->model_catalog_option->getOptionDescriptions($child_id);
				if (isset($child_names[(int)$this->config->get('config_language_id')])) {
					$children[] = $child_names[(int)$this->config->get('config_language_id')]['name'];
				} else if (count($child_names) > 0) {
					$children[] = $child_names[0]['name'];
				}
			}

			$data['options'][] = array(
				'option_id'  => $result['option_id'],
				'option_name' => $result['option_name'],
				'options' => implode(", ", $children),
				'swatch_image' => $result['swatch_image'],
				'sort_order' => $result['sort_order'],
				'edit'       => $this->url->link('extension/may/advanced_options/edit', 'user_token=' . $this->session->data['user_token'] . '&option_id=' . $result['option_id'] . $url, true)
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_options'] = $this->language->get('column_options');
		$data['column_swatch_image'] = $this->language->get('column_swatch_image');
		$data['column_sort_order'] = $this->language->get('column_sort_order');
		$data['column_action'] = $this->language->get('column_action');

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

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . '&sort=option_name' . $url, true);
		$data['sort_sort_order'] = $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $option_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($option_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($option_total - $this->config->get('config_limit_admin'))) ? $option_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $option_total, ceil($option_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/may/advanced_options/list', $data));
	}

	protected function getForm() {
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_form'] = !isset($this->request->get['option_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['text_step_1'] = $this->language->get('text_step_1');
		$data['text_step_2'] = $this->language->get('text_step_2');
		$data['text_step_3'] = $this->language->get('text_step_3');
		$data['text_step_4'] = $this->language->get('text_step_4');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_swatch_image'] = $this->language->get('entry_swatch_image');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['entry_option_model'] = $this->language->get('entry_option_model');
		$data['entry_option_hide'] = $this->language->get('entry_option_hide');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_back'] = $this->language->get('button_back');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['error_select_options'] = $this->language->get('error_select_options');
		$data['error_select_option_values'] = $this->language->get('error_select_option_values');
		$data['error_option_name'] = $this->language->get('error_option_name');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
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

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['option_id'])) {
			$data['action'] = $this->url->link('extension/may/advanced_options/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/may/advanced_options/edit', 'user_token=' . $this->session->data['user_token'] . '&option_id=' . $this->request->get['option_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['option_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$data['option'] = $this->model_extension_may_advanced_options->getOption($this->request->get['option_id']);
			if (isset($data['option']['content'])) {
				$content = json_decode($data['option']['content'], true);
				foreach ($content as $option_id => $option_values) {
					$content[$option_id] = array_keys($option_values);
				}
				$data['option']['content'] = $content;
			}
		}

		$data['may_advanced_options_config'] = array(
			'swatch_image' => $this->config->get('may_advanced_options_swatch_image'),
		);

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$data['url_select_options_form'] = $this->url->link('extension/may/advanced_options/getSelectOptionsForm', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['url_option_values_form'] = $this->url->link('extension/may/advanced_options/getOptionValuesForm', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['url_option_children_form'] = $this->url->link('extension/may/advanced_options/getOptionChildrenForm', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['option_id'])) {
			$data['url_save'] = $this->url->link('extension/may/advanced_options/save', 'user_token=' . $this->session->data['user_token'] . '&option_id=' . $this->request->get['option_id'] . $url, true);
		} else {
			$data['url_save'] = $this->url->link('extension/may/advanced_options/save', 'user_token=' . $this->session->data['user_token'] . $url, true);
		}

		$this->response->setOutput($this->load->view('extension/may/advanced_options/form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/may/advanced_options')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'extension/may/advanced_options')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/may/advanced_options');

			$this->load->model('catalog/option');

			$this->load->model('tool/image');

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'start'       => 0,
				'limit'       => 10
			);

			$advanced_options = $this->model_extension_may_advanced_options->getOptions($filter_data);

			foreach ($advanced_options as $advanced_option) {
				$children = explode(",", $advanced_option['children']);
				$content = json_decode($advanced_option['content'], true);

				$options = array();

				foreach ($children as $option_id) {
					$option = $this->model_catalog_option->getOption($option_id);

					$option_value_data = array();

					$option_values = $this->model_catalog_option->getOptionValues($option['option_id']);

					foreach ($option_values as $option_value) {

						if (!in_array($option_value['option_value_id'], array_keys($content[$option['option_id']]))) {
							continue;
						}


						if (is_file(DIR_IMAGE . $option_value['image'])) {
							$image = $this->model_tool_image->resize($option_value['image'], 50, 50);
						} else {
							$image = $this->model_tool_image->resize('no_image.png', 50, 50);
						}

						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => strip_tags(html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8')),
							'image'           => $image,
							'children'		  => $content[$option['option_id']][$option_value['option_value_id']]
						);
					}

					$options[] = array(
						'option_id'    => $option['option_id'],
						'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
						'option_value' => $option_value_data
					);
				}

				$json[] = array(
					'value'				 => $advanced_option['option_id'],
					'name' 				 => $advanced_option['option_name'],
					'options'			 => $options,
					'swatch_image'		 => is_null($advanced_option['swatch_image']) ? 0 : $advanced_option['swatch_image']
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getSelectOptionsForm() {

		$this->load->language('extension/may/advanced_options');

		$this->load->model('catalog/option');

		$data['options'] = array();

		$filter_data = array(
			'sort'  => 'o.sort_order',
			'order' => 'ASC',
			'start' => 0,
			'limit' => $this->model_catalog_option->getTotalOptions()
		);

		$results = $this->model_catalog_option->getOptions($filter_data);

		foreach ($results as $result) {
			if ($result['type'] != 'radio' && $result['type'] != 'select') {
				continue;
			}

			$option_values = array();

			foreach ($this->model_catalog_option->getOptionValues($result['option_id']) as $option_value) {
				$option_values[] = $option_value["name"];
			}

			$data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'values' => implode(", ", $option_values),
			);
		}

		if (isset($this->request->post['option_children'])) {
			$data['selected'] = explode(",", $this->request->post['option_children']);
		} else {
			$data['selected'] = array();
		}

		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['column_name'] = $this->language->get('column_option_name');
		$data['column_values'] = $this->language->get('column_values');

		$data['button_next'] = $this->language->get('button_next');
		$data['button_back'] = $this->language->get('button_back');

		$this->response->setOutput($this->load->view('extension/may/advanced_options/form/select_options', $data));		
	}

	public function getOptionValuesForm() {

		$this->load->language('extension/may/advanced_options');

		$this->load->model('catalog/option');


		$selected_options = array();

		if (isset($this->request->post['option_values']) &&
			$this->request->post['option_values'] != "") {
			$option_info = explode(":", $this->request->post['option_values']);
			$data['selected'] = explode(",", $option_info[1]);

			$selected_options = explode(",", $option_info[0]);
		} else {
			$data['selected'] = array();

			if (isset($this->request->post['selected'])) {
				$selected_options = $this->request->post['selected'];
			}
		}

		$data['options'] = array();

		foreach ($selected_options as $selected_option) {

			$result =  $this->model_catalog_option->getOption($selected_option);

			if (count($result) == 0) {
				continue;
			}

			$data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'values' => $this->model_catalog_option->getOptionValues($result['option_id']),
			);
		}


		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_deselect_all'] = $this->language->get('text_deselect_all');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_values'] = $this->language->get('column_values');

		$data['button_next'] = $this->language->get('button_next');
		$data['button_back'] = $this->language->get('button_back');

		$this->response->setOutput($this->load->view('extension/may/advanced_options/form/option_values', $data));		
	}

	public function getOptionChildrenForm() {

		$this->load->language('extension/may/advanced_options');

		$this->load->model('catalog/option');

		$option_values = array();

		if (isset($this->request->post['option_values'])) {
			$option_values = $this->request->post['option_values'];
		}

		$data['options'] = array();

		foreach ($option_values as $option_id => $option_value) {

			$result =  $this->model_catalog_option->getOption($option_id);

			if (count($result) == 0) {
				continue;
			}

			$values = array();
			foreach ($this->model_catalog_option->getOptionValues($result['option_id']) as $child) {
				if (in_array($child['option_value_id'], $option_value)) {
					$values[] = $child;
				}
			}


			if (count($data['options']) > 0) {
				$data['options'][count($data['options']) - 1]['cname'] = $result['name'];
				$data['options'][count($data['options']) - 1]['children'] = $values;
			}

			$data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'values' => $values,
				'cname' => '',
				'children' => array()
			);
		}

		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_select_all'] = $this->language->get('text_select_all');
		$data['text_deselect_all'] = $this->language->get('text_deselect_all');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_values'] = $this->language->get('column_values');

		$data['button_next'] = $this->language->get('button_next');
		$data['button_back'] = $this->language->get('button_back');

		$this->response->setOutput($this->load->view('extension/may/advanced_options/form/option_children', $data));		
	}

	public function getCombineOptionsModal() {

		$this->load->language('extension/may/advanced_options');

		$this->load->model('catalog/option');

		$data['options'] = array();

		$filter_data = array(
			'sort'  => 'o.sort_order',
			'order' => 'ASC',
			'start' => 0,
			'limit' => $this->model_catalog_option->getTotalOptions()
		);

		$results = $this->model_catalog_option->getOptions($filter_data);

		foreach ($results as $result) {
			if ($result['type'] != 'radio' && $result['type'] != 'select') {
				continue;
			}

			$option_values = array();

			foreach ($this->model_catalog_option->getOptionValues($result['option_id']) as $option_value) {
				$option_values[] = array(
					'option_value_id' => $option_value['option_value_id'],
					'name'       	  => $option_value["name"]
				);
			}

			$data['options'][] = array(
				'option_id'  => $result['option_id'],
				'name'       => $result['name'],
				'values' => $option_values,
			);
		}

		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['text_select_options'] = $this->language->get('text_select_options');
		$data['text_selected_options'] = $this->language->get('text_selected_options');
		$data['text_select_option_values'] = $this->language->get('text_select_option_values');
		$data['text_select_combinations'] = $this->language->get('text_select_combinations');

		$data['column_name'] = $this->language->get('column_option_name');
		$data['column_values'] = $this->language->get('column_values');

		$data['button_next'] = $this->language->get('button_next');
		$data['button_back'] = $this->language->get('button_back');
		$data['button_generate'] = $this->language->get('button_generate');
		$data['button_select_option_values'] = $this->language->get('button_select_option_values');
		$data['button_select_dependencies'] = $this->language->get('button_select_dependencies');

		$data['may_advanced_options_config'] = array(
			'swatch_image' => $this->config->get('may_advanced_options_swatch_image'),
		);

		$this->response->setOutput($this->load->view('extension/may/advanced_options/modal', $data));		
	}

	public function vCommonColumnLeftBefore($route, &$data) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (!$this->user->hasPermission('access', 'extension/may/advanced_options')) {
			return;
		}

		foreach ($data['menus'] as $menu_key => $menu) {
			if ($menu['id'] != 'menu-catalog') {
				continue;
			}

			$new_catalog = array();
			foreach ($menu['children'] as $submenu) {
				$new_catalog[] = $submenu;
				if (isset($submenu['href']) && $submenu['href'] == $this->url->link('catalog/option', 'user_token=' . $this->session->data['user_token'], true)) {
					$this->load->language('extension/may/advanced_options');
					$new_catalog[] = array(
						'name'	   => $this->language->get('text_advanced_options'),
						'href'     => $this->url->link('extension/may/advanced_options', 'user_token=' . $this->session->data['user_token'], true),
						'children' => array()		
					);
				}
			}

			$data['menus'][$menu_key]['children'] = $new_catalog;
			break;
		}
	}

	public function vCatalogProductFormBefore($route, &$data) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		$this->load->model('tool/image');

		$may_advanced_options = array();
		$may_advanced_root_option_value = "";

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			uasort($data['product_options'], function($a, $b) {
				return ($a['product_option_id'] < $b['product_option_id']) ? -1 : 1;
			});			
		}

		$option_row = 0;
		foreach ($data['product_options'] as $product_option_key => $product_option) {
			if (!(($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'may_advanced_option') && $product_option['value'] != '')) {
				$option_row ++;
				continue;
			}

			if (!isset($data['option_values'][$product_option['option_id']])) {
				$data['option_values'][$product_option['option_id']] = $this->model_catalog_option->getOptionValues($product_option['option_id']);
			}

			if ($may_advanced_root_option_value == '' || strpos($product_option['value'], $may_advanced_root_option_value . '-') !== 0) {

				$may_advanced_root_option_info = explode(':::', $product_option['value']);
				$may_advanced_root_option_value = implode(':::', array_slice($may_advanced_root_option_info, 0, 2));
				$may_advanced_options[] = array(
					'name' => $may_advanced_root_option_info[0],
					'type' => 'may_advanced_option',
					'required' => $product_option['required'],
					'after' => $option_row - 1,
					'options' => array()
				);
			}

			$may_advanced_option_info = explode(':::', $product_option['value']);
			if (count($may_advanced_option_info) > 1) {
				$product_option['value'] = $may_advanced_option_info[0] . ':::' . $may_advanced_option_info[1];

				$may_advanced_option_skus = array();
				$may_advanced_option_images = array();
				$may_advanced_option_hides  = array();

				if (isset($may_advanced_option_info[2])) {
					$tmp = json_decode($may_advanced_option_info[2], true);

					$may_advanced_option_skus = isset($tmp['sku']) ? $tmp['sku'] : array();
					$may_advanced_option_hides = isset($tmp['hide']) ? $tmp['hide'] : array();

					$images = isset($tmp['image']) ? $tmp['image'] : array();
					foreach ($images as $option_value_id => $option_value_images) {
						$may_advanced_option_images[$option_value_id] = array();
						$option_value_images[] = '';
						
						foreach ($option_value_images as $option_value_image) {
							if (is_file(DIR_IMAGE . $option_value_image)) {
								$may_advanced_option_image = $option_value_image;
								$may_advanced_option_thumb = $option_value_image;
							} else {
								$may_advanced_option_image = '';
								$may_advanced_option_thumb = 'no_image.png';
							}

							$may_advanced_option_images[$option_value_id][] = array(
								'image'                    => $may_advanced_option_image,
								'thumb'                    => $this->model_tool_image->resize($may_advanced_option_thumb, 100, 100)
							);
						}
					}
				}

				foreach ($product_option['product_option_value'] as $index => $option_value) {
					$product_option['product_option_value'][$index]['sku'] = isset($may_advanced_option_skus[$option_value['option_value_id']]) ? $may_advanced_option_skus[$option_value['option_value_id']] : "";
					$product_option['product_option_value'][$index]['image'] = isset($may_advanced_option_images[$option_value['option_value_id']]) ? $may_advanced_option_images[$option_value['option_value_id']] : array(array('thumb' => 'no_image.png', 'image' => ''));
					$product_option['product_option_value'][$index]['hide'] = isset($may_advanced_option_hides[$option_value['option_value_id']]) ? $may_advanced_option_hides[$option_value['option_value_id']] : false;
				}

				$product_option['swatch_image'] = isset($tmp['swatch_image']) ? $tmp['swatch_image'] :  $this->config->get('may_advanced_options_swatch_image');

				$may_advanced_options[count($may_advanced_options) - 1]['options'][(string)$may_advanced_option_info[1]] = $product_option;
			}

			unset($data['product_options'][$product_option_key]);
		}

		foreach ($may_advanced_options as $may_option_key => $may_option) {
			$may_option_count = 0;
			$map_option_index = array();
			$map_option_index_no = 0;
			foreach ($may_option['options'] as $may_option_item_key => $may_option_item) {
				$may_option_count += count($may_option_item['product_option_value']);
				$map_option_index[$may_option_item_key] = $map_option_index_no ++;
			}

			$may_option_value_tree = array();
			$may_option_depth = 0;
			while (count($may_option_value_tree) < $may_option_count) {
				if ($may_option_depth == 0) {
					$may_option_item = current($may_option['options']);
					$may_option_item_key = key($may_option['options']);
					foreach ($may_option_item['product_option_value'] as $may_option_item_value) {

						if (!isset($may_advanced_options[$may_option_key]['subtract'])) {
							$may_advanced_options[$may_option_key]['subtract'] = $may_option_item_value['subtract'];
						}

						$option_value_name = "";
						foreach ($data['option_values'][$may_option_item['option_id']] as $option_value) {
							if ($option_value['option_value_id'] == $may_option_item_value['option_value_id']) {
								$option_value_name = $option_value['name'];
							}
						}
						$may_option_value_tree[] = array(
							'key' => $may_option_item_key,
							'name' => $option_value_name,
							'depth' => $may_option_depth,
							'value' => $may_option_item_value,
							'index' => $map_option_index[$may_option_item_key],
							'parent_sibling_index' => array(),
							'is_last_sibling' => 0,
							'tooltip' => array($may_option_item['name'] . ' : ' . $option_value_name),
							'row_key' => array(strtolower($option_value_name))
						);
					}

					$may_option_value_tree[count($may_option_value_tree) - 1]['is_last_sibling'] = 1;
				} else {
					$may_option_value_tree_temp = array();
					foreach ($may_option_value_tree as $tree_item) {
						$may_option_value_tree_temp[] = $tree_item;

						if ($tree_item['depth'] != $may_option_depth - 1) {
							continue;
						}

						$may_option_item_key = $tree_item['key'] . '-' . $tree_item['value']['option_value_id'];
						if (!array_key_exists($may_option_item_key, $may_option['options'])) {
							continue;
						}

						$may_option_item = $may_option['options'][$may_option_item_key];
						foreach ($may_option_item['product_option_value'] as $may_option_item_value) {
							$option_value_name = "";
							foreach ($data['option_values'][$may_option_item['option_id']] as $option_value) {
								if ($option_value['option_value_id'] == $may_option_item_value['option_value_id']) {
									$option_value_name = $option_value['name'];
								}
							}
							$may_option_value_tree_temp[] = array(
								'key' => $may_option_item_key,
								'name' => $option_value_name,
								'depth' => $may_option_depth,
								'value' => $may_option_item_value,
								'index' => $map_option_index[$may_option_item_key],
								'parent_sibling_index' => array_merge($tree_item['parent_sibling_index'], array($tree_item['is_last_sibling'])),
								'is_last_sibling' => 0,
								'tooltip' => array_merge($tree_item['tooltip'], array($may_option_item['name'] . ' : ' . $option_value_name)),
								'row_key' => array_merge($tree_item['row_key'], array(strtolower($option_value_name)))
							);
						}

						$may_option_value_tree_temp[count($may_option_value_tree_temp) - 1]['is_last_sibling'] = 1;
					}

					$may_option_value_tree = $may_option_value_tree_temp;
				}

				$may_option_depth ++;
			}

			$may_advanced_options[$may_option_key]['option_value_tree'] = $may_option_value_tree;
			$may_advanced_options[$may_option_key]['option_value_tree_depth'] = $may_option_depth;
		}

		foreach ($may_advanced_options as $may_option_key => $may_option) {
			$options = array();
			$index = 10000;

			foreach ($may_option['options'] as $option_key => $option) {
				$option['name'] = $may_option['name'];
				$option['type'] = 'may_advanced_option';
				$option_new = $option;
				$option_new['product_option_value'] = array();
				foreach ($option['product_option_value'] as $product_option_value) {
					foreach ($may_option['option_value_tree'] as $tree_index => $tree_item) {
						if ($tree_item['key'] === $option_key && 
							$product_option_value['product_option_value_id'] === $tree_item['value']['product_option_value_id']) {
							$option_new['product_option_value'][$tree_index] = $product_option_value;
						}
					}
				}
				$options[$index ++] = $option_new;
			}

			$may_advanced_options[$may_option_key]['options'] = $options;
		}

		$data['may_advanced_options'] = $may_advanced_options;

		$data['may_advanced_options_config'] = array(
			'attribute_sku' => $this->config->get('may_advanced_options_attribute_sku'),
			'attribute_quantity' => $this->config->get('may_advanced_options_attribute_quantity'),
			'attribute_price' => $this->config->get('may_advanced_options_attribute_price'),
			'attribute_point' => $this->config->get('may_advanced_options_attribute_point'),
			'weight' => $this->config->get('may_advanced_options_weight'),	
		);

		$this->load->language('extension/may/advanced_options');
		$data['text_alert_title'] = $this->language->get('text_alert_title');
		$data['text_alert_content'] = $this->language->get('text_alert_content');
		$data['text_or'] = $this->language->get('text_or');
		$data['button_add_new'] = $this->language->get('button_add_new');

		$data['entry_may_advanced_option'] = $this->language->get('entry_may_advanced_option');
		$data['entry_may_advanced_option_image'] = $this->language->get('entry_may_advanced_option_image');
		$data['entry_swatch_image'] = $this->language->get('entry_swatch_image');

		$data['url_combine_options_modal'] = $this->url->link('extension/may/advanced_options/getCombineOptionsModal', 'user_token=' . $this->session->data['user_token'], true);

		$data['footer'] = $this->load->view('extension/may/advanced_options/catalog/product_form', $data) . $data['footer'];
	}

	public function mCatalogProductAddProductAfter($route, $args, $product_id) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (!$product_id || !isset($args[0])) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$this->model_extension_may_advanced_options->setAdvancedOptionsToProduct($product_id, $args[0]);
	}

	public function mCatalogProductEditProductAfter($route, $args) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (count($args) < 2 || !$args[0]) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$this->model_extension_may_advanced_options->setAdvancedOptionsToProduct($args[0], $args[1]);
	}

	public function mCatalogOptionDeleteOptionAfter($route, $args) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		$this->load->model('extension/may/advanced_options');
		$this->model_extension_may_advanced_options->deleteOptionByChild($args[0]);
	}

	public function mCatalogOptionDeleteOptionBefore($route, $args) {
		if (!$this->config->get('may_advanced_options_status')) {
			return;
		}

		if (count($args) < 2 || !$args[0]) {
			return;
		}

		$child_option_value_ids_new = array();
		foreach ($args[1]['option_value'] as $child_option_value_new) {
			$child_option_value_ids_new[] = $child_option_value_new['option_value_id'];
		}

		$this->load->model('catalog/option');

		$child_option_values_old = $this->model_catalog_option->getOptionValues($args[0]);
		foreach ($child_option_values_old as $child_option_value_old) {
			if (!in_array($child_option_value_old['option_value_id'], $child_option_value_ids_new)) {
				$child_option_value_ids_old[] = $child_option_value_old['option_value_id'];
			}
		}

		if (!count($child_option_value_ids_old)) {
			return;
		}

		$this->load->model('extension/may/advanced_options');

		$options = $this->model_extension_may_advanced_options->getOptionsByChild($args[0]);
		foreach ($options as $option) {
			$this->model_extension_may_advanced_options->deleteChildOptionValues($option, $child_option_value_ids_old);
		}

		$this->model_extension_may_advanced_options->deleteChildOptionValues(['option_id' => 0], $child_option_value_ids_old);
	}
}
