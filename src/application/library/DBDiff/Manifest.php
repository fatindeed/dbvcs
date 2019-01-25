<?php

namespace DBDiff;

class Manifest extends FileData {

    use \Singleton;

    protected $filename = 'gitlab://db/manifest.json';

    private function __construct() {
        parent::__construct(['version' => 0, 'sha1' => '', 'commits' => []]);
        $this->checkIsAhead();
        $this->checkIsBehind();
        // $this->file_data = Factory::produce('FileData', $this->file_params);
        // $this->db_data = Factory::produce('DBData', $this->db_params);
        // $db_data = ['version' => 0];
        // $version = \DB\Factory::create()->getOne('SELECT id FROM db_versions ORDER BY id DESC LIMIT 1', []);
        // if (empty($version)) {
        //     $version = 0;
        // }
        // $this->db_data = new \DataArray(['version' => $version]);
    }

    public function checkIsAhead() {
        $content = \DataFile::open($this->getLocalPath())->getContents();
        $local = \DataArray::loadJson($content);
        // Check remote, ensure file_data.version = remote.version, file_data.sha1 = remote.sha1
        if ($local->version < $this->version) {
            \ErrorStack::addError('本地文件版本过旧，请先通过Git将文件更新到最新版本。', \ErrorStack::E_ERROR);
        }
        else if ($local->version > $this->version) {
            \ErrorStack::addError('本地版本已更新，请尽快提交并推送到GitLab，以免发生冲突。', \ErrorStack::E_ERROR);
        }
        else if ($local->sha1 != $this->sha1) {
            \ErrorStack::addError('本地文件哈希与GitLab不一致，本地提交版本将失效。请通过git pull更新后重新操作。', \ErrorStack::E_ERROR);
        }
    }

    public function checkIsBehind() {
        // Check database, ensure db_data.version = file_data.version, db_data.commit_version <= file_data.version
        // if (empty($this->db_data->version)) {
        //     $this->db_data->version = 0;
        // }
        // if ($this->db_data->version < $this->file_data->version) {
        //     \ErrorStack::addError('本地数据库未更新到最新版本，请先运行SQL升级脚本。', \ErrorStack::E_ERROR);
        //     return false;
        // }
        // else if ($this->db_data->version > $this->file_data->version) {
        //     \ErrorStack::addError('本地数据库版本号异常', \ErrorStack::E_ERROR);
        //     return false;
        // }
        // if ($this->db_data->commit_version > $this->file_data->version) {
        //     \ErrorStack::addError('本地文件有未提交或推送的版本，请尽快推送到GitLab', \ErrorStack::E_ERROR);
        //     return false;
        // }
    }

    public function addCommit(\CommitModel $commit) {
        unset($commit->synced);
        $commits = $this->commits ? $this->commits : [];
        $commits[$commit->version] = $commit->getArray();
        krsort($commits, SORT_NUMERIC);
        $this->version = $commit->version;
        $this->sha1 = $commit->sha1;
        $this->commits = $commits;
        $this->save();
        $db = \DB\Factory::create();
        $db->execute('CREATE TABLE IF NOT EXISTS `db_versions` (
            `id` int(10) unsigned NOT NULL,
            `sha1` char(40) NOT NULL,
            `date_entered` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`,`sha1`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
        $db->insert('db_versions', ['id' => $commit->version, 'sha1' => $commit->sha1]);
        // $db->update('versions', ['file_version' => $commit->version], ['name' => 'DB Version']);
    }

}