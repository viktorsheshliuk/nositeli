<?php
class ControllerExtensionModuleShopRatingHorizontal extends Controller {

    public function index() { 
        $this->load->language('extension/module/shop_rating');
        $this->load->model('catalog/shop_rating');

        $this->document->addStyle('catalog/view/theme/default/stylesheet/shop_rate.css');
        $this->document->addStyle('catalog/view/javascript/jquery/owl-carousel/owl.carousel.css');
        $this->document->addScript('catalog/view/javascript/jquery/owl-carousel/owl.carousel.min.js');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_read_more'] = $this->language->get('text_read_more');
        $data['href_read_more'] = $this->url->link('information/shop_rating','',true);
        $data['show_rating'] = $this->config->get('shop_rating_horizontal_show_rating');
        $data['text_summary'] = $this->language->get('text_summary');
        $data['text_count'] = $this->language->get('text_count');

        $ratings = $this->model_catalog_shop_rating->getLastStoreRatings($this->config->get('shop_rating_horizontal_count'));

        $data['ratings'] =array();
        foreach($ratings as $rating){
            $data['ratings'][] =array(
                'author' => $rating['customer_name'],
                'shop_rating' => $rating['shop_rate'],
                'comment' => utf8_substr(strip_tags(html_entity_decode($rating['comment'], ENT_QUOTES, 'UTF-8')), 0, 150) . '..',
            );
        }
        $data['general']['count'] = 0;
        $data['general']['1'] = 0;
        $data['general']['2'] = 0;
        $data['general']['3'] = 0;
        $data['general']['4'] = 0;
        $data['general']['5'] = 0;
        $x = 0;
        $summ = 0;
        foreach($this->model_catalog_shop_rating->getStoreRatingsAll() as $rate){
            if(isset($rate['shop_rate']) && $rate['shop_rate'] > 0){
                $data['general'][$rate['shop_rate']]++;
                $summ = $summ + $rate['shop_rate'];
                $x++;
            }
        }

        $data['general']['count'] = $x;
        if($x > 0 ){
            $data['general']['summ'] = str_replace('.', ',', round($summ/$x, 1));
            $data['general']['summ_perc'] = round($summ/$x, 1)*100/5;
        }else{
            $data['general']['summ'] = 0;
            $data['general']['summ_perc'] = 0;
        }

        return $this->load->view('extension/module/shop_rating_horizontal', $data);
            
    }

}
?>