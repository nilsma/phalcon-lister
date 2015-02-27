<?php

use Phalcon\Forms\Form,
    Phalcon\Forms\Element\Select,
    Phalcon\Forms\Element\Submit,
    Phalcon\Mvc\Model\Query;

class MemberController extends ControllerBase
{

    public function indexAction() {

        $this->assets->addCss('css/main.css');
        $this->assets->addCss('css/member.css');
        $this->assets->addJs('js/main.js');
        $this->assets->addJs('js/jquery-2.1.3.min.js');

        if($this->session->has("user") && $this->session->get("auth")) {
            $user = unserialize($this->session->get("user"));
            $user_lists = $this->getUserLists($user);

            if(!$this->request->has('list')) {

                $current_list = $this->getCurrentList($user);

            } else {
                $current_list = Lists::findFirst("id = {$this->request->get('list')}");
                $user->last_list = $current_list->id;

                $this->session->set('user', serialize($user));
                $user->save();
            }

            $items = Items::find("list_id = {$current_list->id}");

            $form = new Form();

            $selection = new Select("list", $user_lists, array(
                'using' => array('id', 'title'),
                'class' => 'form-control'
            ));

            $selection->setLabel('Select a list');
            $selection->setDefault($current_list->id);
            $form->add($selection);

            $submit = new Submit('Load', array(
                'class' => 'btn btn-primary'
            ));

            $form->add($submit);

            $this->view->setVar("user", $user);
            $this->view->setVar("current_list", $current_list);
            $this->view->setVar("items", $items);
            $this->view->setVar("form", $form);
            $this->view->setVar("user_lists", $user_lists);

        } else {
            $this->flash->error('You have to login first');
            return $this->response->redirect('');
        }

    }

    public function getCurrentList($user) {

        if($user->last_list != NULL) {
            $current_list = Lists::findFirst("id = {$user->last_list}");

            /*
            if(!$current_list) {
                $current_list = Lists::findFirst("owner = {$user->id}");
            }
            */

        } else {
            $current_list = Lists::findFirst("owner = {$user->id}");
        }

        return $current_list;

    }

    public function getUserLists(Users $user) {
        try {

            $user_lists = Lists::findLists($user->id);
            return $user_lists;

        } catch(\Phalcon\Exception $e) {
            $this->flash->error($e->getMessage());
            return $this->response->redirect('member/');
        }
    }

    public function deleteItemAction() {

        if($this->session->has("user") && $this->session->get("auth") == true) {
            $user = unserialize($this->session->get("user"));

            $list_id = $this->request->getPost("list_id");

            $list = Lists::findFirst('id = "' . $list_id . '"');
            $item = Items::findFirst("id = {$this->request->getPost("item_id")}");

            $member = Members::findFirst(array("list_id" => $list->id, "user_id" => $user->id));

            if($list->owner == $user->id || $member) {

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

            $list_id = $this->request->getPost('working_list');
            $item_name = $this->request->getPost('item_to_add');

            $list = Lists::findFirst(array("list_id" => $list_id));
            $member = Members::findFirst(array("list_id" => $list_id, "user_id" => $user->id));

            $item = new Items();
            $item->id = NULL;
            $item->list_id = $list_id;
            $item->name = $item_name;
            $item->tapped = false;

            if($list->owner = $user->id || $member) {

                $item->save();

            }

            $this->response->redirect('member/');

        } else {
            $this->flash->error('Something went wrong fetching user');
            $this->response->redirect('');
        }

    }

    public function tapItemAction() {

        $this->view->disable();

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