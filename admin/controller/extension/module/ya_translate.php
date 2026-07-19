<?php
class ControllerExtensionModuleYaTranslate extends Controller{
	private $error = array();
	private $path_module ='extension/module/ya_translate';
	private $module_name ='ya_translate';
	private $file_error_log = 'ya_translate.log';

	private $logger;

	public function __construct($registry) {
		parent::__construct($registry);
		$this->logger = new LOG($this->file_error_log);
	}

	public function index() {
		$data = $this->load->language($this->path_module);
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting($this->module_name, $this->request->post);		

			$this->session->data['success'] = $this->language->get('text_success');
			$this->install();
			if (isset($this->request->get['apply'])) {
				$this->response->redirect($this->makeUrl($this->path_module));
			} else {
				$this->response->redirect($this->makeUrl('marketplace/extension', 'type=module'));
			}
		}

		$data['text_edit']          = $this->language->get('text_edit');
		$data['text_enabled']       = $this->language->get('text_enabled');
		$data['text_disabled']      = $this->language->get('text_disabled');
		$data['text_language_from'] = $this->language->get('text_language_from');
		$data['text_language_to']   = $this->language->get('text_language_to');
		$data['text_test']          = $this->language->get('text_test');
		$data['text_warn']          = $this->language->get('text_warn');
		$data['entry_code_lang']    = $this->language->get('entry_code_lang');
		$data['entry_type']         = $this->language->get('entry_type');
		$data['text_type_help']     = $this->language->get('text_type_help');

		$data['types'] = array();
		$data['types']['even']   = $this->language->get('text_type_even');
		$data['types']['stream'] = $this->language->get('text_type_stream');

