<?php
class ModelCatalogShopRating extends Model {

	public function getAllRatings(){

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating ORDER BY date_added DESC";

		$query = $this->db->query($sql);

		return $query->rows;

	}
	public function getCustomTypes(){

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating_custom_types ORDER BY id";

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

	public function getRating($rate_id){

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating WHERE rate_id = '".$rate_id."'";

		$query = $this->db->query($sql);

		return $query->row;

	}
	public  function deleteRating($rate_id){
		$sql = "DELETE FROM " . DB_PREFIX . "shop_rating WHERE rate_id = '".$rate_id."'";
		if($this->db->query($sql)){
			return array('rate_id' => $rate_id, 'status' => 'success');
		}else{
			return array('rate_id' => $rate_id, 'status' => 'error');
		}
	}
	public function getRatingAnswer($rate_id){

		$sql = "SELECT * FROM " . DB_PREFIX . "shop_rating_answers WHERE rate_id = '".$rate_id."'";

		$query = $this->db->query($sql);

		return $query->row;

	}
	public function getRatingAnswers(){

		$sql = "SELECT id, rate_id FROM " . DB_PREFIX . "shop_rating_answers WHERE comment <> ''";

		$query = $this->db->query($sql);

		$arr = array();
		foreach($query->rows as $ans){
			$arr[$ans['rate_id']] = $ans['id'];
		}
		return $arr;

	}
	public function addAnswer($rate_id, $answer)
	{


		if ($answer['answer_id']) {
			$sql = "UPDATE " . DB_PREFIX . "shop_rating_answers SET comment='" . $answer['answer'] . "' WHERE id = '" . $answer['answer_id'] . "'";
			$this->db->query($sql);
			$answer_id=$answer['answer_id'];
		} else {

			$this->db->query("INSERT INTO " . DB_PREFIX . "shop_rating_answers SET rate_id = '" . $rate_id . "', user_id = '" . (int)$this->user->getId() . "', date_added =  NOW(), comment = '" . $this->db->escape($answer['answer']) . "'");
			$answer_id = $this->db->getLastId();

		}
		if($answer_id && $this->config->get('shop_rating_notify') && $answer['answer']){
			$answer_item = $this->getRatingAnswer($rate_id);

			if ($answer_item['notified'] != 1) {
				$rating = $this->getRating($rate_id);
				$this->load->language('catalog/shop_rating');

				$subject = sprintf($this->language->get('text_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

				$message = sprintf($this->language->get('text_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8')) . "\n";
				$message .= $this->language->get('text_mail_comment') . "\n";
				$message .= html_entity_decode($answer['answer'], ENT_QUOTES, 'UTF-8') . "\n\n";

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
				$mail->smtp_username = $this->config->get('config_mail_smtp_username');
				$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
				$mail->smtp_port = $this->config->get('config_mail_smtp_port');
				$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');


				$mail->setTo($rating['customer_email']);
				$mail->setFrom($this->config->get('config_email'));

				$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
				$mail->setSubject($subject);
				$mail->setText($message);
				$mail->send();


				$emails = explode(',', $this->config->get('config_mail_alert_email'));

				foreach ($emails as $email) {
					if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
						$mail->setTo($email);
						$mail->send();
					}
				}


				$sql = "UPDATE " . DB_PREFIX . "shop_rating_answers SET notified='1' WHERE id = '" . $answer_id . "'";
				$this->db->query($sql);
			}
		}

	}


	public  function createCustomType($title){
		$this->db->query("INSERT INTO " . DB_PREFIX . "shop_rating_custom_types SET title = '" . $this->db->escape($title) . "', status = '0' ");
		return array('id' => $this->db->getLastId(), 'title' => $title);

	}
	public  function statusCustomType($type_id){
		$sql = "UPDATE " . DB_PREFIX . "shop_rating_custom_types SET status=IF(status=0, 1, 0) WHERE id = '".$type_id."'";
		if($this->db->query($sql)){
			return array('id' => $type_id, 'status' => 'success');
		}else{
			return array('id' => $type_id, 'status' => 'error');
		}
	}
	public  function removeCustomType($type_id){
		$sql = "DELETE FROM " . DB_PREFIX . "shop_rating_custom_types WHERE id = '".$type_id."'";
		if($this->db->query($sql)){
			return array('id' => $type_id, 'status' => 'success');
		}else{
			return array('id' => $type_id, 'status' => 'error');
		}
	}
	public  function changeStatus($rate_id){
		$sql = "UPDATE " . DB_PREFIX . "shop_rating SET rate_status=IF(rate_status=0, 1, 0) WHERE rate_id = '".$rate_id."'";

		if($this->db->query($sql)){
			return 'OK';
		}else{
			return 'DB Error';
		}


	}
	public  function changeDate($rate_id, $new_date){

		$date = date('Y-m-d H:i', strtotime($new_date)).':00';

		$sql = "UPDATE " . DB_PREFIX . "shop_rating SET date_added='".$date."' WHERE rate_id = '".$rate_id."'";
		$this->db->query($sql);

	}
	public  function changeComment($rate_id, $new_comment){

		$sql = "UPDATE " . DB_PREFIX . "shop_rating SET comment='".$new_comment['comment']."', good='".$new_comment['good']."', bad='".$new_comment['bad']."' WHERE rate_id = '".$rate_id."'";
		$this->db->query($sql);

	}
	public function update() {

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating_custom_types` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `title` VARCHAR(255) NOT NULL,
			  `status` INT(1) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating_custom_values` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `custom_id` INT(11) NOT NULL,
			  `rate_id` INT(11) NOT NULL,
			  `custom_value` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");

		$notif_exist = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "shop_rating_answers` WHERE Field = 'notified' ");
		if(count($notif_exist->row) == 0){
			$this->db->query("ALTER TABLE " . DB_PREFIX . "shop_rating_answers ADD COLUMN `notified` INT(1) DEFAULT NULL");
		}

	}
	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating` (
			  `rate_id` INT(11) NOT NULL AUTO_INCREMENT,
			  `store_id` INT(11) NOT NULL,
			  `customer_id` INT(11) DEFAULT NULL,
			  `date_added` DATETIME NOT NULL,
			  `rate_status` INT(1) NOT NULL,
			  `customer_name` VARCHAR(255) NOT NULL,
			  `customer_email` VARCHAR(255) NOT NULL,
			  `shop_rate` INT(1) DEFAULT NULL,
			  `site_rate` INT(1) DEFAULT NULL,
			  `comment` TEXT DEFAULT NULL,
			  `good` TEXT DEFAULT NULL,
			  `bad` TEXT DEFAULT NULL,
			  PRIMARY KEY (`rate_id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating_answers` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `rate_id` INT(11) NOT NULL,
			  `user_id` INT(11) NOT NULL,
			  `date_added` DATETIME NOT NULL,
			  `comment` TEXT NOT NULL,
			  `notified` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating_custom_types` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `title` VARCHAR(255) NOT NULL,
			  `status` INT(1) NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");

		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "shop_rating_custom_values` (
			  `id` INT(11) NOT NULL AUTO_INCREMENT,
			  `custom_id` INT(11) NOT NULL,
			  `rate_id` INT(11) NOT NULL,
			  `custom_value` INT(1) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM DEFAULT COLLATE=utf8_unicode_ci;");


	}
	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "shop_rating`;");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "shop_rating_answers`;");
	}
	public function toogleEvent($set) {
/*
		$sql = "SELECT COUNT(*) AS srrm FROM " . DB_PREFIX . "event WHERE `code` = 'shop_rating_request_mail'" ;
		$events = $this->db->query($sql);


		if($events->row['srrm'] == 0 && $set == 1){
			$this->load->model('extension/event');
			$this->model_extension_event->addEvent('shop_rating_request_mail', 'post.order.history.add', 'information/shop_rating/request_mail');
		}elseif($events->row['srrm'] != 0 && $set == 0){
			$this->load->model('extension/event');
			$this->model_extension_event->deleteEvent('shop_rating_request_mail');
		}
*/
	}


}