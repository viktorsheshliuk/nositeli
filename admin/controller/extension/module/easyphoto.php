<?php
class ControllerExtensionModuleEasyPhoto extends Controller {

	//versions (in 3.0 = only for 3.0 comment)
	//for 3.0 $this->config->get(module_var);
	private $path = 'extension/module/easyphoto';
	private $module = 'marketplace/extension';

	public function install() {

		// $response = $this->send();

		// if($response['status'] && $response['content']){
		// 	$this->load->model('setting/setting');
		// 	$this->model_setting_setting->editSettingValue('module_easyphoto', "module_easyphoto_key", $response['content']);
		// }
	}

	public function index() {
		$data = $this->load->language($this->path);
		$a = 0;
		// $response = $this->send();

		// if($response['status'] && $response['content'] && $this->key($response['content'])){
		// 	$a = 1;
		// 	$this->load->model('setting/setting');
		// 	$this->model_setting_setting->editSettingValue('module_easyphoto', "module_easyphoto_key", $response['content']);
		// }

		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('module_easyphoto', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link($this->module, 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link($this->module, 'user_token=' . $this->session->data['user_token'], 'SSL')
		);
		$data['breadcrumbs'][] = array(
			'text' => '<span style="color:#00b32d;font-weight:bold;">Easy Photo</span>',
			'href' => $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], 'SSL')
		);

		$data['action'] = $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link($this->module, 'user_token=' . $this->session->data['user_token'], 'SSL');

		//array vars
		$vars = array(
			'easyphoto_status',
			'easyphoto_key',
			'easyphoto_direct',
			'easyphoto_main',
			'easyphoto_name',
			'easyphoto_separate', //3.1+
			'easyphoto_from', //3.1-
			'easyphoto_language',
		);

 		foreach($vars as $var){
			//only for 3.0
			$var = 'module_' . $var;
			if (isset($this->request->post[$var])) {
				$data[$var] = $this->request->post[$var];
			} else {
				$data[$var] = $this->config->get($var);
			}
		}

		//3.1+
		$data['fields'] = array('name','model','sku','upc','ean','jan','isbn','mpn','location');
		//3.1-

		// if($response['status'] && $response['content'] && empty($data['module_easyphoto_key'])){
		// 	$this->model_setting_setting->editSettingValue('module_easyphoto', "module_easyphoto_key", $response['content']);
		// 	$data['module_easyphoto_key'] = $response['content'];
		// }
		$data['module_easyphoto_key'] = 'nulled'; // $a?$data['easyphoto_key']:false;

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		//3.1+
		$data['more_info'] = false;
		// $proposal = 'https://microdata.pro/index.php?route=sale/proposal&module=Easyphoto';
		// if($this->get_http_response_code($proposal) == 200){
		// 	$more_info = file_get_contents($proposal);
		// 	$data['more_info'] = $more_info;
		// }
		//3.1-

