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
		$this->document->setTitle($this->language->get('text_title'));
		$this->document->setDescription($this->language->get('text_description'));
		$this->document->setKeywords($this->language->get('text_keywords'));

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('catalog/alllatest', $data));
		
		} 
}

	

