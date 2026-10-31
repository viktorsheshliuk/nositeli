<?php
class ControllerStartupSeo extends Controller {
    public function index() {
        $this->load->library('seo');

        $this->registry->set(
            'seo',
            new Seo($this->registry)
        );
    }
}