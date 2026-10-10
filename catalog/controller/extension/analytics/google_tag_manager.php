<?php
// catalog/controller/extension/analytics/google_tag_manager.php
class ControllerExtensionAnalyticsGoogleTagManager extends Controller {
    
    public function index() {
        // Завантажуємо helper для визначення ботів
        //require_once(DIR_SYSTEM . 'helper/bot_detector.php');

        // Якщо це бот - не завантажуємо GTM скрипти
        // if (isBot()) {
        //     return '';
        // }

        return '';
    }

    public function init(&$route, &$args, &$output) {
        if ($this->config->get('analytics_google_tag_manager_status')) {
            // Завантажуємо helper для визначення ботів
            //require_once(DIR_SYSTEM . 'helper/bot_detector.php');

            // Якщо це бот - не завантажуємо GTM скрипти (для покращення PageSpeed)
            //if (isBot()) {
            //    return;
            //}

            $container_id = $this->config->get('analytics_google_tag_manager_container_id');

            if (!empty($container_id)) {
                $gtm_head_code = $this->getGtmHeadCode($container_id);
                $event_data = $this->getEventData();
                $js_tracking = $this->getJavaScriptTracking();

                $head_scripts = $gtm_head_code . $event_data . $js_tracking;
                $output = str_replace('</head>', $head_scripts . '</head>', $output);

                $body_code = $this->getGtmBodyCode($container_id);
                $output = preg_replace('/(<body[^>]*>)/', '$1' . $body_code, $output);
            }
        }
    }

    private function getGtmHeadCode($container_id) {
        return '';
        // переход на Google Tag Gateway, поэтому отключаем стандартный код GTM
        
        $code = "\n";
        $code .= "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n";
        $code .= "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n";
        $code .= "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n";
        $code .= "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n";
        $code .= "})(window,document,'script','dataLayer','" . $container_id . "');</script>\n";
        $code .= "\n";
    
        return $code;
    }

    private function getGtmBodyCode($container_id) {
        return '';
        // переход на Google Tag Gateway, поэтому отключаем стандартный код GTM
        
        $code = "\n\n";
        $code .= "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id=" . $container_id . "\"\n";
        $code .= "height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
        $code .= "\n";
    
        return $code;
    }
    
    public function beginCheckout($route, $args) {
        if (!$this->config->get('analytics_google_tag_manager_status') || 
            !$this->config->get('analytics_google_tag_manager_begin_checkout')) {
            return;
        }

        $this->load->helper('gtm_event');
        GTMEventHelper::init($this->registry);
        $event_data = GTMEventHelper::createBeginCheckoutEvent();

        if (!empty($event_data)) {
            $this->session->data['gtm_begin_checkout'] = $this->createGtmScript($event_data);
        }
    }
    
    public function purchase($route, $args) {
        if (!$this->config->get('analytics_google_tag_manager_status') || 
            !$this->config->get('analytics_google_tag_manager_purchase')) {
            return;
        }
        
        // Тригер gtm_purchase спрацьовує на catalog/controller/checkout/success/before,
        // тобто ДО того, як success.php скопіює order_id у last_order_id.
        // Тому спершу читаємо саме order_id, а last_order_id - лише як резерв.
        if (!empty($this->session->data['order_id'])) {
            $this->session->data['gtm_order_id'] = (int)$this->session->data['order_id'];
        } elseif (!empty($this->session->data['last_order_id'])) {
            $this->session->data['gtm_order_id'] = (int)$this->session->data['last_order_id'];
        }
    }
    
