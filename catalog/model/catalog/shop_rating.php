<?php
class ModelCatalogShopRating extends Model {

	public function getCustomTypes(){

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating_custom_types WHERE status = '1' ORDER BY id";

		$query = $this->db->query($sql);

		return $query->rows;

	}
	public function getRateCustomRatings($rate_id){
		$customs =$this->getCustomTypes();

		$rates= array();
		foreach($customs as $custom){
			$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating_custom_values WHERE custom_id = '".$custom['id']."' AND rate_id = '".$rate_id."' ORDER BY id";
			$query = $this->db->query($sql);
			$result = $query->row;

			if($result = $query->row){
				$rates[$custom['id']] = array(
					'type_id' => $custom['id'],
					'title' => $custom['title'],
					'value' => $result['custom_value'],
				);
			}
		}

		return $rates;

	}

	public function getStoreRatings($data = array()){

		$store_id =$this->config->get('config_store_id');

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating WHERE store_id ='" . $store_id . "' AND rate_status = '1' ORDER BY  rate_id DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);
		return $query->rows;

	}
	public function getStoreRatingsAll(){

		$store_id =$this->config->get('config_store_id');

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating WHERE store_id ='" . $store_id . "' AND rate_status = '1' ORDER BY  date_added DESC";


		$query = $this->db->query($sql);

		return $query->rows;

	}
	public function getStoreRatingsTotal(){

		$store_id =$this->config->get('config_store_id');

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "shop_rating WHERE store_id ='" . $store_id . "' AND rate_status = '1' ORDER BY  rate_id DESC";


		$query = $this->db->query($sql);

		return $query->row['total'];

	}
	public function customerRatingsCount($customer_id){

		$store_id =$this->config->get('config_store_id');

		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "shop_rating WHERE store_id ='" . $store_id . "' AND customer_id = '".$customer_id."'";


		$query = $this->db->query($sql);

		return $query->row['total'];

	}
	public function getLastStoreRatings($limit){

		$store_id =$this->config->get('config_store_id');

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating WHERE store_id ='" . $store_id . "' AND rate_status = '1' ORDER BY  rate_id DESC LIMIT ".$limit;

		$query = $this->db->query($sql);

		return $query->rows;

	}
	public function getRatingAnswers(){

		$sql = "SELECT rate_id, comment FROM " . DB_PREFIX . "shop_rating_answers ";

		$query = $this->db->query($sql);

		$arr = array();
		foreach($query->rows as $ans){
			$arr[$ans['rate_id']] = $ans['comment'];
		}
		return $arr;

	}

	public function addRating($data){
		//$this->event->trigger('pre.shop_rating.add', $data);
		$store_id =$this->config->get('config_store_id');
		if($this->config->get('shop_rating_moderate')){
			$status = 0;
		}else{
			$status = 1;
		}
		if(isset($data['shop_rate-input'])){
			if((int)$data['shop_rate-input'] < 0){
				$shop_rate = 0;
			}else if((int)$data['shop_rate-input'] > 5){
				$shop_rate = 5;
			}else{
				$shop_rate = (int)$data['shop_rate-input'];
			}
		}else{
			$shop_rate = null;
		}
		if(isset($data['site_rate-input'])){
			if((int)$data['site_rate-input'] < 0){
				$site_rate = 0;
			}else if((int)$data['site_rate-input'] > 5){
				$site_rate = 5;
			}else{
				$site_rate = (int)$data['site_rate-input'];
			}

		}else{
			$site_rate = null;
		}
		if(isset($data['good'])){
			$good = $this->db->escape($data['good']);
		}else{
			$good = null;
		}
		if(isset($data['bad'])){
			$bad = $this->db->escape($data['bad']);
		}else{
			$bad = null;
		}

		$this->db->query("INSERT INTO " . DB_PREFIX . "shop_rating SET store_id = '" . $store_id . "', customer_id = '" . (int)$this->customer->getId() . "', date_added =  NOW(), rate_status = '". $status ."', customer_name = '" . $this->db->escape($data['name']) . "', customer_email= '" . $this->db->escape($data['email']) . "', shop_rate= '" . $shop_rate . "', site_rate= '" . $site_rate . "', comment= '" . $this->db->escape($data['comment']) . "', good= '" . $good . "', bad= '" . $bad . "'");

		$rating_id = $this->db->getLastId();

		if ($rating_id) {
			$customs =$this->getCustomTypes();

			foreach($customs as $custom){
				if(isset($data['custom_'.$custom['id'].'_rate-input'])){
					if((int)$data['custom_'.$custom['id'].'_rate-input'] < 0 ){
						$rate = 0;
					}else{
						$rate = (int)$data['custom_'.$custom['id'].'_rate-input'];
					}

					$this->db->query("INSERT INTO " . DB_PREFIX . "shop_rating_custom_values SET custom_id = '" . $custom['id'] . "', rate_id = '" . $rating_id . "', custom_value =  '". $rate ."'");

				}


			}




			$this->load->language('information/shop_rating');

			$subject = sprintf($this->language->get('text_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_mail_waiting') . "\n";
			$message .= sprintf($this->language->get('text_mail_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			if($shop_rate){
				$message .= sprintf($this->language->get('text_mail_shop_rating'), $shop_rate) . "\n";
			}
			if($site_rate){
				$message .= sprintf($this->language->get('text_mail_site_rating'), $site_rate) . "\n";
			}
			$message .= $this->language->get('text_mail_comment') . "\n";
			$message .= html_entity_decode($data['comment'], ENT_QUOTES, 'UTF-8') . "\n\n";
			if($good) {
				$message .= $this->language->get('text_mail_good') . "\n";
				$message .= html_entity_decode($good, ENT_QUOTES, 'UTF-8') . "\n\n";
			}
			if($bad){
				$message .= $this->language->get('text_mail_bad') . "\n";
				$message .= html_entity_decode($bad, ENT_QUOTES, 'UTF-8') . "\n\n";
			}

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


			if($this->config->get('shop_rating_email')){
				$mail->setTo($this->config->get('shop_rating_email'));
			}else{
				$mail->setTo($this->config->get('config_email'));
			}
			$mail->setFrom($this->config->get('config_email'));

			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}

		return $rating_id;
	}

	public function request_mail($order_id) {
        $this->load->language('information/shop_rating');

        $this->load->model('checkout/order');
        $this->load->model('catalog/shop_rating');

        $order_info = $this->model_checkout_order->getOrder($order_id);
        $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');
        $store_url = '<a href="'.$order_info['store_url'].'">'.$store_name.'</a>';
        $customer_name = $order_info['firstname'];
        $ratings_link = '<a href="'.$this->url->link('information/shop_rating','',true).'">"'.$this->language->get('sr_title').'"</a>';

        if (isset($order_info['customer_id']) && $order_info['customer_id'] > 0) {
            $count = $this->model_catalog_shop_rating->customerRatingsCount($order_info['customer_id']);
            if($count && $count > 0){
                $send = false;
            } else {
                $send = true;
            }
        } else {
            $send = true;
        }

        if($order_info['order_status_id'] == $this->config->get('shop_rating_request_status') && $send == true) {


            if($this->config->get('shop_rating_request_subject')){
                $subject = html_entity_decode($this->config->get('shop_rating_request_subject'));
            }else{
                $subject = sprintf($this->language->get('text_request_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
            }

            if($this->config->get('shop_rating_request_text') && $this->config->get('shop_rating_request_text') != ''){
                $mail_text = html_entity_decode($this->config->get('shop_rating_request_text'), ENT_QUOTES, 'UTF-8');
            }else{
                $mail_text = html_entity_decode($this->language->get('text_request_mail_text'), ENT_QUOTES, 'UTF-8');
            }
            $mail_text = str_replace('[store_name]', $store_name, $mail_text);
            $mail_text = str_replace('[store_name_link]', $store_url, $mail_text);
            $mail_text = str_replace('[customer_name]', $customer_name, $mail_text);
            $mail_text = str_replace('[ratings_link]', $ratings_link, $mail_text);

            $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>';

            $message .= $mail_text;
            $message .= '</body></html>';

            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');
            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


            if(!empty($order_info['email'])) {
                $mail->setTo($order_info['email']);
                $mail->setFrom($this->config->get('config_email'));

                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject($subject);
                $mail->setHtml($message);
                $mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
                $mail->send();
            }

        }

    }

}