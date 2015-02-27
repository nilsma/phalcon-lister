<?php

use Phalcon\Validation,
    Phalcon\Security;

class IndexController extends ControllerBase
{

    public function indexAction()
    {

        //TODO add auth-check to every method/controller
        //refactor to auth-check method

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/index.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        $form = new LoginForm();

        $this->view->form = $form;

    }

}