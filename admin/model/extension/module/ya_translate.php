<?php
class ModelExtensionModuleYaTranslate extends Model {

	public function install() {
		$sql = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "ya_translate (
			`entity` char(10),
			`id` int(11),
			`language_id` INT(11),
			PRIMARY KEY (`id`,`entity`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin";
		$this->db->query($sql);
		
		$sql ="ALTER TABLE `" . DB_PREFIX ."ya_translate` CHANGE `entity` `entity` CHAR(10)CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT ''";
		$this->db->query($sql);
		$sql = "SHOW COLUMNS FROM " . DB_PREFIX . "ya_translate LIKE 'language_id'";
		$res = $this->db->query($sql);
		if (!$res->num_rows) {
			$sql ="ALTER TABLE `" . DB_PREFIX ."ya_translate` ADD `language_id` INT(11)";
			$this->db->query($sql);
		}
	}

	public function uninstall() {
		$sql = "DROP TABLE IF EXISTS  " . DB_PREFIX . "ya_translate";
		$this->db->query($sql);		
	}
	
	public function attributeFill($data=array()) {
		if (isset($data['from']) && isset($data['to'])) {
			$sql =  "INSERT INTO " . DB_PREFIX . "product_attribute (`product_id`,`attribute_id`,`text`,`language_id`)
				SELECT a.`product_id`,a.`attribute_id`,a.`text`, " . (int)$data['to'] . "
					FROM " . DB_PREFIX ."product_attribute a
					LEFT JOIN " . DB_PREFIX ."product_attribute a2 ON (a2.product_id = a.product_id and a2.language_id= " . (int)$data['to'] . " AND a.attribute_id = a2.attribute_id)
					WHERE a.`language_id`=" . (int)$data['from'] . "
					AND a2.product_id is null";
			$this->db->query($sql);
			return $this->db->countAffected();
		} else {
			return 0;
		}
	}
	
	public function getProduct($product_id) {
	}

	public function getManufacturers($data = array()) {
		$sql = "SELECT m.* FROM " . DB_PREFIX . "manufacturer m ";
		
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if ($data['filter_ready']) {
				$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (m.manufacturer_id = y.id and entity = 'manufacturer')";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "ya_translate as y ON (m.manufacturer_id = y.id and entity = 'manufacturer')";
			}
		}

		$sql .= " WHERE 1 ";
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'name',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY m.name";
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

	public function getTotalManufacturers($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "manufacturer m";
		
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT ";
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (m.manufacturer_id = y.id and entity = 'manufacturer')";
		}
			
		
		$sql .= " WHERE 1 ";
		if (!empty($data['filter_name'])) {
			$sql .= " AND m.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getAttributeValuesTotal($data = array()) {
		$sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "product_attribute pa ";
		$sql .= " WHERE 1"; 
		$sql .= " AND language_id = " . (int)$data['from'];
//		$sql .= " AND product_id > " . (int)$data['last_product_id'];
		$sql .= " AND attribute_id = " . (int)$data['attribute_id'];
		$sql .= " AND md5(pa.text) > '" . $this->db->escape($data['md5_text']) . "'";
		$sql .= " GROUP BY pa.text ";

		$result = $this->db->query($sql);
		if ($result->num_rows) {
			return $result->row['total'];
		} else return 0;
	}

	public function getAttributeValues($data = array()) {

		$sql = "SELECT pa.text, md5(pa.text) as md5_text, pa.attribute_id FROM " . DB_PREFIX . "product_attribute pa";
		$sql .= " WHERE 1"; 
		$sql .= " AND pa.language_id = " . (int)$data['from'];
		//$sql .= " AND pa.product_id > " . (int)$data['last_product_id'];
		$sql .= " AND pa.attribute_id = " . (int)$data['attribute_id'];
		$sql .= " AND md5(pa.text) > '" . $this->db->escape($data['md5_text']) . "'";
		$sql .= " GROUP BY pa.text ";
		$sql .= " ORDER BY md5(pa.text)";
		if (isset($data['limit'])) {
			$sql .= " LIMIT " . (int)$data['limit'];
		}
		$result = $this->db->query($sql);
		return $result->rows;
	}

	public function updateAttribute($data = array()) {
		foreach ($data['value'] as $index_key=>$value) {
			$keys = json_decode($index_key,true);
			foreach ($keys as $fields) {
				list($attribute_id, $md5_text, $language_id) = explode('_',$fields);
				$sql = "UPDATE " . DB_PREFIX . "product_attribute pa1
					JOIN  " . DB_PREFIX ."product_attribute pa2 ON (
						md5(pa2.text) = '" . $this->db->escape($md5_text) . "'
						AND pa2.language_id = '" . (int)$language_id . "'
						AND pa2.attribute_id = pa1.attribute_id
						AND pa1.product_id = pa2.product_id
					)
				SET pa1.text = '" . $this->db->escape($value) . "'
				WHERE 1 
				AND pa1.language_id = '" . (int)$data['language_id'] . "'
				AND pa1.attribute_id = '" . (int)$attribute_id . "'";

				$this->db->query($sql);
			}
		}
	}

	public function getAttributes($data = array()) {
		$sql = "
		SELECT ad.*
		FROM " . DB_PREFIX . "attribute a ";
		$sql .= " JOIN " . DB_PREFIX . "attribute_description ad ON a.attribute_id = ad.attribute_id AND language_id = " . (int)$this->config->get('config_language_id');

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT ";
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (a.attribute_id = y.id and entity = 'attribute')";
			
		}

		$sql .= " WHERE 1 ";
		$sql .= " AND ad.language_id = " . (int)$this->config->get('config_language_id');
		
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'ad.name',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY ad.name";
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

	public function getTotalAttribute($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute a";
		$sql .= " JOIN " . DB_PREFIX . "attribute_description ad ON a.attribute_id = ad.attribute_id AND language_id = " . (int)$this->config->get('config_language_id');
		
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT ";
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (a.attribute_id = y.id and entity = 'attribute')";
			
		}			
		
		$sql .= " WHERE 1 ";
		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$sql .= " AND ad.language_id = " . (int)$this->config->get('config_language_id');
		
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getCategories($data = array()) {
		$sql = "SELECT 
			cp.category_id AS category_id, 
			GROUP_CONCAT(cd1.name ORDER BY cp.level SEPARATOR '&nbsp;&nbsp;&gt;&nbsp;&nbsp;') AS name, 
			c1.parent_id, 
			c1.status, 
			c1.sort_order 
			FROM " . DB_PREFIX . "category_path cp 
			LEFT JOIN " . DB_PREFIX . "category c1 ON (cp.category_id = c1.category_id) 
			LEFT JOIN " . DB_PREFIX . "category c2 ON (cp.path_id = c2.category_id) 
			LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (cp.path_id = cd1.category_id) 
			LEFT JOIN " . DB_PREFIX . "category_description cd2 ON (cp.category_id = cd2.category_id) ";

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if ($data['filter_ready']) {
				$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (cp.category_id = y.id and entity = 'category')";
			} else {
				$sql .= " LEFT JOIN " . DB_PREFIX . "ya_translate as y ON (cp.category_id = y.id and entity = 'category')";
			}
		}
			
		$sql .= " WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "' 
			AND cd2.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd2.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND c1.status = '" . $this->db->escape($data['filter_status']) . "'";
		}

