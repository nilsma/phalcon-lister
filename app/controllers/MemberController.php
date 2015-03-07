<?php

use Phalcon\Forms\Form,
    Phalcon\Forms\Element\Select,
    Phalcon\Forms\Element\Submit,
    Phalcon\Mvc\Model\Query;
use Phalcon\Filter as Filter;

class MemberController extends ControllerBase
{

    public function indexAction() {

        $this->view->disable();

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/member.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if($this->session->has("user") && $this->session->get("auth")) {

            $user = unserialize($this->session->get("user"));

            $list = $this->getCurrentList($user);

            $this->response->redirect('member/list/' . $list->id . '/');

        } else {

            $this->flash->error('You have to login first');

            return $this->response->redirect('');

        }

    }

    private function getCurrentList($user) {

        if($user->last_list != NULL) {

            $current_list = $this->getUserLastList($user);

        } else {

            $current_list = $this->getUserDefaultList($user);

        }

        return $current_list;

    }

    private function getUserDefaultList($user) {

        $users_lists = Lists::findLists($user->id);

        return $users_lists[0];

    }

    private function getUserLastList($user) {

        $last_lists = Lists::find(array(
            "conditions" => "id = ?1",
            "bind" => array(1 => $user->last_list),
            "limit" => 1
        ));

        if(count($last_lists) > 0 && ($last_lists[0]) && (($last_lists[0]->owner_id == $user->id) || $this->validateMembership($last_lists[0], $user))) {

            $current_list = $last_lists[0];

        } else {

            $current_list = $this->getUserDefaultList($user);

        }

        return $current_list;

    }

    private function validateMembership($list, $user) {

        $isMember = false;

        $memberships = Members::find(array(
            "conditions" => "list_id = ?1 AND member_id = ?2",
            "bind" => array(1 => $list->id, 2 => $user->id),
            "limit" => 1
        ));

        if(count($memberships) > 0) {

            $isMember = true;

        }

        return $isMember;

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
        $this->assets->addCss('css/member.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if($this->session->has("user") && $this->session->get("auth")) {

            $user = unserialize($this->session->get("user"));

            $params = $this->dispatcher->getParams();

            if(count($params) > 0) {
                $list_id = $params[0];

                $candidate = Lists::findFirst("id = {$list_id}");

                if($this->validateOwnership($candidate, $user) || $this->validateMembership($candidate, $user)) {

                    $list = $candidate;
                    $user->last_list = $list->id;
                    $user->save();
                    $this->session->set("user", serialize($user));

                } else {

                    $user->last_list = null;

                    try {

                        $user->save();

                    } catch(\Phalcon\Exception $e) {

                        $this->flash->error('Something went wrong trying to fetch the list, you have been logged out');
                        $this->response->redirect('logout/');

                    }

                }

                $users_lists = Lists::findLists($user->id);

                $this->view->setVar("listselectform", new ListSelectForm());
                $this->view->setVar("itemform", new AddItemForm($list));
                $this->view->setVar("user", $user);
                $this->view->setVar("current_list", $list);
                $this->view->setVar("items", Items::find(array("conditions" => "list_id = ?1", "bind" => array(1 => $list->id))));
                $this->view->setVar("user_lists", $users_lists);

                $this->view->pick("member/index");

            } else {

                return $this->response->redirect('member/');

            }

        } else {
            $this->flash->error('You have to login first');
            return $this->response->redirect('');
        }

    }

    public function deleteItemAction() {

        if($this->session->has("user") && $this->session->get("auth") == true) {

            $user = unserialize($this->session->get("user"));

            $list_id = $this->request->getPost("list_id");

            $list = Lists::findFirst("id = {$list_id}");
            $item = Items::findFirst("id = {$this->request->getPost("item_id")}");
            $member = Members::findFirst(array("list_id" => $list_id, "member_id" => $user->id));

            if($list->owner_id == $user->id || $member) {

                $item->delete();

            }

        } else {

            $this->flash->error('Something went wrong fetching user');
            $this->response->redirect('');

        }

    }

    public function addItemAction() {

        if($this->session->has("user") && $this->session->get("auth") == true) {

            $filter = new \Phalcon\Filter();

            $user = unserialize($this->session->get("user"));

            $list_id = $this->request->getPost('working_list');
            $item_name = $filter->sanitize($this->request->getPost('item_name'), "string");

            $list = Lists::findFirst(array("list_id" => $list_id));
            $member = Members::findFirst(array("list_id" => $list_id, "member_id" => $user->id));

            $item = new Items();
            $item->id = NULL;
            $item->list_id = $list_id;
            $item->name = $item_name;
            $item->tapped = false;

            if($list->owner_id = $user->id || $member) {

                $item->save();

            }

            $this->response->redirect('member/');

        } else {
            $this->flash->error('Something went wrong fetching user');
            $this->response->redirect('');
        }

    }

    public function tapItemAction() {

        $item_id = $this->request->getPost('id');
        $current_status = $this->request->getPost('tap');

        if($current_status === 'true') {
            $new_status = 0;
        } else {
            $new_status = 1;
        }

        if($this->session->has("user") && $this->session->get("auth") == true) {

            try {

                $this->db->connect();
                $this->db->execute("UPDATE items SET tapped = ? WHERE id = ?", array($new_status, $item_id));
                $affectedRows = $this->db->affectedRows();
                $this->db->close();

                $result = json_encode($affectedRows, JSON_FORCE_OBJECT);
                echo $result;

            } catch(\Phalcon\Mvc\Model\Exception $e) {

                $this->flash->error($e->getMessage());
                return $this->response->redirect('member/');

            }

        }

    }

}