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

    public function deleteMembershipAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $list_id = $this->request->getPost('list_id');
            $member_id = $user->id;

            $sql = "DELETE FROM Members WHERE list_id = :list_id: AND member_id = :member_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'member_id' => $member_id));


        }

    }

    public function deleteInviterAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $list_id = $this->request->getPost('list_id');
            $invited_id = $this->request->getPost('member_id');

            $sql = "DELETE FROM Invitations WHERE list_id = :list_id: AND invited_id = :invited_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'invited_id' => $invited_id));

        }

    }

    public function inviteUserAction() {

        if($this->request->isPost()) {

            $this->view->disable();

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $filter = new Filter();

            $list_id = $this->request->getPost('list');
            $username_to_invite = $filter->sanitize($this->request->getPost('invite_username'), "string");

            $user_to_invite = Users::findFirst("username = \"{$username_to_invite}\"");

            $invitations = Invitations::find(array(
                "conditions" => "owner_id = ?1 AND list_id = ?2 AND invited_id = ?3",
                "bind" => array(1 => $user->id, 2 => $list_id, 3 => $user_to_invite->id),
                "limit" => 1
            ));

            $members = Members::find(array(
                "conditions" => "owner_id = ?1 AND list_id = ?2 AND member_id = ?3",
                "bind" => array(1 => $user->id, 2 => $list_id, 3 => $user_to_invite->id),
                "limit" => 1
            ));

            if(count($invitations) > 0) {
                $invitation = $invitations[0];
            } else {
                $invitation = NULL;
            }

            if(count($members) > 0) {
                $member = $members[0];
            } else {
                $member = NULL;
            }

            if($invitation || $member || !$user_to_invite) {

                if($invitation) {
                    $this->flash->error("{$user_to_invite->username} has already been invited to this list");
                }

                if($member) {
                    $this->flash->error("{$user_to_invite->username} is already a member of this list");
                }

                if(!$user_to_invite) {
                    $this->flash->error("The user {$username_to_invite} does not exist");
                }

            } else {
                $invitation = new Invitations();

                $invitation->id = NULL;
                $invitation->owner_id = $user->id;
                $invitation->list_id = $list_id;
                $invitation->invited_id = $user_to_invite->id;

                $invitation->save();
            }

        }

        return $this->response->redirect('invitations/');

    }

}

