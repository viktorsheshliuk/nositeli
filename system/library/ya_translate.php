<?php
/**
 * Yandex Translate
 * 
 * API offers text translation features for over 30 languages. 
 * 
 * To begin using this class you need to get your own API key, you can do it just in pair of clicks.
 * Firstly create a Yandex account - 	://passport.yandex.com/passport
 * Finally get your API key - 	https://tech.yandex.ru/keys/get/?service=trnsl
						   
 * 
 * Official api description and documentation:
 * in russian - 	https://tech.yandex.ru/translate/doc/dg/reference/translate-docpage/
 * 
 **/
final class API2 {
	const API_URL = 'https://translate.api.cloud.yandex.net/translate/v2/';

	private $_apiKey = array(
		'api_key'    => '',
		'folder_id' => 'b1gikh8j6ov7rpljeb60'

	);

	public function setApiKey($apiKey) {

		$this->_apiKey = $apiKey;
	}

	public function getApiKey() {
		return $this->_apiKey;
	}

	public function getLangs($ui) {
		$callResult = $this->makeCall('languages', array('folder_id'=>$this->_apiKey['folder_id']));
		if (isset($callResult['languages'])) {
			$langs = array();
			foreach ( $callResult['languages'] as $l) {
		
				$langs[$l['code']] = isset($l['name'])?$l['name']:'';
			}
			return array('dirs' => array(), 'langs' =>$langs);

		}
		return $callResult;
	}
	
	public function __construct($apiKey) {
		//$this->_apiKey = $api_key;
	}

	protected function makeCall($uri, array $requestParameters) {
		$data_string = json_encode($requestParameters);
		$url = self::API_URL . $uri;
		$curlOptions = array(
			CURLOPT_URL				=> $url,
			CURLOPT_POSTFIELDS		=> $data_string,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_CONNECTTIMEOUT  => 20,
			CURLOPT_TIMEOUT			=> 60,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_CUSTOMREQUEST   => 'POST',
			CURLOPT_HTTPHEADER      => array(                                                                          
				'Content-Type: application/json',
				'Authorization: Api-Key ' . $this->_apiKey['api_key']
   			)
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$callResult = curl_exec($ch);

		if (!$callResult) {
		//	$this->log->write('Error: makeCall() - cURL error: ' . curl_error($ch));
		}
		curl_close($ch);
		
		$callResult = json_decode($callResult, true);
		
		if (isset($callResult['code']) && $callResult['code'] > 200) {
			$this->log->write('API error: ' .$callResult['message'], $callResult['code']);
		}
		
		return $callResult;
	}

	public function translate($text, $language, $format = 'auto', $options = null) {
			list($sourceLanguageCode,$targetLanguageCode) = explode('-', $language);
			if ($format == 'auto') {
				if (is_array($text)) {
					$textD = implode('', $text);
				} else {
					$textD = $text;
				}
				$format = $textD == strip_tags($textD) ? 'PLAIN_TEXT' : 'HTML';
			}
			if (!is_array($text)) {
				$texts = array($text);
			} else {
				$texts = $text;
			}
			
			$data = array(
				"sourceLanguageCode" => $sourceLanguageCode,
				"targetLanguageCode" => $targetLanguageCode,
				"format" => strtoupper($format),
				"texts" => $texts,
				"folderId" =>$this->_apiKey['folder_id']
				//'folder_id'=>$this->_apiKey['folder_id']
			);

			$callResult = $this->makeCall('translate',$data);
			
		$return =  array(
			'code' => isset($callResult['code'])?$callResult['code']:200,
			'message' => isset($callResult['message'])?$callResult['message']:'',
			'text' => array(isset($callResult['translations'][0]['text'])?$callResult['translations'][0]['text']:'')
		);
		return $return;
	}
}

final class API1 {

	const API_URL = 'https://translate.yandex.net/api/v1.5/tr.json/';

	private $_apiKey;
	
	public function __construct($apiKey) {
		$this->_apiKey = $apiKey;
	}

	public function setApiKey($apiKey) {
		$this->_apiKey = $apiKey;
	}

	public function getApiKey() {
		return $this->_apiKey;
	}

	public function getLangs($ui) {
		$callResult = $this->makeCall('getLangs', array('ui'=>$ui));
		return $callResult;
	}

	public function detectLanguage($text, $hint = null)	{
		if (is_array($hint)) {
			$hint = implode(',', $hint);
		}
		$callResult = $this->makeCall(
			'detect', array(
				'text' => $text,
				'hint' => $hint
			)
		);
		return $callResult['lang'];
	}