		$sql .= " GROUP BY cp.category_id";

		$sort_data = array(
			'name',
			'c1.status',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY sort_order";
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

	public function getCategoriesAuto($data = array()) {
		$sql = "SELECT 
			c1.category_id, 
			cd1.name AS name, 
			c1.status
			FROM " . DB_PREFIX . "category c1 
			LEFT JOIN " . DB_PREFIX . "category_description cd1 ON (c1.category_id = cd1.category_id) 
			WHERE cd1.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND cd1.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}
		$sql .= " ORDER BY cd1.name";
		$sql .= " LIMIT 10";
		$query = $this->db->query($sql);
		return $query->rows;
	}

	public function getTotalCategories($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "category c";
		
		if (!empty($data['filter_name'])) {
			$sql .= " LEFT JOIN " . DB_PREFIX . "category_description cd ON cd.category_id = c.category_id";
		}

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT ";
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (c.category_id = y.id and entity = 'category')";
		}
			
		
		$sql .= " WHERE 1 ";
		if (!empty($data['filter_name'])) {
			$sql .= " AND cd.language_id = " . (int)$this->config->get('config_language_id');
			$sql .= " AND cd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND c.status = '" . $this->db->escape($data['filter_status']) . "'";
		}
		$query = $this->db->query($sql);

