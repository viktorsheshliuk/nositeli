<?php
class ControllerCommonPopupCart extends Controller {
	public function index() {
		$this->load->language('common/cart');
		$this->load->language('common/popup_cart');

		// Показываем только последний добавленный товар (если передан product_id)
		$filter_product_id = isset($this->request->get['product_id']) ? (int)$this->request->get['product_id'] : 0;

		// Totals
		$this->load->model('setting/extension');

		$totals = array();
		$taxes = $this->cart->getTaxes();
		$total = 0;

		// Because __call can not keep var references so we put them into an array.
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		// Display prices
		if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get('total_' . $result['code'] . '_status')) {
					$this->load->model('extension/total/' . $result['code']);

					// We have to put the totals in an array so that they pass by reference.
					$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
				}
			}

			$sort_order = array();

			foreach ($totals as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $totals);
		}

		$data['totals'] = array();

		foreach ($totals as $total) {
			$data['totals'][] = array(
				'title' => $total['title'],
				'text'  => $this->currency->format($total['value'], $this->session->data['currency']),
			);
		}

		$this->load->model('tool/image');
		$this->load->model('tool/upload');

		$data['products'] = array();

		foreach ($this->cart->getProducts() as $product) {
			if ($filter_product_id && $product['product_id'] != $filter_product_id) {
				continue;
			}

			if ($product['image']) {
				$image = $this->model_tool_image->resize($product['image'], 100, 100);
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($product['option'] as $option) {
				if ($option['type'] != 'file') {
					$value = $option['value'];
				} else {
					$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

					if ($upload_info) {
						$value = $upload_info['name'];
					} else {
						$value = '';
					}
				}

				$option_data[] = array(
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
					'type'  => $option['type']
				);
			}

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

				$price = $this->currency->format($unit_price, $this->session->data['currency']);
				$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);

				$regular_price_raw = $this->currency->format($product['regular_price'], $this->session->data['currency'], '', false);
				$price_raw = $this->currency->format($unit_price, $this->session->data['currency'], '', false);
				$total_raw = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency'], '', false);
			} else {
				$price = false;
				$total = false;
				$price_raw = false;
				$total_raw = false;
			}

			if ($regular_price_raw > $price_raw || $price_raw !== 0){
					$data['discount_percent'] = '-'. round(($regular_price_raw - $price_raw) / $regular_price_raw * 100) . '%';
				}	

			$data['products'][] = array(
				'cart_id'    => $product['cart_id'],
				'product_id' => $product['product_id'],
				'thumb'      => $image,
				'name'       => $product['name'],
				'model'      => $product['model'],
				'option'     => $option_data,
				'recurring'  => ($product['recurring'] ? $product['recurring']['name'] : ''),
				'quantity'   => $product['quantity'],
				'minimum'    => $product['minimum'],
				'price'      => $price,
				'regular_price_raw' => $regular_price_raw,
				'total'      => $total,
				'price_raw'  => $price_raw,
				'total_raw'  => $total_raw,
				'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
			);
		}

		// Gift Voucher
		$data['vouchers'] = array();

		if (!$filter_product_id && !empty($this->session->data['vouchers'])) {
			foreach ($this->session->data['vouchers'] as $key => $voucher) {
				$data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
				);
			}
		}

		$data['cart'] = $this->url->link('checkout/cart');
		$data['checkout'] = $this->url->link('checkout/checkout', '', true);

		$data['currency_code'] = $this->session->data['currency'];

		$data['text_empty'] = $this->language->get('text_empty');
		$data['text_items'] = $this->cart->countProducts();

		$data['popup_add_to_cart']      = $this->language->get('popup_add_to_cart');
		$data['go_to_cart']             = $this->language->get('go_to_cart');
		$data['cart_continue_shopping'] = $this->language->get('cart_continue_shopping');
		$data['popup_cart_remove']      = $this->language->get('popup_cart_remove');

		$this->response->setOutput($this->load->view('common/popup_cart', $data));
	}

	public function update() {
		$json = array();

		if (isset($this->request->post['key']) && isset($this->request->post['quantity'])) {
			$key = $this->request->post['key'];
			$quantity = (int)$this->request->post['quantity'];

			if ($quantity > 0) {
				$this->cart->update($key, $quantity);
			} else {
				$this->cart->remove($key);
			}

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$json['success'] = true;
			$json['total'] = $this->cart->countProducts();
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
