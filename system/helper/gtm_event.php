<?php
// system/helper/gtm_event.php
class GTMEventHelper {
    
    private static $registry;
    
    public static function init($registry) {
        self::$registry = $registry;
    }
    
    private static function getRegistry() {
        if (!self::$registry) {
            global $registry;
            self::$registry = $registry;
        }
        return self::$registry;
    }

    /**
     * 1. Просмотр страницы товара (view_item)
     */
    public static function createViewItemEvent($product_id) {
        $registry = self::getRegistry();
        $registry->get('load')->model('catalog/product');
        $product_info = $registry->get('model_catalog_product')->getProduct($product_id);
      
        if (!$product_info) {
            return array();
        }

        // Определяем язык пользователя
        //$lang_code = substr($registry->get('language')->get('code'), 0, 2); // 'uk', 'ru' 
        
        $price = self::getProductPrice($product_info);

        // Получаем иерархию категорий (первая категория товара)
        //$categoriesAll = self::getProductCategoryHierarchy($registry, $product_id);
        //$categoriesHierarchy = $categoriesAll[0] ?? []; // берём первую категорию (ее иерархию)

        // Форматируем категории для dataLayer с динамическими ключами
        $categoryFields = GTMEventHelper::getFormattedCategories($product_id);

        $item = array_merge([
            'item_name'     => $product_info['name'],
            'item_id'       => $product_info['product_id'], 
            'price'         => (float)number_format($price, 2, '.', ''),
            'item_brand'    => $product_info['manufacturer'],
            'item_variant'  => '' // Добавьте сюда логику для опций товара, если необходимо
        ], $categoryFields);
        
        return [
            ['ecommerce' => null], // Первый push для очистки
            [
                'event'     => 'view_item',
                'ecommerce' => [
                    'items' => [$item]
                ],
            ]
        ];
    }

    /**
     * 2. Добавление в корзину (add_to_cart)
     */
    public static function createAddToCartEvent($product_id, $quantity = 1) {
        $registry = self::getRegistry();
        $registry->get('load')->model('catalog/product');
        $product_info = $registry->get('model_catalog_product')->getProduct($product_id);

        if (!$product_info) {
            return array();
        }

        $price = self::getProductPrice($product_info);

        $categoryFields = GTMEventHelper::getFormattedCategories($product_id);

        $variant = '';
        
        $item = array_merge([
            'item_name'     => $product_info['name'],
            'item_id'       => $product_info['product_id'],
            'price'         => (float)number_format($price, 2, '.', ''),
            'item_brand'    => $product_info['manufacturer'],
            //'item_category' => self::getProductCategoryName($product_id),
            'item_variant'  => $variant,
            'quantity'      => (int)$quantity,
            'google_business_vertical' => "retail"
        ], $categoryFields);

        return [
            ['ecommerce' => null],
            [
                'event'     => 'add_to_cart',
                'ecommerce' => [
                    'items' => [$item]
                ],
            ]
        ];
    }

    /**
     * 3. Удаление из корзины (remove_from_cart)
     */
    public static function createRemoveFromCartEvent($product_id, $quantity = 1) {
        $registry = self::getRegistry();
        $registry->get('load')->model('catalog/product');
        $product_info = $registry->get('model_catalog_product')->getProduct($product_id);

        if (!$product_info) {
            return array();
        }

        $price = self::getProductPrice($product_info);

        $categoryFields = GTMEventHelper::getFormattedCategories($product_id);

        $variant = '';
        
        $item = array_merge([
            'item_name'     => $product_info['name'],
            'item_id'       => $product_info['product_id'],
            'price'         => (float)number_format($price, 2, '.', ''),
            'item_brand'    => $product_info['manufacturer'],
            'item_variant'  => $variant,
            'quantity'      => (int)$quantity,
            'google_business_vertical' => "retail"
        ], $categoryFields);

        return [
            ['ecommerce' => null],
            [
                'event'     => 'remove_from_cart',
                'ecommerce' => [
                    'items' => [$item]
                ]
            ]
        ];
    }
    
    /**
     * 4. Просмотр корзины (view_cart)
     */
    public static function createViewCartEvent() {
        $items = self::getCartItems();
        if (empty($items)) {
            return [];
        }

        return [
            ['ecommerce' => null],
            [
                'event'     => 'view_cart',
                'ecommerce' => [
                    'items' => $items
                ]
            ]
        ];
    }

