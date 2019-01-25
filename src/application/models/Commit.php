<?php

use DBDiff\Manifest;

/**
 * Commit: 数据库变更提交
 */
class CommitModel extends DataArray
{
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR   = 3600;
    const SECONDS_PER_DAY    = 86400;
    const SECONDS_PER_MONTH  = 2592000;
    const SECONDS_PER_YEAR   = 31536000;
    const SECONDS_PER_DECADE = 315360000; // 10 years

    private static $now;

    public function save() {
        // new version
        if (empty($this->version)) {
            $version = Manifest::getInstance()->version + 1;
            $content = '-- DBVSC UPGRADE SQL - Revision'.$version.PHP_EOL;
            $content .= '-- ------------------------------------------------------'.PHP_EOL.PHP_EOL;
            $content .= $this->content;
            $content .= '-- Created at '.date('Y-m-d H:i:s').PHP_EOL;
            $this->version = $version;
        } else {
            $content = $this->content;
        }
        unset($this->content);
        $sql_file = DataFile::open('db/revisions/'.$this->version.'.sql', true);
        $sql_file->putContents($content);
        $this->sha1 = $sql_file->sha1();
        $this->created_at = time();
        Manifest::getInstance()->addCommit($this);
    }

    public static function getList($page = 1) {
        $results = [];
        $manifest = Manifest::getInstance();
        foreach ($manifest->commits as $version => $commit) {
            $commit['version'] = $version;
            $commit['short_id'] = substr($commit['sha1'], 0, 8);
            $commit['create_date'] = date('F jS, Y', $commit['created_at']);
            $commit['create_time'] = date('j M, Y g:ia', $commit['created_at']);
            $commit['time_elapsed'] = self::getTimeElapsed($commit['created_at']);
            $results[] = new self($commit);
        }
        return $results;
    }

    public static function load($version) {
        $manifest = Manifest::getInstance();
        $commit = $manifest->commits[$version];
        if (empty($commit)) {
            return false;
        } else {
            // $commit['synced'] = ($commit['sha1'] == $manifest->remote->commits[$version]['sha1']);
            $commit['synced'] = 1;
            return new self($commit);
        }
    }

    private static function getTimeElapsed($time) {
        if (!isset(self::$now)) {
            self::$now = time();
        }
        $diff = self::$now - $time;
        if ($diff >= self::SECONDS_PER_YEAR * 2) {
            $time_elapsed_string = floor($diff / self::SECONDS_PER_YEAR).' years ago';
        } else if ($diff >= self::SECONDS_PER_YEAR) {
            $time_elapsed_string = 'a year ago';
        } else if ($diff >= self::SECONDS_PER_MONTH * 2) {
            $time_elapsed_string = floor($diff / self::SECONDS_PER_MONTH).' months ago';
        } else if ($diff >= self::SECONDS_PER_MONTH) {
            $time_elapsed_string = 'a month ago';
        } else if ($diff >= self::SECONDS_PER_DAY * 2) {
            $time_elapsed_string = floor($diff / self::SECONDS_PER_DAY).' days ago';
        } else if ($diff >= self::SECONDS_PER_DAY) {
            $time_elapsed_string = 'a day ago';
        } else if ($diff >= self::SECONDS_PER_HOUR * 2) {
            $time_elapsed_string = 'about '.floor($diff / self::SECONDS_PER_HOUR).' hours ago';
        } else if ($diff >= self::SECONDS_PER_HOUR) {
            $time_elapsed_string = 'about an hour ago';
        } else if ($diff >= self::SECONDS_PER_MINUTE * 2) {
            $time_elapsed_string = floor($diff / self::SECONDS_PER_MINUTE).' minutes ago';
        } else if ($diff >= self::SECONDS_PER_MINUTE) {
            $time_elapsed_string = 'a minute ago';
        } else if ($diff > 0) {
            $time_elapsed_string = 'less than a minute ago';
        } else if ($diff == 0) {
            $time_elapsed_string = 'just now';
        } else {
            $time_elapsed_string = 'sometime in the furture';
        }
        return $time_elapsed_string;
    }

}