<?php
class ControllerCatalogShopRating extends Controller {
    private $error = array();

    public function index() {
        $installed = $this->config->get('shop_rating_installed');



        $data['installed'] = $installed;
        if(!$installed){
            $this->response->redirect($this->url->link('catalog/shop_rating/install', 'user_token=' . $this->session->data['user_token'], true));
        }

        $this->update();

        $this->load->language('catalog/shop_rating');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->addStyle('view/stylesheet/shop_rate.css');

        $this->load->model('catalog/shop_rating');

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if(isset($this->request->post['shop_rating_request_status']) && $this->request->post['shop_rating_request_status'] != 0){
                $this->model_catalog_shop_rating->toogleEvent(1);
            }else{
                $this->model_catalog_shop_rating->toogleEvent(0);
            }


            $this->model_setting_setting->editSetting('shop_rating', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_settings_success');

            $this->response->redirect($this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_disabled_not_sent'] = $this->language->get('text_disabled_not_sent');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_moderate'] = $this->language->get('entry_moderate');
        $data['entry_moderate_desc'] = $this->language->get('entry_moderate_desc');
        $data['entry_authorized'] = $this->language->get('entry_authorized');
        $data['entry_authorized_desc'] = $this->language->get('entry_authorized_desc');
        $data['entry_good_bad'] = $this->language->get('entry_good_bad');
        $data['entry_summary'] = $this->language->get('entry_summary');
        $data['entry_shop_rating'] = $this->language->get('entry_shop_rating');
        $data['entry_shop_rating_desc'] = $this->language->get('entry_shop_rating_desc');
        $data['entry_site_rating'] = $this->language->get('entry_site_rating');
        $data['entry_site_rating_desc'] = $this->language->get('entry_site_rating_desc');
        $data['entry_email'] = $this->language->get('entry_email');
        $data['entry_count'] = $this->language->get('entry_count');
        $data['entry_notify'] = $this->language->get('entry_notify');
        $data['entry_request_mail_status'] = $this->language->get('entry_request_mail_status');
        $data['entry_request_subject'] = $this->language->get('entry_request_subject');
        $data['tokens_label'] = $this->language->get('tokens_label');
        $data['tokens_desc'] = $this->language->get('tokens_desc');
        $data['store_name'] = $this->language->get('store_name');
        $data['store_name_link'] = $this->language->get('store_name_link');
        $data['customer_name'] = $this->language->get('customer_name');
        $data['ratings_link'] = $this->language->get('ratings_link');
        $data['remove_custom_type'] = $this->language->get('remove_custom_type');

        $data['rates_block_title'] = $this->language->get('rates_block_title');
        $data['settings_block_title'] = $this->language->get('settings_block_title');
        $data['answer_name_text'] = $this->language->get('answer_name_text');
        $data['answered'] = $this->language->get('answered');
        $data['date'] = $this->language->get('date');
        $data['name'] = $this->language->get('name');
        $data['shop'] = $this->language->get('shop');
        $data['site'] = $this->language->get('site');
        $data['comment'] = $this->language->get('comment');
        $data['good'] = $this->language->get('good');
        $data['bad'] = $this->language->get('bad');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['action'] = $this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['change_status'] = $this->url->link('catalog/shop_rating/status', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];
        $data['view_rate_link'] = $this->url->link('catalog/shop_rating/viewRate', 'user_token=' . $this->session->data['user_token'], true);
        $data['delete_link'] = $this->url->link('catalog/shop_rating/delete', 'user_token=' . $this->session->data['user_token'], true);
        $data['create_custom_type_url'] = $this->url->link('catalog/shop_rating/custom_types', 'user_token=' . $this->session->data['user_token'], true);


        $data['ratings'] = $this->model_catalog_shop_rating->getAllRatings();
        $data['rating_answers'] = $this->model_catalog_shop_rating->getRatingAnswers();
        $data['custom_types'] = $this->model_catalog_shop_rating->getCustomTypes();

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true)
            );


