<?php

class LogoutController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

        $this->view->disable();

        if(!$this->session->has('auth') || $this->session->get('auth') == null) {

            $this->flash->error('You have to login first');
            return $this->response->redirect('');

        } else {

            $user = new Users();

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $cookie = $this->cookies->get('remember-me');
            $cookie->delete();
            $this->session->destroy();

            return $this->response->redirect('');

        }

    }

}

