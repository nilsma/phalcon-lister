<?php

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

            $lists = Lists::findLists("owner = {$user->id}");
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

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $invite_user = Users::findFirst('username = "' . $this->request->getPost('invite_username') . '"');
            $invitation = new Invitations();

            $invitation->id = NULL;
            $invitation->inviter_id = $user->id;
            $invitation->list_id = $this->request->getPost('list');
            $invitation->user_id = $invite_user->id;

            $invitation->save();

        }

        return $this->response->redirect('invitations/');

    }

}

