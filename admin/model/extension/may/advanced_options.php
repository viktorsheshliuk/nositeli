<?php
class ModelExtensionMayAdvancedOptions extends Model {

	public function install() {
		$this->db->query("
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "may_advanced_option` (
			  `option_id` int(11) NOT NULL AUTO_INCREMENT,
			  `option_name` varchar(255) CHARACTER SET latin1 NOT NULL,
			  `children` varchar(255) NOT NULL,
			  `swatch_image` int(1) DEFAULT NULL,
			  `sort_order` int(3) DEFAULT NULL,
			  `content` text CHARACTER SET latin1,
			  PRIMARY KEY (`option_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
	}

	public function uninstall() {
		//$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "may_advanced_option`;");
	}

	public function addOption($data) {
		$this->db->query("INSERT INTO `" . DB_PREFIX . "may_advanced_option` SET option_name = '" . $this->db->escape($data['advanced_option_name']) . "', children = '" . implode(",", array_keys($data['option_values'])) . "', swatch_image = '" . (int)$data['swatch_image'] . "', sort_order = '" . (int)$data['sort_order'] . "', content = '" . json_encode($data['option_values']) . "'");

		$option_id = $this->db->getLastId();

		return $option_id;
	}

	public function editOption($option_id, $data) {
		$this->db->query("UPDATE `" . DB_PREFIX . "may_advanced_option` SET option_name = '" . $this->db->escape($data['advanced_option_name']) . "', children = '" . implode(",", array_keys($data['option_values'])) . "', swatch_image = '" . (int)$data['swatch_image'] . "', sort_order = '" . (int)$data['sort_order'] . "', content = '" . json_encode($data['option_values']) . "' WHERE option_id = '" . (int)$option_id . "'");
	}

	public function updateOptionContent($option_id, $content) {
		if (is_array($content)) {
			$content = json_encode($content);
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "may_advanced_option` SET content = '" . $content . "' WHERE option_id = '" . (int)$option_id . "'");
	}

	public function deleteOption($option_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "may_advanced_option` WHERE option_id = '" . (int)$option_id . "'");
	}

	public function deleteOptionByChild($child_option_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "may_advanced_option` WHERE FIND_IN_SET(" . (int)$child_option_id . ", children)");
	}

