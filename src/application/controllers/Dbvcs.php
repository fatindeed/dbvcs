<?php

class DbvcsController extends \Yaf\Controller_Abstract {

    private $commit_mode = false;

    public function init() {
        $this->commit_mode = (strtolower($_ENV['COMMIT_MODE']) == 'on');
    }

    public function indexAction() {
        $commits = CommitModel::getList();
        if (count($commits) == 0) {
            \ErrorStack::addError('版本库内容为空', \ErrorStack::E_WARNING);
        }
        $this->_view->commits = $commits;
        //or
        // $this->getView()->word = "hello world";
    }

    public function diffAction() {
        $this->_view->status_icons = [
            'added' => 'plus-square text-success',
            'deleted' => 'minus-square text-danger',
            'modified' => 'pencil-square text-warning',
        ];
        $dbdiff = new DBDiff\Manager;
        $this->_view->changes = $dbdiff->getChanges();
        if (count($this->_view->changes) == 0) {
            ErrorStack::addError('数据库没有变化', ErrorStack::E_NOTICE);
        } elseif (!$this->commit_mode) {
            ErrorStack::addError('当前环境不支持提交', ErrorStack::E_WARNING);
        }
    }

    public function newAction() {
        if (!$this->commit_mode) {
            throw new InvalidArgumentException('当前环境不支持提交', 1);
        }
        $request = $this->getRequest();
        $method = $request->getMethod();
        if ($method == 'GET') {
            $dbdiff = new DBDiff\Manager;
            $this->_view->content = $dbdiff->getContent();
            ErrorStack::addError('写入变更后，请尽快将本地版本推送到GitLab上，以免发生冲突。如果Git推送时发生冲突，请先还原本地版本，更新到最新版后，重新写入并提交。', ErrorStack::E_NOTICE);
        } elseif ($method == 'POST') {
            $dbdiff = new DBDiff\Manager;
            $dbdiff->saveChanges();
            $commit = new CommitModel;
            $commit->author = $request->getPost('author');
            $commit->message = $request->getPost('message');
            $commit->content = $request->getPost('content');
            $commit->save();
            $this->redirect('/dbvcs');
        } else {
            throw new BadMethodCallException('Invalid method.', 1);
        }
    }

    public function versionAction() {
        $request = $this->getRequest();
        $method = $request->getMethod();
        $params = $request->getParams();
        $version = key($params);
        $commit = CommitModel::load($version);
        if (empty($commit)) {
            throw new InvalidArgumentException('无效的版本', 1);
        }
        if ($method == 'GET') {
            $this->_view->commit = $commit;
            $this->_view->content = DataFile::open('gitlab://db/revisions/'.$version.'.sql')->getContents();
        } elseif ($method == 'POST') {
            if ($commit->synced) {
                throw new InvalidArgumentException('已提交到GitLab的文件无法编辑', 1);
            }
            $commit->author = $request->getPost('author');
            $commit->message = $request->getPost('message');
            $commit->content = $request->getPost('content');
            $commit->save();
            $this->redirect('/dbvcs');
        } else {
            throw new BadMethodCallException('Invalid method.', 1);
        }
    }

}