    public function getProductData() {
        $json = array();
        
        if ($this->config->get('analytics_google_tag_manager_status') && 
            $this->config->get('analytics_google_tag_manager_add_to_cart') && 
            isset($this->request->get['product_id'])) {
            
            $product_id = (int)$this->request->get['product_id'];
            $quantity = isset($this->request->get['quantity']) ? (int)$this->request->get['quantity'] : 1;
            
            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);

            $json = GTMEventHelper::createAddToCartEvent($product_id, $quantity);
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Получение данных для события remove_from_cart
     */
    public function getRemoveProductData() {
        $json = array();

        // Проверяем, включена ли аналитика и разрешено ли событие remove_from_cart
        if ($this->config->get('analytics_google_tag_manager_status') && 
            $this->config->get('analytics_google_tag_manager_remove_from_cart') &&
            isset($this->request->post['key']) &&
            isset($this->request->post['product_id']) &&
            isset($this->request->post['quantity'])) {

            $product_id = (int)$this->request->post['product_id'];
            $quantity = (int)$this->request->post['quantity'];

            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);

            // Создаем событие remove_from_cart
            $json = GTMEventHelper::createRemoveFromCartEvent($product_id, $quantity);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Повертає карту поточного кошика: cart_id -> {product_id, quantity}.
     * Використовується клієнтським JS, щоб визначити товар при видаленні
     * позиції (після видалення цієї інформації в DOM вже немає).
     */
    public function getCartProducts() {
        $json = array();

        if ($this->config->get('analytics_google_tag_manager_status')) {
            foreach ($this->cart->getProducts() as $product) {
                if (empty($product['cart_id'])) {
                    continue;
                }

                $json[(string)$product['cart_id']] = array(
                    'product_id' => (int)$product['product_id'],
                    'quantity'   => (int)$product['quantity']
                );
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getViewCartData() {
        $json = [];

        if ($this->config->get('analytics_google_tag_manager_status') && 
            $this->config->get('analytics_google_tag_manager_view_cart')) {

            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);
            $json = GTMEventHelper::createViewCartEvent();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }


    
    private function getEventData() {
        $event_data = '';
        
        // Событие view_item
        if ($this->config->get('analytics_google_tag_manager_view_item') && 
            isset($this->request->get['route']) && 
            $this->request->get['route'] == 'product/product' && 
            isset($this->request->get['product_id'])) {
            
            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);
            $view_item_data = GTMEventHelper::createViewItemEvent((int)$this->request->get['product_id']);
            
            if (!empty($view_item_data)) {
                $event_data .= $this->createGtmScript($view_item_data);
            }
        }
        
        // Событие begin_checkout (передается через сессию)
        if (!empty($this->session->data['gtm_begin_checkout'])) {
            $event_data .= $this->session->data['gtm_begin_checkout'];
            unset($this->session->data['gtm_begin_checkout']);
        }

        // Событие purchase (передается через сессию)
        if (!empty($this->session->data['gtm_order_id'])) {
            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);

            $order_id = (int)$this->session->data['gtm_order_id'];

            // Надсилаємо purchase лише один раз на замовлення (захист від повторних
            // рефрешів сторінки успіху та від другого тригера на /payment-success)
            if (empty($this->session->data['gtm_purchased'][$order_id])) {
                $purchase_data = GTMEventHelper::createPurchaseEvent($order_id);

                if (!empty($purchase_data)) {
                    $event_data .= $this->createGtmScript($purchase_data);

                    // Логируем событие purchase, отправляемое в Google Analytics / GTM,
                    // в отдельный файл system/storage/logs/gtm_purchase.log
                    $purchase_event = null;
                    foreach ($purchase_data as $ev) {
                        if (is_array($ev) && isset($ev['event']) && $ev['event'] === 'purchase') {
                            $purchase_event = $ev;
                            break;
                        }
                    }

                    $log = new Log('gtm_purchase.log');
                    $log->write('[purchase] order_id=' . $order_id . ' '
                       . json_encode($purchase_event ?: $purchase_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

                    $this->session->data['gtm_purchased'][$order_id] = true;
                }
            }

            unset($this->session->data['gtm_order_id']);
        }

        // Событие view_search_results
        if ($this->config->get('analytics_google_tag_manager_view_search_results') &&
            isset($this->request->get['route']) &&
            $this->request->get['route'] === 'product/search' &&
            !empty($this->request->get['search'])) {

            $this->load->helper('gtm_event');
            GTMEventHelper::init($this->registry);
            $search_event = GTMEventHelper::createSearchEvent($this->request->get['search']);

            if (!empty($search_event)) {
                $event_data .= $this->createGtmScript($search_event);
            }
        }

        
        return $event_data;
    }
    
    /**
     * JS-код для отслеживания AJAX событий
     */
    private function getJavaScriptTracking() {
        $js = '';
        
        if ($this->config->get('analytics_google_tag_manager_status')) {
            $route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : '';

            $is_checkout_page = (strpos($route, 'simplecheckout') !== false);
            $is_cart_page     = $is_checkout_page || (strpos($route, 'checkout/cart') !== false);

            // При simple_replace_cart=1 сторінка корзини та оформлення замовлення - це одна
            // й та сама сторінка checkout/simplecheckout. Щоб не дублювати події на одній
            // сторінці з однаковими товарами, view_cart надсилаємо лише тоді, коли на цій
            // самій сторінці НЕ буде надіслано begin_checkout.
            $config = array(
                'addToCart'      => (bool)$this->config->get('analytics_google_tag_manager_add_to_cart'),
                'removeFromCart' => (bool)$this->config->get('analytics_google_tag_manager_remove_from_cart'),
                'viewCart'       => (bool)$this->config->get('analytics_google_tag_manager_view_cart')
                    && $is_cart_page
                    && !($is_checkout_page && $this->config->get('analytics_google_tag_manager_begin_checkout'))
            );

            $js .= "\n<script type=\"text/javascript\">\n";
            $js .= "/* GTM ecommerce: add_to_cart / remove_from_cart / view_cart */\n";
            $js .= "var gtmEcommerceConfig = " . json_encode($config) . ";\n";
            $js .= <<<'GTMJS'
(function($) {
    'use strict';

    var cfg = gtmEcommerceConfig;
    var endpoint = 'index.php?route=extension/analytics/google_tag_manager/';

    function pushEvents(data) {
        if (!data) {
            return;
        }

        if (!(data instanceof Array)) {
            data = [data];
        }

        for (var i = 0; i < data.length; i++) {
            var event = data[i];

            if (!event || typeof event !== 'object') {
                continue;
            }

            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(event);
        }
    }

    function getParam(data, name) {
        if (data === null || typeof data === 'undefined') {
            return '';
        }

        if (typeof data === 'object') {
            return (typeof data[name] !== 'undefined' && data[name] !== null) ? data[name] : '';
        }

        var parts = String(data).replace(/^[?&]/, '').split('&');

        for (var i = 0; i < parts.length; i++) {
            var pair = parts[i].split('=');

            if (decodeURIComponent(pair[0].replace(/\+/g, ' ')) === name) {
                return decodeURIComponent((pair[1] || '').replace(/\+/g, ' '));
            }
        }

        return '';
    }

    // Карта поточного кошика: cart_id -> {product_id, quantity}.
    // Її віддає серверний метод getCartProducts, бо після видалення позиції
    // визначити товар по DOM вже неможливо.
    var cartProducts = {};
    var cartLoading  = null;

    function loadCartProducts() {
        if (cartLoading) {
            return cartLoading;
        }

        cartLoading = $.ajax({
            url: endpoint + 'getCartProducts&_=' + (+new Date()),
            dataType: 'json',
            cache: false,
            success: function(data) {
                cartProducts = data || {};
            },
            complete: function() {
                cartLoading = null;
            }
        });

        return cartLoading;
    }

    if (cfg.addToCart || cfg.removeFromCart) {
        loadCartProducts();
    }

    $(document).ajaxSuccess(function(event, xhr, settings) {
        var url = settings.url || '';

        // Запросы самого модуля (getCartProducts, getProductData и т.п.) через
        // ajaxSuccess НЕ обрабатываем: иначе getCartProducts вызывает сам себя
        // и получается бесконечный цикл запросов, который блокирует оформление заказа.
        if (url.indexOf('extension/analytics/google_tag_manager/') !== -1) {
            return;
        }

        // add_to_cart
        if (cfg.addToCart && url.indexOf('checkout/cart/add') !== -1) {
            var productId = getParam(settings.data, 'product_id');
            var quantity = getParam(settings.data, 'quantity') || 1;

            if (productId) {
                $.getJSON(endpoint + 'getProductData&product_id=' + encodeURIComponent(productId) + '&quantity=' + encodeURIComponent(quantity), pushEvents);
            }

            loadCartProducts();

            return;
        }

        // remove_from_cart
        if (cfg.removeFromCart) {
            var key = '';

            if (url.indexOf('checkout/cart/remove') !== -1) {
                key = getParam(settings.data, 'key') || getParam(url, 'key');
            } else if (url.indexOf('checkout/simplecheckout') !== -1) {
                key = getParam(settings.data, 'remove');
            }

            // это не удаление позиции (createOrder, обновление блока и т.п.) - выходим
            if (!key) {
                return;
            }

            var item = cartProducts[key] || null;

            if (item) {
                // сразу убираем ключ из локальной карты: защита от повторного
                // срабатывания, если simplecheckout отправит тот же remove ещё раз
                delete cartProducts[key];

                $.ajax({
                    url: endpoint + 'getRemoveProductData',
                    type: 'post',
                    data: {key: key, product_id: item.product_id, quantity: item.quantity},
                    dataType: 'json',
                    success: pushEvents
                });
            }

            // кошик справді змінився - перечитуємо карту
            loadCartProducts();
        }
    });

    // view_cart
    if (cfg.viewCart) {
        $(function() {
            $.getJSON(endpoint + 'getViewCartData', pushEvents);
        });
    }
})(jQuery);
GTMJS;
            $js .= "</script>\n";
        }
        
        return $js;
    }
    
    /**
     * Создание <script> тега для dataLayer
     */
    private function createGtmScript($events_data) {
        $script = "\n<script>\n";
        // Проверяем, вложенный ли это массив событий
        if (isset($events_data[0]) && is_array($events_data[0])) {
            foreach ($events_data as $event) {
                $script .= "dataLayer.push(" . $this->jsonEncode($event) . ");\n";
            }
        } else {
             // Обработка старого формата для обратной совместимости, если где-то останется
            $script .= "dataLayer.push(" . $this->jsonEncode($events_data) . ");\n";
        }
        $script .= "</script>\n";
        return $script;
    }

    /**
     * JSON для вставки в inline <script>:
     * - JSON_UNESCAPED_UNICODE  - кирилица в названиях товаров не превращается в \uXXXX
     * - JSON_UNESCAPED_SLASHES  - читаемые URL
     * - экранирование "</"       - защита от преждевременного закрытия тега </script>
     */
    private function jsonEncode($data) {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return str_replace('</', '<\/', $json);
    }
}