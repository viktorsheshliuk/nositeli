<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

/**
 * Класс YML экспорта
 * YML (Yandex Market Language) - стандарт, разработанный "Яндексом"
 * для принятия и публикации информации в базе данных Яндекс.Маркет
 * YML основан на стандарте XML (Extensible Markup Language)
 * описание формата YML http://partner.market.yandex.ru/legal/tt/
 */
class ControllerExtensionFeedProm extends Controller {
	private $shop = array();
	private $currencies = array();
	private $categories = array();
	private $offers = array();
	private $from_charset = 'utf-8';
	private $eol = "\n";
	public $array_fotoalbums_category_id = array(52,53,54); // id категорий фотоальбомов
	public $array_flash_category_id = array(16,17,18,19,20,21,59,60,61,62,63,64); // id категорий флешек и карт памяти
	public function index() {
		if ($this->config->get('feed_prom_status')) {

			// Язык фида

			$code = 'ru-ru'; //язык по умолчанию

			if (!isset($this->session->data['language']) || $this->session->data['language'] != $code) {
				$this->session->data['language'] = $code;
			}
					
			if (!isset($this->request->cookie['language']) || $this->request->cookie['language'] != $code) {
				setcookie('language', $code, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
			}
			$language = new Language($code);

			$language->load($code);
			
			$this->registry->set('language', $language);
			
			// Set the config language_id
			$this->load->model('localisation/language');
		
			$languages = $this->model_localisation_language->getLanguages();
			
			$this->config->set('config_language_id', $languages[$code]['language_id']);	


			// Защитный ключ
			$secret_key = $this->config->get('feed_prom_secret_key');

			if ($secret_key) {
				if (isset($this->request->get['secret_key'])) {
					if ($this->request->get['secret_key'] != $secret_key) exit();
				} else {
					exit();
				}
			}

			// Выборка категорий и производителей
			$allowed_categories = $this->config->get('feed_prom_categories');
			$allowed_manufacturers = $this->config->get('feed_prom_manufacturers');

			//if (!$allowed_categories && !$allowed_manufacturers) exit();

			$this->load->model('extension/feed/prom');
			$this->load->model('localisation/currency');
			$this->load->model('tool/image');
			$this->load->model('catalog/product');

			// Магазин
			$this->setShop('name', $this->config->get('feed_prom_shopname'));
			$this->setShop('company', $this->config->get('feed_prom_company'));
			$this->setShop('url', HTTP_SERVER);
			$this->setShop('phone', $this->config->get('config_telephone'));
			$this->setShop('platform', 'OCSTORE.COM');
			$this->setShop('version', VERSION);

			// Валюты
			// TODO: Добавить возможность настраивать проценты в админке.
			$offers_currency = $this->config->get('feed_prom_currency');
			if (!$this->currency->has($offers_currency)) exit();

			$decimal_place = $this->currency->getDecimalPlace($offers_currency);

			$shop_currency = $this->config->get('config_currency');

			$this->setCurrency($offers_currency, 1);

			$currencies = $this->model_localisation_currency->getCurrencies();

			$supported_currencies = array('RUR', 'RUB', 'USD', 'BYR', 'KZT', 'EUR', 'UAH');

			$currencies = array_intersect_key($currencies, array_flip($supported_currencies));

			foreach ($currencies as $currency) {
				if ($currency['code'] != $offers_currency && $currency['status'] == 1) {
					$this->setCurrency($currency['code'], number_format(1/$this->currency->convert($currency['value'], $offers_currency, $shop_currency), 4, '.', ''));
				}
			}

			// Категории
			$categories = $this->model_extension_feed_prom->getCategory();

			// foreach ($categories as $category) {
			// 	$this->setCategory(trim($category['name']), $category['category_id'], $category['parent_id']);
			// }
			foreach ($categories as $category) {
				$portal_id = 1;
				switch ($category['category_id']) {
					case 11:
						$portal_id = 1905;
						break;
					case 52:
						$portal_id = 1905;
						break;
					case 53:
						$portal_id = 1905;
						break;
					case 54:
						$portal_id = 1905;
						break;	
				}
				$this->setCategory(trim($category['name']), $category['category_id'], $category['parent_id'], $portal_id);
			}

			// Товарные предложения
			$bus_id = $this->config->get('feed_prom_id'); // Идентификатор товара - "id"
			$bus_type = $this->config->get('feed_prom_type'); // Тип предложений - "type"
			$bus_name = $this->config->get('feed_prom_name'); // Название товара - "name"
			$bus_model = $this->config->get('feed_prom_model'); // Код товара - "model"
			$bus_vendorCode = $this->config->get('feed_prom_vendorCode'); // Артикул товара - "vendorCode"

			$bus_image = $this->config->get('feed_prom_image'); // Статус товара без изображений
			$bus_image_width = $this->config->get('feed_prom_image_width'); // Ширина изображения товара
			$bus_image_height = $this->config->get('feed_prom_image_height'); // Высота изображения товара
			$bus_image_quantity = $this->config->get('feed_prom_image_quantity'); // Количество изображений товара

			$bus_price_nacenka = $this->config->get('feed_prom_price_nacenka'); // Наценка для выгружаемых цен

			$bus_main_category = $this->config->get('feed_prom_main_category'); // Статус товара без главной категории

			$in_stock_id = $this->config->get('feed_prom_in_stock'); // id статуса товара "В наличии"
			$out_of_stock_id = $this->config->get('feed_prom_out_of_stock'); // id статуса товара "Нет на складе"
			$bus_quantity_status = $this->config->get('feed_prom_quantity_status'); // Статус товара "количество равное 0"

			$vendor_required = false; // true - только товары у которых задан производитель, необходимо для 'vendor.model'

			$products = $this->model_extension_feed_prom->getProduct($allowed_categories, $allowed_manufacturers, $out_of_stock_id, $vendor_required, $bus_image, $bus_image_quantity, $bus_main_category, $bus_quantity_status);
			$products_ua = $this->model_extension_feed_prom->getProduct_UA($allowed_categories, $allowed_manufacturers, $out_of_stock_id, $vendor_required, $bus_image, $bus_image_quantity, $bus_main_category, $bus_quantity_status);
//echo '<pre>';var_dump($products[396]);echo '</pre>';
//echo '<pre>';var_dump($products_ua[396]);echo '</pre>';
			//добавляем украинские описание, названия и т.д.
			foreach($products as $key=>$pr1){
			    foreach($products_ua as $pr2){
			        if($pr1['product_id']==$pr2['product_id']){
			            $products[$key]['description_ua'] = $pr2['description'];
			            $products[$key]['name_ua'] = $pr2['name'];
			        }
			    }
			}  
//echo '<pre>';var_dump($products);echo '</pre>';die();

			foreach ($products as $product) {
				$data = array();

				// Атрибуты товарного предложения
				if (!empty($product[$bus_id])) {
					$data['id'] = $product[$bus_id];
				} else {
					$data['id'] = $product['product_id'];
				}
//				$data['type'] = $bus_type;
//				$data['type'] = 'vendor.model';
				$data['available'] = true;//($product['quantity'] > 0 || $product['stock_status_id'] == $in_stock_id);
				$data['in_stock'] = ($product['quantity'] > 0 );
				$data['quantity_in_stock'] = ($product['quantity'] <= 0) ? 0 : $product['quantity'];
//				$data['bid'] = 10;
//				$data['cbid'] = 15;

				// Параметры товарного предложения
			
				$data['url'] = $this->url->link('product/product', 'path=' . $this->getPath($product['category_id']) . '&product_id=' . $product['product_id']);
				$data['price'] = number_format($this->currency->convert($this->tax->calculate($product['price']* $bus_price_nacenka, $product['tax_class_id']), $shop_currency, $offers_currency), $decimal_place, '.', '');
				if ($data['price'] > 49){ 
					$data['price'] = round($data['price']);
				} else {
					$data['price'] = round($data['price'],1);
				}

				$data['clear_price'] = number_format($this->currency->convert($this->tax->calculate($product['price'], $product['tax_class_id']), $shop_currency, $offers_currency), $decimal_place, '.', '');

				$data['ean'] = $product['ean'];

				$data['currencyId'] = $offers_currency;
				$data['categoryId'] = $product['category_id'];
				$data['delivery'] = 'true';
//				$data['local_delivery_cost'] = 100;
				if (!empty($product[$bus_name])) {
					$data['name'] = $product[$bus_name];
				} else {
					$data['name'] = $product['name'];
				}
				//if (!empty($product[$bus_name])) {
				//	$data['name_ua'] = $product[$bus_name];
				//} else {
					$data['name_ua'] = $product['name_ua'];
				//}
				if (!empty($product['manufacturer'])) {
					if($product['manufacturer'] == 'Foto'){
						$data['vendor'] = 'Nobrand';
					} else {
						$data['vendor'] = $product['manufacturer'];
					}
					} else {
					$data['vendor'] = '';
				}
				if (!empty($product[$bus_model])) {
					//$data['vendorCode'] = $product[$bus_vendorCode];
					$data['vendorCode'] = $product[$bus_model];
				} else {
					$data['vendorCode'] = '';
				}
				if (!empty($product[$bus_model])) {
					$data['model'] = $product[$bus_model];
				} else {
					$data['model'] = '';
				}
				if (!empty($product['description'])) {
					$data['description'] = $product['description'];
				} else {
					$data['description'] = '';
				}
				if (!empty($product['description_ua'])) {
					$data['description_ua'] = $product['description_ua'];
				} else {
					$data['description_ua'] = '';
				}
//				$data['manufacturer_warranty'] = 'true';
//				$data['barcode'] = $product['sku'];

				if (!empty($product['image'])) {
					$data['picture'] = $this->model_tool_image->resize_old($product['image'], $bus_image_width, $bus_image_height);
				}

				if (isset($product['images'])) {
					foreach (explode(',', $product['images']) as $image) {
						$data['picture'] .= ',' . $this->model_tool_image->resize_old($image, $bus_image_width, $bus_image_height);
					}
				}
				$data['selling_type'] ='u';

				$data['keywords'] = $data['name'].','.$data['vendor'].','.$product['ean'];	
				if (in_array($data['categoryId'],$this->array_fotoalbums_category_id)){
					$data['keywords'] .=',Фотоальбомы для фотографий,Фотоальбом,Альбом для фотографий,Самые красивые фотоальбомы,Фотоальбомы для фото,Семейные фотоальбомы,Красивый фотоальбом подарок';	
				}
				$data['keywords_ua'] = $data['name'].','.$data['vendor'].','.$product['ean'];	
				if (in_array($data['categoryId'],$this->array_fotoalbums_category_id)){
					$data['keywords_ua'] .=',Фотоальбоми для фотографій, Фотоальбом, Альбом для фотографій, Найкрасивіші фотоальбоми, Фотоальбоми для фото, Сімейні фотоальбоми, Красивий фотоальбом подарунок';	
				}
				//скидки
				$data['discounts']= array();
				foreach ($this->model_catalog_product->getProductDiscounts($product['product_id']) as $disc) {
					$data['discounts'][] = array(
						'quantity' => $disc['quantity'], 
						'price'    => $disc['price'] * $bus_price_nacenka
					);
				   
				}	//var_dump($data['discounts']);
				if(empty($data['discounts'])){
					unset($data['discounts']);
				}
/*				
				// пример структуры массива для вывода параметров
				$data['param'] = array(
					array(
						'name'=>'Wi-Fi',
						'value'=>'есть'
					), array(
						'name'=>'Размер экрана',
						'unit'=>'дюйм',
						'value'=>'20'
					), array(
						'name'=>'Вес',
						'unit'=>'кг',
						'value'=>'4.6'
					)
				);
*/
				$data['param'] = $this->model_catalog_product->getProductAttributesForYML($product['product_id']);
				$data['param'][] = array(
					'name' => 'Состояние',
					'value' => 'Новое'
				); 
				if (isset($product['ean'])){
					$data['param'][] = array(
						'name' => 'Штрих-код',
						'value' => $product['ean']
					); 
				}	

				$tmp = explode(' ', $data['name']);
				if (($tmp[0] == 'Альбом')||($tmp[0] == 'Фотоальбом')){
					$data['param'][] = array(
						'name' => 'Тип',
						'value' => 'Фотоальбом'
					); 
				}
				if(($data['vendor'] == 'Evg')||($data['vendor'] == 'Chako')||($data['vendor'] == 'Ufo')||($data['vendor'] == 'BookBound')||($data['vendor'] == 'Nobrand')||($data['vendor'] == 'Poldom')||($data['vendor'] == 'Gedeon')){
					$data['param'][] = array(
						'name' => 'Страна производитель',
						'value' => 'Китай'
					); 
				}
				foreach ($data['param'] as $key_param => $tmp_param ) {
					if (($tmp_param['name'] == 'Количество страниц') && ($tmp_param['value'] != '')){
						$data['param'][] = array(
						'name' => 'Количество листов',
						'value' =>  $tmp_param['value'] /2 
					);  	
					}
					if ($tmp_param['name'] == 'Тип крепления фото'){
						$data['param'][$key_param]['name'] = 'Тип крепления';
						switch ($tmp_param['value']) {
							case 'С кармашками 10см*15см':
								$data['param'][$key_param]['value'] = 'Пленочный карман';
								//ключевые слова для категорий фотоальбомов
								$data['keywords'] .=',Фотоальбом с кармашками,Фотоальбом для фотографий 10*15';  
								break;
							case 'С кармашками 13см*18см':
								$data['param'][$key_param]['value'] = 'Пленочный карман';
								$data['keywords'] .=',Фотоальбом с кармашками, 13x18,13*18,Фотоальбомы 13х18'; 
								break;
							case 'С кармашками 15см*12см':
								$data['param'][$key_param]['value'] = 'Пленочный карман';
								$data['keywords'] .=',Фотоальбом с кармашками, 15x21,15*21'; 
								break;	
							case 'С магнитной плёнкой':
								$data['param'][$key_param]['value'] = 'Магнитные страницы';
								$data['keywords'] .=',Фотоальбом с магнитной пленкой, самоклеющийся фотоальбом,Самоклеющиеся фотоальбомы'; 
								break;
							case 'На уголки или скотч':
								$data['param'][$key_param]['value'] = 'Двухсторонний скотч';
								$data['keywords'] .=',традиционный фотоальбом , фотоальбом на уголки, фотоальбом на скотч,Фотоальбомы Традиционные'; 
								break;
						}
										  	
					}
					if ($tmp_param['name'] == 'Материал обложки'){
						//$data['param'][$key_param]['name'] = 'Тип крепления';
						switch ($tmp_param['value']) {
							case 'Кожзам':
								$data['param'][$key_param]['value'] = 'Искусственная кожа';
								break;
						}
										  	
					}
					
				}
				if (($product['quantity'] <= 0)&&(in_array($product['category_id'], $this->array_flash_category_id) )){  //не выыодим offer по флешкам если их нет в наличии
										continue;
					}

				$this->setOffer($data);
			}
		

			$this->categories = array_filter($this->categories, array($this, "filterCategory"));

			if (!$this->categories) exit();

			$this->response->addHeader('Content-Type: application/xml');
			$this->response->setOutput($this->getYml());
		}
	}

	/**
	 * Методы формирования YML
	 */

	/**
	 * Формирование массива для элемента shop описывающего магазин
	 *
	 * @param string $name - Название элемента
	 * @param string $value - Значение элемента
	 */
	private function setShop($name, $value) {
		$allowed = array('name', 'company', 'url', 'phone', 'platform', 'version', 'agency', 'email');
		if (in_array($name, $allowed)) {
			$this->shop[$name] = $this->prepareField($value);
		}
	}

	/**
	 * Валюты
	 *
	 * @param string $id - код валюты (RUR, RUB, USD, BYR, KZT, EUR, UAH)
	 * @param float|string $rate - курс этой валюты к валюте, взятой за единицу.
	 *	Параметр rate может иметь так же следующие значения:
	 *		CBRF - курс по Центральному банку РФ.
	 *		NBU - курс по Национальному банку Украины.
	 *		NBK - курс по Национальному банку Казахстана.
	 *		СВ - курс по банку той страны, к которой относится интернет-магазин
	 * 		по Своему региону, указанному в Партнерском интерфейсе Яндекс.Маркета.
	 * @param float $plus - используется только в случае rate = CBRF, NBU, NBK или СВ
	 *		и означает на сколько увеличить курс в процентах от курса выбранного банка
	 * @return bool
	 */
	private function setCurrency($id, $rate = 'CBRF', $plus = 0) {
		$allow_id = array('RUR', 'RUB', 'USD', 'BYR', 'KZT', 'EUR', 'UAH');
		if (!in_array($id, $allow_id)) {
			return false;
		}
		$allow_rate = array('CBRF', 'NBU', 'NBK', 'CB');
		if (in_array($rate, $allow_rate)) {
			$plus = str_replace(',', '.', $plus);
			if (is_numeric($plus) && $plus > 0) {
				$this->currencies[] = array(
					'id'=>$this->prepareField(strtoupper($id)),
					'rate'=>$rate,
					'plus'=>(float)$plus
				);
			} else {
				$this->currencies[] = array(
					'id'=>$this->prepareField(strtoupper($id)),
					'rate'=>$rate
				);
			}
		} else {
			$rate = str_replace(',', '.', $rate);
			if (!(is_numeric($rate) && $rate > 0)) {
				return false;
			}
			$this->currencies[] = array(
				'id'=>$this->prepareField(strtoupper($id)),
				'rate'=>(float)$rate
			);
		}

		return true;
	}

	/**
	 * Категории товаров
	 *
	 * @param string $name - название рубрики
	 * @param int $id - id рубрики
	 * @param int $parent_id - id родительской рубрики
	 * @return bool
	 */
	private function setCategory($name, $id, $parent_id = 0, $portal_id) {
		$id = (int)$id;
		if ($id < 1 || trim($name) == '') {
			return false;
		}
		if ((int)$parent_id > 0) {
			$this->categories[$id] = array(
				'id'=>$id,
				'parentId'=>(int)$parent_id,
				'name'=>$this->prepareField($name),
				'portal_id'=>(int)$portal_id
			);
		} else {
			$this->categories[$id] = array(
				'id'=>$id,
				'name'=>$this->prepareField($name),
				'portal_id'=>(int)$portal_id
			);
		}

		return true;
	}

	/**
	 * Товарные предложения
	 *
	 * @param array $data - массив параметров товарного предложения
	 */
	private function setOffer($data) {
		$offer = array();

		$attributes = array('id', 'type', 'available', 'bid', 'cbid', 'param','selling_type','discounts', 'in_stock');
		$attributes = array_intersect_key($data, array_flip($attributes));

		foreach ($attributes as $key => $value) {
			switch ($key)
			{
				case 'id':
				case 'bid':
				case 'cbid':
					$value = (int)$value;
					if ($value > 0) {
						$offer[$key] = $value;
					}
					break;

				case 'type':
					if (in_array($value, array('vendor.model', 'book', 'audiobook', 'artist.title', 'tour', 'ticket', 'event-ticket'))) {
						$offer['type'] = $value;
					}
					break;

				case 'available':
				 	$offer['available'] = ($value ? 'true' : 'false');
				 	break;

				case 'in_stock':
				 	$offer['in_stock'] = ($value ? 'true' : 'false');
				 	break; 	

				case 'selling_type':
					$offer['selling_type'] = 'r';
					break;	
				
				case 'param':
					if (is_array($value)) {
						$offer['param'] = $value;
					}
					break;
				
				 case 'discounts':
				 		if (is_array($value)) {
				 			$offer['discounts'] = $value;
							$offer['selling_type'] = 'u';
				 		}
				 	break;	

				default:
					break;
			}
		}

		$type = isset($offer['type']) ? $offer['type'] : '';

		$allowed_tags = array('buyurl'=>0, 'price'=>1, 'wprice'=>0, 'currencyId'=>1, 'xCategory'=>0, 'categoryId'=>1, 'picture'=>0,  'store'=>0, 'pickup'=>0, 'delivery'=>0, 'deliveryIncluded'=>0, 'local_delivery_cost'=>0, 'orderingTime'=>0);

		switch ($type) {
			case 'vendor.model':
				$allowed_tags = array_merge($allowed_tags, array('typePrefix'=>0, 'vendor'=>1, 'vendorCode'=>0, 'model'=>1, 'provider'=>0, 'tarifplan'=>0));
				break;

			case 'book':
				$allowed_tags = array_merge($allowed_tags, array('author'=>0, 'name'=>1, 'publisher'=>0, 'series'=>0, 'year'=>0, 'ISBN'=>0, 'volume'=>0, 'part'=>0, 'language'=>0, 'binding'=>0, 'page_extent'=>0, 'table_of_contents'=>0));
				break;

			case 'audiobook':
				$allowed_tags = array_merge($allowed_tags, array('author'=>0, 'name'=>1, 'publisher'=>0, 'series'=>0, 'year'=>0, 'ISBN'=>0, 'volume'=>0, 'part'=>0, 'language'=>0, 'table_of_contents'=>0, 'performed_by'=>0, 'performance_type'=>0, 'storage'=>0, 'format'=>0, 'recording_length'=>0));
				break;

			case 'artist.title':
				$allowed_tags = array_merge($allowed_tags, array('artist'=>0, 'title'=>1, 'year'=>0, 'media'=>0, 'starring'=>0, 'director'=>0, 'originalName'=>0, 'country'=>0));
				break;

			case 'tour':
				$allowed_tags = array_merge($allowed_tags, array('worldRegion'=>0, 'country'=>0, 'region'=>0, 'days'=>1, 'dataTour'=>0, 'name'=>1, 'hotel_stars'=>0, 'room'=>0, 'meal'=>0, 'included'=>1, 'transport'=>1, 'price_min'=>0, 'price_max'=>0, 'options'=>0));
				break;

			case 'event-ticket':
				$allowed_tags = array_merge($allowed_tags, array('name'=>1, 'place'=>1, 'hall'=>0, 'hall_part'=>0, 'date'=>1, 'is_premiere'=>0, 'is_kids'=>0));
				break;

			default:
				//$allowed_tags = array_merge($allowed_tags, array('name'=>1, 'vendor'=>0, 'vendorCode'=>0, 'model'=>1, 'available'=>0, 'keywords'=>0));
			$allowed_tags = array_merge($allowed_tags, array('name'=>1, 'name_ua'=>0, 'vendor'=>0, 'vendorCode'=>0, 'model'=>1, 'keywords'=>0 ,'keywords_ua'=>0, 'ean'=>0,'clear_price'=>0,'quantity_in_stock'=>0));
				break;
		}

		$allowed_tags = array_merge($allowed_tags, array('aliases'=>0, 'additional'=>0, 'description'=>0, 'description_ua'=>0, 'sales_notes'=>0, 'promo'=>0, 'manufacturer_warranty'=>0, 'country_of_origin'=>0, 'downloadable'=>0, 'adult'=>0, 'barcode'=>0));

		$required_tags = array_filter($allowed_tags);

		if (sizeof(array_intersect_key($data, $required_tags)) != sizeof($required_tags)) {
			return;
		}

		$data = array_intersect_key($data, $allowed_tags);
//		if (isset($data['tarifplan']) && !isset($data['provider'])) {
//			unset($data['tarifplan']);
//		}

		$allowed_tags = array_intersect_key($allowed_tags, $data);

		// Стандарт XML учитывает порядок следования элементов,
		// поэтому важно соблюдать его в соответствии с порядком описанным в DTD
		$offer['data'] = array();
		foreach ($allowed_tags as $key => $value) {
			$offer['data'][$key] = $this->prepareField($data[$key]);
		}

		$this->offers[] = $offer;
	}

	/**
	 * Формирование YML файла
	 *
	 * @return string
	 */
	private function getYml() {
		$yml  = '<?xml version="1.0" encoding="windows-1251"?>' . $this->eol;
		$yml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . $this->eol;
		$currentTime = time();
		$hoursToAdd = 3;
		$secondsToAdd = $hoursToAdd * (60 * 60);
		$newTime = $currentTime + $secondsToAdd;
		$yml .= '<yml_catalog date="' . date('Y-m-d H:i', $newTime) . '">' . $this->eol;
		$yml .= '<shop>' . $this->eol;

		// информация о магазине
		$yml .= $this->array2Tag($this->shop);

		// валюты
		$yml .= '<currencies>' . $this->eol;
		foreach ($this->currencies as $currency) {
			$yml .= $this->getElement($currency, 'currency');
		}
		$yml .= '</currencies>' . $this->eol;

		// категории
		$yml .= '<categories>' . $this->eol;
		foreach ($this->categories as $category) {
			$category_name = $category['name'];
			unset($category['name'], $category['export']);
			$yml .= $this->getElement($category, 'category', $category_name);
		}
		$yml .= '</categories>' . $this->eol;

		// товарные предложения
		$yml .= '<offers>' . $this->eol;
		foreach ($this->offers as $offer) {
			$tags = $this->array2Tag($offer['data']);
			unset($offer['data']);
			if (isset($offer['param'])) {
				//var_dump($offer['param']);
				$tags .= $this->array2Param($offer['param']);
				//var_dump($this->array2Param($offer['param']));
				unset($offer['param']);
				
			}
			if (isset($offer['discounts'])) {
				//	var_dump($offer['discounts']);
				$tags .= $this->array2ParamDiscounts($offer['discounts']);
				//var_dump($this->array2Param($offer['discounts']));
				//print_r($tags);
				unset($offer['discounts']);
			}
			$yml .= $this->getElement($offer, 'offer', $tags);
		}
		$yml .= '</offers>' . $this->eol;

		$yml .= '</shop>';
		$yml .= '</yml_catalog>';

		return $yml;
	}

	/**
	 * Фрмирование элемента
	 *
	 * @param array $attributes
	 * @param string $element_name
	 * @param string $element_value
	 * @return string
	 */
	private function getElement($attributes, $element_name, $element_value = '') {
		$retval = '<' . $element_name . ' ';
		foreach ($attributes as $key => $value) {
			$retval .= $key . '="' . $value . '" ';
		}
		$retval .= $element_value ? '>' . $this->eol . $element_value . '</' . $element_name . '>' : '/>';
		$retval .= $this->eol;

		return $retval;
	}

	/**
	 * Преобразование массива в теги
	 *
	 * @param array $tags
	 * @return string
	 */
	private function array2Tag($tags) {
		$retval = '';
		foreach ($tags as $key => $value) {
			if ($key == 'picture') {
				foreach (explode(',', $value) as $val) {
					$retval .= '<image>' . $val . '</image>' . $this->eol;
				}
			} elseif (($key == 'description') || ($key == 'description_ua')) {
				$retval .= '<' . $key . '><![CDATA[' . substr($value, 0, 3000) . ']]></' . $key . '>' . $this->eol;
				//﻿
			} elseif ($key == 'available'){
				if ($value == 1) {$out ='true';} else { $out ='false';}
				$retval .= '<' . $key . '>' . $out . '</' . $key . '>' . $this->eol;
			}  else {
				$retval .= '<' . $key . '>' . $value . '</' . $key . '>' . $this->eol;
			}
		}

		return $retval;
	}

	/**
	 * Преобразование массива в теги параметров
	 *
	 * @param array $params
	 * @return string
	 */
	private function array2Param($params) {
		$retval = '';
		foreach ($params as $param) {
		//	var_dump($param['value']);var_dump($param['name']);
			$retval .= '<param name="' . $this->prepareField($param['name']);
			if (isset($param['unit'])) {
				$retval .= '" unit="' . $this->prepareField($param['unit']);
			}
			$retval .= '">' . $this->prepareField($param['value']) . '</param>' . $this->eol;
		}

		return $retval;
	}

		/**
	 * Преобразование массива в теги скидочных цен
	 *
	 * @param array $params
	 * @return string
	 */
	private function array2ParamDiscounts($params) {
		$retval = '';	
		$retval .='<prices>' . $this->eol;
		foreach ($params as $param) {
		//	var_dump($param['value']);var_dump($param['name']);
			$retval.= '<price>';
			$retval.= '<value>'. $param['price'] .'</value>'.$this->eol;
			$retval.= '<quantity>'. $param['quantity'] .'</quantity>'.$this->eol;
			$retval.= '</price>';
			//$retval .= '<param name="' . $this->prepareField($param['name']);
			//$retval .= '">' . $this->prepareField($param['value']) . '</param>' . $this->eol;
		
		}
		$retval .='</prices>' . $this->eol;
		return $retval;
	}

	/**
	 * Подготовка текстового поля в соответствии с требованиями Яндекса
	 * Запрещаем любые html-тэги, стандарт XML не допускает использования в текстовых данных
	 * непечатаемых символов с ASCII-кодами в диапазоне значений от 0 до 31 (за исключением
	 * символов с кодами 9, 10, 13 - табуляция, перевод строки, возврат каретки). Также этот
	 * стандарт требует обязательной замены некоторых символов на их символьные примитивы.
	 * @param string $text
	 * @return string
	 */
	private function prepareField($field) {
		//var_dump($field);
		$field = htmlspecialchars_decode($field);
		$field = strip_tags($field);
		$from = array('"', '&', '>', '<', '\'');
		$to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;');
		$field = str_replace($from, $to, $field);
		if ($this->from_charset != 'windows-1251') {
			$field = iconv($this->from_charset, 'windows-1251//TRANSLIT//IGNORE', $field);
		}
		$field = preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F]+#is', ' ', $field);

		return trim($field);
	}

	protected function getPath($category_id, $current_path = '') {
		if (isset($this->categories[$category_id])) {
			$this->categories[$category_id]['export'] = 1;

			if (!$current_path) {
				$new_path = $this->categories[$category_id]['id'];
			} else {
				$new_path = $this->categories[$category_id]['id'] . '_' . $current_path;
			}	

			if (isset($this->categories[$category_id]['parentId'])) {
				return $this->getPath($this->categories[$category_id]['parentId'], $new_path);
			} else {
				return $new_path;
			}

		}
	}

	function filterCategory($category) {
		return isset($category['export']);
	}
}