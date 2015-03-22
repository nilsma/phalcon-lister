<?php

use Phalcon\Forms\Form,
    Phalcon\Forms\Element\Select,
    Phalcon\Forms\Element\Submit,
    Phalcon\Mvc\Model\Query;
use Phalcon\Filter as Filter;

class MemberController extends ControllerBase
{

    public function indexAction() {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/member.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if($this->session->has("user") && $this->session->get("auth")) {

            $user = unserialize($this->session->get("user"));
            $user_lists = Lists::findLists($user->id);

            if(count($user_lists) > 0) {

                $this->view->disable();
                $list = $this->getCurrentList($user);
                $this->response->redirect('member/list/' . $list->id . '/');

            } else {

                $list = new Lists();

                $this->view->setVar("user", $user);
                $user_lists = Lists::findLists($user->id);
                $this->view->setVar("user_lists", $user_lists);
                $this->view->setVar("listselectform", new ListSelectForm($user, $list));
                $this->view->setVar("items", Items::find(array("conditions" => "list_id = ?1", "bind" => array(1 => $list->id))));
                $this->view->setVar("itemform", new AddItemForm($list));

            }

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

        if(count($users_lists) > 0) {
            $list = $users_lists[0];
        } else {
            $list = new Lists();
        }

        return $list;

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

    public function addListAction() {

        $this->view->disable();

        if($this->request->isPost()) {

            if($this->session->has("user")) {
                $user = unserialize($this->session->get("user"));
            } else {
                $this->flashDirect->error('Something went wrong fetching user');
                $this->response->redirect('');
            }

            $filter = new Filter();
            $list = new Lists();

            $list->id = NULL;
            $list->owner_id = $user->id;
            $list->title = $filter->sanitize($this->request->getPost('list_title'), "string");

            if(empty($list->title) || strlen($list->title) < 1) {

                $this->flashDirect->error('You have to enter a list title');
                $this->response->redirect('member/');

            } else {

                $list->save();
                $user->last_list = $list->id;
                $user->save();
                $this->session->set('user', serialize($user));

                echo json_encode($list->id);

            }

        }

    }

    public function getTableHTMLAction() {

        $this->view->disable();

        if($this->session->has("user") && $this->session->get("auth")) {

            $user = unserialize($this->session->get("user"));
            $list = Lists::findFirst("id = {$this->request->get('list_id')}");

            if($this->validateOwnership($list, $user) || $this->validateMembership($list, $user)) {

                $view = new Phalcon\Mvc\View();
                $view->setViewsDir("../app/views/");

                $user_lists = Lists::findLists($user->id);
                $items = Items::find(array("conditions" => "list_id = ?1", "bind" => array(1 => $list->id)));

                $view->setVar("user_lists", $user_lists);
                $view->setVar("items", $items);

                $view->start();

                $view->render("partials", "items");

                $view->finish();

                echo $view->getContent();

            } else {

                $this->flash->error('You have to login first');
                return $this->response->redirect('');

            }

        } else {

            $this->flash->error('You have to login first');
            return $this->response->redirect('');

        }

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

                $candidates = Lists::find(array(
                    "conditions" => "id = ?1",
                    "bind" => array(1 => $list_id),
                    "limit" => 1
                ));

                if(count($candidates) > 0) {
                    $candidate = $candidates[0];
                } else {
                    $candidate = $this->getUserDefaultList($user);
                    $this->response->redirect('');
                }

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

                $user_lists = Lists::findLists($user->id);

                $this->view->setVar("listselectform", new ListSelectForm($user, $list));
                $this->view->setVar("itemform", new AddItemForm($list));
                $this->view->setVar("user", $user);
                $this->view->setVar("current_list", $list);
                $this->view->setVar("items", Items::find(array("conditions" => "list_id = ?1", "bind" => array(1 => $list->id))));
                $this->view->setVar("user_lists", $user_lists);

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

            $user = unserialize($this->session->get("user"));

            $filter = new \Phalcon\Filter();

            $list_id = $this->request->getPost('working_list');
            $item_name = $filter->sanitize($this->request->getPost('item_name'), "string");

            $list = Lists::findFirst(array("list_id" => $list_id));
            $member = Members::findFirst(array("list_id" => $list_id, "member_id" => $user->id));

            $form = new AddItemForm($list);

            if($form->isValid($_POST)) {

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

                $this->flash->error('You forgot to add a name for the item');
                $this->response->redirect('member/');

            }

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