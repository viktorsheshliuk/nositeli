<?php
class ControllerExtensionModuleShowcase extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/showcase');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/showcase/showcase.css');

		$this->load->model('setting/module');

		if (!isset($this->request->get['module_id'])) {
			$data['apply_btn'] = false;
		} else {
			$data['apply_btn'] = true;
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('showcase', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			if (isset($this->request->post['apply']) && $this->request->post['apply'] == '1') {
				$this->session->data['success'] = $this->language->get('text_apply');
				$this->response->redirect($this->url->link('extension/module/showcase', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true));
			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		$data['default_store'] = $this->config->get('config_name');

		$data['version'] = '2.1.5';
		$data['text_author'] = $this->language->get('text_author');
		$data['text_author_link'] = $this->language->get('text_author_link');
		$data['text_support'] = $this->language->get('text_support');
		$data['text_more'] = $this->language->get('text_more');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_allcat'] = $this->language->get('text_allcat');
		$data['text_fcat'] = $this->language->get('text_fcat');
		$data['text_allbrands'] = $this->language->get('text_allbrands');
		$data['text_fbrands'] = $this->language->get('text_fbrands');
		$data['text_all_customers'] = $this->language->get('text_all_customers');
		$data['text_cat_autocomplete'] = $this->language->get('text_cat_autocomplete');
		$data['text_count'] = $this->language->get('text_count');
		$data['text_categories'] = $this->language->get('text_categories');
		$data['text_brands'] = $this->language->get('text_brands');
		$data['text_current'] = $this->language->get('text_current');
		$data['text_parent'] = $this->language->get('text_parent');
		$data['text_lg'] = $this->language->get('text_lg');
		$data['text_md'] = $this->language->get('text_md');
		$data['text_sm'] = $this->language->get('text_sm');
		$data['text_xs'] = $this->language->get('text_xs');
		$data['text_left'] = $this->language->get('text_left');
		$data['text_right'] = $this->language->get('text_right');
		$data['text_top'] = $this->language->get('text_top');
		$data['text_bottom'] = $this->language->get('text_bottom');
		$data['text_inside'] = $this->language->get('text_inside');
		$data['text_outside'] = $this->language->get('text_outside');
		$data['text_heading'] = $this->language->get('text_heading');
		$data['text_description'] = $this->language->get('text_description');
		$data['text_btn'] = $this->language->get('text_btn');
		$data['text_list'] = $this->language->get('text_list');
		$data['text_column'] = $this->language->get('text_column');
		$data['text_autoplay'] = $this->language->get('text_autoplay');
		$data['text_more_btn'] = $this->language->get('text_more_btn');
		$data['text_cart'] = $this->language->get('text_cart');
		$data['text_price'] = $this->language->get('text_price');
		$data['text_rating'] = $this->language->get('text_rating');

		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_items'] = $this->language->get('entry_items');
		$data['entry_subitems'] = $this->language->get('entry_subitems');
		$data['entry_cat'] = $this->language->get('entry_cat');
		$data['entry_brands'] = $this->language->get('entry_brands');
		$data['entry_products_byitem'] = $this->language->get('entry_products_byitem');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_products_limit'] = $this->language->get('entry_products_limit');
		$data['entry_itemrow'] = $this->language->get('entry_itemrow');
		$data['entry_subitemrow'] = $this->language->get('entry_subitemrow');
		$data['entry_margin'] = $this->language->get('entry_margin');
		$data['entry_subitems_limit'] = $this->language->get('entry_subitems_limit');
		$data['entry_image'] = $this->language->get('entry_image');
		$data['entry_size'] = $this->language->get('entry_size');
		$data['entry_image_pos'] = $this->language->get('entry_image_pos');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_btn'] = $this->language->get('entry_btn');
		$data['entry_cart'] = $this->language->get('entry_cart');
		$data['entry_desc'] = $this->language->get('entry_desc');
		$data['entry_parent_desc'] = $this->language->get('entry_parent_desc');
		$data['entry_subitems_desc'] = $this->language->get('entry_subitems_desc');
		$data['entry_desc_limit'] = $this->language->get('entry_desc_limit');
		$data['entry_subitems_pos'] = $this->language->get('entry_subitems_pos');
		$data['entry_subitems_view'] = $this->language->get('entry_subitems_view');
		$data['entry_column'] = $this->language->get('entry_column');
		$data['entry_carousel'] = $this->language->get('entry_carousel');
		$data['entry_items_carousel'] = $this->language->get('entry_items_carousel');
		$data['entry_subitems_carousel'] = $this->language->get('entry_subitems_carousel');
		$data['entry_mousewheel'] = $this->language->get('entry_mousewheel');
		$data['entry_autoplay'] = $this->language->get('entry_autoplay');
		$data['entry_drag'] = $this->language->get('entry_drag');
		$data['entry_dots'] = $this->language->get('entry_dots');
		$data['entry_nav'] = $this->language->get('entry_nav');
		$data['entry_nav_text'] = $this->language->get('entry_nav_text');
		$data['entry_nav_speed'] = $this->language->get('entry_nav_speed');
		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_location'] = $this->language->get('entry_location');
		$data['entry_customers'] = $this->language->get('entry_customers');
		$data['entry_sc_class'] = $this->language->get('entry_sc_class');
		$data['entry_store'] = $this->language->get('entry_store');

		$data['tab_sc_setting'] = $this->language->get('tab_sc_setting');
		$data['tab_module_setting'] = $this->language->get('tab_module_setting');
		$data['tab_items'] = $this->language->get('tab_items');
		$data['tab_subitems'] = $this->language->get('tab_subitems');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_apply'] = $this->language->get('button_apply');

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

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
			);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true)
			);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/showcase', 'user_token=' . $this->session->data['user_token'], true)
				);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/showcase', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
				);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/showcase', 'user_token=' . $this->session->data['user_token'], 'SSL');
		} else {
			$data['action'] = $this->url->link('extension/module/showcase', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], 'SSL');
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL');

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

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
			$data['status'] = 1;
		}

		if (isset($this->request->post['showcase'])) {
			$data['showcase'] = $this->request->post['showcase'];
		} elseif (!empty($module_info)) {
			$data['showcase'] = $module_info['showcase'];
		} else {
			$data['showcase'] = array();
			$data['showcase']['item_image'] = 1;
			$data['showcase']['item_heading'] = 1;
			$data['showcase']['items_nav'] = 1;
			$data['showcase']['subitems_status'] = 1;
			$data['showcase']['subitem_image'] = 1;
			$data['showcase']['subitem_heading'] = 1;
			$data['showcase']['subitems_nav'] = 1;
			$data['showcase']['store_id'][] = 0;
			$data['showcase']['all_customers'] = 1;
			$data['showcase']['allcat'] = 1;
			$data['showcase']['location'] = 1;
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		$this->load->model('catalog/category');
		$data['categories'] = array();

		if (!empty($this->request->post['showcase']['fcat'])) {
			$categories = $this->request->post['showcase']['fcat'];
		} elseif (!empty($module_info) && !empty($module_info['showcase']['fcat'])) {
			$categories = $module_info['showcase']['fcat'];
		} else {
			$categories = array();
		}

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => $category_info['path'] ? $category_info['path'] . ' - &gt; ' . $category_info['name'] : $category_info['name']
					);
			}
		}

		$data['locations'] = array();

		if (!empty($this->request->post['showcase']['fcid'])) {
			$locations = $this->request->post['showcase']['fcid'];
		} elseif (!empty($module_info) && !empty($module_info['showcase']['fcid'])) {
			$locations = $module_info['showcase']['fcid'];
		} else {
			$locations = array();
		}

		foreach ($locations as $location) {
			$category_info = $this->model_catalog_category->getCategory($location);

			if ($category_info) {
				$data['locations'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => $category_info['path'] ? $category_info['path'] . ' - &gt; ' . $category_info['name'] : $category_info['name']
					);
			}
		}

		$this->load->model('catalog/manufacturer');
		$data['brands'] = array();

		if (!empty($this->request->post['showcase']['fbrand'])) {
			$brands = $this->request->post['showcase']['fbrand'];
		} elseif (!empty($module_info) && !empty($module_info['showcase']['fbrand'])) {
			$brands = $module_info['showcase']['fbrand'];
		} else {
			$brands = array();
		}

		foreach ($brands as $brand_id) {
			$brand_info = $this->model_catalog_manufacturer->getManufacturer($brand_id);

			if ($brand_info) {
				$data['brands'][] = array(
					'brand_id' => $brand_info['manufacturer_id'],
					'name'     => $brand_info['name']
					);
			}
		}

		$this->load->model('extension/module/showcase');
		$data['customer_groups'] = $this->model_extension_module_showcase->getCustomerGroups();

		$data['sorts'] = array();

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_default_asc'),
			'value' => 'p.sort_order-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_viewed_asc'),
			'value' => 'p.viewed-DESC'
			);

		if ($this->config->get('config_review_status')) {
			$data['sorts'][] = array(
				'name'  => $this->language->get('text_rating_desc'),
				'value' => 'rating-DESC'
				);
		}

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_date_desc'),
			'value' => 'p.date_added-DESC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_name_asc'),
			'value' => 'pd.name-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_name_desc'),
			'value' => 'pd.name-DESC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_price_asc'),
			'value' => 'p.price-ASC'
			);

		$data['sorts'][] = array(
			'name'  => $this->language->get('text_price_desc'),
			'value' => 'p.price-DESC'
			);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/showcase', $data));
	
	}

	public function install() {
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` ADD `sc_image` varchar(255) NULL default '' AFTER image;");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` ADD `sc_name` varchar(255) NULL default '' AFTER name, ADD `sc_description` TEXT NULL default '' AFTER description;");

	}

	public function uninstall () {
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "category` DROP `sc_image`;");
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "category_description` DROP `sc_name`, DROP `sc_description`;");
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/showcase')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}