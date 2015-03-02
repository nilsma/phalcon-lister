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

        if($this->cookies->has('remember-me')) {

            $rememberMe = $this->cookies->get('remember-me')->getValue();

            $user = Users::findFirst("id = {$rememberMe}");
            $this->session->set("user", serialize($user));
            $this->session->set("auth", true);

            $this->response->redirect('member');

        }

        $form = new LoginForm();

        $this->view->form = $form;

    }

}