<?php
class ControllerExtensionModuleCategorywall extends Controller {
	public function index() {
		$this->load->language('extension/module/categorywall');

		$this->load->model('catalog/category');
		$this->load->model('tool/image');

		$data['categories'] = array();

		$selected_categories = $this->config->get('module_categorywall_categories');

		if (!empty($selected_categories)) {
			foreach ($selected_categories as $category_id) {
				$category_info = $this->model_catalog_category->getCategory($category_id);

				if ($category_info) {
					if ($category_info['image']) {
						$image = HTTP_SERVER . 'image/' . $category_info['image'];
					} else {
						$image = HTTP_SERVER . 'image/placeholder.png';
					}

					$data['categories'][] = array(
						'category_id' => $category_info['category_id'],
						'name'        => $category_info['name'],
						'image'       => $image,
						'href'        => $this->url->link('product/category', 'path=' . $category_info['category_id'])
					);
				}
			}
		} 

		return $this->load->view('extension/module/categorywall', $data);
	}
}