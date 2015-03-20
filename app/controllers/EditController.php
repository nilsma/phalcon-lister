<?php

use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Filter as Filter;
use Phalcon\Exception as Exception;

class EditController extends ControllerBase
{

    public function indexAction() {

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

        }

        $lists = Lists::findLists($user->id);

        $this->view->setVar('addListForm', new AddListForm());
        $this->view->setVar('lists', $lists);
        $this->view->setVar("user", $user);

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

                try {

                    $list->save();
                    $user->last_list = $list->id;
                    $user->save();
                    $this->session->set('user', serialize($user));
                    $this->flash->success('New list added');

                } catch(Exception $e) {

                    $this->flash->error('Something went wrong when saving list');

                }

                $this->response->redirect('edit/');

            }

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

            $list_id = $this->request->getPost('invite_list_id');
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
                $new_invitation = NULL;
            }

            if(count($members) > 0) {
                $member = $members[0];
            } else {
                $member = NULL;
            }

            if($member || !$user_to_invite) {

                if($member) {
                    $this->flash->error("{$user_to_invite->username} is already a member of this list");
                }

                if(!$user_to_invite) {
                    $this->flash->error("The user {$username_to_invite} does not exist");
                }

            } else {

                if($invitation) {
                    $this->flash->success("You have invited {$user_to_invite->username}");
                } else {

                    $new_invitation = new Invitations();

                    $new_invitation->id = NULL;
                    $new_invitation->owner_id = $user->id;
                    $new_invitation->list_id = $list_id;
                    $new_invitation->invited_id = $user_to_invite->id;

                    $new_invitation->save();

                    $this->flash->success("You have invited {$user_to_invite->username}");

                }
            }

        }

        return $this->response->redirect('edit/users/' . $list_id . '/');

    }

    public function deleteMemberAction() {

        $this->view->disable();

        if($this->request->isPost()) {

            $user = new Users();

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $list_id = $this->request->getPost('list_id');
            $member_id = $this->request->getPost('member_id');

            $members = Members::find(array(
                "conditions" => "owner_id = ?1 AND list_id = ?2 AND member_id = ?3",
                "bind" => array(1 => $user->id, 2 => $list_id, 3 => $member_id),
                "limit" => 1
            ));

            if($members[0]) {

                try {

                    $members[0]->delete();
                    $this->flash->success('Member deleted');

                } catch(Exception $e) {

                    $this->flash->error('Something went wrong when deleting member: ' . $e->getMessage());

                }

            }

        }

    }

    private function deleteOwnedList($user, $list) {

        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        $invitations = Invitations::find(array(
            "conditions" => "owner_id = ?1 AND list_id = ?2",
            "bind" => array(1 => $user->id, 2 => $list->id)
        ));

        $members = Members::find(array(
            "conditions" => "owner_id = ?1 AND list_id = ?2",
            "bind" => array(1 => $user->id, 2 => $list->id)
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

            $this->flash->success('List deleted');
            $this->response->redirect('edit');

        } catch(Phalcon\Mvc\Model\Transaction\Failed $e) {

            $this->flash->error('Something went wrong when deleting list: ' . $e->getMessage());

        }

    }

    private function deleteMembershipList($user, $list) {

    }

    public function deleteListAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));

                /*
                $transactionManager = new TransactionManager();
                $transaction = $transactionManager->get();
                */

                $list_id = $this->request->getPost('list_to_delete');
                $list = Lists::findFirst("id = {$list_id}");
                $participation = $this->request->getPost('participation');

                if($participation == "owner") {

                    $this->deleteOwnedList($user, $list);

                    /*
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

                        $this->flash->success('List deleted');
                        $this->response->redirect('edit');

                    } catch(Phalcon\Mvc\Model\Transaction\Failed $e) {

                        $this->flash->error('Something went wrong when deleting list: ' . $e->getMessage());

                    }
                    */

                } else {

                    $this->deleteMembershipList($user, $list);

                }

            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

        }

    }

    private function validateOwnership($list, $user) {

        $isMember = false;

        $ownerships = Lists::find(array(
            "conditions" => "id = ?1 AND owner_id = ?2",
            "bind" => array(1 => $list->id, 2 => $user->id),
            "limit" => 1
        ));

        if(count($ownerships) > 0) {

            $isMember = true;

        }

        return $isMember;

    }

    public function listAction() {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/editlist.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if(!$this->session->has('auth') || $this->session->get('auth') == null) {

            $this->flash->error('You have to login first');
            return $this->response->redirect('');

        } else {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
                $this->view->setVar('user', $user);
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $params = $this->dispatcher->getParams();

            if(count($params) > 0) {
                $list_id = $params[0];
            } else {
                return $this->response->redirect('edit/');
            }

            $list = Lists::findFirst("id = {$list_id}");

            if($this->validateOwnership($list, $user)) {

                $members = Members::find("list_id = {$list_id}");

                $this->view->setVar('editListForm', new EditListForm($list));
                $this->view->setVar('list', $list);
                $this->view->setVar('members', $members);

            } else {

                $this->flash->error('You are not the owner of that list');
                return $this->response->redirect('edit');

            }

        }
    }

    public function usersAction() {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/users.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if(!$this->session->has('auth') || $this->session->get('auth') == null) {

            $this->flash->error('You have to login first');
            return $this->response->redirect('');

        } else {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
                $this->view->setVar('user', $user);
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $params = $this->dispatcher->getParams();

            if(count($params) > 0) {
                $list_id = $params[0];
            } else {
                return $this->response->redirect('edit/');
            }

            $list = Lists::findFirst("id = {$list_id}");

            if($this->validateOwnership($list, $user)) {

                $members = Members::find("list_id = {$list_id}");

                $this->view->setVar('invitationsForm', new InvitationsForm($list));
                $this->view->setVar('editListForm', new EditListForm($list));
                $this->view->setVar('list', $list);
                $this->view->setVar('members', $members);

            } else {

                $this->flash->error('You are not the owner of that list');
                return $this->response->redirect('edit');

            }

        }
    }

    public function saveTitleAction() {

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flash->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $filter = new Filter();

            $list = Lists::findFirst("id = {$this->request->getPost('list_id')}");
            $new_title = $filter->sanitize($this->request->getPost('new_title'), "string");

            if(strlen($new_title) > 0) {

                try {

                    $list->title = $new_title;
                    $list->save();
                    $this->flash->success('List title updated');

                } catch(Exception $e) {

                    $this->flash->error('Something went wrong setting new title: ' . $e->getMessage());

                }

            } else {

                $this->flash->error('You have to enter a new title');
                $this->response->redirect('edit/');

            }

        }

        return $this->response->redirect('edit/list/' . $list->id . '/');

    }

}