        if (isset($this->request->post['shop_rating_status'])) {
            $data['shop_rating_status'] = $this->request->post['shop_rating_status'];
        } else {
            $data['shop_rating_status'] = $this->config->get('shop_rating_status');
        }
        if (isset($this->request->post['shop_rating_email'])) {
            $data['shop_rating_email'] = $this->request->post['shop_rating_email'];
        } else {
            if($this->config->get('shop_rating_email')){
                $data['shop_rating_email'] = $this->config->get('shop_rating_email');
            }else{
                $data['shop_rating_email'] = $this->config->get('config_email');
            }
        }
        if (isset($this->request->post['shop_rating_notify'])) {
            $data['shop_rating_notify'] = $this->request->post['shop_rating_notify'];
        } else {
            $data['shop_rating_notify'] = $this->config->get('shop_rating_notify');
        }
        if (isset($this->request->post['shop_rating_request_status'])) {
            $data['shop_rating_request_status'] = $this->request->post['shop_rating_request_status'];
        } else {
            $data['shop_rating_request_status'] = $this->config->get('shop_rating_request_status');
        }
        if (isset($this->request->post['shop_rating_request_subject'])) {
            $data['shop_rating_request_subject'] = $this->request->post['shop_rating_request_subject'];
        } elseif($this->config->get('shop_rating_request_subject') && $this->config->get('shop_rating_request_subject') != ''){
            $data['shop_rating_request_subject'] = $this->config->get('shop_rating_request_subject');
        }else{
            $data['shop_rating_request_subject'] = sprintf($this->language->get('text_request_mail_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
        }
        if (isset($this->request->post['shop_rating_request_text'])) {
            $data['shop_rating_request_text'] = $this->request->post['shop_rating_request_text'];
        } elseif($this->config->get('shop_rating_request_text') && $this->config->get('shop_rating_request_text') !='') {
            $data['shop_rating_request_text'] = $this->config->get('shop_rating_request_text');
        }else{
            $data['shop_rating_request_text'] = $this->language->get('text_request_mail_text');
        }
        if (isset($this->request->post['shop_rating_count'])) {
            $data['shop_rating_count'] = $this->request->post['shop_rating_count'];
        } else {
            $data['shop_rating_count'] = $this->config->get('shop_rating_count');
        }
        if (isset($this->request->post['shop_rating_moderate'])) {
            $data['shop_rating_moderate'] = $this->request->post['shop_rating_moderate'];
        } else {
            $data['shop_rating_moderate'] = $this->config->get('shop_rating_moderate');
        }
        if (isset($this->request->post['shop_rating_summary'])) {
            $data['shop_rating_summary'] = $this->request->post['shop_rating_summary'];
        } else {
            $data['shop_rating_summary'] = $this->config->get('shop_rating_summary');
        }
        if (isset($this->request->post['shop_rating_authorized'])) {
            $data['shop_rating_authorized'] = $this->request->post['shop_rating_authorized'];
        } else {
            $data['shop_rating_authorized'] = $this->config->get('shop_rating_authorized');
        }
        if (isset($this->request->post['shop_rating_shop_rating'])) {
            $data['shop_rating_shop_rating'] = $this->request->post['shop_rating_shop_rating'];
        } else {
            $data['shop_rating_shop_rating'] = $this->config->get('shop_rating_shop_rating');
        }
        if (isset($this->request->post['shop_rating_site_rating'])) {
            $data['shop_rating_site_rating'] = $this->request->post['shop_rating_site_rating'];
        } else {
            $data['shop_rating_site_rating'] = $this->config->get('shop_rating_site_rating');
        }
        if (isset($this->request->post['shop_rating_good_bad'])) {
            $data['shop_rating_good_bad'] = $this->request->post['shop_rating_good_bad'];
        } else {
            $data['shop_rating_good_bad'] = $this->config->get('shop_rating_good_bad');
        }


        $data['heading_title'] = $this->language->get('heading_title');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/shop_rating', $data));
    }

    public function delete(){
        $this->load->model('catalog/shop_rating');
        $result = $this->model_catalog_shop_rating->deleteRating($this->request->get['rating_id']);
        if ($result['status'] = 'success') {
            $this->load->language('catalog/shop_rating');
            $this->session->data['success'] = $this->language->get('text_settings_success');
        }
        $this->response->redirect($this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function viewRate(){
        $this->load->language('catalog/shop_rating');
        $this->document->setTitle($this->language->get('heading_title_view'));
        $this->document->addStyle('view/stylesheet/shop_rate.css');

        $this->load->model('catalog/shop_rating');

        $this->load->model('setting/setting');
        $rate_id = $this->request->get['rating_id'];

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if(isset($this->request->post['rating_date_change']) && $this->request->post['rating_date_change'] != ''){
                if(!isset($this->request->post['old_rating_date']) || $this->request->post['old_rating_date'] != $this->request->post['rating_date_change']){
                    $this->model_catalog_shop_rating->changeDate($rate_id, $this->request->post['rating_date_change']);
                }
            }
            $this->model_catalog_shop_rating->changeComment($rate_id, array(
                'comment' => $this->request->post['new_rating_comment'],
                'good' => $this->request->post['new_rating_good'],
                'bad' => $this->request->post['new_rating_bad'],
            ));

            $this->model_catalog_shop_rating->addAnswer($rate_id, $this->request->post);

            $this->session->data['success'] = $this->language->get('text_answer_success');

            $this->response->redirect($this->url->link('catalog/shop_rating/viewRate', 'user_token=' . $this->session->data['user_token'].'&rating_id='.$rate_id, true));
        }
        $data['rating'] = $this->model_catalog_shop_rating->getRating($rate_id);
        $data['rating']['customs'] = $this->model_catalog_shop_rating->getRateCustomRatings($rate_id);
        $data['rating_answer'] = $this->model_catalog_shop_rating->getRatingAnswer($rate_id);

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }


        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['rating_date'] = $this->language->get('rating_date');
        $data['shop_rating'] = $this->language->get('shop_rating');
        $data['site_rating'] = $this->language->get('site_rating');
        $data['rating_sender'] = $this->language->get('rating_sender');
        $data['status_published'] = $this->language->get('status_published');
        $data['status_unpublished'] = $this->language->get('status_unpublished');
        $data['rating_sender_email'] = $this->language->get('rating_sender_email');
        $data['comment'] = $this->language->get('comment');
        $data['good'] = $this->language->get('good');
        $data['bad'] = $this->language->get('bad');
        $data['answer'] = $this->language->get('answer');
        $data['edit'] = $this->language->get('edit');


        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['action'] = $this->url->link('catalog/shop_rating/viewRate', 'user_token=' . $this->session->data['user_token'].'&rating_id='.$rate_id, true);

        $data['cancel'] = $this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true);

        $data['change_status_url'] = $this->url->link('catalog/shop_rating/status', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token'] = $this->session->data['user_token'];

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true)
            );
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title_view').$rate_id,
                'href' => $this->url->link('catalog/shop_rating/viewRate', 'user_token=' . $this->session->data['user_token']. '&rating_id=' . $rate_id, true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('catalog/shop_rating/', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title_view').$rate_id,
                'href' => $this->url->link('catalog/shop_rating/viewRate', 'user_token=' . $this->session->data['user_token'] .'&rating_id=' . $rate_id. '&module_id=' . $this->request->get['module_id'], true)
            );
        }


