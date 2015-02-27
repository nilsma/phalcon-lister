<?php

class ControllerBase extends Phalcon\Mvc\Controller
{

    public function checkAuth() {

        if(!$this->session->has('auth') || $this->session->get('auth') == null) {

            $this->session->destroy();
            $this->flash->error('You have to login first');
            return $this->response->redirect('');

        }

    }

}
