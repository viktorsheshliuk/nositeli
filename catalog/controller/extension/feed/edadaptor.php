<?php
class ControllerExtensionFeedEDAdaptor extends Controller {
    
	/*
	 * OpenCart default setting
	 */

	private $dir_download;
    
        public function __construct($registry) {
	    
            $this->registry = $registry;
	    
	    $this->dir_download = DIR_DOWNLOAD;
	    
        }
        
	public function index() {
	    
	    if(isset($this->request->get['check_callback_host'])){
		
		$this->checkCallbackHost();
		
	    }
	    elseif (isset ($this->request->get['file_path']) && isset ($this->request->get['private_token']) && isset ($this->request->get['get_file_by_file_path'])) {
	    
		$this->getFileByFilePath();
		
	    }
	    else{
		
		$this->get404();
		
	    }
	    
	}
	
	public function getFileByFilePath(){
	    
	    $this->load->model('tool/edadaptor'); 
	    
	    $api = $this->model_tool_edadaptor->getSetting('api');
	    
	    if(!$api){
		
		$this->get404();
		
	    }
	    else{
		
		if(isset($api['api']['dir_download']) && $api['api']['dir_download'] && is_dir($api['api']['dir_download']) && is_writable($api['api']['dir_download'])){
		    
		    $dir_download = $api['api']['dir_download'];
		    
		    $file_path = urldecode($this->request->get['file_path']);
		    
		    $private_token1 = $this->request->get['private_token'];
		    
		    $private_token2 = md5($api['api']['private_key'].$file_path);
		    
		    $file = $dir_download.$file_path;
		    
		    if($private_token1!==$private_token2 || !is_file($file)){
			
			$this->get404();
			
		    }else{
			
			if (!headers_sent()) {
				if (file_exists($file)) {
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="' . md5($file_path) . '"');
					header('Expires: 0');
					header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
					header('Pragma: public');
					header('Content-Length: ' . filesize($file));

					if (ob_get_level()) {
						ob_end_clean();
					}

					readfile($file, 'rb');

					exit();
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				$this->get404('Headers already sent out!');
			}
			
		    }
		    
		}
		else{
		    
		    $this->get404();
		    
		}
		
	    }
	    
	}
	
	public function checkCallbackHost(){
	    
	    $result = array(
		'Edadaptor_dir_local_files' => 'false',
	    );
	    
	    $this->load->model('tool/edadaptor'); 
	    
	    $api = $this->model_tool_edadaptor->getSetting('api');
	    
	    if($api && isset($api['api']['dir_download']) && $api['api']['dir_download'] && is_dir($api['api']['dir_download']) && is_writable($api['api']['dir_download'])){
		
		$result['Edadaptor_dir_local_files'] = 'true';
		
	    }elseif(is_dir($this->dir_download) && is_writable($this->dir_download)){
		
		$result['Edadaptor_dir_local_files'] = 'true';
		
	    }
	    
	    foreach ($result as $name => $value) {
		
		$this->response->addHeader($name.': '.$value);
		
	    }
	    
	    $this->response->addHeader('Content-Type: application/json');
	    
	    $this->response->setOutput(json_encode($result));
	    
	}
	
	public function get404($text_error='') {
	    
		if(!$text_error){
		    
		    $text_error = $this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found';
		    
		}
	    
		$data['breadcrumbs'][] = array(
			'text' => $this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found',
			'href' => '#'
		);
		$data['continue'] = $this->url->link('common/home');
		$data['button_continue'] = 'continue';
		$data['text_error'] = $text_error;
		$data['heading_title'] = $text_error;
		$this->document->setTitle($this->language->get('text_error'));
		$this->document->setTitle($this->language->get('text_error'));
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
		$this->response->addHeader('EDAdapter Error: '.$text_error);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		$this->response->setOutput($this->load->view('error/not_found', $data));
	    
	}
	
}
?>