	public function deleteChildOptionValues($option, $child_option_value_ids) {
		if (!is_array($option) && is_numeric($option)) {
			$option = $this->getOption($option);
		}

		if ($option['option_id']) {
			$content = json_decode($option['content'], true);
			$content = $this->walk_recursive_remove($content, $child_option_value_ids, true);
			$this->updateOptionContent($option['option_id'], $content);
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` WHERE `value` LIKE '%:::" . $option['option_id'] . "-%'");
		foreach ($query->rows as $row) {
			$combination = explode(':::', $row['value']);
			foreach (explode('-', $combination[1]) as $index => $option_value_id) {
				if (!$index || !in_array($option_value_id, $child_option_value_ids)) {
					continue;
				}

				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE product_option_id = '" . (int)$row['product_option_id'] . "'");
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option` WHERE product_option_id = '" . (int)$row['product_option_id'] . "'");

				$row['product_option_id'] = 0;
				break;
			}

			foreach ($child_option_value_ids as $option_value_id) {
				$this->db->query("DELETE FROM `" . DB_PREFIX . "product_option_value` WHERE product_id = '" . (int)$row['product_id'] . "' AND option_value_id = '" . $option_value_id . "'");
			}

			if (!$row['product_option_id'] || !isset($combination[2])) {
				continue;
			}

			$option_value_info = json_decode($combination[2], true);
			$option_value_info = $this->walk_recursive_remove($option_value_info, $child_option_value_ids, true, false);
			$combination[2] = json_encode($option_value_info);

			$this->db->query("UPDATE `" . DB_PREFIX . "product_option` SET `value`='" . implode(':::', $combination) . "' WHERE product_option_id = '" . $row['product_option_id'] . "'");
		}
	}

	public function getOption($option_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "may_advanced_option` WHERE option_id = '" . (int)$option_id . "'");

		return $query->row;
	}

	public function getOptionsByChild($child_option_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "may_advanced_option` WHERE FIND_IN_SET(" . (int)$child_option_id . ", children)");

		return $query->rows;
	}

	public function getOptions($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "may_advanced_option`";

		if (!empty($data['filter_name'])) {
			$sql .= " WHERE option_name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		$sort_data = array(
			'option_name',
			'sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY option_name";
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

	public function getOptionContent($option_id) {
		$query = $this->db->query("SELECT content FROM `" . DB_PREFIX . "may_advanced_option` WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['content'];
	}

	public function getChildren($option_id) {
		$query = $this->db->query("SELECT children FROM `" . DB_PREFIX . "may_advanced_option` WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['children'];
	}

	public function getTotalOptions() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "may_advanced_option`");

		return $query->row['total'];
	}

	public function setAdvancedOptionsToProduct($product_id, $data) {
		if (!isset($data['may_advanced_option_values'])) {
			return;
		}

		foreach ($data['may_advanced_option_values'] as $option_row => $product_options) {
			$product_options = json_decode(html_entity_decode($product_options), true);

			$max_depth = 0;
			foreach (array_reverse($product_options) as $option_value_row => $product_option) {
				if ($product_option['type'] != 'may_advanced_option') {
					continue;
				}

				$max_depth = count(explode('-', str_replace($product_option['name'] . ':::', '', $product_option['value'])));
				break;
			}

			foreach ($product_options as $option_value_row => $product_option) {
				if ($product_option['type'] != 'may_advanced_option') {
					continue;
				}
	
				$skus = array();
				$images = array();
				$prices = array();
				$hides = array();
				$subtracts = array();
				$quantities = array();
	
				foreach ($product_option['product_option_value'] as $option_value_index => $product_option_value) {
					$skus[$product_option_value['option_value_id']] = $product_option_value['sku'];
					$prices[$product_option_value['option_value_id']] = $product_option_value['price'];
					$hides[$product_option_value['option_value_id']] = isset($product_option_value['hide']) ? $product_option_value['hide'] : false;
					$subtracts[$product_option_value['option_value_id']] = isset($product_option_value['subtract']) ? $product_option_value['subtract'] : false;
					$quantities[$product_option_value['option_value_id']] = $product_option_value['quantity'];

					if (isset($data['may_advanced_option_images'][$option_row][$option_value_row][$product_option_value['option_value_id']])) {
						$images[$product_option_value['option_value_id']] = array_filter(array_unique($data['may_advanced_option_images'][$option_row][$option_value_row][$product_option_value['option_value_id']]));
					}

					if (count(explode('-', str_replace($product_option['name'] . ':::', '', $product_option['value']))) == $max_depth) {
						continue;
					}

					$option_value_key = $product_option['value'] . '-' . $product_option_value['option_value_id'];
					$quantity = 0;
					foreach ($product_options as $option_value_row2 => $product_option2) {
						if ($product_option2['type'] == 'may_advanced_option' &&
							strpos($product_option2['value'], $option_value_key) === 0 &&
							count(explode('-', str_replace($product_option2['name'] . ':::', '', $product_option2['value']))) == $max_depth) {
							foreach ($product_option2['product_option_value'] as $product_option_value2) {
								$quantity += $product_option_value2['quantity'];
							}
						}
					}
					$product_option['product_option_value'][$option_value_index]['quantity'] = $quantity;
					$quantities[$product_option_value['option_value_id']] = $quantity;
				}

				$values = array(
					'sku' => $skus,
					'image' => $images,
					'price' => $prices,
					'hide' => $hides,
					'subtract' => $subtracts,
					'quantity' => $quantities,
					'swatch_image' => (int)$product_option['swatch_image']
				);
	
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . ":::" . json_encode($values) . "', required = '" . (int)$product_option['required'] . "'");
	
				$product_option_id = $this->db->getLastId();
	
				foreach ($product_option['product_option_value'] as $product_option_value) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
				}

				if (isset($product_option_value['subtract']) && $product_option_value['subtract'] && !isset($total_quantity)) {
					$total_quantity = array_sum($quantities);
					$this->db->query("UPDATE " . DB_PREFIX . "product SET quantity = '" . $total_quantity . "' WHERE product_id = '" . (int)$product_id . "'");
				}
			}
		}
	}

	protected function walk_recursive_remove (array $array, $values_to_remove, $key_check = false, $value_check = true) {
		$is_flat_array = true;
		foreach ($array as $k => $v) {
			if ($key_check && in_array($k, $values_to_remove) && (!$value_check || is_array($v))) {
				unset($array[$k]);
				continue;
			}
	
			if (is_array($v)) {
				$is_flat_array = false;
				$array[$k] = $this->walk_recursive_remove($v, $values_to_remove, $key_check, $value_check);
			} else {
				if ($value_check && in_array($v, $values_to_remove)) {
					unset($array[$k]);
				}
			}
		}
	
		if ($value_check && $is_flat_array) {
			$array = array_values($array);
		}
	
		return $array;
	}
}
