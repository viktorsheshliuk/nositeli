<?php
class ControllerExtensionModuleDiscountprice extends Controller {

  public function index() {

    // Загружаем "модель" модуля
    $this->load->model('extension/module/discountprice');

  if ($this->request->server['REQUEST_METHOD'] == 'POST')//&& $this->validate()) 
  {
      if (is_uploaded_file($this->request->files['import']['tmp_name'])) {
        $this->model_extension_module_discountprice->import($this->request->files['import']['tmp_name']);
      } else {
        $this->error['warning'] = $this->language->get('error_empty');
      }
    }






    // Сохранение настроек модуля, когда пользователь нажал "Записать"
    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
      // Вызываем метод "модели" для сохранения настроек
     // $this->model_extension_module_discountprice->SaveSettings();
      // Выходим из настроек с выводом сообщения
     // $this->session->data['success'] = 'Настройки сохранены';
     // $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
    }

    // Загружаем настройки через метод "модели"
    $data = array();
    $data['module_discountprice_status'] = $this->model_extension_module_discountprice->LoadSettings();
    // Загружаем языковой файл
    $data += $this->load->language('extension/module/discountprice');
    // Загружаем "хлебные крошки"
    $data += $this->GetBreadCrumbs();

    // Кнопки действий
    $data['action'] = $this->url->link('extension/module/discountprice', 'user_token=' . $this->session->data['user_token'], true);
    $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
    // Загрузка шаблонов для шапки, колонки слева и футера
    $data['header'] = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer'] = $this->load->controller('common/footer');
    // мои действия
    //$data['import'] = $this->url->link('extension/module/discountprice', 'token=' . $this->session->data['token'], 'SSL');



    // Выводим в браузер шаблон
    $this->response->setOutput($this->load->view('extension/module/discountprice', $data));

  }

  // Хлебные крошки
  private function GetBreadCrumbs() {
    $data = array(); $data['breadcrumbs'] = array();
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_home'),
      'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('text_extension'),
      'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
    );
    $data['breadcrumbs'][] = array(
      'text' => $this->language->get('heading_title'),
      'href' => $this->url->link('extension/module/discountprice', 'user_token=' . $this->session->data['user_token'], true)
    );
    return $data;
  }

}
?>