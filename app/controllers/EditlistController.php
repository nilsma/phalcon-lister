<?php

use Phalcon\Filter as Filter;

class EditlistController extends ControllerBase
{

    public function indexAction()
    {

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

            $list_id = $this->session->get('edit_list_id');
            $list = Lists::findFirst("id = {$list_id}");
            $members = Members::find("list_id = {$list_id}");

            $this->view->setVar('editListForm', new EditListForm($list));
            $this->view->setVar('list', $list);
            $this->view->setVar('members', $members);

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

                $list->title = $new_title;
                $list->save();

            } else {

                $this->flash->error('Something went wrong setting new title');
                $this->response->redirect('editlist/');

            }

        }

        return $this->response->redirect('editlist/');

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

                $members[0]->delete();

            }

        }

    }

}

