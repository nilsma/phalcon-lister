<?php

use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Filter as Filter;

class EditController extends ControllerBase
{

    public function indexAction()
    {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/edit.css');
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

            $this->view->setVar("user", $user);

        }

        $lists = Lists::find(array(
            "conditions" => "owner_id = ?1",
            "bind" => array(1 => $user->id),
            "order" => "title"
        ));

        $this->view->setVar('lists', $lists);

    }

    public function addListAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $filter = new Filter();

            $list = new Lists();

            $list->id = NULL;
            $list->owner_id = $user->id;
            $list->title = $filter->sanitize($this->request->getPost('list_title'), "string");

            if(empty($list->title)) {

                $this->flash->error('You have to enter a list title');
                $this->response->redirect('edit/');

            } else {

                $list->save();
                $this->response->redirect('edit/');

            }

        }

    }

    public function deleteListAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));

                $transactionManager = new TransactionManager();
                $transaction = $transactionManager->get();

                $list_id = $this->request->getPost('list_id');
                $list = Lists::findFirst("id = {$list_id}");

                $invitations = Invitations::find(array(
                    "conditions" => "owner_id = ?1 AND list_id = ?2",
                    "bind" => array(1 => $user->id, 2 => $list_id)
                ));

                $members = Members::find(array(
                    "conditions" => "owner_id = ?1 AND list_id = ?2",
                    "bind" => array(1 => $user->id, 2 => $list_id)
                ));

                try {

                    foreach($invitations as $invitation) {
                        if($invitation->delete() == false) {
                            $transaction->rollback("Failed invitation delete");
                        }
                    }

                    foreach($members as $member) {
                        if($member->delete() == false) {
                            $transaction->rollback("Failed member delete");
                        }
                    }

                    if($list->delete() == false) {
                        $transaction->rollback("Failed list delete");
                    }

                    $transaction->commit();

                } catch(Phalcon\Mvc\Model\Transaction\Failed $e) {

                    echo 'Failed, reason: ', $e->getMessage();

                }

            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

        }

    }

    public function editListAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $edit_list_id = $this->request->getPost('edit_list_id');

            $this->session->set('edit_list_id', $edit_list_id);

            return $this->response->redirect('editlist/');

        }

    }

}

