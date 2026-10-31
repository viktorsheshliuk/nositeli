<?php
class Seo {
    private $registry;
    private $product;

    public function __construct($registry) {
        $this->registry = $registry;
        $this->product = '';
    }

    public function getProduct() {
        return $this->product;
    }

    public function productData($data = array()) {
       
        $this->product = '';

        $name = $this->cleanText(isset($data['name']) ? $data['name'] : '');

        if ($name === '') {
            return; // без имени разметка бессмысленна
        }

        $product = array(
            '@context'    => 'https://schema.org/',
            '@type'       => 'Product',
            'name'        => $name,
        );

        if (!empty($data['href'])) {
            $product['@id'] = $data['href'] . '#product';
            $product['url'] = $data['href'];
        }

        // Обратная совместимость: старый контроллер передавал только main_image
        if (empty($data['images']) && !empty($data['main_image'])) {
            $data['images'] = array(
                array(
                    'url'    => $data['main_image'],
                    'width'  => !empty($data['main_image_width']) ? $data['main_image_width'] : 600,
                    'height' => !empty($data['main_image_height']) ? $data['main_image_height'] : 600,
                )
            );
        }

        // Изображения: главное + дополнительные (готовятся в контроллере)
        $images = array();

        if (!empty($data['images'])) {
            foreach ($data['images'] as $image) {
                if (empty($image['url'])) {
                    continue;
                }

                $images[] = array(
                    '@type'  => 'ImageObject',
                    'url'    => $image['url'],
                    'width'  => (int)$image['width'],
                    'height' => (int)$image['height'],
                );
            }
        }

        if ($images) {
            $product['image'] = (count($images) == 1) ? $images[0] : $images;
        }
        
        $description = $this->cleanText(isset($data['description']) ? $data['description'] : '', 500);

        if (!empty($description )){
            $product['description'] = $description;
        }

        if (!empty($data['manufacturer'])) {
            $brand = trim($this->cleanText($data['manufacturer']));
            if ($brand !== '') {
                $product['brand'] = array('@type' => 'Brand', 'name' => $brand);
            }
        }

        if (!empty($data['sku'])) {
            $product['sku'] = $this->cleanText($data['sku']);
        } elseif (!empty($data['model'])) {
            $product['sku'] = $this->cleanText($data['model']);
        }

        if (!empty($data['mpn'])) {
            $product['mpn'] = $this->cleanText($data['mpn']);
        }

        if (!empty($data['ean']) && preg_match('/^\d{13}$/', (string)$data['ean'])) {
            $product['gtin13'] = $this->cleanText($data['ean']);
        }

        $availability = (!empty($data['quantity']) && $data['quantity'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';

        $product['offers'] = array(
            '@type'         => 'Offer',
            'url'           => isset($data['href']) ? $data['href'] : '',
            'priceCurrency' => 'UAH', //$data['currency'],
            'price'         => $this->toPrice(!empty($data['special']) ? $data['special'] : (isset($data['price']) ? $data['price'] : 0)),
            'availability'  => $availability,
            'itemCondition' => 'https://schema.org/NewCondition',
        );

        if (!empty($data['price_valid_until'])) {
            $product['offers']['priceValidUntil'] = $data['price_valid_until'];
        }

        // Продавец
        if (!empty($data['seller_name'])) {
            $product['offers']['seller'] = array(
                '@type' => 'Organization',
                'name'  => $this->cleanText($data['seller_name']),
            );

            if (!empty($data['seller_url'])) {
                $product['offers']['seller']['url'] = $data['seller_url'];
            }
        }

        // Зачёркнутая старая цена
        if (!empty($data['special']) && isset($data['price']) && $data['price'] > $data['special']) {
            $old = $this->toPrice($data['price']);
            if ((float)$old > (float)$product['offers']['price']) {
                $product['offers']['priceSpecification'] = array(
                    '@type'         => 'UnitPriceSpecification',
                    'priceType'     => 'https://schema.org/StrikethroughPrice',
                    'price'         => $old,
                    'priceCurrency' => 'UAH', //$data['currency'],
                );
            }
        }

        // Средний рейтинг
        if (!empty($data['rating_value']) && !empty($data['review_count'])) {
            $product['aggregateRating'] = array(
                '@type'       => 'AggregateRating',
                'ratingValue' => round((float)$data['rating_value'], 1),
                'reviewCount' => (int)$data['review_count'],
            );
        }

        $this->product = $this->jsonLd($product);
    }

    private function cleanText($html, $limit = 0) {
        $t = html_entity_decode((string)$html, ENT_QUOTES, 'UTF-8'); // &lt;p&gt; -> <p>

        // битые UTF-8 байты ломают preg_replace с /u (вернёт null) — вычищаем их
        if (preg_match('//u', $t) !== 1) {
            $t = function_exists('iconv') ? iconv('UTF-8', 'UTF-8//IGNORE', $t) : mb_convert_encoding($t, 'UTF-8', 'UTF-8');
        }
        $t = preg_replace('/<\/(p|div|li|ul|ol|h[1-6]|tr)>|<br\s*\/?>/i', ' ', $t);
        $t = strip_tags($t);
        $t = html_entity_decode($t, ENT_QUOTES, 'UTF-8');            // &mdash;, &nbsp; и т.п.
        $t = trim(preg_replace('/\s+/u', ' ', $t));                  // переносы, табы, двойные пробелы
        if ($limit > 0 && mb_strlen($t, 'UTF-8') > $limit) {
            $t = mb_substr($t, 0, $limit, 'UTF-8');

            $space = mb_strrpos($t, ' ', 0, 'UTF-8');

            if ($space !== false) {
                $t = mb_substr($t, 0, $space, 'UTF-8');
            }

            $t = rtrim($t, ' .,;:-') . '…';
        }
        return $t;
    }

    private function jsonLd(array $data) {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            return ''; // лучше нет разметки, чем битая
        }
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    private function toPrice($value) {
        $v = str_replace(',', '.', (string)$value);
        $v = preg_replace('/[^\d.]/', '', $v); // убираем пробелы и символ валюты
        return number_format((float)$v, 2, '.', '');
    }

}