        $data['heading_title'] = $this->language->get('heading_title_view');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('catalog/shop_rating_view', $data));
    }
    public function status(){
        $this->load->model('catalog/shop_rating');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $json = $this->model_catalog_shop_rating->changeStatus($this->request->post['rate_id']);
           // var_dump($json);
            $this->response->setOutput(json_encode($json));
        }
    }
    public function custom_types(){
        $this->load->model('catalog/shop_rating');
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            switch($this->request->post['action']){
                case 'create':
                    $json =  $this->model_catalog_shop_rating->createCustomType($this->request->post['new_custom_title']);
                    $this->response->setOutput(json_encode($json));
                    break;

                case 'status':
                    $json =  $this->model_catalog_shop_rating->statusCustomType($this->request->post['custom_id']);
                    $this->response->setOutput(json_encode($json));
                    break;

                case 'remove':
                    $json =  $this->model_catalog_shop_rating->removeCustomType($this->request->post['custom_id']);
                    $this->response->setOutput(json_encode($json));
                    break;
            }
        }
    }

    public function install() {
        $this->load->model('catalog/shop_rating');
        $this->model_catalog_shop_rating->install();
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting('shop_rating', array('shop_rating_installed' => '1'));


        $this->response->redirect($this->url->link('catalog/shop_rating', 'user_token=' . $this->session->data['user_token'], true));

    }

    public function update() {
        $this->load->model('catalog/shop_rating');
        $this->model_catalog_shop_rating->update();


    }

    public function uninstall() {
        $this->load->model('catalog/shop_rating');
        $this->model_catalog_shop_rating->uninstall();
        $this->load->model('setting/setting');

        $this->model_setting_setting->editSetting('shop_rating', array('shop_rating_installed' => '0'));

        $this->response->redirect($this->url->link('extension/modification', 'user_token=' . $this->session->data['user_token'], true));
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', 'catalog/shop_rating')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }

}
?>