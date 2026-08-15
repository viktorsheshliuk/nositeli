<?php
// catalog/controller/product/thumb.php

class ControllerProductThumb extends Controller
{

	/**
	 * Масова обробка товарів для оптимізації
	 */
	public function batch(array $products, array $setting = [])
	{
		if (empty($products)) {
			return [];
		}

		$this->load->language('product/thumb');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

		// Завантажуємо MetaTemplate для шаблонів alt/title зображень
		//if (!class_exists('MetaTemplate')) {
		//	require_once(DIR_SYSTEM . 'library/metatemplate.php');
		//}

		// Налаштування за замовчуванням
		$setting = array_merge([
			'template' => 'default',
			'return_data' => true,
			'load_images' => true,
			'load_attributes' => false, //true,
			'image_limit' => 7
		], $setting);

		// Отримуємо ID товарів
		$product_ids = array_column($products, 'product_id');

		// Масово завантажуємо додаткові дані
		$batch_data = $this->loadBatchData($product_ids, $setting);

		$results = [];

		foreach ($products as $product) {
			$product_id = $product['product_id'];
			$data = $this->prepareProductData($product, $batch_data, $setting);

			if ($setting['return_data']) {
				$results[] = $data;
			} else {
				$results[] = $this->load->view('product/thumb/' . $setting['template'], $data);
			}
		}

		return $results;
	}

	/**
	 * Оригінальний метод для сумісності
	 */
	public function index(array $setting)
	{
		if (isset($setting['products']) && is_array($setting['products'])) {
			// Якщо передано масив товарів - використовуємо batch обробку
			return $this->batch($setting['products'], $setting);
		}

		// Оригінальна логіка для одного товару
		return $this->processSingleProduct($setting);
	}

	/**
	 * Масове завантаження допоміжних даних
	 */
	private function loadBatchData(array $product_ids, array $setting)
	{
		$batch_data = [
			'images' => [],
			'attributes' => [],
			//'credits' => [],
			'config' => $this->getConfigData(),
			'category_id' => isset($setting['category_id']) ? (int)$setting['category_id'] : 0
		];

		if ($setting['load_images']) {
			$batch_data['images'] = $this->loadBatchImages($product_ids, $setting['image_limit']);
		}

		if ($setting['load_attributes']) {
			$batch_data['attributes'] = $this->loadBatchAttributes($product_ids);
		}

		return $batch_data;
	}