		$data['button_translate']   = $this->language->get('button_translate');
		$data['button_save']        = $this->language->get('button_save');
		$data['button_apply']       = $this->language->get('button_apply');
		$data['button_cancel']      = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->makeUrl('extension/module', 'type=module')
		);

		$data['breadcrumbs'][] = array(
			'text' => strip_tags($this->language->get('heading_title')),
			'href' => $this->makeUrl($this->path_module)
		);

		$data['action'] = $this->makeUrl($this->path_module);
		$data['apply'] = $this->makeUrl($this->path_module, '&apply');
		$data['check_api'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/checkApiKey'));
		$data['test_translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/test_translate'));
		$data['clearLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/clearLog'));
		$data['viewLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/viewLog'));

		$data['cancel'] = $this->makeUrl('marketplace/extension', 'type=module');

		if (isset($this->request->post[$this->module_name . '_api_key'])) {
			$data[$this->module_name . '_api_key'] = $this->request->post[$this->module_name . '_api_key'];
		} else {
			$data[$this->module_name . '_api_key'] = $this->config->get($this->module_name . '_api_key');
		}

		if (isset($this->request->post[$this->module_name . '_api_provider'])) {
			$data[$this->module_name . '_api_provider'] = $this->request->post[$this->module_name . '_api_provider'];
		} else {
			$data[$this->module_name . '_api_provider'] = $this->config->get($this->module_name . '_api_provider');
		}

		$data['api_providers'] = array(
			'*'    => " -- ",
			'API2' => "Yandex cloud",
			'API1' => "Yandex translate",
			'Google' => "Google translate - v2"
		);

		if (isset($this->request->post[$this->module_name . '_codelanguage'])) {
			$data[$this->module_name . '_codelanguage'] = $this->request->post[$this->module_name . '_codelanguage'];
		} else {
			$data[$this->module_name . '_codelanguage'] = $this->config->get($this->module_name . '_codelanguage');
		}

		if (isset($this->request->post[$this->module_name . '_type'])) {
			$data[$this->module_name . '_type'] = $this->request->post[$this->module_name . '_type'];
		} else {
			$data[$this->module_name . '_type'] = $this->config->get($this->module_name . '_type');
		}
		if (isset($this->request->post[$this->module_name . '_default_lang'])) {
			$data[$this->module_name . '_default_lang'] = $this->request->post[$this->module_name . '_default_lang'];
		} elseif ($this->config->has($this->module_name . '_default_lang')) {
			$data[$this->module_name . '_default_lang'] = $this->config->get($this->module_name . '_default_lang');
		} else {
			$data[$this->module_name . '_default_lang'] = $this->config->get('config_language_id');
		}
		$this->load->model('localisation/language');
		$data['config_admin_language'] = $this->config->get('config_admin_language');
		$languages = $this->getLanguages();
		$data['languages'] = array();
		foreach ($languages as $language) {
			$data['languages'][$language['language_id']] = $language;
			$data['languages'][$language['language_id']]['image'] = 'language/' . $language['code'] . '/' . $language['code'] . '.png';
		}

		$this->nav($this->path,$data);


		$sql = "SHOW TABLES LIKE '" . DB_PREFIX . "manufacturer_description'";
		$results = $this->db->query($sql);
		$data['manufacturer_description_fields'] = array();
		if ($results->num_rows) {
			$sql = "SHOW FIELDS FROM " . DB_PREFIX . "manufacturer_description WHERE `Type` like '%char%' or `Type` like '%text%' ";
			$results = $this->db->query($sql);
			foreach ($results->rows as $result) {
				$data['manufacturer_description_fields'][] = $result['Field'];
			}
		}
		
		if (isset($this->request->post[$this->module_name . '_manufacturer_description_field'])) {
			$data[$this->module_name . '_manufacturer_description_field'] = $this->request->post[$this->module_name . '_manufacturer_description_field'];
		} elseif ($this->config->has($this->module_name . '_manufacturer_description_field')) {
			$data[$this->module_name . '_manufacturer_description_field'] = $this->config->get($this->module_name . '_manufacturer_description_field');
		} else {
			$data[$this->module_name . '_manufacturer_description_field'] = array();
		}
		

		$sql = "SHOW FIELDS FROM " . DB_PREFIX . "product_description WHERE `Type` like '%char%' or `Type` like '%text%' ";
		$results = $this->db->query($sql);
		$data['product_description_fields'] = array();
		foreach ($results->rows as $result) {
			$data['product_description_fields'][] = $result['Field'];
		}

		$sql = "SHOW FIELDS FROM " . DB_PREFIX . "product_description WHERE `Type` like '%char%' or `Type` like '%text%' ";
		$results = $this->db->query($sql);
		$data['product_description_fields'] = array();
		foreach ($results->rows as $result) {
			$data['product_description_fields'][] = $result['Field'];
		}
		if (isset($this->request->post[$this->module_name . '_product_description_field'])) {
			$data[$this->module_name . '_product_description_field'] = $this->request->post[$this->module_name . '_product_description_field'];
		} elseif ($this->config->has($this->module_name . '_product_description_field')) {
			$data[$this->module_name . '_product_description_field'] = $this->config->get($this->module_name . '_product_description_field');
		} else {
			$data[$this->module_name . '_product_description_field'] = array();
		}

		$sql = "SHOW FIELDS FROM " . DB_PREFIX . "category_description WHERE `Type` like '%char%' or `Type` like '%text%' ";
		$results = $this->db->query($sql);
		$data['category_description_fields'] = array();
		foreach ($results->rows as $result) {
			$data['category_description_fields'][] = $result['Field'];
		}
		if (isset($this->request->post[$this->module_name . '_category_description_field'])) {
			$data[$this->module_name . '_category_description_field'] = $this->request->post[$this->module_name . '_category_description_field'];
		} elseif ($this->config->has($this->module_name . '_category_description_field')) {
			$data[$this->module_name . '_category_description_field'] = $this->config->get($this->module_name . '_category_description_field');
		} else {
			$data[$this->module_name . '_category_description_field'] = array();
		}
		$data['permission'] = $this->user->hasPermission('modify', $this->path_module);
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$this->response->setOutput($this->load->view($this->path_module, $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', $this->path_module)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}

	public function install() {
		$events = $this->getEvents();
		$this->load->model('setting/event');
		foreach ($events as $code=>$value) {
			$this->model_setting_event->deleteEventByCode($code);
			$this->model_setting_event->addEvent($code, $value['trigger'], $value['action'], 1);
		}
		$this->load->model($this->path_module);
		$my_model = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
		$my_model->install();
	}

	public function uninstall() {
		$events = $this->getEvents();
		$this->load->model('setting/event');
		foreach ($events as $code=>$value) {
			$this->model_setting_event->deleteEventByCode($code);
		}
		$this->load->model($this->path_module);
		$my_model = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
		$my_model->uninstall();

	}

	private function getEvents() {
		$events = array(
			'yat_product_formAfter' => array(
				'trigger' => 'admin/view/catalog/*_form/after',
				'action'  => 'extension/module/ya_translate/addFooter',
			),
			'yat_blog_formAfter' => array(
				'trigger' => 'admin/view/blog/*_form/after',
				'action'  => 'extension/module/ya_translate/addFooter',
			),
			'yat_batch_editor_descriptionAfter' => array(
				'trigger' => 'admin/view/batch_editor/catalog/description/after',
				'action'  => 'extension/module/ya_translate/addFooter',
			),

		);
		return $events;
	}	

	public function addFooter(&$route, &$data, &$output) {
		$data_ya = $this->load->language($this->path_module);
		$entity = $this->request->get['route'];
		$allow_default = array(
			'catalog/product/edit',
			'catalog/category/edit',
			'catalog/information/edit',
			'catalog/product/add',
			'catalog/category/add',
			'catalog/information/add',
			'catalog/manufacturer/edit',
			'catalog/manufacturer/add',
			'blog/blog/update',
		);
		$allow_cms = array(
			'catalog/record/update'
		);
		$allow_batch = array(
			'batch_editor/index/getLink',
		);
		$allow_other = array(
			'catalog/attribute/edit',
			'catalog/attribute/add',
			'catalog/attribute_group/edit',
			'catalog/attribute_group/add',
			'catalog/option/edit',
			'catalog/option/add',
			'catalog/filter/edit',
			'catalog/filter/add',
		);
		$result = '';

		$type = '';

		if (in_array($entity, $allow_default)) {
			$type = 'product';
		} elseif (in_array($entity, $allow_cms)) {
			$type = 'cmsblog';
		} elseif (in_array($entity, $allow_other)) {
			$type = 'attribute';
		} elseif (in_array($entity, $allow_batch)) {
			$type = 'batch';
		}
		if ($type) {
			$data_ya['languages'] = $data['languages'];
			$data_ya['ya_script_translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module .'/translate'));
			$data_ya['ya_script'] = $this->makeUrl($this->path_module . '/getScript', '&type=' . $type);
			$api_provider = $this->config->get($this->module_name . '_api_provider');
			$data_ya['yandex_copyright'] = ($api_provider == 'Google')?$this->language->get('google_copyright'):$this->language->get('yandex_copyright');
			
			$result = $this->load->view($this->path_module . '/ya_translate_footer_product', $data_ya);
		}
		if ($type == 'batch') {
			$output .= $result;
		}
		if (in_array($entity, array('catalog/filter/edit','catalog/filter/add'))) {
			$output = str_replace('id="option-value"', '',$output);
		}
		$output = str_replace('</body>', $result . "\n" . '</body>',$output);
	}

	public function dataTranslate($data_translate=array(),$from,$to) {
		$return_results = array();
		
		$translator = new Ya_translate($this->registry);

		$key_orginal = array();
		$text = array();
		foreach ($data_translate as $field=>$value) {
			$value  = trim(html_entity_decode(str_replace('&amp;','&',$value), ENT_QUOTES,'utf-8'));
			if ($value && strip_tags($value)) {
				$text[] = str_replace('~','',$value);
				$key_orginal[] = $field;
			}
		}
	
		$config_language = $this->config->get($this->module_name . '_codelanguage');
		$languages = $this->getLanguages();

		if (isset($config_language[$languages[$from]['code']])) {
			$direct_from = $config_language[$languages[$from]['code']];
		} else {
			$direct_from = $languages[$from]['ya_code'];
		}
		if (isset($config_language[$languages[$to]['code']])) {
			$direct_to = $config_language[$languages[$to]['code']];
		} else {
			$direct_to = $languages[$to]['ya_code'];
		}

		$direction =  $direct_from . '-' . $direct_to;


		$post_limit = 10000;
		$type = $this->config->get($this->module_name . '_type');
		$types = array ('even','stream');

		if (!in_array($type,$types)) $type = 'even';

		$text_for_translate = implode('~',$text);

		if ($type == 'even' || strlen($text_for_translate) > $post_limit) {
			$textas = array();
			$translate_ok = true;
			foreach ($text as $value) {

				if (!$translate_ok) continue;

				if (strlen($value) < $post_limit) {
					$text_for_translate = $value;

					$result = $translator->translate($text_for_translate, $direction, 'html');
					$results = $result->getTranslation();
					if (isset($results['code']) && $results['code'] == 200) {
						$textas[] = $results['text'][0];
					} else {
						$translate_ok = false;
					}
				} else {
					$text_for_translate = preg_replace("/[\r\n]+/", "\n", str_replace('><',">\n<",$value));
					$array_subtext = explode("\n",$text_for_translate);
					$text_for_translate = '';
					$res_translate = '';
					$accumulative_text ='';
					foreach ($array_subtext as $subtext) {
						$len_text_for_translate = strlen($accumulative_text);
						$len_subtext = strlen($subtext);
						if (($len_text_for_translate + $len_subtext) > $post_limit) {
							$result = $translator->translate($text_for_translate, $direction, 'html');
							$results = $result->getTranslation();
							if ($results['code'] == 200) {
								$res_translate .= $results['text'][0];
								$translate_ok = true;
							} else {
								$translate_ok = false;
							}
							$accumulative_text = $subtext; 
						} else {
							$text_for_translate = $accumulative_text;
							$accumulative_text .= $subtext; 
						}
					}
					if ($accumulative_text) {
						$text_for_translate = $accumulative_text;
						$result = $translator->translate($text_for_translate, $direction, 'html');
						$results = $result->getTranslation();
						if ($results['code'] == 200) {
							$res_translate .= $results['text'][0];
							$translate_ok = true;
						} else {
							$translate_ok = false;
						}
					}
					if ($translate_ok) {
						$textas[] = $res_translate;
					}
				}
			}
			if ($translate_ok) {
				$texts = implode('~',$textas);
			}
		} else {
			$translate_ok = true;

			$result = $translator->translate($text_for_translate, $direction, 'html');
			$results = $result->getTranslation();
			if ($results['code'] == 200) {
				$texts = $results['text'][0];
			} else {
				$translate_ok = false;
			}
		}

		if ($translate_ok) {
			$text = explode('~',$texts);
			$new_text = array();
			foreach ($key_orginal as $key=>$field) {
				$new_text[$field] = str_replace('&amp;','&',$text[$key]);
			}
			$return_results['success'] = 'translate complete';
			$return_results['text'] = $new_text;
		} else {
			$return_results['error'] = $results;
			$this->logger->write($results);
			$this->logger->write('from:' . $from . ' to:' . $to);
			$this->logger->write($data_translate);
		}
		return $return_results;
	}

	public function clearLog() {
		$fp = fopen(DIR_LOGS. $this->file_error_log,'w');
		fclose($fp);
		$this->response->setOutput('ok');
	}

	public function viewLog() {
		$data = $this->load->language($this->path_module);
		if (is_file(DIR_LOGS . $this->file_error_log)) {
			$data['log'] = file_get_contents(DIR_LOGS . $this->file_error_log);
		} else {
			$data['log'] = 'empty';
		}
		$this->response->setOutput($this->load->view($this->path_module . '/ya_log', $data));
	}

	public function translate() {

		$data = $this->load->language($this->path_module);
		
		$results = $this->dataTranslate($this->request->post, $this->request->get['from'], $this->request->get['to']);
		
		$json = $results;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function checkApiKey() {
		$data = $this->load->language($this->path_module);
		$json = array();
		if (isset($this->request->post[$this->module_name . '_api_key'])) {
			$api_provider = $this->config->get($this->module_name . '_api_provider');
			$translator = new Ya_translate($this->registry,$api_provider,$this->request->post[$this->module_name . '_api_key'][$api_provider]);
			$result = $translator->getLangs('ru');
			$results = $result->getTranslation();
			if (isset($results['code']) ) {
				$json['error'] = $this->language->get('text_'.$results['code']);
			} else {
				$json['success'] = $this->language->get('text_' . '200');
				$html = '';
				foreach ($results['dirs'] as $dir) {
					$html .= '<div>' . $dir . '</div>';
				}
				$json['dirs'] = $html;
				$html = '<table class="table table-bordered table-hover"><tr><td>Code</td><td>Name</td></tr>';
				foreach ($results['langs'] as $ya_code=>$name) {
					$html .= '<tr><td>' . $ya_code . '</td><td>' . $name . '</td></tr>';
				}
				$html .='</table>';
				$json['langs'] = $html;
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function test_translate() {
		$data = $this->load->language($this->path_module);
		if (isset($this->request->post['ya_translate_api_key'])) {
			$api_key = $this->request->post['ya_translate_api_key'];
			$translator = new Ya_translate($this->registry);
			if (is_array($this->request->post['text_from'])) {
				$text = array();
				foreach ($this->request->post['text'] as $key=>$val) {
					$text[] = html_entity_decode($val, ENT_QUOTES,'utf-8');
					$key_orginal[] = $key;
				}
			} else {
				$text =  html_entity_decode($this->request->post['text_from'], ENT_QUOTES,'utf-8');
			}
			$config_language = $this->config->get($this->module_name . '_codelanguage');

			$this->load->model('localisation/language');
			//$data['languages'] = $this->model_localisation_language->getLanguages();

			
			$language_from = $this->request->post['lang_from'];
			$language_to =   $this->request->post['lang_to'];
			
			if (isset($config_language[$language_from])) {
				$direct_from = $config_language[$language_from];
			} else {
				$codes_from = explode('-',$language_from);
				$direct_from = $codes_from[0];
			}

			if (isset($config_language[$language_to])) {
				$direct_to = $config_language[$language_to];
			} else {
				$codes_to = explode('-',$language_to);
				$direct_to = $codes_to[0];
			}

			if ($direct_to == 'ua') $direct_to = 'uk';
			$direction =  $direct_from . '-' . $direct_to;
			$result = $translator->translate($text, $direction, 'auto');
			
			$results = $result->getTranslation();

			if (isset($results['code']) && $results['code'] == 200) {
				$json = array();
				$json['success'] = 'translate complete';
				$json['request_lang'] = $direction;
				$json['result'] = $results;
				$json['text'] = $results['text'][0];
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			} else {
				$json['error'] = $this->language->get('text_'.$results['code']);
				$json['message'] = isset($results['message'])?$results['message']:$results;
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode($json));
			}
		
		}
	}

	public function getScript() {
		$config_language = $this->config->get($this->module_name . '_codelanguage');

		$languages = $this->getLanguages();
		reset($languages);
		//$first = current($languages);
		$first = $languages[$this->config->get($this->module_name . '_default_lang')];
		$language_from = $first['code'];

		if (isset($config_language[$language_from])) {
			$direct_from = $config_language[$language_from];
		} else {
			$direct_from = $first['ya_code'];;
		}

		$data['first_id'] = $first['language_id'];
		
		$data['user_token'] = $this->session->data['user_token'];
		$data['languages'] = $this->getLanguages();;
		$data['ya_script_translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module .'/translate'));

		if (isset($this->request->get['type'])) {
			$this->response->addHeader('Content-Type: application/javascript');
			if ($this->request->get['type'] == 'product') {
				$this->response->setOutput($this->load->view($this->path_module . '/ya_translate_js', $data));
			}
			if ($this->request->get['type'] == 'cmsblog') {
				$this->response->setOutput($this->load->view($this->path_module . '/ya_translate_blog_js', $data));
			}
			if ($this->request->get['type'] == 'batch') {
				$this->response->setOutput($this->load->view($this->path_module . '/ya_translate_batch_js', $data));
			}
			if ($this->request->get['type'] == 'attribute') {
				$data['ya_script_field_name'] = array(
					'attribute_description',
					'attribute_group_description',
					'option_description',
					'filter_group_description',
				);
				$data['ya_script_field_name_value'] = array(
					'option_value',
					'filter',
				);
				$this->response->setOutput($this->load->view($this->path_module . '/ya_translate_attribute', $data));
			}
		}
	}

	private function nav($route,&$data) {
		$data['navs'] = array();

		$active = 'active';
		$data['navs'][] = array(
			'href' => $this->makeUrl($this->path_module),
			'text' => $this->language->get('text_nav_settings'),
			'active' => ($route == $this->path_module)?$active:''
		);

																					   
		$data['navs'][] = array(
			'href' => $this->makeUrl($this->path_module . '/product'),
			'text' => $this->language->get('text_nav_products'),
			'active' => ($route == $this->path_module . '/product')?$active:''
		);

																						
		$data['navs'][] = array(
			'href' => $this->makeUrl($this->path_module . '/category'),
			'text' => $this->language->get('text_nav_categories'),
			'active' => ($route == $this->path_module . '/category')?$active:''
		);

		$sql = "SHOW TABLES LIKE '" . DB_PREFIX . "manufacturer_description'";
		$results= $this->db->query($sql);
		if ($results->num_rows) {
		$data['navs'][] = array(
			'href' => $this->makeUrl($this->path_module . '/manufacturer'),
			'text' => $this->language->get('text_nav_manufacturers'),
			'active' => ($route == $this->path_module . '/manufacturer')?$active:''
		);
		}

		$data['navs'][] = array(
			'href' => $this->makeUrl($this->path_module . '/attribute'),
			'text' => $this->language->get('text_nav_attributes'),
			'active' => ($route == $this->path_module . '/attribute')?$active:''
		);
	}

	public function bulkTranslateCategory() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/category')
				&& isset($this->request->get['from']) && isset($this->request->get['to'])) {
			$this->load->model($this->path_module);
			$this->logger->write('Last category_id:' . $this->request->post['category_id']);
			$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
			$category_description = $model_product->getCategoryDescriptions($this->request->post['category_id']);

			$allow_fields = $this->config->get($this->module_name . '_category_description_field');

			$old_value = $category_description[$this->request->get['from']];
			
			$translate_value = array();
			if ($allow_fields) {
				foreach ($allow_fields as $field) {
					$translate_value[$field] = $old_value[$field];
				}
			} else {
				$translate_value = $old_value;
			}
			
			$results = $this->dataTranslate($translate_value,$this->request->get['from'], $this->request->get['to']);
			if (isset($results['success'])) {

				$new_value = array_merge($old_value, $results['text']);

				$model_product->updateCategoryDescriptions($this->request->post['category_id'],$this->request->get['to'],$new_value);
				$json['success'] = $results['success'];
				$json['last_id'] = $this->request->post['category_id'];
			} else {
				$json = $results;
			}
		}

				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function bulkTranslateManufacturer() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/manufacturer')
				&& isset($this->request->get['from']) && isset($this->request->get['to'])) {
			$this->load->model($this->path_module);
			$this->logger->write('Last manufacturer_id:' . $this->request->post['manufacturer_id']);
			$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
			$manufacturer_description = $model_product->getManufacturerDescriptions($this->request->post['manufacturer_id']);

			$allow_fields = $this->config->get($this->module_name . '_manufacturer_description_field');

			$old_value = $manufacturer_description[$this->request->get['from']];
			
			$translate_value = array();
			if ($allow_fields) {
				foreach ($allow_fields as $field) {
					$translate_value[$field] = $old_value[$field];
				}
			} else {
				$translate_value = $old_value;
			}
			
			$results = $this->dataTranslate($translate_value,$this->request->get['from'], $this->request->get['to']);
			if (isset($results['success'])) {

				$new_value = array_merge($old_value, $results['text']);

				$model_product->updateManufacturerDescriptions($this->request->post['manufacturer_id'],$this->request->get['to'],$new_value);
				$json['success'] = $results['success'];
				$json['last_id'] = $this->request->post['manufacturer_id'];
			} else {
				$json = $results;
			}
		}

				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function bulkTranslateAttribute() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/product')
				&& isset($this->request->get['from']) && isset($this->request->get['to'])) {
			$this->load->model($this->path_module);
			$this->logger->write('Last attribute_id:' . $this->request->post['attribute_id']);
			
			$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));

			$filter_data = array();

			$filter_data['attribute_id'] = $this->request->post['attribute_id'];

			$filter_data['from']  = $this->request->get['from'];
			$filter_data['to']    = $this->request->get['to'];
			$filter_data['md5_text'] = isset($this->request->post['md5_text'])?$this->request->post['md5_text']:'';

			$attribute_values_total = $model_product->getAttributeValuesTotal($filter_data);
			
			if ($attribute_values_total > 50) {
				$filter_data['limit'] = 20;
			}

			$attribute_values  = $model_product->getAttributeValues($filter_data);

			$translate_value = array();
			if ($attribute_values) {
				foreach ($attribute_values as $attribute_value) {
					$value = trim($attribute_value['text']);
					if (utf8_strlen($value) >= 3) {
						$tempvalue = preg_replace('/[^a-zA-Zа-яА-Я]/ui', '',$value);
						if (utf8_strlen($tempvalue) >= 2) {
							$index_field = $attribute_value['attribute_id'] . '_' . $attribute_value['md5_text'] . '_' .  $this->request->get['from'];
							$translate_value[$index_field] = $value;
						}
					}
					$json['md5_text'] = $attribute_value['md5_text'];
					$json['success'] = 1;
				}
			} else {
				$json['md5_text'] = '';
				$results['success'] = 1;
				$query = $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "ya_translate SET entity = 'attribute', id = " . (int)$this->request->post['attribute_id']);
			}


			if ($translate_value) {
				$translate_values = array();
				foreach ($translate_value as $key=>$value) {
					$translate_values[$value][] = $key;
				}
				$_translate_values = array();
				foreach ($translate_values as $key=>$value) {
					$_translate_values[$key] = json_encode($value);
				}
				$translate_value = array_flip($_translate_values);
			
				$results = $this->dataTranslate($translate_value,$this->request->get['from'], $this->request->get['to']);
			}
			
			
			if (isset($results['success'])) {

				if (isset($results['text'])) {
					$new_value = $results['text'];
					$this->logger->write($new_value);	
					$data_update = array(
						'attribute_id' => $this->request->post['attribute_id'],
						'language_id' => $this->request->get['to'],
						'value' =>$new_value
					);
					$model_product->updateAttribute($data_update);
				}
				
				$json['success'] = $results['success'];
				$json['last_id'] = $this->request->post['attribute_id'];
			} else {
				$json = $results;
			}
		}
				
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function bulkTranslate() {
		$json = array();
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/product')
				&& isset($this->request->get['from']) && isset($this->request->get['to'])) {

			$this->logger->write('Last product_id:' . $this->request->post['product_id']);	

			$this->load->model($this->path_module);
			$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
			$product_description = $model_product->getProductDescriptions($this->request->post['product_id']);

			$allow_fields = $this->config->get($this->module_name . '_product_description_field');

			$old_value = $product_description[$this->request->get['from']];
			
			$translate_value = array();
			if ($allow_fields) {
				foreach ($allow_fields as $field) {
					$translate_value[$field] = $old_value[$field];
				}
			} else {
				$translate_value = $old_value;
			}
			
			$results = $this->dataTranslate($translate_value,$this->request->get['from'], $this->request->get['to']);
			if (isset($results['success'])) {

				$new_value = array_merge($old_value, $results['text']);
				$model_product->updateProductDescriptions($this->request->post['product_id'],$this->request->get['to'],$new_value);

				$json['success'] = $results['success'];
				$json['last_id'] = $this->request->post['product_id'];
			} else {
//				print_r($results);
				$json = $results;
			}
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function getLanguages() {
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();

		$config_language = $this->config->get($this->module_name . '_codelanguage');

		reset($languages);
		$first = current($languages);
		foreach ($languages as $lang=>$language) {
			if ($language['language_id'] == $this->config->get($this->module_name . '_default_lang')) {
				$first = $languages[$lang];
				break;
			}
		}
		$language_from = $first['code'];
		$ya_code = explode('-',$first['code']);
		if ($ya_code[0] == 'ua') $ya_code[0] = 'uk';
		if (isset($config_language[$language_from])) {
			$direct_from = $config_language[$language_from];
		} else {
			$direct_from = $ya_code[0];
		}
		
		$data = array();
		foreach ($languages as $lang=>$language) {
			$data[$language['language_id']] = $language;
			$ya_code = explode('-',$language['code']);
			if ($ya_code[0] == 'ua') $ya_code[0] = 'uk';
			$data[$language['language_id']]['ya_code'] = $ya_code[0];
			$data[$language['language_id']]['direction'] = $direct_from . '-' . $ya_code[0];
		}

		return $data;
	}
	
	public function manufacturer() {
		$data = $this->load->language($this->path_module);
		$this->document->setTitle($this->language->get('heading_title_manufacurer'));
		
		$filter_array = array(
			'filter_name',
			'filter_empty_description',
			'filter_language_id',
			'filter_ready',
		);
		foreach ($filter_array as $filter) {
			if (isset($this->request->get[$filter])) {
				${$filter} = $this->request->get[$filter];
			} else {
				${$filter} = null;
			}
		}
		$get_array = array(
			'sort'  => 'name',
			'order' => 'ASC',
			'page'  => 1,
		);
		foreach ($get_array as $get=>$value) {
			if (isset($this->request->get[$get])) {
				${$get} = $this->request->get[$get];
			} else {
				${$get} = $value;
			}
		}
		

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
			'page' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->makeUrl($this->path_module)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_manufacurer'),
			'href' => $this->makeUrl($this->path_module . '/manufacturer', $url)
		);

		$data['viewLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/viewLog'));
		$data['clearLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/clearLog'));
		$data['translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/bulkTranslateManufacturer'));
		$data['filter'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/manufacturer'));
		$data['filter_manufacturer'] = $this->makeUrlScript('catalog/manufacturer/autocomplete');
		$data['manufacturers'] = array();

		$filter_data = array(
			'filter_name'	           => $filter_name,
			'filter_empty_description' => $filter_empty_description,
			'filter_language_id'       => $filter_language_id,
			'filter_ready'             => $filter_ready,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$this->load->model($this->path_module);
		$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));

		$manufacturer_total = $model_product->getTotalManufacturers($filter_data);

		$results = $model_product->getManufacturers($filter_data);

		foreach ($results as $result) {

			$data['manufacturers'][] = array(
				'manufacturer_id' => $result['manufacturer_id'],
				'name'       => $result['name'],
				'edit'       => $this->makeUrl($this->path_module . 'manufaturerTranslate',  'manufacturer_id=' . $result['manufacturer_id'])
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_bulk_translate'] = $this->language->get('button_bulk_translate');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['entry_ready'] = $this->language->get('entry_ready');
		$data['text_not_ready'] = $this->language->get('text_not_ready');
		$data['text_ready'] = $this->language->get('text_ready');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']     = $this->makeUrl($this->path_module . '/manufacturer', '&sort=name' . $url);

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$pagination = new Pagination();
		$pagination->total = $manufacturer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->makeUrl($this->path_module . '/manufacturer', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($manufacturer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($manufacturer_total - $this->config->get('config_limit_admin'))) ? $manufacturer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $manufacturer_total, ceil($manufacturer_total / $this->config->get('config_limit_admin')));

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,

			'sort' => false,
			'order' => false,
		);

		foreach ($url_filter_array as $filter=>$value) {
			$data[$filter] = ${$filter};
		}

		$data['config_admin_language'] = $this->config->get('config_admin_language');

		$data['languages'] = $this->getLanguages();

		$this->nav($this->path_module . '/manufacturer', $data);
		
		$data['module_page'] = $this->path_module . '/manufacturer';
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module . '/ya_manufacturer_list', $data));

	}

	public function attribute() {
		$data = $this->load->language($this->path_module);
		$this->document->setTitle($this->language->get('heading_title_attribute'));
		
		$filter_array = array(
			'filter_name',
			'filter_ready',
		);
		foreach ($filter_array as $filter) {
			if (isset($this->request->get[$filter])) {
				${$filter} = $this->request->get[$filter];
			} else {
				${$filter} = null;
			}
		}
		$get_array = array(
			'sort'  => 'name',
			'order' => 'ASC',
			'page'  => 1,
		);
		foreach ($get_array as $get=>$value) {
			if (isset($this->request->get[$get])) {
				${$get} = $this->request->get[$get];
			} else {
				${$get} = $value;
			}
		}
		

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
			'page' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->makeUrl($this->path_module)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_attribute'),
			'href' => $this->makeUrl($this->path_module . '/attribute', $url)
		);

		$data['viewLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/viewLog'));
		$data['clearLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/clearLog'));
		$data['translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/bulkTranslateAttribute'));
		$data['filter'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/attribute'));

		$data['attributes'] = array();

		$filter_data = array(
			'filter_name'	           => $filter_name,
			'filter_ready'             => $filter_ready,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$this->load->model($this->path_module);

		$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));

		$attribute_total = $model_product->getTotalAttribute($filter_data);

		$results = $model_product->getAttributes($filter_data);
		foreach ($results as $result) {
			$total_values = $this->db->query("SELECT COUNT(tot) total_value
				FROM (SELECT COUNT(*) as tot 
				FROM " . DB_PREFIX . "product_attribute pa 
				WHERE pa.attribute_id = " . (int)$result['attribute_id'] . "
				AND pa.language_id =  " . (int)$this->config->get('config_language_id') . " 
				GROUP BY pa.text) as tt1");
			
			$total_value = $total_values->row['total_value'];

			$data['attributes'][] = array(
				'attribute_id' => $result['attribute_id'],
				'name'         => $result['name'],
				'total_value'  => $total_value,
				'edit'         => $this->makeUrl($this->path_module . 'attributeTranslate',  'attribute_id=' . $result['attribute_id'])
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_bulk_translate'] = $this->language->get('button_bulk_translate');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['entry_ready'] = $this->language->get('entry_ready');
		$data['text_not_ready'] = $this->language->get('text_not_ready');
		$data['text_ready'] = $this->language->get('text_ready');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_ready' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']     = $this->makeUrl($this->path_module . '/attribute', '&sort=name' . $url);

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$pagination = new Pagination();
		$pagination->total = $attribute_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->makeUrl($this->path_module . '/attribute', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($attribute_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($attribute_total - $this->config->get('config_limit_admin'))) ? $attribute_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $attribute_total, ceil($attribute_total / $this->config->get('config_limit_admin')));

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_ready' => false,

			'sort' => false,
			'order' => false,
		);

		foreach ($url_filter_array as $filter=>$value) {
			$data[$filter] = ${$filter};
		}

		$data['config_admin_language'] = $this->config->get('config_admin_language');

		$data['languages'] = $this->getLanguages();
		$data['attributeFill'] = $this->makeUrlScript($this->path_module . '/attributeFill');

		$this->nav($this->path_module . '/attribute', $data);
		
		$data['module_page'] = $this->path_module . '/attribute';
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module . '/ya_attribute_list', $data));

	}

	public function attributeFill(){
		$json = array();
		$json['total'] = 0;
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'catalog/product')
				&& isset($this->request->post['from']) && isset($this->request->post['to'])) {
					$this->load->model($this->path_module);
					$my_model = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
					$json['total'] = $my_model->attributeFill($this->request->post);
					
		}
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function category() {
		$data = $this->load->language($this->path_module);
		$this->document->setTitle($this->language->get('heading_title_category'));
		
		$filter_array = array(
			'filter_name',
			'filter_status',
			'filter_empty_description',
			'filter_language_id',
			'filter_ready',
		);
		foreach ($filter_array as $filter) {
			if (isset($this->request->get[$filter])) {
				${$filter} = $this->request->get[$filter];
			} else {
				${$filter} = null;
			}
		}
		$get_array = array(
			'sort'  => 'cd.name',
			'order' => 'ASC',
			'page'  => 1,
		);
		foreach ($get_array as $get=>$value) {
			if (isset($this->request->get[$get])) {
				${$get} = $this->request->get[$get];
			} else {
				${$get} = $value;
			}
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
			'page' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->makeUrl($this->path_module)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title_category'),
			'href' => $this->makeUrl($this->path_module . '/category', $url)
		);

		$data['viewLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/viewLog'));
		$data['clearLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/clearLog'));
		$data['translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/bulkTranslateCategory'));
		$data['filter'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/category'));
		$data['filter_category'] = $this->makeUrlScript($this->path_module . '/autocompleteCategory');
		$data['categories'] = array();

		$filter_data = array(
			'filter_name'	           => $filter_name,
			'filter_status'            => $filter_status,
			'filter_empty_description' => $filter_empty_description,
			'filter_language_id'       => $filter_language_id,
			'filter_ready'             => $filter_ready,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);
		$this->load->model($this->path_module);
		$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));

		$category_total = $model_product->getTotalCategories($filter_data);

		$results = $model_product->getCategories($filter_data);

		foreach ($results as $result) {

			$data['categories'][] = array(
				'category_id' => $result['category_id'],
				'name'       => $result['name'],
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'edit'       => $this->makeUrl('catalog/category/edit',  'category_id=' . $result['category_id'])
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_name'] = $this->language->get('column_name');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_bulk_translate'] = $this->language->get('button_bulk_translate');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['entry_ready'] = $this->language->get('entry_ready');
		$data['text_not_ready'] = $this->language->get('text_not_ready');
		$data['text_ready'] = $this->language->get('text_ready');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']     = $this->makeUrl($this->path_module . '/category', '&sort=cd.name' . $url);
		$data['sort_status']   = $this->makeUrl($this->path_module . '/category', '&sort=p.status' . $url);
		$data['sort_order']    = $this->makeUrl($this->path_module . '/category', '&sort=p.sort_order' . $url);

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$pagination = new Pagination();
		$pagination->total = $category_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->makeUrl($this->path_module . '/category', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($category_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($category_total - $this->config->get('config_limit_admin'))) ? $category_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $category_total, ceil($category_total / $this->config->get('config_limit_admin')));

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'sort' => false,
			'order' => false,
		);

		foreach ($url_filter_array as $filter=>$value) {
			$data[$filter] = ${$filter};
		}

		$data['config_admin_language'] = $this->config->get('config_admin_language');

		$data['languages'] = $this->getLanguages();

		$this->nav($this->path_module . '/category', $data);
		
		$data['module_page'] = $this->path_module . '/category';
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module . '/ya_category_list', $data));

	}

	public function autocompleteCategory() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model($this->path_module);
			$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));

			$filter_data = array(
				'filter_name' => $this->request->get['filter_name'],
				'sort'        => 'name',
				'order'       => 'ASC',
				'start'       => 0,
				'limit'       => 10
			);

			$results = $model_product->getCategoriesAuto($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'category_id' => $result['category_id'],
					'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$sort_order = array();

		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
		
	public function product() {
		$this->getList();
	}
	private function getList() {
		$data = $this->load->language($this->path_module);
		$this->document->setTitle($this->language->get('heading_title_product'));
		
		$filter_array = array(
			'filter_name',
			'filter_model',
			'filter_price',
			'filter_quantity',
			'filter_status',
			'filter_empty_description',
			'filter_language_id',
			'filter_ready',
			'filter_category_id',
			'filter_manufacturer_id',
		);
		foreach ($filter_array as $filter) {
			if (isset($this->request->get[$filter])) {
				${$filter} = $this->request->get[$filter];
			} else {
				${$filter} = null;
			}
		}
		$get_array = array(
			'sort'  => 'pd.name',
			'order' => 'ASC',
			'page'  => 1,
		);
		foreach ($get_array as $get=>$value) {
			if (isset($this->request->get[$get])) {
				${$get} = $this->request->get[$get];
			} else {
				${$get} = $value;
			}
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_model' => 'decode',
			'filter_price' => false,
			'filter_quantity' => false,
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'filter_category_id' => false,
			'filter_manufacturer_id' => false,
			'sort' => false,
			'order' => false,
			'page' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->makeUrl('common/dashboard')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->makeUrl($this->path_module)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->makeUrl($this->path_module . '/product', $url)
		);

		$data['viewLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/viewLog'));
		$data['translate'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/bulkTranslate'));
		$data['clearLog'] = str_replace('&amp;','&',$this->makeUrl($this->path_module . '/clearLog'));
		$data['products'] = array();

		$filter_data = array(
			'filter_name'	           => $filter_name,
			'filter_model'	           => $filter_model,
			'filter_price'	           => $filter_price,
			'filter_quantity'          => $filter_quantity,
			'filter_status'            => $filter_status,
			'filter_empty_description' => $filter_empty_description,
			'filter_language_id'       => $filter_language_id,
			'filter_ready'             => $filter_ready,
			'filter_category_id'       => $filter_category_id,
			'filter_manufacturer_id'   => $filter_manufacturer_id,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$this->load->model('tool/image');
		$this->load->model($this->path_module);
		$model_product = $this->registry->get('model_' . str_replace('/','_',$this->path_module));
		$product_total = $model_product->getTotalProducts($filter_data);

		$results = $model_product->getProducts($filter_data);

		foreach ($results as $result) {
			if (is_file(DIR_IMAGE . $result['image'])) {
				$image = $this->model_tool_image->resize($result['image'], 40, 40);
			} else {
				$image = $this->model_tool_image->resize('no_image.png', 40, 40);
			}

			$data['products'][] = array(
				'product_id' => $result['product_id'],
				'image'      => $image,
				'name'       => $result['name'],
				'model'      => $result['model'],
				'price'      => $result['price'],
				'quantity'   => $result['quantity'],
				'status'     => ($result['status']) ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'view'       => HTTPS_CATALOG . 'index.php?route=product/product&amp;product_id=' . $result['product_id'],
				'edit'       => $this->makeUrl('catalog/product/edit',  '&product_id=' . $result['product_id'])
			);
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_list'] = $this->language->get('text_list');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_image'] = $this->language->get('column_image');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_model'] = $this->language->get('column_model');
		$data['column_price'] = $this->language->get('column_price');
		$data['column_quantity'] = $this->language->get('column_quantity');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');

		$data['entry_name'] = $this->language->get('entry_name');
		$data['entry_model'] = $this->language->get('entry_model');
		$data['entry_price'] = $this->language->get('entry_price');
		$data['entry_quantity'] = $this->language->get('entry_quantity');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['button_bulk_translate'] = $this->language->get('button_bulk_translate');
		$data['button_filter'] = $this->language->get('button_filter');

		$data['entry_ready'] = $this->language->get('entry_ready');
		$data['text_not_ready'] = $this->language->get('text_not_ready');
		$data['text_ready'] = $this->language->get('text_ready');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_model' => 'decode',
			'filter_price' => false,
			'filter_quantity' => false,
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'filter_category_id' => false,
			'filter_manufacturer_id' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name']     = $this->makeUrl($this->path_module . '/product', '&sort=pd.name' . $url);
		$data['sort_model']    = $this->makeUrl($this->path_module . '/product', '&sort=p.model' . $url);
		$data['sort_price']    = $this->makeUrl($this->path_module . '/product', '&sort=p.price' . $url);
		$data['sort_quantity'] = $this->makeUrl($this->path_module . '/product', '&sort=p.quantity' . $url);
		$data['sort_status']   = $this->makeUrl($this->path_module . '/product', '&sort=p.status' . $url);
		$data['sort_order']    = $this->makeUrl($this->path_module . '/product', '&sort=p.sort_order' . $url);

		$url = '';

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_model' => 'decode',
			'filter_price' => false,
			'filter_quantity' => false,
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'filter_category_id' => false,
			'filter_manufacturer_id' => false,
			'sort' => false,
			'order' => false,
		);
		foreach ($url_filter_array as $filter=>$value) {
			if (isset($this->request->get[$filter])) {
				if ($value) {
					$url .= '&' . $filter . '=' . urlencode(html_entity_decode($this->request->get[$filter], ENT_QUOTES, 'UTF-8'));
				} else {
					$url .= '&' . $filter . '=' . $this->request->get[$filter];
				}
			}
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->makeUrl($this->path_module . '/product', $url . '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$url_filter_array = array(
			'filter_name' => 'decode',
			'filter_model' => 'decode',
			'filter_price' => false,
			'filter_quantity' => false,
			'filter_status' => false,
			'filter_empty_description' => false,
			'filter_language_id' => false,
			'filter_ready' => false,
			'filter_category_id' => false,
			'filter_manufacturer_id' => false,
			'sort' => false,
			'order' => false,
		);

		foreach ($url_filter_array as $filter=>$value) {
			$data[$filter] = ${$filter};
		}
		$data['category'] = '';
		if ($data['filter_category_id']) {
			$this->load->model('catalog/category');
			$category_info = $this->model_catalog_category->getCategory($data['filter_category_id']);
			$data['category'] = $category_info['name'];
		}

		$data['manufacturer'] = '';
		if ($data['filter_manufacturer_id']) {
			$this->load->model('catalog/manufacturer');
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($data['filter_manufacturer_id']);
			$data['manufacturer'] = $manufacturer_info['name'];
		}
		$data['product_auto_complete'] = $this->makeUrlScript('catalog/product/autocomplete');
		$data['filter_category'] = $this->makeUrlScript($this->path_module . '/autocompleteCategory');
		$data['filter_manufacturer'] = $this->makeUrlScript('catalog/manufacturer/autocomplete');
		$data['filter'] = $this->makeUrlScript($this->path_module . '/product');

		$data['config_admin_language'] = $this->config->get('config_admin_language');

		$data['languages'] = $this->getLanguages();

		$this->nav($this->path_module . '/product', $data);
		
		$data['module_page'] = $this->path_module . '/product';
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->path_module . '/ya_product_list', $data));
	}

	private function makeUrl($route, $arg=''){
		if ($arg) {
			$arg = '&' . ltrim($arg,'&');
		}
		return $this->url->link($route, 'user_token=' . $this->session->data['user_token'] . $arg, true);
	}
	private function makeUrlScript($route, $arg=''){
		return str_replace('&amp;','&',$this->makeUrl($route, $arg));
	}
}