<?php
class ControllerExtensionFeedGoogleBase extends Controller {
	public function index() {
		if ($this->config->get('feed_google_base_status')) {
			$output  = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
			$output .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">'."\n";
			$output .= '  <channel>'."\n";
			$output .= '  <title>' . $this->config->get('config_name') . '</title>'."\n";
			$output .= '  <description>' . html_entity_decode($this->config->get('config_meta_description'), ENT_QUOTES, 'UTF-8') . '</description>'."\n";
			$output .= '  <link>' . $this->config->get('config_url') . '</link>'."\n";

			$this->load->model('extension/feed/google_base');
			$this->load->model('catalog/category');
			$this->load->model('catalog/product');

			$this->load->model('tool/image');

			$product_data = array();

			$google_base_categories = $this->model_extension_feed_google_base->getCategories();

			foreach ($google_base_categories as $google_base_category) {
				$filter_data = array(
					'filter_category_id' => $google_base_category['category_id'],
					'filter_filter'      => false
				);

				$products = $this->model_catalog_product->getProducts($filter_data);

				foreach ($products as $product) {
					if (!in_array($product['product_id'], $product_data) && $product['description']) {
						
						$product_data[] = $product['product_id'];
						
						$output .= '<item>'."\n";
						$output .= '<title><![CDATA[' . $product['name'] . ']]></title>'."\n";
						$output .= '<link>' . $this->url->link('product/product', 'product_id=' . $product['product_id']) . '</link>'."\n";
						$output .= '<description><![CDATA[' . htmlentities(html_entity_decode($product['description'], ENT_QUOTES, 'UTF-8')) . ']]></description>'."\n";
						$output .= '<g:brand><![CDATA[' . html_entity_decode($product['manufacturer'], ENT_QUOTES, 'UTF-8') . ']]></g:brand>'."\n";
						$output .= '<g:condition>new</g:condition>'."\n";
						$output .= '<g:id>' . $product['product_id'] . '</g:id>'."\n";

						if ($product['image']) {
							$output .= '  <g:image_link>' . $this->model_tool_image->resize($product['image'], 500, 500) . '</g:image_link>'."\n";
						} else {
							$output .= '  <g:image_link></g:image_link>'."\n";
						}

						$output .= '  <g:model_number>' . $product['model'] . '</g:model_number>'."\n";

						if ($product['mpn']) {
							$output .= '  <g:mpn><![CDATA[' . $product['mpn'] . ']]></g:mpn>' ."\n";
						} else {
							$output .= '  <g:identifier_exists>false</g:identifier_exists>'."\n";
						}

						if ($product['upc']) {
							$output .= '  <g:upc>' . $product['upc'] . '</g:upc>'."\n";
						}

						if ($product['ean']) {
							$output .= '  <g:ean>' . $product['ean'] . '</g:ean>'."\n";
						}

						$currencies = array(
							'UAH',
							'USD',
							'EUR',
							'GBP'
						);

						if (in_array($this->session->data['currency'], $currencies)) {
							$currency_code = $this->session->data['currency'];
							$currency_value = $this->currency->getValue($this->session->data['currency']);
						} else {
							$currency_code = 'USD';
							$currency_value = $this->currency->getValue('USD');
						}

						if ((float)$product['special']) {
							$output .= '  <g:price>' .  $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id']), $currency_code, $currency_value, false) . ' UAH' .'</g:price>'."\n";
						} else {
							$output .= '  <g:price>' . $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id']), $currency_code, $currency_value, false) . ' UAH' .'</g:price>'."\n";
						}

						$output .= '  <g:google_product_category>' . $google_base_category['google_base_category_id'] . '</g:google_product_category>'."\n";

						$categories = $this->model_catalog_product->getCategories($product['product_id']);

						foreach ($categories as $category) {
							$path = $this->getPath($category['category_id']);

							if ($path) {
								$string = '';

								foreach (explode('_', $path) as $path_id) {
									$category_info = $this->model_catalog_category->getCategory($path_id);

									if ($category_info) {
										if (!$string) {
											$string = $category_info['name'];
										} else {
											$string .= ' &gt; ' . $category_info['name'];
										}
									}
								}

								$output .= '<g:product_type><![CDATA[' . $string . ']]></g:product_type>'."\n";
							}
						}

						$output .= '  <g:quantity>' . $product['quantity'] . '</g:quantity>'."\n";
						$output .= '  <g:weight>' . $this->weight->format($product['weight'], $product['weight_class_id']) . '</g:weight>'."\n";
						$output .= '  <g:availability><![CDATA[' . ($product['quantity'] ? 'in stock' : 'out of stock') . ']]></g:availability>'."\n";
						$output .= '</item>'."\n";
					}
				}
			}

			$output .= '  </channel>'."\n";
			$output .= '</rss>'."\n";
			//header("Content-type: text/xml;charset=window-1251");
			$this->response->addHeader('Content-Type: text/xml;charset=window-1251');
			$this->response->setOutput($output);
		}
	}

	protected function getPath($parent_id, $current_path = '') {
		$category_info = $this->model_catalog_category->getCategory($parent_id);

		if ($category_info) {
			if (!$current_path) {
				$new_path = $category_info['category_id'];
			} else {
				$new_path = $category_info['category_id'] . '_' . $current_path;
			}

			$path = $this->getPath($category_info['parent_id'], $new_path);

			if ($path) {
				return $path;
			} else {
				return $new_path;
			}
		}
	}
}
