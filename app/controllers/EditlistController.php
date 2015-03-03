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

            $list = new Lists();
            $list = Lists::findFirst("id = {$this->request->getPost('list_id')}");
            $list->title = $filter->sanitize($this->request->getPost('new_title'), "string");

            if(strlen($list->title) > 0) {

                $list->save();

            } else {

                $this->flash->error('Something went wrong setting new title');
                $this->response->redirect('editlist/');

            }

            $sql = "UPDATE Lists SET title=:new_title: WHERE id=:list_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'new_title' => $new_title));

        }

        return $this->response->redirect('editlist/');

    }

    public function deleteMemberAction() {

        if($this->request->isPost()) {

            $list_id = $this->request->getPost('list_id');
            $member_id = $this->request->getPost('member_id');

            $sql = "DELETE FROM Members WHERE list_id = :list_id: AND user_id = :member_id:";
            $this->modelsManager->executeQuery($sql, array('list_id' => $list_id, 'member_id' => $member_id));

        }

    }

}

