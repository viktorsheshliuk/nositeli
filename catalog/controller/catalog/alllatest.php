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

		$data['text_empty'] = $this->language->get('text_empty');

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
			$this->document->setRobots('noindex,follow');
		} else {
			$sort = 'p.date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
			$this->document->setRobots('noindex,follow');
		} else {
			$order = 'DESC';
		}

		$data['products'] = array();

		$filter_data = array(
			'filter_latest' => true,
			'sort'          => $sort,
			'order'         => $order,
			'start'         => 0,
			'limit'         => 60
		);

		$results = $this->model_catalog_product->getProducts($filter_data);


		$data['products'] = $this->load->controller('product/thumb', [
            'products' => $results
        ]);

		$url = '';

		$data['sorts'] = array();

		$data['sorts'][] = array(
				'text'  => $this->language->get('text_default'),
				'value' => 'p.sort_order-ASC',
				'href'  => $this->url->link('catalog/alllatest', '&sort=p.sort_order&order=ASC' . $url)
		);

		$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_asc'),
				'value' => 'pd.name-ASC',
				'href'  => $this->url->link('catalog/alllatest', '&sort=pd.name&order=ASC' . $url)
		);

		$data['sorts'][] = array(
				'text'  => $this->language->get('text_name_desc'),
				'value' => 'pd.name-DESC',
				'href'  => $this->url->link('catalog/alllatest', '&sort=pd.name&order=DESC' . $url)
		);

		$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_asc'),
				'value' => 'p.price-ASC',
				'href'  => $this->url->link('catalog/alllatest', '&sort=p.price&order=ASC' . $url)
		);

		$data['sorts'][] = array(
				'text'  => $this->language->get('text_price_desc'),
				'value' => 'p.price-DESC',
				'href'  => $this->url->link('catalog/alllatest', '&sort=p.price&order=DESC' . $url)
		);

		// $url = '';

		// if (isset($this->request->get['sort'])) {
		// 	$url .= '&sort=' . $this->request->get['sort'];
		// }

		$data['sort'] = $sort;
		$data['order'] = $order;

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

	

