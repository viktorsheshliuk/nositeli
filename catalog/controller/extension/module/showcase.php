<?php
class ControllerExtensionModuleShowcase extends Controller {
	public function index($setting) {
		static $module = 1;

		if (!isset($setting['showcase']['store_id']) || !in_array($this->config->get('config_store_id'), $setting['showcase']['store_id'])) {
			return;
		}

		if (!isset($setting['showcase']['all_customers'])) {
			if (!$this->customer->isLogged() || !isset($setting['showcase']['customer_group_id']) || !in_array($this->config->get('config_customer_group_id'), $setting['showcase']['customer_group_id'])) {
				return;
			}
		}

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (!isset($setting['showcase']['location']) && isset($this->request->get['path'])) {
			if (!isset($setting['showcase']['fcid']) || !in_array((int)end($parts), $setting['showcase']['fcid'])) {
				return;
			}
		}

		$data['subitems_pos'] = !empty($setting['showcase']['subitems_pos']) ? $setting['showcase']['subitems_pos'] : 'inside';
		$this->document->addStyle('catalog/view/theme/default/stylesheet/showcase/sc-' . $data['subitems_pos'] . '.css');

		$data['items_carousel'] = !empty($setting['showcase']['items_carousel']) ? $setting['showcase']['items_carousel'] : '';
		$data['subitems_carousel'] = !empty($setting['showcase']['subitems_carousel']) ? $setting['showcase']['subitems_carousel'] : '';

		$data['items_mousewheel'] = !empty($setting['showcase']['items_mousewheel']) ? $setting['showcase']['items_mousewheel'] : '';
		$data['subitems_mousewheel'] = !empty($setting['showcase']['subitems_mousewheel']) ? $setting['showcase']['subitems_mousewheel'] : '';

		if ($data['items_carousel'] || $data['subitems_carousel']) {
			$this->document->addStyle('catalog/view/theme/default/stylesheet/showcase/sc-carousel.css');
			$this->document->addScript('catalog/view/javascript/jquery/showcase/sc-carousel.js');
			if ($data['items_mousewheel'] || $data['subitems_mousewheel']) {
				$this->document->addScript('catalog/view/javascript/jquery/showcase/jquery.mousewheel.min.js');
			}
		}

		if (isset($setting['showcase'][$this->config->get('config_language_id')]['title'])) {
			$data['title'] = $setting['showcase'][$this->config->get('config_language_id')]['title'];
		} else {
			$data['title'] = '';
		}

		$data['products_by_item'] = !empty($setting['showcase']['products_by_item']) ? $setting['showcase']['products_by_item'] : '';
		$data['button_cart'] = $this->language->get('button_cart');

		$data['cart_icon'] = !empty($setting['showcase']['cart_icon']) ? html_entity_decode($setting['showcase']['cart_icon'], ENT_QUOTES, 'UTF-8') : '';
		$data['cart_class'] = !empty($setting['showcase']['cart_class']) ? $setting['showcase']['cart_class'] : 'btn btn-default';

		$data['price'] = !empty($setting['showcase']['price']) ? $setting['showcase']['price'] : '';
		$data['rating'] = !empty($setting['showcase']['rating']) ? $setting['showcase']['rating'] : '';
		$data['cart'] = !empty($setting['showcase']['cart']) ? $setting['showcase']['cart'] : '';

		$data['sc_class'] = !empty($setting['showcase']['sc_class']) ? $setting['showcase']['sc_class'] : 'showcase';
		$data['count_status'] = !empty($setting['showcase']['count_status']) ? $setting['showcase']['count_status'] : '';

		$data['subitems_status'] = !empty($setting['showcase']['subitems_status']) ? $setting['showcase']['subitems_status'] : '';
		$data['sublist'] = isset($setting['showcase']['sublist']) ? (int)$setting['showcase']['sublist'] : '';
		$data['column'] = isset($setting['showcase']['column']) ? (int)$setting['showcase']['column'] : '2';

		$data['item_image'] = !empty($setting['showcase']['item_image']) ? $setting['showcase']['item_image'] : '';
		$data['subitem_image'] = !empty($setting['showcase']['subitem_image']) ? $setting['showcase']['subitem_image'] : '';

		$data['item_img_pos'] = !empty($setting['showcase']['item_img_pos']) ? $setting['showcase']['item_img_pos'] : 'top';
		$data['subitem_img_pos'] = !empty($setting['showcase']['subitem_img_pos']) ? $setting['showcase']['subitem_img_pos'] : 'top';

		$data['item_width'] = !empty($setting['showcase']['item_width']) ? $setting['showcase']['item_width'] : '200';
		$data['subitem_width'] = !empty($setting['showcase']['subitem_width']) ? $setting['showcase']['subitem_width'] : '200';

		$data['item_height'] = !empty($setting['showcase']['item_height']) ? $setting['showcase']['item_height'] : '200';
		$data['subitem_height'] = !empty($setting['showcase']['subitem_height']) ? $setting['showcase']['subitem_height'] : '200';

		$data['item_heading'] = !empty($setting['showcase']['item_heading']) ? $setting['showcase']['item_heading'] : '';
		$data['subitem_heading'] = !empty($setting['showcase']['subitem_heading']) ? $setting['showcase']['subitem_heading'] : '';

		$data['item_desc'] = !empty($setting['showcase']['item_desc']) ? $setting['showcase']['item_desc'] : '';
		$data['subitem_desc'] = !empty($setting['showcase']['subitem_desc']) ? $setting['showcase']['subitem_desc'] : '';

		$data['description_status'] = !empty($setting['showcase']['description_status']) ? $setting['showcase']['description_status'] : '';
		$data['description_limit'] = !empty($setting['showcase']['description_limit']) ? $setting['showcase']['description_limit'] : '-1';

		$data['item_btn'] = !empty($setting['showcase']['item_btn']) ? $setting['showcase']['item_btn'] : '';
		$data['subitem_btn'] = !empty($setting['showcase']['subitem_btn']) ? $setting['showcase']['subitem_btn'] : '';

		$data['btn_class'] = !empty($setting['showcase']['btn_class']) ? $setting['showcase']['btn_class'] : 'btn btn-default';
		$data['subbtn_class'] = !empty($setting['showcase']['subbtn_class']) ? $setting['showcase']['subbtn_class'] : 'btn btn-default';

		$data['item_btn_text'] = !empty($setting['showcase']['item_btn_text']) ? html_entity_decode($setting['showcase']['item_btn_text'], ENT_QUOTES, 'UTF-8') : 'View More';
		$data['subitem_btn_text'] = !empty($setting['showcase']['subitem_btn_text']) ? html_entity_decode($setting['showcase']['subitem_btn_text'], ENT_QUOTES, 'UTF-8') : 'View More';

		$data['item_margin'] = isset($setting['showcase']['item_margin']) ? (int)$setting['showcase']['item_margin'] : '20';
		$data['subitem_margin'] = isset($setting['showcase']['subitem_margin']) ? (int)$setting['showcase']['subitem_margin'] : '20';

		$data['items_lg'] = !empty($setting['showcase']['items_lg']) ? $setting['showcase']['items_lg'] : '4';
		$data['subitems_lg'] = !empty($setting['showcase']['subitems_lg']) ? $setting['showcase']['subitems_lg'] : '4';

		$data['items_md'] = !empty($setting['showcase']['items_md']) ? $setting['showcase']['items_md'] : '3';
		$data['subitems_md'] = !empty($setting['showcase']['subitems_md']) ? $setting['showcase']['subitems_md'] : '3';

		$data['items_sm'] = !empty($setting['showcase']['items_sm']) ? $setting['showcase']['items_sm'] : '2';
		$data['subitems_sm'] = !empty($setting['showcase']['subitems_sm']) ? $setting['showcase']['subitems_sm'] : '2';

		$data['items_xs'] = !empty($setting['showcase']['items_xs']) ? $setting['showcase']['items_xs'] : '1';
		$data['subitems_xs'] = !empty($setting['showcase']['subitems_xs']) ? $setting['showcase']['subitems_xs'] : '1';

		// Carousel
		$data['autoplay'] = !empty($setting['showcase']['autoplay']) ? $setting['showcase']['autoplay'] : '';
		$data['autoplay_timeout'] = !empty($setting['showcase']['autoplay_timeout']) ? $setting['showcase']['autoplay_timeout'] : '5000';
		$data['autoplay_speed'] = !empty($setting['showcase']['autoplay_speed']) ? $setting['showcase']['autoplay_speed'] : '500';

		$data['items_nav'] = !empty($setting['showcase']['items_nav']) ? 'true' : 'false';
		$data['subitems_nav'] = !empty($setting['showcase']['subitems_nav']) ? 'true' : 'false';

		$data['items_drag'] = !empty($setting['showcase']['items_drag']) ? 'true' : 'false';
		$data['subitems_drag'] = !empty($setting['showcase']['subitems_drag']) ? 'true' : 'false';

		$data['items_nav_speed'] = !empty($setting['showcase']['items_nav_speed']) ? $setting['showcase']['items_nav_speed'] : '250';
		$data['subitems_nav_speed'] = !empty($setting['showcase']['subitems_nav_speed']) ? $setting['showcase']['subitems_nav_speed'] : '250';

		$data['items_dots'] = !empty($setting['showcase']['items_dots']) ? '1' : '0';
		$data['subitems_dots'] = !empty($setting['showcase']['subitems_dots']) ? '1' : '0';

		$data['items_prev_nav'] = !empty($setting['showcase']['items_prev_nav']) ? html_entity_decode($setting['showcase']['items_prev_nav'], ENT_QUOTES, 'UTF-8') : 'prev';
		$data['subitems_prev_nav'] = !empty($setting['showcase']['subitems_prev_nav']) ? html_entity_decode($setting['showcase']['subitems_prev_nav'], ENT_QUOTES, 'UTF-8') : 'prev';

		$data['items_next_nav'] = !empty($setting['showcase']['items_next_nav']) ? html_entity_decode($setting['showcase']['items_next_nav'], ENT_QUOTES, 'UTF-8') : 'next';
		$data['subitems_next_nav'] = !empty($setting['showcase']['subitems_next_nav']) ? html_entity_decode($setting['showcase']['subitems_next_nav'], ENT_QUOTES, 'UTF-8') : 'next';

		$this->load->model('catalog/showcase');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		$data['items'] = array();

		if ($setting['showcase']['type'] !== 'brands') {

			if ($setting['showcase']['type'] == 'tree') {
				if ($setting['showcase']['allcat']) {
					$items = $this->model_catalog_showcase->getShowcaseCategories(0);
				} else {
					if (!empty($setting['showcase']['fcat'])) {
						$items = $setting['showcase']['fcat'];
					} else {
						$items = array();
					}
				}
			} else {
				if ($setting['showcase']['type'] == 'current') {
					$current_id = (int)end($parts);
				} else {
					$current_id = (int)array_shift($parts);
				}
				$items = $this->model_catalog_showcase->getShowcaseCategories($current_id);
			}

			foreach ($items as $item) {
				if ($setting['showcase']['type'] == 'tree' && !$setting['showcase']['allcat']) {
					$item = $this->model_catalog_showcase->getShowcaseCategory($item);
				}

				if ($item) {
					if ($data['subitems_status']) {
						$subitems_data = array();

						if ($data['products_by_item']) {
							$products_sort_order = explode('-', $setting['showcase']['products_sort']);
							$sort = $products_sort_order['0'];
							$order = $products_sort_order['1'];

							$products_data = array(
								'filter_category_id' => $item['category_id'],
								'sort'                   => $sort,
								'order'                  => $order,
								'start'                  => 0,
								'limit'                  => $setting['showcase']['products_limit']
								);

							$subitems = $this->model_catalog_product->getProducts($products_data);

							foreach ($subitems as $subitem) {
								if ($subitem['image']) {
									$image = $this->model_tool_image->resize($subitem['image'], $data['subitem_width'], $data['subitem_height']);
								} else {
									$image = $this->model_tool_image->resize('placeholder.png', $data['subitem_width'], $data['subitem_height']);
								}

								if (($data['price'] && $this->config->get('config_customer_price') && $this->customer->isLogged()) || $data['price'] && !$this->config->get('config_customer_price')) {
									$price = $this->currency->format($this->tax->calculate($subitem['price'], $subitem['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$price = false;
								}

								if ((float)$subitem['special']) {
									$special = $this->currency->format($this->tax->calculate($subitem['special'], $subitem['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
								} else {
									$special = false;
								}

								if ($this->config->get('config_review_status') && $data['rating']) {
									$rating = $subitem['rating'];
								} else {
									$rating = false;
								}

								if ($setting['showcase']['type'] == 'current') {
									$href = $this->url->link('product/product', isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '_' . $item['category_id'] . '&product_id=' . $subitem['product_id'] : 'path=' . $item['category_id'] . '&product_id=' . $subitem['product_id']);
								} else if ($setting['showcase']['type'] == 'parent') {
									$href = $this->url->link('product/product', isset($this->request->get['path']) ? 'path=' . $current_id . '_' . $item['category_id'] . '&product_id=' . $subitem['product_id'] : 'path=' . $item['category_id'] . '&product_id=' . $subitem['product_id']);
								} else {
									$href = $this->url->link('product/product', 'path=' . $item['category_id'] . '&product_id=' . $subitem['product_id']);
								}

								$subitems_data[] = array(
									'product_id'  => $subitem['product_id'],
									'name'        => $subitem['name'],
									'description' => utf8_substr(strip_tags(html_entity_decode($subitem['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '...',
									'href'        => $href,
									'thumb'       => $image,
									'price'       => $price,
									'special'     => $special,
									'rating'      => $rating,
									'count'       => false,
									'active'      => false
									);
							}
						} else {

							if (!empty($setting['showcase']['subitem_limit'])) {
								$subitems = array_slice($this->model_catalog_showcase->getShowcaseCategories($item['category_id']), 0, $setting['showcase']['subitem_limit']);
							} else {
								$subitems = $this->model_catalog_showcase->getShowcaseCategories($item['category_id']);
							}

							foreach($subitems as $subitem) {
								$filter_data = array(
									'filter_category_id' => $subitem['category_id'],
									'filter_sub_category' => true
									);

								if ($setting['showcase']['type'] == 'current') {
									$href = $this->url->link('product/category', isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '_' . $item['category_id'] . '_' . $subitem['category_id'] : 'path=' . $item['category_id'] . '_' . $subitem['category_id']);
								} else if ($setting['showcase']['type'] == 'parent') {
									$href = $this->url->link('product/category', isset($this->request->get['path']) ? 'path=' . $current_id . '_' . $item['category_id'] . '_' . $subitem['category_id'] : 'path=' . $item['category_id'] . '_' . $subitem['category_id']);
								} else {
									$href = $this->url->link('product/category', 'path=' . $item['category_id'] . '_' . $subitem['category_id']);
								}

								$subitems_data[] = array(
									'name'        => $subitem['sc_name'] ? $subitem['sc_name'] : $subitem['name'],
									'href'        => $href,
									'description' => html_entity_decode($subitem['sc_description'], ENT_QUOTES, 'UTF-8'),
									'active'      => in_array($subitem['category_id'], $parts),
									'count'       => $data['count_status'] ? $this->model_catalog_product->getTotalProducts($filter_data) : '',
									'thumb'       => $this->model_tool_image->resize(($subitem['image'] == '' && $subitem['sc_image']  == '' ? 'placeholder.png' : ($subitem['sc_image'] ? $subitem['sc_image'] : $subitem['image'])), $data['subitem_width'], $data['subitem_height']),
									'price'       => false,
									'special'     => false,
									'rating'      => false
									);
							}
						}
					}

					$filter_data = array(
						'filter_category_id'  => $item['category_id'],
						'filter_sub_category' => true
						);

					if ($setting['showcase']['type'] == 'current') {
						$href = $this->url->link('product/category', isset($this->request->get['path']) ? 'path=' . $this->request->get['path'] . '_' . $item['category_id'] : 'path=' . $item['category_id']);
					} else if ($setting['showcase']['type'] == 'parent') {
						$href = $this->url->link('product/category', isset($this->request->get['path']) ? 'path=' . $current_id . '_' . $item['category_id'] :  'path=' . $item['category_id']);
					} else {
						$href = $this->url->link('product/category', 'path=' . $item['category_id']);
					}

					$data['items'][] = array(
						'name'        => $item['sc_name'] ? $item['sc_name'] : $item['name'],
						'href'        => $href,
						'item_sd'     => html_entity_decode($item['sc_description'], ENT_QUOTES, 'UTF-8'),
						'active'      => in_array($item['category_id'], $parts),
						'description' => utf8_substr(strip_tags(html_entity_decode($item['description'], ENT_QUOTES, 'UTF-8')), 0, $data['description_limit']) . ($item['description'] && $data['description_limit'] > 0 ? '...' : ''),
						'thumb'       => $this->model_tool_image->resize(($item['image'] == '' && $item['sc_image']  == '' ? 'placeholder.png' : ($item['sc_image'] ? $item['sc_image'] : $item['image'])), $data['item_width'], $data['item_height']),
						'count'       => $data['count_status'] ? $this->model_catalog_product->getTotalProducts($filter_data) : '',
						'subitems'    => $data['subitems_status'] ? $subitems_data : ''
						);
				}
			}
		} else {
			$this->load->model('catalog/manufacturer');

			if ($setting['showcase']['allbrands']) {
				$items =$this->model_catalog_manufacturer->getManufacturers();
			} else {
				if (!empty($setting['showcase']['fbrand'])) {
					$items = $setting['showcase']['fbrand'];
				} else {
					$items = array();
				}
			}

			foreach ($items as $item) {
				if (!$setting['showcase']['allbrands']) {
					$item = $this->model_catalog_manufacturer->getManufacturer($item);
				}

				if ($item) {
					if ($data['subitems_status'] && $data['products_by_item']) {
						$products_sort_order = explode('-', $setting['showcase']['products_sort']);
						$sort = $products_sort_order['0'];
						$order = $products_sort_order['1'];

						$products_data = array(
							'filter_manufacturer_id' => $item['manufacturer_id'],
							'sort'                   => $sort,
							'order'                  => $order,
							'start'                  => 0,
							'limit'                  => $setting['showcase']['products_limit']
							);

						$subitems_data = array();
						$subitems = $this->model_catalog_product->getProducts($products_data);

						foreach ($subitems as $subitem) {
							if ($subitem['image']) {
								$image = $this->model_tool_image->resize($subitem['image'], $data['subitem_width'], $data['subitem_height']);
							} else {
								$image = $this->model_tool_image->resize('placeholder.png', $data['subitem_width'], $data['subitem_height']);
							}

							if (($data['price'] && $this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price') && $data['price']) {
								$price = $this->currency->format($this->tax->calculate($subitem['price'], $subitem['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$price = false;
							}

							if ((float)$subitem['special']) {
								$special = $this->currency->format($this->tax->calculate($subitem['special'], $subitem['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
							} else {
								$special = false;
							}

							if ($this->config->get('config_review_status') && $data['rating']) {
								$rating = $subitem['rating'];
							} else {
								$rating = false;
							}

							$subitems_data[] = array(
								'product_id'  => $subitem['product_id'],
								'name'        => $subitem['name'],
								'href'        => $this->url->link('product/product', 'manufacturer_id=' . $item['manufacturer_id'] . '&product_id=' . $subitem['product_id']),
								'description' => utf8_substr(strip_tags(html_entity_decode($subitem['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('config_product_description_length')) . '...',
								'thumb'       => $image,
								'price'       => $price,
								'special'     => $special,
								'rating'      => $rating,
								'count'       => false,
								'active'      => false
								);
						}
					}

					$data['items'][] = array(
						'name'        => $item['name'],
						'href'        => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $item['manufacturer_id']),
						'thumb'       => $this->model_tool_image->resize(($item['image'] == '' ? 'placeholder.png' : $item['image']), $data['item_width'], $data['item_height']),
						'subitems'    => $data['products_by_item'] ? $subitems_data : false,
						'item_sd'     => !empty($item['description']) ? html_entity_decode($item['description'], ENT_QUOTES, 'UTF-8') : false,
						'active'      => false,
						'description' => false,
						'count'       => false
						);
				}
			}
		}

		$data['module'] = $module++;

		if ($data['items']) {
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_theme') . '/template/module/showcase/sc' . $data['subitems_pos'] . '.twig')) {
				return $this->load->view( '/module/showcase/sc' . $data['subitems_pos'], $data);
			} else {
				return $this->load->view('default/template/module/showcase/sc-' . $data['subitems_pos'], $data);
			}
		}
	}
}