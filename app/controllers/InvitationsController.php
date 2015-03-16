<?php

use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Filter as Filter;

class InvitationsController extends ControllerBase
{

    public function indexAction()
    {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/invitations.css');
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

            $invitations = Invitations::find(array(
                "conditions" => "invited_id = ?1",
                "bind" => array(1 => $user->id)
            ));

            $this->view->setVar("user", $user);
            $this->view->setVar("invitations", $invitations);

        }

    }

    public function acceptInvitationAction() {

        if($this->request->isPost()) {

            if($this->session->has("user") && $this->session->get("auth") == true) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $invitation_id = $this->request->get("invitation_to_accept");
            $invitation = Invitations::findFirst("id = {$invitation_id}");

            $transactionManager = new TransactionManager();
            $transaction = $transactionManager->get();

            $member = new Members();
            $member->id = NULL;
            $member->owner_id = $invitation->owner_id;
            $member->list_id = $invitation->list_id;
            $member->member_id = $user->id;

            try {

                if($member->save() == false) {
                    $transaction->rollback("Failed member save");
                }

                if($invitation->delete() == false) {
                    $transaction->rollback("Failed invitation delete");
                }

                $this->flash->success('New list membership added');
                $this->response->redirect('invitations/');

            } catch(Phalcon\Exception $e) {

                $this->flash->error('Something went wrong with accepting the invitation' . $e->getMessage());
                $this->response->redirect('invitations/');

            }

        }

    }

    public function deleteInvitationAction() {

        if($this->request->isPost()) {

            if($this->session->has("user") && $this->session->get("auth") == true) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $invitation_id = $this->request->getPost('invitation_to_delete');
            $invitation = Invitations::findFirst("id = {$invitation_id}");

            try {

                $invitation->delete();
                $this->flash->error('You have declined the invitation');

                $this->response->redirect('invitations/');

            } catch(\Phalcon\Exception $e) {

                echo 'Something went wrong: ' . $e->getMessage();

            }

        }

    }

}

