<?php
class ControllerCatalogAlllatest extends Controller {

	public function index() {
		$this->load->language('product/product');
		$this->load->language('catalog/alllatest');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);
 
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_latest'),
			'href' =>''
		);

		$data['products'] = array();

		$filter_data = array(
			'filter_latest' => true,
			'sort'          => 'p.date_added',
			'order'         => 'DESC',
			'start'         => 0,
			'limit'         => 60
		);

		$results = $this->model_catalog_product->getProducts($filter_data);


		$data['products'] = $this->load->controller('product/thumb', [
            'products' => $results
        ]);

		$this->document->setTitle($this->language->get('text_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keywords'));

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('catalog/alllatest', $data));
		
		} 
}

	

