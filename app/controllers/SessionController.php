<?php

class SessionController extends \Phalcon\Mvc\Controller {

    public function indexAction() {
        if($this->session->has('auth')) {
            $this->response->redirect('/member/list/');
        } else {
            $this->response->redirect('/member/list/');
        }
    }

    public function loginAction() {

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = Users::findFirst('username = "' . $username . '"');
        $password_check = $this->security->checkHash($password, $user->password);

        if($user && $password_check) {
            $this->cookies->set('remember-me', $user->id, time() + 15 * 86400);
            $this->session->set('user', serialize($user));
            $this->session->set('auth', True);
            $this->response->redirect('member/');
        } else {

            if(empty($username) || empty($password)) {
                $this->flash->error('Both fields are required');
            } else {
                $this->flash->error('Wrong username or password');
            }

            $this->response->redirect('');
        }

    }

    public function registerAction() {

        $username = $this->request->getPost('username', array('string', 'trim', 'striptags'));
        $_POST['username'] = $username;

        $email = $this->request->getPost('email', array('email', 'trim', 'striptags'));
        $_POST['email'] = $email;

        $form = new RegisterForm();

        $username_exists = Users::findFirst('username = "' . $this->request->getPost('username') . '"');
        $email_exists = Users::findFirst('email = "' . $this->request->getPost('email') . '"');
        $password_match = ($this->request->getPost('password') == $this->request->getPost('repeat'));

        if($form->isValid($this->request->getPost())) {

            if(
                !$username_exists &&
                !empty($username) &&
                !$email_exists &&
                !empty($email) &&
                $password_match
            ) {

                $user = new Users();
                $user->username = $this->request->getPost('username');
                $user->email = $this->request->getPost('email');
                $user->password = $this->security->hash($this->request->getPost('password'));

                $user->save();

                $this->session->set('user', serialize($user));
                $this->session->set('auth', true);
                return $this->response->redirect('member/');

            } else {

                if($username_exists) {
                    $this->flash->error('Username already exists');
                }

                if(empty($username)) {
                    $this->flash->error('Username is required');
                }

                if($email_exists) {
                    $this->flash->error('Email already exists');
                }

                if(empty($email)) {
                    $this->flash->error('Email is required');
                }

                if(empty($password)) {
                    $this->flash->error('Password is required');
                }

                if(!$password_match) {
                    $this->flash->error('The passwords does not match');
                }

                if(empty($repeat)) {
                    $this->flash->error('You have to repeat the password');
                }

                $this->response->redirect('/register');

            }

        } else {

            if($username_exists) {
                $this->flash->error('Username already exists');
            }

            if(empty($username)) {
                $this->flash->error('Username is required');
            }

            if($email_exists) {
                $this->flash->error('Email already exists');
            }

            if(empty($email)) {
                $this->flash->error('Email is required');
            }

            if(empty($password)) {
                $this->flash->error('Password is required');
            }

            if(!$password_match) {
                $this->flash->error('The passwords does not match');
            }

            if(empty($repeat)) {
                $this->flash->error('You have to repeat the password');
            }

            return $this->response->redirect('register/');

        }

    }

}