    /**
     * 5. Начало оформления заказа (begin_checkout)
     */
    public static function createBeginCheckoutEvent() {
        $items = self::getCartItems();
        if (empty($items)) {
            return [];
        }

        return [
            ['ecommerce' => null],
            [
                'event'     => 'begin_checkout',
                'ecommerce' => [
                    'items' => $items
                ]
            ]
        ];
    }
    
    /**
     * 6. Покупка (purchase)
     */
    public static function createPurchaseEvent($order_id) {
        $registry = self::getRegistry();
        $registry->get('load')->model('checkout/order');
        $order_info = $registry->get('model_checkout_order')->getOrder($order_id);
        
        if (!$order_info) {
            return array();
        }
        
        $order_products = $registry->get('model_checkout_order')->getOrderProducts($order_id);

        $items = [];
        $ads_items = [];
        foreach ($order_products as $product) {

            $categoryFields = GTMEventHelper::getFormattedCategories($product['product_id']);

            $variant = '';

            $items[] = array_merge([
                'item_name'     => $product['name'],
                'item_id'       => $product['product_id'], 
                'price'         => (float)number_format($product['price'], 2, '.', ''),
                'item_brand'    => self::getProductBrandName($product['product_id']),
                'item_variant'  => $variant, 
                'quantity'      => (int)$product['quantity'],
                'google_business_vertical' => 'retail'
            ], $categoryFields);

            $ads_items[] = ['id' => $product['model'], 'google_business_vertical' => 'retail'];
        }
        
        $currency_code  = $order_info['currency_code'];
        $currency_value = $order_info['currency_value'];

        $total = $registry->get('currency')->format($order_info['total'], $currency_code, $currency_value, false);

        // Реальные tax / shipping / coupon берём из таблицы order_total,
        // иначе в GA4 всегда уходили нули.
        $shipping = 0;
        $tax      = 0;
        $coupon   = 0;

        foreach ($registry->get('model_checkout_order')->getOrderTotals($order_id) as $order_total) {
            $value = (float)$order_total['value'];

            switch ($order_total['code']) {
                case 'shipping':
                    $shipping += $value;
                    break;
                case 'tax':
                    $tax += $value;
                    break;
                case 'coupon':
                case 'voucher':
                case 'reward':
                    // значения скидок в OpenCart отрицательные
                    $coupon += $value;
                    break;
            }
        }

        // приводим к валюте заказа так же, как и value
        $shipping = (float)$registry->get('currency')->format($shipping, $currency_code, $currency_value, false);
        $tax      = (float)$registry->get('currency')->format($tax, $currency_code, $currency_value, false);
        $coupon   = abs((float)$registry->get('currency')->format($coupon, $currency_code, $currency_value, false));

        $ecommerce = [
            'transaction_id' => (string)$order_id,
            'affiliation'    => 'cart',
            'value'          => (float)number_format($total, 2, '.', ''),
            'currency'       => $currency_code,
            'tax'            => (float)number_format($tax, 2, '.', ''),
            'shipping'       => (float)number_format($shipping, 2, '.', ''),
            'items'          => $items
        ];

        if ($coupon > 0) {
            $ecommerce['coupon'] = number_format($coupon, 2, '.', '');
        }

        return [
            ['ecommerce' => null],
            [
                'event'         => 'purchase',
                // [НОВОЕ] Блок user_data для Enhanced Conversions
                // 'user_data' => [
                //     'email'        => $order_info['email'],
                //     'phone_number' => $order_info['telephone'],
                //     'address'      => [
                //         'first_name'  => $order_info['payment_firstname'],
                //         'last_name'   => $order_info['payment_lastname'],
                //         'street'      => $order_info['payment_address_1'],
                //         'city'        => $order_info['payment_city'],
                //         'region'      => $order_info['payment_zone'],
                //         'postal_code' => $order_info['payment_postcode'],
                //         'country'     => 'UA'//$order_info['payment_iso_code_2']
                //     ]
                // ],
                'ecommerce' => $ecommerce,
            ]
        ];
    }