		return $query->row['total'];
	}
	
	public function getCategoryDescriptions($category_id) {

		$results = $this->db->query("DESC " . DB_PREFIX . "category_description"); 
		$fields = array();
		foreach ($results->rows as $desc) {
			if (strpos(strtoupper($desc['Type']),'TEXT') !== false || strpos(strtoupper($desc['Type']), 'CHAR') !== false) {
				$fields[] = $desc['Field'];
			}
		}

		$category_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $key=>$result) {
			foreach ($fields as $field) {
				$category_description_data[$result['language_id']][$field] = $result[$field];
			}
		}

		return $category_description_data;
	}

	public function getManufacturerDescriptions($manufacturer_id) {

		$results = $this->db->query("DESC " . DB_PREFIX . "manufacturer_description"); 
		$fields = array();
		foreach ($results->rows as $desc) {
			if (strpos(strtoupper($desc['Type']),'TEXT') !== false || strpos(strtoupper($desc['Type']), 'CHAR') !== false) {
				$fields[] = $desc['Field'];
			}
		}

		$manufacturer_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		foreach ($query->rows as $key=>$result) {
			foreach ($fields as $field) {
				$manufacturer_description_data[$result['language_id']][$field] = $result[$field];
			}
		}

		return $manufacturer_description_data;
	}

	public function updateCategoryDescriptions($category_id,$language_id, $category_description) {
		$sql = "SELECT 1 FROM `" . DB_PREFIX . "category_description` WHERE `category_id` = " . (int)$category_id . "
			AND `language_id` = " . (int)$language_id;
		$res = $this->db->query($sql);
		if (!$res->num_rows) {
			$sql = "INSERT INTO `" . DB_PREFIX . "category_description` SET 
				category_id = " . (int)$category_id . ", 
				language_id = " . (int)$language_id;
			$res = $this->db->query($sql);
		}
		foreach ($category_description as $field=>$value) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "category_description SET
			`" . $this->db->escape($field) . "` = '" . $this->db->escape($value) . "'
			WHERE category_id = '" . (int)$category_id . "'
			AND language_id = '" . (int)$language_id . "'
			");
		}
		$query = $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "ya_translate SET entity = 'category', id = " . (int)$category_id . ",
		`language_id` = " . (int)$language_id);
		
	}

	public function updateManufacturerDescriptions($manufacturer_id,$language_id, $manufacturer_description) {
		foreach ($manufacturer_description as $field=>$value) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "manufacturer_description SET
			`" . $this->db->escape($field) . "` = '" . $this->db->escape($value) . "'
			WHERE manufacturer_id = '" . (int)$manufacturer_id . "'
			AND language_id = '" . (int)$language_id . "'
			");
		}
		$query = $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "ya_translate SET entity = 'manufacturer', id = " . (int)$manufacturer_id . ",
		`language_id` = " . (int)$language_id);

	}

	public function getProducts($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT ";
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (p.product_id = y.id and entity = 'product')";
		}
		if (!empty($data['filter_category_id'])) {
			$sql .= " JOIN " . DB_PREFIX . "product_to_category p2c  ON p.product_id = p2c.product_id AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
		}
	
		$sql .= " WHERE 1";

		$sql .= " AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		if (!empty($data['filter_empty_description'])) {
			if (!empty($data['filter_language_id']) && $data['filter_language_id'] != $this->config->get('config_language_id')) {
				$sql .= " AND p.product_id IN 
					(SELECT product_id FROM " . DB_PREFIX . "product_description 
					WHERE (description = '' OR description = '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') AND 
					language_id = '" . (int)$data['filter_language_id'] . "')";
				
			} else {
				$sql .= " AND (description = '' OR description = '&lt;p&gt;&lt;br&gt;&lt;/p&gt;')";
			}
		}

		$sql .= " GROUP BY p.product_id";

		$sort_data = array(
			'pd.name',
			'p.model',
			'p.price',
			'p.quantity',
			'p.status',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.name";
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

	public function getProductDescriptions($product_id) {

		$results = $this->db->query("DESC " . DB_PREFIX . "product_description"); 
		$fields = array();
		foreach ($results->rows as $desc) {
			if (strpos(strtoupper($desc['Type']),'TEXT') !== false || strpos(strtoupper($desc['Type']), 'CHAR') !== false) {
				$fields[] = $desc['Field'];
			}
		}

		$product_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $key=>$result) {
			foreach ($fields as $field) {
				$product_description_data[$result['language_id']][$field] = $result[$field];
			}
		}

		return $product_description_data;
	}

	public function updateProductDescriptions($product_id,$language_id, $product_description) {
		$sql = "SELECT 1 FROM `" . DB_PREFIX . "product_description` WHERE `product_id` = " . (int)$product_id . "
			AND `language_id` = " . (int)$language_id;
		$res = $this->db->query($sql);
		if (!$res->num_rows) {
			$sql = "INSERT INTO `" . DB_PREFIX . "product_description` SET 
				product_id = " . (int)$product_id . ", 
				language_id = " . (int)$language_id;
			$res = $this->db->query($sql);
		}
		
		foreach ($product_description as $field=>$value) {
			$query = $this->db->query("UPDATE " . DB_PREFIX . "product_description SET
			`" . $this->db->escape($field) . "` = '" . $this->db->escape($value) . "'
			WHERE product_id = '" . (int)$product_id . "'
			AND language_id = '" . (int)$language_id . "'
			");
		}

		$query = $this->db->query("INSERT IGNORE INTO " . DB_PREFIX . "ya_translate SET entity = 'product', id = " . (int)$product_id) . ",
		`language_id` = " . (int)$language_id;
	}

	public function getProductCategories($product_id) {
		$product_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getTotalProducts($data = array()) {
		$sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " LEFT "; 
			}
			$sql .= " JOIN " . DB_PREFIX . "ya_translate as y ON (p.product_id = y.id and entity = 'product')";
		}

		if (!empty($data['filter_category_id'])) {
			$sql .= " JOIN " . DB_PREFIX . "product_to_category p2c  ON p.product_id = p2c.product_id AND p2c.category_id = " . (int)$data['filter_category_id'];
		}

		$sql .= " WHERE 1";
		$sql .= "  AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (isset($data['filter_ready']) && !is_null($data['filter_ready'])) {
			if (!$data['filter_ready']) {
				$sql .= " AND y.entity IS NULL";
			}
		}

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_model'])) {
			$sql .= " AND p.model LIKE '" . $this->db->escape($data['filter_model']) . "%'";
		}

		if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
			$sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
		}

		if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
			$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
		}
		if (!empty($data['filter_manufacturer_id'])) {
			$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
		}

		if (!empty($data['filter_empty_description'])) {
			if (!empty($data['filter_language_id']) && $data['filter_language_id'] != $this->config->get('config_language_id')) {
				$sql .= " AND p.product_id IN 
					(SELECT product_id FROM " . DB_PREFIX . "product_description 
					WHERE (description = '' OR description = '&lt;p&gt;&lt;br&gt;&lt;/p&gt;') AND 
					language_id = '" . (int)$data['filter_language_id'] . "')";
				
			} else {
				$sql .= " AND (description = '' OR description = '&lt;p&gt;&lt;br&gt;&lt;/p&gt;')";
			}
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

}
