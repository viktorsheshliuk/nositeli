<?php
final class Ocext_Loader {
	protected $registry;

	
	public function __construct($registry) {
		$this->registry = $registry;
	}
        
        public function view($route, $data = array()) {
		$output = null;
		
		$route = preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$route);
		
		$result = $this->registry->get('event')->trigger('view/' . $route . '/before', array(&$route, &$data, &$output));
		
		if ($result) {
			return $result;
		}
		
		if (!$output) {
			$template = new Template('ocext_php',   $this->registry); /// я добавил $registry              $this->registry
 			
			foreach ($data as $key => $value) {
				$template->set($key, $value);
			}
		
			$output = $template->render($route . '.tpl');
		}
                
		$result = $this->registry->get('event')->trigger('view/' . $route . '/after', array(&$route, &$data, &$output));
		
		if ($result) {
			return $result;
		}
		
		return $output;
	}
}