    /**
     * 7. Поиск по сайту (view_search_results)
    */
    public static function createSearchEvent($search_term) {
        $search_term = trim($search_term);

        if (!$search_term) {
            return [];
        }

        return [
            [
                'event' => 'view_search_results',
                'search_term' => $search_term
            ]
        ];
    }

    /*
     * Вспомогательные (приватные) функции
     */
    private static function getCartItems() { 
        $registry = self::getRegistry();
        $cart_products = $registry->get('cart')->getProducts();

        $items = [];

        foreach ($cart_products as $product) {

            $categoryFields = GTMEventHelper::getFormattedCategories($product['product_id']);

            $registry->get('load')->model('catalog/product');
            
            $variant = '';
            
            $items[] = array_merge([
                'item_name'     => $product['name'],
                'item_id'       => $product['product_id'],
                'price'         => (float)number_format(self::getProductPrice($product), 2, '.', ''),
                'item_brand'    => self::getProductBrandName($product['product_id']),
                'item_variant'  => $variant,
                'quantity'      => (int)$product['quantity'],
                'google_business_vertical' => 'retail'
            ], $categoryFields);
        }
        return $items;
    }

    private static function getProductPrice($product_info) {
        $registry = self::getRegistry();
        $price = !empty($product_info['special']) ? $product_info['special'] : $product_info['price'];
        return $registry->get('tax')->calculate($price, $product_info['tax_class_id'], $registry->get('config')->get('config_tax'));
    }

    private static function getProductCategoryName($product_id) {
        $registry = self::getRegistry();
        $db = $registry->get('db');
        $config = $registry->get('config');
        $query = $db->query("SELECT cd.name FROM " . DB_PREFIX . "product_to_category p2c LEFT JOIN " . DB_PREFIX . "category_description cd ON (p2c.category_id = cd.category_id) WHERE p2c.product_id = '" . (int)$product_id . "' AND cd.language_id = '" . (int)$config->get('config_language_id') . "' ORDER BY p2c.main_category DESC LIMIT 1");
        return $query->num_rows ? $query->row['name'] : '';
    }

    private static function getProductBrandName($product_id) {
        $registry = self::getRegistry();
        $db = $registry->get('db');
        $query = $db->query("SELECT m.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "'");
        return $query->num_rows ? $query->row['name'] : '';
    }

    public static function getFormattedCategories($product_id) {
        $registry = self::getRegistry();
        $allHierarchies = self::getProductCategoryHierarchy($registry, $product_id);
        $firstHierarchy = $allHierarchies[0] ?? [];

        return self::formatCategoriesForDataLayer($firstHierarchy);
    }

    public static function getProductCategoryHierarchy($registry, $product_id) {
        $db = $registry->get('db');
        $config = $registry->get('config');
        $language_id = (int)$config->get('config_language_id');

        $categories = [];
        $query = $db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            foreach ($query->rows as $row) {
                $category_id = (int)$row['category_id'];
                $category_hierarchy = [];

                while ($category_id) {
                    $cat_query = $db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . $category_id . "'");
                    $parent_id = $cat_query->num_rows ? (int)$cat_query->row['parent_id'] : 0;

                    $desc_query = $db->query("SELECT name FROM " . DB_PREFIX . "category_description WHERE category_id = '" . $category_id . "' AND language_id = '" . $language_id . "'");
                    if ($desc_query->num_rows) {
                        array_unshift($category_hierarchy, $desc_query->row['name']);
                    }

                    $category_id = $parent_id;
                }

                $categories[] = $category_hierarchy;
            }
        }

        return $categories;
    }

    private static function formatCategoriesForDataLayer(array $categoriesHierarchy): array {
        $result = [];
        foreach ($categoriesHierarchy as $index => $categoryName) {
            $key = 'item_category' . ($index === 0 ? '' : ($index + 1));

            $result[$key] = self::mb_ucfirst_utf8($categoryName);
        }
        return $result;
    }

    private static function mb_ucfirst_utf8($string) {
        $string = mb_strtolower($string, 'UTF-8'); // всё в нижний регистр
        $firstChar = mb_substr($string, 0, 1, 'UTF-8');
        $rest = mb_substr($string, 1, null, 'UTF-8');
        return mb_strtoupper($firstChar, 'UTF-8') . $rest;
    }

}