<?php
// admin/controller/extension/analytics/google_tag_manager.php
class ControllerExtensionAnalyticsGoogleTagManager extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/analytics/google_tag_manager');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('analytics_google_tag_manager', $this->request->post);
            
            $this->session->data['success'] = $this->language->get('text_success');
            
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true));
        }
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/analytics/google_tag_manager', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['action'] = $this->url->link('extension/analytics/google_tag_manager', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=analytics', true);
        
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        if (isset($this->error['container_id'])) {
            $data['error_container_id'] = $this->error['container_id'];
        } else {
            $data['error_container_id'] = '';
        }
         
        // Дані форми
        if (isset($this->request->post['analytics_google_tag_manager_status'])) {
            $data['analytics_google_tag_manager_status'] = $this->request->post['analytics_google_tag_manager_status'];
        } else {
            $data['analytics_google_tag_manager_status'] = $this->config->get('analytics_google_tag_manager_status');
        }
        
        if (isset($this->request->post['analytics_google_tag_manager_container_id'])) {
            $data['analytics_google_tag_manager_container_id'] = $this->request->post['analytics_google_tag_manager_container_id'];
        } else {
            $data['analytics_google_tag_manager_container_id'] = $this->config->get('analytics_google_tag_manager_container_id');
        }
        
        $events = array('begin_checkout', 'purchase', 'add_to_cart', 'remove_from_cart', 'view_item', 'view_cart', 'view_search_results');
        
        foreach ($events as $event) {
            if (isset($this->request->post['analytics_google_tag_manager_' . $event])) {
                $data['analytics_google_tag_manager_' . $event] = $this->request->post['analytics_google_tag_manager_' . $event];
            } else {
                $data['analytics_google_tag_manager_' . $event] = $this->config->get('analytics_google_tag_manager_' . $event);
            }
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/analytics/google_tag_manager', $data));
    }
    
    public function install() {
        $this->load->model('setting/event');
        
        $this->model_setting_event->addEvent('gtm_init', 'catalog/view/common/header/after', 'extension/analytics/google_tag_manager/init');
        $this->model_setting_event->addEvent('gtm_begin_checkout', 'catalog/controller/checkout/simplecheckout/before', 'extension/analytics/google_tag_manager/beginCheckout');
        $this->model_setting_event->addEvent('gtm_purchase', 'catalog/controller/checkout/success/before', 'extension/analytics/google_tag_manager/purchase');
        
        $data = array(
            'analytics_google_tag_manager_status' => 0,
            'analytics_google_tag_manager_container_id' => '',
            'analytics_google_tag_manager_begin_checkout' => 1,
            'analytics_google_tag_manager_purchase' => 1,
            'analytics_google_tag_manager_add_to_cart' => 1,
            'analytics_google_tag_manager_remove_from_cart' => 1,
            'analytics_google_tag_manager_view_cart' => 1,
            'analytics_google_tag_manager_view_item' => 1,
            'analytics_google_tag_manager_view_search_results' => 1
        );
        
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('analytics_google_tag_manager', $data);
    }
    
    public function uninstall() {
        $this->load->model('setting/event');
        
        $this->model_setting_event->deleteEventByCode('gtm_begin_checkout');
        $this->model_setting_event->deleteEventByCode('gtm_purchase');
        $this->model_setting_event->deleteEventByCode('gtm_init');

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('analytics_google_tag_manager');
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/analytics/google_tag_manager')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (empty($this->request->post['analytics_google_tag_manager_container_id'])) {
            $this->error['container_id'] = $this->language->get('error_container_id');
        }
        
        return !$this->error;
    }
}