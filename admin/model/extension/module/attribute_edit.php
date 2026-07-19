<?php
class ModelExtensionModuleAttributeEdit extends Model {

	public function editAttribute($language_id,$data) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product_attribute pa WHERE `text` = '" . $this->db->escape(base64_decode($data['old_text'])) . "' AND language_id = " . (int)$language_id;
		$result = $this->db->query($sql);
		foreach ($result->rows as $row) {
			$sql = "UPDATE " . DB_PREFIX . "product_attribute SET `text` = '" . $this->db->escape($data['text']) . "' WHERE 
			product_id = " . (int)$row['product_id'] . " AND attribute_id = " . (int)$row['attribute_id'] . " AND language_id = " . (int)$language_id;
			$this->db->query($sql);
		}
		return $result->num_rows;
	}

	public function getAttributes($data = array()) {
		if (!empty($data['filter_language_id'])) {
			$language_id = $data['filter_language_id'];
		} else {
			$language_id = $this->config->get('config_language_id');
		}
		
		$sql = "SELECT pa.`text`, pa.`language_id`, COUNT(text) as total, ad.name, agd.name as group_name FROM " . DB_PREFIX . "product_attribute pa
		LEFT JOIN " . DB_PREFIX . "attribute a ON a.attribute_id = pa.attribute_id
		LEFT JOIN " . DB_PREFIX . "attribute_description ad ON pa.attribute_id = ad.attribute_id
		LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON agd.attribute_group_id = a.attribute_group_id
		
		WHERE ad.language_id = " . (int)$language_id . "
		AND agd.language_id = " . (int)$language_id . "
		AND pa.language_id = " . (int)$language_id ;
		
		if (!empty($data['filter_text'])) {
			$sql .= " AND pa.`text` LIKE '" . $this->db->escape($data['filter_text']) . "%'";
		}
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_group_name'])) {
			$sql .= " AND agd.`name` LIKE '" . $this->db->escape($data['filter_group_name']) . "%'";
		}
		$sql .= " GROUP by pa.`text`";

		$sort_data = array(
			'pa.text',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pa.text";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

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

	public function getTotalAttributes($data=array()) {
		if (!empty($data['filter_language_id'])) {
			$language_id = $data['filter_language_id'];
		} else {
			$language_id = $this->config->get('config_language_id');
		}
		
		$sql = "SELECT COUNT(DISTINCT pa.`text`) AS total FROM " . DB_PREFIX . "product_attribute pa";
		
		$sql .= " LEFT JOIN " . DB_PREFIX . "attribute a ON a.attribute_id = pa.attribute_id
		LEFT JOIN " . DB_PREFIX . "attribute_description ad ON pa.attribute_id = ad.attribute_id
		LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON agd.attribute_group_id = a.attribute_group_id
		
		WHERE ad.language_id = " . (int)$language_id . "
		AND agd.language_id = " . (int)$language_id . "
		AND pa.language_id = " . (int)$language_id ;
		
		if (!empty($data['filter_text'])) {
			$sql .= " AND pa.`text` LIKE '" . $this->db->escape($data['filter_text']) . "%'";
		}
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.`name` LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}
		if (!empty($data['filter_group_name'])) {
			$sql .= " AND agd.`name` LIKE '" . $this->db->escape($data['filter_group_name']) . "%'";
		}
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
}