	protected function makeCall($uri, array $requestParameters) {
		$requestParameters['key'] = $this->getApiKey()['api_key'];
		
		$text = '';
		if (isset($requestParameters['text']) && is_array($requestParameters['text'])) {
			$text = '&text=' . implode('&text=', $requestParameters['text']);
			unset($requestParameters['text']);
		}


		$requestParameters = http_build_query($requestParameters) . $text;
		$url = self::API_URL . $uri;
		$curlOptions = array(
			CURLOPT_URL             => $url,
			CURLOPT_POSTFIELDS      => $requestParameters,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_CONNECTTIMEOUT  => 20,
			CURLOPT_TIMEOUT         => 60,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_CUSTOMREQUEST   => 'POST',
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$callResult = curl_exec($ch);

		if (!$callResult) {
			//$this->log->write('Error: makeCall() - cURL error: ' . curl_error($ch));
		}
		curl_close($ch);
		
		$callResult = json_decode($callResult, true);
		
		if (isset($callResult['code']) && $callResult['code'] > 200) {
			//$this->log->write('API error: ' .$callResult['message'], $callResult['code']);
		}
		
		return $callResult;
	}

	public function translate($text, $language, $format = 'plain', $options = null) {
			if ($format == 'auto') {
				if (is_array($text)) {
					$textD = implode('', $text);
				} else {
					$textD = $text;
				}
				$format = $textD == strip_tags($textD) ? 'plain' : 'html';
			}
		
			$callResult = $this->makeCall(
				'translate', array(
					'text'	  => $text,
					'lang'	  => $language,
					'format'  => $format,
				)
			);
		return $callResult;
	}

}

final class Google {

	const API_URL = 'https://translation.googleapis.com/language/translate/v2';

	private $_apiKey;
	
	public function __construct($apiKey) {
		$this->_apiKey = $apiKey;
	}

	public function setApiKey($apiKey) {
		$this->_apiKey = $apiKey;
	}

	public function getApiKey() {
		return $this->_apiKey;
	}

	public function getLangs($ui) {
		$callResult = $this->makeCall('getLangs', array('ui'=>$ui));
		return $callResult;
	}

	public function detectLanguage($text, $hint = null)	{
		if (is_array($hint)) {
			$hint = implode(',', $hint);
		}
		$callResult = $this->makeCall(
			'detect', array(
				'text' => $text,
				'hint' => $hint
			)
		);
		return $callResult['lang'];
	}

	protected function makeCall(array $requestParameters) {
		$requestParameters['key'] = $this->getApiKey()['api_key'];
		if (is_array($requestParameters['q'])) {
			$q_request[] = array();
			foreach ($requestParameters['q'] as $q){
				$q_request[] = urlencode(html_entity_decode($q, ENT_QUOTES, 'UTF-8'));
			}
			$input = implode('&q=',$q_request);
		} else {
			$input = $requestParameters['q'];
		}

		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . $requestParameters['key'] . '&source=' . $from . '&target=' . $to . '&format=' . $format . '&q=' . $text);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-HTTP-Method-Override: GET'));
		
//            curl_setopt($ch, CURLOPT_REFERER, ($this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG));
		
		$result=array();
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'key'      => $requestParameters['key'],
			'source'   => $requestParameters['from'],
			'target'   => $requestParameters['to'],
			'format='  => $requestParameters['format'],
			'q'        => $input,
			)
		);
					
		$callResult = curl_exec($ch);
        curl_close($ch);
		$json = json_decode($callResult, true);
		if(isset($json['data']['translations'])){
			$result['code'] = 200;
			$result['text'][] = $json['data']['translations'][0]['translatedText'];
		} elseif ($json['error']){
			$error = $json['error']['message'] . ' ' . $json['error']['code'];
			if(isset($json['error']['errors'][0]['reason'])){
				$error .= ' / Reason: ' . $json['error']['errors'][0]['reason'];
			}
			$result['error'] = $error;
		} else {
			$result['error'] = 'error_unknown_problem';
		}

        return $result;
	}

	public function translate($text, $language, $format = 'html', $options = null) {
		list($sourceLanguageCode,$targetLanguageCode) = explode('-', $language);
			if ($format == 'auto') {
				if (is_array($text)) {
					$textD = implode('', $text);
				} else {
					$textD = $text;
				}
				$format = $textD == strip_tags($textD) ? 'plain' : 'html';
			}

		$callResult = $this->makeCall(
			array(
				'q'	      => $text,
				'from'    => $sourceLanguageCode,
				'to'      => $targetLanguageCode,
				'format'  => $format,
			)
		);
		return $callResult;
	}
}
 
