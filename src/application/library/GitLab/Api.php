<?php

/**
 * @see http://gitlab.eainc.com/help/api/README.md
 */

namespace GitLab;

class Api {

    use \Singleton;

    private $config;

    private function __construct() {
        $this->config = \Yaf\Registry::get('config')->gitlab;
    }

    /**
     * Get file
     * 
     * @return string
     */
    public function getFile($filepath) {
        if (empty($filepath)) {
            throw new \InvalidArgumentException('$filepath not given when calling GitLab\\Api::getFile($filepath).', 1);
        }
        return $this->callApi('/projects/'.$this->config->project_id.'/repository/files?'.http_build_query(['file_path' => $filepath, 'ref' => $this->config->branch]));
    }

    /**
     * Get file content
     * 
     * @return string
     */
    public function getFileContent($filepath) {
        if (empty($filepath)) {
            throw new \InvalidArgumentException('$filepath not given when calling GitLab\\Api::getFileContent($filepath).', 1);
        }
        return $this->callApi('/projects/'.$this->config->project_id.'/repository/blobs/'.urlencode($this->config->branch).'?'.http_build_query(['filepath' => $filepath]));
    }

    /**
     * List repository commits
     * 
     * @return array
     */
    public function getCommits() {
        return $this->callApi('/projects/'.$this->config->project_id.'/repository/commits');
    }

    // curl -sSL --header "PRIVATE-TOKEN: ZZdHWkgvuyMfy814wLo1" "http://gitlab.eainc.com/api/v3/projects/search/dbv"
    private function callApi($path, $postdata = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config->host.'api/'.$this->config->version.$path);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: '.$this->config->private_token]);
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \RuntimeException(curl_error($ch), curl_errno($ch));
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // HTTP CODE 4xx / 5xx
        if ($http_code >= 400 && $http_code < 600) {
            throw new \RuntimeException($content, $http_code);
        }
        return $content;
    }
}
