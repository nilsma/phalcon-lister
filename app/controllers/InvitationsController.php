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

            $lists = Lists::find(array(
                "conditions" => "owner = ?1",
                "bind" => array(1 => $user->id),
                "order" => "title"
            ));

            $inviter = $this->getInvitationsAsInviter($user->id);
            $invited = $this->getInvitationsAsInvited($user->id);
            $memberships = $this->getMemberships($user->id);

            $this->view->setVar("user", $user);
            $this->view->setVar("inviter", $inviter);
            $this->view->setVar("invited", $invited);
            $this->view->setVar("memberships", $memberships);
            $this->view->setVar("lists", $lists);

        }

    }

    private function getMemberships($user_id) {

        $memberships = Members::find("user_id = {$user_id}");

        $mships = array();

        foreach($memberships as $m) {

            $m_user = Users::findFirst("id = {$m->owner_id}");
            $m_list = Lists::findFirst("id = {$m->list_id}");

            $mship = array(
                'owner_id' => $m_user->id,
                'username' => $m_user->username,
                'list_id' => $m_list->id,
                'list_title' => $m_list->title
            );

            array_push($mships, $mship);

        }

        return $mships;

    }

    private function getInvitationsAsInvited($user_id) {

        $invitations = Invitations::find("user_id = {$user_id}");

        $invs = array();

        foreach($invitations as $i) {

            $inviter = Users::findFirst("id = {$i->inviter_id}");
            $inv_list = Lists::findFirst("id = {$i->list_id}");

            $inv = array(
                'username' => $inviter->username,
                'list_id' => $inv_list->id,
                'list_title' => $inv_list->title
            );

            array_push($invs, $inv);

        }

        return $invs;

    }

    private function getInvitationsAsInviter($user_id) {

        $invitations = Invitations::find("inviter_id = {$user_id}");

        $invs = array();

        foreach($invitations as $i) {

            $inv_user = Users::findFirst("id = {$i->user_id}");
            $inv_list = Lists::findFirst("id = {$i->list_id}");

            $inv = array(
                'user_id' => $inv_user->id,
                'username' => $inv_user->username,
                'list_id' => $inv_list->id,
                'list_title' => $inv_list->title
            );

            array_push($invs, $inv);

        }

        return $invs;

    }

    public function acceptInvitedAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $transactionManager = new TransactionManager();
            $transaction = $transactionManager->get();

            $list_id = $this->request->getPost('list_id');

            $invitation = Invitations::findFirst(array(
                "list_id" => $list_id,
                "user_id" => $user->id
            ));

            $member = new Members();
            $member->id = NULL;
            $member->owner_id = $invitation->inviter_id;
            $member->list_id = $list_id;
            $member->user_id = $user->id;

            try {

                if($member->save() == false) {
                    $transaction->rollback("Failed member save");
                }

                if($invitation->delete() == false) {
                    $transaction->rollback("Failed invitation delete");
                }

            } catch(\Phalcon\Mvc\Model\Transaction\Failed $e) {

                $this->flash->error('Something went wrong with accepting the invitation');
                $this->response->redirect('');

            }

        }

    }

    public function deleteInvitedAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $list_id = $this->request->getPost('list_id');
            $user_id = $user->id;

            $sql = "DELETE FROM Invitations WHERE list_id = :list_id: AND user_id = :user_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'user_id' => $user_id));

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
            $user_id = $user->id;

            $sql = "DELETE FROM Members WHERE list_id = :list_id: AND user_id = :user_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'user_id' => $user_id));


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
            $user_id = $this->request->getPost('user_id');

            $sql = "DELETE FROM Invitations WHERE list_id = :list_id: AND user_id = :user_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'user_id' => $user_id));

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
                "conditions" => "inviter_id = ?1 AND list_id = ?2 AND user_id = ?3",
                "bind" => array(1 => $user->id, 2 => $list_id, 3 => $user_to_invite->id),
                "limit" => 1
            ));

            $members = Members::find(array(
                "conditions" => "owner_id = ?1 AND list_id = ?2 AND user_id = ?3",
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
                $invitation->inviter_id = $user->id;
                $invitation->list_id = $list_id;
                $invitation->user_id = $user_to_invite->id;

                $invitation->save();
            }

        }

        return $this->response->redirect('invitations/');

    }

}