class Ya_translate {

	private $adaptor;	 
	
	/**
	* Oc config.
	*
	* @var string
	*/
	private $config;

	private $log;
	

	/**
	* Default constructor.
	*
	* @param string $apiKey Yandex API Key
	* 
	* @return self
	*/
	public function __construct($register, $adaptor=null ,$apiKey=null) {
		$this->config = $register->get('config');
		$this->log = NEW Log('ya_translate.log');
		if ($adaptor) {
			$class = $adaptor;
		} else {
			$class = $this->config->get('ya_translate_api_provider');
		}
		if (class_exists($class)) {
			$this->adaptor = new $class($apiKey);
		} else {
			throw new \Exception('Error: Could not load Yatranslaye adaptor ' . $adaptor . '!');
		}
		
		if ($apiKey) {
			$this->adaptor->setApiKey($apiKey);
		} else {
			$apiKey_config = $this->config->get('ya_translate_api_key');
			
			$apiKey = $apiKey_config[$class];
			$this->adaptor->setApiKey($apiKey);
		}
		

		return $this;
	}

	public function setApiKey($apiKey) {
		if ($apiKey) {
			$this->adaptor->setApiKey($apiKey);	
		} else {
			$this->log->write('Error: setApiKey() - Api key is required');
		}
		return $this;
	}

	public function getApiKey() {
		$api_key = $this->adaptor->getApiKey();
		if ($api_key) {
			$this->log->write('Error: makeCall() - API key was not set');
		}
		return $api_key;
	}
	
	/**
	* Gets a list of translation directions supported by the service.
	* 
	* @param string $ui If set, the response contains explanations of language codes. Language names are output in the language corresponding to the code in this parameter.
	*
	* @return array
	*/
	public function getLangs($ui) {
		$callResult = $this->adaptor->getLangs($ui);
		
		return new TranslationResponse($callResult, $ui);
	}
	
	/**
	* Detects the language of the specified text.
	* 
	* @link 	://tech.yandex.com/translate/doc/dg/reference/detect-docpage/
	* 
	* @param string $text The text to detect the language for.
	* @param string $hint Optional parameter. List of the most possible languages, separator is comma.
	*
	* @return string
	*/
	public function detectLanguage($text, $hint = null)	{
		$callResult = $this->adaptor->detectLanguagemake($text, $hint);
		return $callResult['lang'];
	}
	
	/**
	* Translates text to the specified language.
	* 
	* @link 	://tech.yandex.com/translate/doc/dg/reference/translate-docpage/
	* 
	* @param string|array		   $text	   The text to translate.
	* @param string				 $language   The translation direction.
	* @param "plain"|"html"|"auto"  $format	 If input text contains html code
	* @param int					$options	Read more on the website
	*
	* @return object
	*/
	public function translate($text, $language, $format = 'plain', $options = null) {
		
		$callResult = $this->adaptor->translate($text, $language, $format, $options);

		return new TranslationResponse($callResult, $text);
	}
	
	/**
	* Makes call to an API server
	* 
	* @param string $uri				API method
	* @param array  $requestParameters  API parameters
	*
	* @return array
	*/
}

/**
 * Yandex Translate
 * Response class
 * 
 * @author Tebiev Aleksandr <tebiev@mail.com>
 **/
class TranslationResponse {
	/**
	* Yandex API request result
	*
	* @var string
	*/
	private $_callResult;
	
	/**
	* Original text before translation
	*
	* @var string
	*/
	private $_sourceText;
	
	/**
	* Default constructor.
	*
	* @param array  $callResult  Yandex API request result
	* @param string $sourceText Original text before translation
	* 
	* @return void
	*/
	function __construct($callResult, $sourceText) {
		$this->_callResult = $callResult;
		$this->_sourceText = $sourceText;
	}
	
	/**
	* Original text before translation
	*
	* @return string
	*/
	public function sourceText() {
		return $this->_sourceText;
	}

	/**
	* Translation direction
	*
	* @return string
	*/
	public function translationDirection() {
		return $this->_callResult['lang'];
	}
	
	/**
	* Transalted text
	*
	* @return array
	*/
	public function translation() {
		return $this->_callResult['text'];
	}

	public function getTranslation() {
		return $this->_callResult;
	}

	/**
	* Transalted text
	*
	* @return string
	*/
	public function __toString() {
		if (isset($this->_callResult['text'][1])) {
			return implode("\n", $this->_callResult['text']);
		}
		return $this->_callResult['text'][0];
	}
}