	/**
	 * Масове завантаження зображень
	 */
	private function loadBatchImages(array $product_ids, int $limit = 7)
	{
		$images = [];

		$query = $this->db->query("
            SELECT product_id, image, sort_order 
            FROM " . DB_PREFIX . "product_image 
            WHERE product_id IN (" . implode(',', array_map('intval', $product_ids)) . ") 
            ORDER BY product_id, sort_order
        ");

		foreach ($query->rows as $row) {
			if (!isset($images[$row['product_id']])) {
				$images[$row['product_id']] = [];
			}

			if (count($images[$row['product_id']]) < $limit) {
				$images[$row['product_id']][] = $row['image'];
			}
		}

		return $images;
	}

	/**
	 * Масове завантаження атрибутів
	 */
	private function loadBatchAttributes(array $product_ids)
	{
		$attributes = [];
		$catalog_attributes = $this->config->get('theme_' . $this->config->get('config_theme') . '_catalog_attribute');

		if (!$catalog_attributes) {
			return $attributes;
		}

		$attribute_ids = implode(',', array_map('intval', array_keys($catalog_attributes)));
		
		// Отримуємо поточний language_id з конфігурації (він оновлюється в startup.php)
		$language_id = (int) $this->config->get('config_language_id');

		$query = $this->db->query("
            SELECT pa.product_id, pa.attribute_id, pa.text, ad.name
            FROM " . DB_PREFIX . "product_attribute pa
            LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id)
            WHERE pa.product_id IN (" . implode(',', array_map('intval', $product_ids)) . ")
            AND pa.attribute_id IN (" . $attribute_ids . ")
            AND pa.language_id = '" . $language_id . "'
            AND ad.language_id = '" . $language_id . "'
        ");

		foreach ($query->rows as $row) {
			if (!isset($attributes[$row['product_id']])) {
				$attributes[$row['product_id']] = [];
			}

			$attributes[$row['product_id']][$row['attribute_id']] = [
				'attribute_id' => $row['attribute_id'],
				'name' => $row['name'],
				'text' => $row['text'],
				'image' => $this->model_tool_image->resize($catalog_attributes[$row['attribute_id']], 50, 50)
			];
		}

		return $attributes;
	}

	/**
	 * Підготовка даних для одного товару
	 */
	private function prepareProductData(array $product, array $batch_data, array $setting)
	{
		$product_id = $product['product_id'];

		$data = [
			'compare_id' => isset($product['compare_id']) ? $product['compare_id'] : 0,
			'wishlist_id' => isset($product['wishlist_id']) ? $product['wishlist_id'] : 0,
			'product_id' => $product_id,
			'model' => $product['model'],
			'name' => htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'),
			'minimum' => $product['minimum'] > 0 ? $product['minimum'] : 1,
			'stock_status_id' => $product['stock_status_id'],
			'stock_status' => $product['stock_status'],
			'stock' => $product['quantity'] > 0,
			'quantity' => $product['quantity'] > 0 ? $product['quantity'] : 0,
			'image_width' => (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
			'image_height' => (int) $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'),
		];

		// Зображення
		$data['image'] = $this->getProductMainImage($product);
		$data['images'] = $this->getProductImages($product_id, $batch_data['images']);

		// Alt та Title атрибути для зображень (preview - перше зображення)
		//$this->setImageAltTitle($data, $product, $batch_data);

		// Ціни
		$this->setProductPrices($data, $product, $batch_data['config']);

		// Рейтинг та відгуки
		//$this->setProductRating($data, $product, $batch_data['config']);

		// Атрибути
		//$data['attributes'] = isset($batch_data['attributes'][$product_id])
		//	? $batch_data['attributes'][$product_id]
		//	: [];

		// Додаткові дані
		$this->setAdditionalData($data, $product, $batch_data['config']);

		return $data;
	}

	/**
	 * Отримання основного зображення товару
	 */
	private function getProductMainImage(array $product)
	{
		$width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
		$height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

		if ($product['image']) {
			return $this->model_tool_image->resize($product['image'], $width, $height);
		} else {
			return $this->model_tool_image->resize('placeholder.png', $width, $height);
		}
	}

	/**
	 * Отримання додаткових зображень товару
	 */
	private function getProductImages(int $product_id, array $batch_images)
	{
		$images = [];

		if (isset($batch_images[$product_id])) {
			$width = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width');
			$height = $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height');

			foreach ($batch_images[$product_id] as $image) {
				$images[] = $this->model_tool_image->resize($image, $width, $height);
			}
		}

		return $images;
	}

	/**
	 * Встановлення Alt та Title атрибутів для зображень товару
	 */
	private function setImageAltTitle(array &$data, array $product, array $batch_data)
	{
		// Перше зображення (preview) - номер 1
		$category_id = $batch_data['category_id'];
		
		$metaTemplate = new MetaTemplate($this->registry);
		
		// Отримуємо шаблони через ієрархію категорій (тільки для першого зображення)
		$alt_preview_template = $metaTemplate->getImageTemplateHierarchy($category_id, 'alt_preview');
		$title_preview_template = $metaTemplate->getImageTemplateHierarchy($category_id, 'title_preview');
		
		// Застосовуємо шаблони тільки для першого зображення (preview)
		$data['thumb_alt'] = $metaTemplate->applyImageTemplate($alt_preview_template, $product, 1);
		$data['thumb_title'] = $metaTemplate->applyImageTemplate($title_preview_template, $product, 1);
	}

	/**
	 * Встановлення цін товару
	 */
	private function setProductPrices(array &$data, array $product, array $config)
	{
		if ($this->customer->isLogged() || !$config['customer_price']) {
			$data['price'] = $this->currency->format(
				$this->tax->calculate($product['price'], $product['tax_class_id'], $config['tax']),
				$this->session->data['currency']
			);
			$data['price_raw'] = $this->currency->format(
				$this->tax->calculate($product['price'], $product['tax_class_id'], $config['tax']),
				$this->session->data['currency'], '', false
			);
		} else {
			$data['price'] = false;
			$data['price_raw'] = false;
		}

		if (!is_null($product['special']) && (float) $product['special'] >= 0) {
			$data['special'] = $this->currency->format(
				$this->tax->calculate($product['special'], $product['tax_class_id'], $config['tax']),
				$this->session->data['currency']
			);
			$data['special_raw'] = $this->currency->format(
				$this->tax->calculate($product['special'], $product['tax_class_id'], $config['tax']),
				$this->session->data['currency'], '', false
			);
			$data['economy'] = round(100 - ($product['special'] / ($product['price'] / 100)));
			$tax_price = (float) $product['special'];
		} else {
			$data['special'] = false;
			$data['special_raw'] = false;
			$data['economy'] = false;
			$tax_price = (float) $product['price'];
		}

		if ($config['tax']) {
			$data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
		} else {
			$data['tax'] = false;
		}
	}

	/**
	 * Встановлення рейтингу товару
	 */
	private function setProductRating(array &$data, array $product, array $config)
	{
		if ($config['review_status']) {
			$data['rating'] = (int) $product['rating'];
			$data['reviews'] = sprintf(
				$this->language->get('text_reviews_declension')[word_declension($product['reviews'])],
				$product['reviews']
			);
		} else {
			$data['rating'] = false;
			$data['reviews'] = '';
		}

		$data['review_status'] = (int) $config['review_status'];
	}

	/**
	 * Встановлення додаткових даних
	 */
	private function setAdditionalData(array &$data, array $product, array $config)
	{
		// Попопередні замовлення та доступність
		//$preorder_data = $this->config->get('artilab_popup_preorder_data');
		//$data['preorder'] = ($preorder_data && $preorder_data['status']) ? $preorder_data : [];

		//$availible_data = $this->config->get('artilab_popup_availible_data');
		//$data['availible'] = ($availible_data && $availible_data['status']) ? $availible_data : [];

		// Посилання на товар
		$data['href'] = $this->url->link('product/product', 'product_id=' . $product['product_id']);
	}

	/**
	 * Отримання конфігураційних даних
	 */
	private function getConfigData()
	{
		return [
			'customer_price' => $this->config->get('config_customer_price'),
			'tax' => $this->config->get('config_tax'),
			'review_status' => $this->config->get('config_review_status')
		];
	}

	/**
	 * Обробка одного товару (оригінальна логіка)
	 */
	private function processSingleProduct(array $setting)
	{
		// Тут залишається оригінальна логіка для сумісності
		// Можна перенести сюди існуючий код методу index

		if (empty($setting['template'])) {
			$setting['template'] = 'default';
		}

		$product = $setting['product'];
		$batch_data = $this->loadBatchData([$product['product_id']], $setting);
		$data = $this->prepareProductData($product, $batch_data, $setting);

		return $this->load->view('product/thumb/' . $setting['template'], $data);
	}
}