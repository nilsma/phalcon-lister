<?php

use Phalcon\Filter as Filter;

class ProfileController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/profile.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

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

            $this->view->setVar("changePasswordForm", new ChangePasswordForm());
            $this->view->setVar("changeEmailForm", new ChangeEmailForm($user));
            $this->view->setVar("user", $user);

        }

    }

    public function changePasswordAction() {

        $this->view->disable();

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $form = new ChangePasswordForm();

            $current_password = $this->request->getPost('current_password');
            $new_password = $this->request->getPost('new_password');

            $password_check = $this->security->checkHash($current_password, $user->password);

            if($password_check) {

                if($form->isValid($_POST)) {

//                    $new_password = $this->request->getPost('new_password');
//                    $repeat_password = $this->request->getPost('repeat_password');

//                    if($new_password == $repeat_password) {

                    $user->password = $this->security->hash($new_password);
                    $user->save();

                    //TODO refactor user->session refresh
                    $user = Users::findFirst('username = "' . $user->username . '"');
                    $this->session->set('user', serialize($user));

                    $this->flash->success('The password has been updated');
                    $this->response->redirect('profile/');

                } else {

                    $messages = $form->getMessages();

                    foreach($messages as $message) {
                        $this->flash->error($message);
                    }

                    $this->response->redirect('profile/');

                }

            } else {

                $this->flash->error('Incorrect current password');
                $this->response->redirect('profile/');

            }

        }

    }

    public function changeEmailAction() {

        $this->view->disable();

        if($this->request->isPost()) {

            $filter = new Filter();

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $new_email = $filter->sanitize($this->request->getPost('new_email'), "email");

            $form = new ChangeEmailForm($user);

            if($form->isValid($_POST)) {

                $user->email = $new_email;
                $user->save();

                //TODO refactor user->session refresh
                $user = Users::findFirst('username = "' . $user->username . '"');
                $this->session->set('user', serialize($user));

                $this->flash->success('The email has been updated');
                $this->response->redirect('profile/');

            } else {

                $messages = $form->getMessages();

                foreach($messages as $message) {
                    $this->flash->error($message);
                }

                $this->response->redirect('profile/');

            }

        }

    }

}

