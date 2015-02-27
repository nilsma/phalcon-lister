<?php

use Phalcon\Validation,
    Phalcon\Security;

class RegisterController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/index.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        $form = new RegisterForm();

        $this->view->form = $form;

    }

}