		$this->response->setOutput($this->load->view($this->path, $data));
	}

	//3.1+
	private function get_http_response_code($url) {
	    $headers = get_headers($url);
	    return substr($headers[0], 9, 3);
	}
	//3.1-

	public function getForm($product_images) {
		$data = $this->load->language($this->path);
		$this->document->addScript('view/javascript/easyphoto/jquery.magnific-popup.min.js');
		$this->document->addStyle('view/javascript/easyphoto/magnific-popup.css');
		$data['product_images'] = $product_images['product_images'];
		$data['main_photo'] = $product_images['image'];
		$data['main_thumb'] = $product_images['thumb'];
		$data['user_token'] = $this->session->data['user_token'];
		$data['module_easyphoto_status'] = $this->config->get('module_easyphoto_status');
		$data['upload_link'] = "index.php?route=" . $this->path . "/upload";
		$data['resize_link'] = "index.php?route=" . $this->path . "/resize_rename";
		$data['clear_cart_link'] = "index.php?route=" . $this->path . "/clear_cart";
		$data['rotate_link'] = "index.php?route=" . $this->path . "/rotate";
		$data['easy_product_id'] = !isset($this->request->get['product_id']) ? false : "&product_id=" . $this->request->get['product_id'];
		$data['module_easyphoto_main'] = $this->config->get('module_easyphoto_main');
		$data['easyphoto_not_delete'] = $this->config->get('easyphoto_not_delete');
		$data['easyphoto_for'] = false; //for new product

		$this->load->model('tool/image');
		$data['placeholder'] = $this->model_tool_image->resize('no_image.png', 100, 100);

		if(isset($this->request->get['product_id'])){
			$language_id = $this->config->get('module_easyphoto_language') ? $this->config->get('module_easyphoto_language') : '1';

			$product_name_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $this->request->get['product_id'] . "' AND language_id = '" . $language_id . "'");

			if(isset($product_name_query->row['name'])){
				$data['easyphoto_for'] = $product_name_query->row['name'];
			}
		}

		//trash_photo
		$data['trash_photos'] = array();
		if(isset($this->request->get['product_id'])){
			$in_product = array();
			$in_product[$product_images['image']] = $product_images['image'];
			foreach($product_images['product_images'] as $image_item){
				$in_product[$image_item['image']] = $image_item['image'];
			}
			$dir = DIR_IMAGE . $this->getDirectory() . $this->request->get['product_id'] . '/';
			if (file_exists($dir)){
				foreach(glob($dir . '*') as $file){
					if (file_exists($file)){
						$image = str_replace(DIR_IMAGE, "", $file);
						if(!in_array($image, $in_product)){
							$data['trash_photos'][$file] = array(
								'image' => $image,
								'thumb' => $this->model_tool_image->resize($image, 100, 100)
							);
						}
					}
				}
			}
		}

		//only for 3.0 + user_token
		$data['https_server'] = HTTPS_SERVER;
		$data['https_catalog'] = HTTPS_CATALOG;
		$data['count_product_images'] = count($data['product_images']);
		$data['count_trash_photos'] = count($data['trash_photos']);
		$data['microtime_true'] = microtime(true);

		return $this->load->view('extension/module/easyphoto_form', $data);
	}

	public function upload() {
		if(isset($this->request->files["easyphoto"]["name"])){
				if (!is_dir($this->getDirectory(1))) { //если нет директории
					mkdir($this->getDirectory(1), 0777, true);
				}
				if (!is_dir($this->getDirectory(1) . "tmp/")) { //если нет tmp директории
					mkdir($this->getDirectory(1) . "tmp/", 0777, true);
				}
		 		move_uploaded_file($this->request->files["easyphoto"]["tmp_name"], $this->getDirectory(1) . "tmp/" . $this->request->files["easyphoto"]["name"]);
		 }
	}

	public function resize_rename($from_model = array()) {
		if(!$from_model){ //прямая загрузка
			$photo = $this->request->get['photo'];
		}else{
			$photo = $from_model['image']; //фото передаем из модели при добавлении товара
		}

		$ext = "." . strtolower(preg_replace('/^.*\.(.*)$/s', '$1', $photo)); //расширение файла
		$directory = $this->getDirectory() . 'tmp/';

		//если товар уже есть - переименование фото и перемещение в директорию с id товара
		if(isset($this->request->get['product_id']) or isset($from_model['product_id'])){
			if(!$from_model){
				$product_id = $this->request->get['product_id'];
			}else{
				$product_id = $from_model['product_id'];
			}
			$language_id = $this->config->get('module_easyphoto_language') ? $this->config->get('module_easyphoto_language') : '1';

			//3.1+
			if($this->config->get('module_easyphoto_name')){
				if(!$this->config->get('module_easyphoto_from')){
					$easyphoto_from = "name";
				}else{
					$easyphoto_from = $this->config->get('module_easyphoto_from');
				}
				if($easyphoto_from == "name"){
					$product_name_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product_id . "' AND language_id = '" . $language_id . "'");
					$name_from = isset($product_name_query->row['name'])?$product_name_query->row['name']:false;
				}else{
					$product_name_query = $this->db->query("SELECT " . $easyphoto_from . " FROM " . DB_PREFIX . "product WHERE product_id = '" . $product_id . "'");
					$name_from = isset($product_name_query->row[$easyphoto_from])?$product_name_query->row[$easyphoto_from]:false;
					//проверка на пустое поле
					if(empty($name_from)){
						$product_name_query = $this->db->query("SELECT name FROM " . DB_PREFIX . "product_description WHERE product_id = '" . $product_id . "' AND language_id = '" . $language_id . "'");
						$name_from = isset($product_name_query->row['name'])?$product_name_query->row['name']:false;
					}
				}
			}
			if(!isset($name_from)){$name_from ='';}
			//новое название фото
			if($name_from && $this->config->get('module_easyphoto_name')){ //если есть имя товара в том языке + включена опция - фото из названия
				$photo_name = $this->transform($name_from);
			}else{
				$photo_name = $this->transform($photo); //если не надо названия или его нет - очистка фото от мусора
				$photo_name = str_replace("." . $ext, '', $photo_name); //убираем расширение
			}

			//скан папки для обхода фото и обнаружения последнего идентификатора
			$photo_dir_id = $this->getDirectory(1) . $product_id;
			if (!is_dir($photo_dir_id)) { //если нет директории
				mkdir($photo_dir_id, 0777, true); //создаем
			}

			if(!$this->config->get('module_easyphoto_separate')){
				$easyphoto_separate = "-";
			}else{
				$easyphoto_separate = trim($this->config->get('module_easyphoto_separate'));
				$easyphoto_separate = str_replace(array('"',"'","&","/"), "-", $easyphoto_separate);
			}
			//3.1-

			$all_photos = scandir($photo_dir_id); //берем все файлы в массив
			$counter = count($all_photos)-1; //счетчик для текущей фотографии
			//3.1+
			$new_photo_name = $photo_name . $easyphoto_separate . $counter . $ext; //новое имя фото со счетчиком
			//3.1-
			if (is_file($photo_dir_id . '/' . $new_photo_name)) {
				$new_photo_name = "alt_" . $new_photo_name;
			}

			if(!$from_model){ //прямая загрузка
				$old_file = $this->getDirectory(1) . "tmp/" . $photo;
			}else{
				$old_file = DIR_IMAGE . $photo;
			}

			if(is_file($old_file)){
				if(!$from_model){ //прямая загрузка
					rename($old_file, $photo_dir_id . '/' . $new_photo_name); //перемещаем в папку с id товара
				}else{
					copy($old_file, $photo_dir_id . '/' . $new_photo_name); //копируем! в папку с id товара - если новый товар
				}
			}

			$photo = $new_photo_name; //передаем уже новое фото на ресайз и в админку
			$directory = $this->getDirectory() . $product_id . '/';
		}

		$image = array();
		$image['mt'] = str_replace(".", "", microtime(true));
		$this->load->model('tool/image');
		$image['image'] = $directory . $photo;

		if(!$from_model){
			if (is_file(DIR_IMAGE . $image['image'])) {
				$image['thumb'] = $this->model_tool_image->resize($image['image'], 100, 100);
			} else {
				$image['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($image));
		}else{
			return $image['image'];
		}
	}

	public function clear_cart(){
		$count_cart = 0;

		if(isset($this->request->get['product_image_delete'])){
			foreach($this->request->get['product_image_delete'] as $filename){
				$file = DIR_IMAGE . $filename['image'];
				if (file_exists($file)){
					unlink($file);
					$count_cart++;
				}
			}
		}

		echo $count_cart;
	}

	public function clear_tmp(){
		$dir = DIR_IMAGE . $this->getDirectory() . 'tmp/';
		if (file_exists($dir)){
			foreach(glob($dir . '*') as $file){
				unlink($file);
			}
		}
	}

	public function transform($string){ //3.0
		if($string){
			$translit=array(
				"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"zh","З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"shch","Ъ"=>"","Ы"=>"y","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya",
				"а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"zh","з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r","с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"shch","ъ"=>"","ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
				"A"=>"a","B"=>"b","C"=>"c","D"=>"d","E"=>"e","F"=>"f","G"=>"g","H"=>"h","I"=>"i","J"=>"j","K"=>"k","L"=>"l","M"=>"m","N"=>"n","O"=>"o","P"=>"p","Q"=>"q","R"=>"r","S"=>"s","T"=>"t","U"=>"u","V"=>"v","W"=>"w","X"=>"x","Y"=>"y","Z"=>"z"
			);
			$string = str_replace("_", "-", $string);
			$string = mb_strtolower($string, 'UTF-8');
			$string = strip_tags($string);
			$string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');
			$string = strtr($string,$translit);
			$string = preg_replace("/[^a-zA-Z0-9_]/i","-",$string);
			$string = preg_replace("/\-+/i","-",$string);
			$string = preg_replace("/(^\-)|(\-$)/i","",$string);
			$string = preg_replace('/-{2,}/', '-', $string);
			$string = trim($string, "-");

			return $string;
		}
	}

	public function rotate() {

		$status = false;

		$degrees = $this->request->get['degrees'];
		$ext = strtolower(preg_replace('/^.*\.(.*)$/s', '$1', $this->request->get['photo']));

		$rotateFilename = str_replace("." . $ext, "", $this->request->get['photo']);
		$new_file = str_replace(array("_r90", "_r270"), "", $rotateFilename) . "_r" . $degrees . "." . $ext;
		$rotateFilename = DIR_IMAGE . $new_file; //новый файл

		copy(DIR_IMAGE . $this->request->get['photo'], $rotateFilename); //копируем  в name_r90.ext

		if($ext == 'png'){
		   header('Content-type: image/png');
		   $source = imagecreatefrompng($rotateFilename);
		   $bgColor = imagecolorallocatealpha($source, 255, 255, 255, 127);
		   $rotate = imagerotate($source, $degrees, $bgColor);
		   imagesavealpha($rotate, true);
		   imagepng($rotate, $rotateFilename);
			 $status = true;
		}

		if($ext == 'jpg' || $ext == 'jpeg'){
		   header('Content-type: image/jpeg');
		   $source = imagecreatefromjpeg($rotateFilename);
		   $rotate = imagerotate($source, $degrees, 0);
		   imagejpeg($rotate, $rotateFilename);
			 $status = true;
		}

		if($status){
			imagedestroy($source);
			imagedestroy($rotate);

			$image = array();
			$this->load->model('tool/image');
			$image['image'] = $new_file;
			$image['mt'] = str_replace(".", "", microtime(true));

			if (is_file(DIR_IMAGE . $image['image'])) {
				$image['thumb'] = $this->model_tool_image->resize($image['image'], 100, 100);
			} else {
				$image['thumb'] = $this->model_tool_image->resize('no_image.png', 100, 100);
			}
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($image));
		}

	}

	public function getDirectory($full = false) {

		$directory_set = $this->config->get('module_easyphoto_direct')?rtrim(ltrim($this->config->get('module_easyphoto_direct'), '/'), '/'):'easyphoto';
		$directory = "catalog/" . $directory_set . "/";

		if($full){
			$directory = DIR_IMAGE . $directory;
		}

		return $directory;
	}

	// public function send() {
	// 	$this->load->language($this->path);

	// 	$domen = explode("//", HTTP_CATALOG);

	// 	$prepare_data = array(
	// 		'email'     => $this->config->get('config_email'),
	// 		'module'    => $this->language->get('module') . " " . $this->language->get('version'),
	// 		'site' 	    => str_replace("/", "", $domen[1]),
	// 		'sec_token' => "3274507573",
	// 		'method'	=> 'POST',
	// 		'lang'		=> $this->config->get('config_language'),
	// 		'engine'	=> VERSION,
	// 		'date'		=> date("Y-m-d H:i:s")
	// 	);

	// 	if($curl = curl_init()) { //POST CURL
	// 		curl_setopt($curl, CURLOPT_URL, "https://microdata.pro/index.php?route=sale/easyphoto");
	// 		curl_setopt($curl, CURLOPT_HEADER, false);
	// 		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	// 		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
	// 		curl_setopt($curl, CURLOPT_POST, true);
	// 		curl_setopt($curl, CURLOPT_POSTFIELDS, $prepare_data);
	// 		$register_number = curl_exec($curl);
	// 		curl_close($curl);
	// 		$response['content'] = $register_number;
	// 		$response['status'] = true;
	// 	}else{ //file_get_contents
	// 		$header = "User-Agent: " . (isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT']:'';
	// 		$header .= " \r\n";
	// 		$header .= "Content-type: application/x-www-form-urlencoded\r\n";
	// 		$header .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8 \r\n";
	// 		$header .= "Accept-language: en-us,en;q=0.5\r\n";
	// 		$header .= "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n";
	// 		$header .= "Keep-Alive: 300\r\n";
	// 		$header .= "Connection: keep-alive\r\n";
	// 		$header .= "Referer: " . $prepare_data['site'];
	// 		$header .= "\r\n";
	// 		$option = array('http' => array('method' => 'POST', 'header' => $header, 'content' => http_build_query($prepare_data)));
	// 		$stream_context = stream_context_create($option);
	// 		try {
	// 			$response['content'] = file_get_contents("https://microdata.pro/index.php?route=sale/easyphoto", FALSE , $stream_context);
	// 			$response['status'] = true;
	// 		}  catch (E_WARNING $e) {
	// 			$response['content'] = '';
	// 			$response['status'] = false;
	// 		}
	// 	}

	// 	return $response;
	// }

	// public function key($key){
	// 	$domen = explode("//", HTTP_CATALOG);
	// 	$license = false;
	// 	$a=0;if(isset($key) && !empty($key)){ $key_array = explode("327450", base64_decode(strrev(substr($key, 0, -7))));if($key_array[0] == base64_encode(str_replace("/", "", $domen[1])) && $key_array[1] == base64_encode(3274507473+100)){$a= 1;}}
	// 	return $license=str_replace($key,str_replace("/", "", $domen[1]),$a);
	// }
}
