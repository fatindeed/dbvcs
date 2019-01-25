<?php

namespace GitLab;

class File {

    private static $_instances;

    private $data;
    private $content;
    private $offset = 0;

    public static function getInstance($path) {
        if (empty($path)) {
            throw new \InvalidArgumentException('$path not given when calling GitLab\\File::getInstance($path).', 1);
        }
        $filepath = substr($path, strlen(StreamWrapper::STREAM_NAME.'://'));
        if (!isset($_instances[$filepath])) {
            $_instances[$filepath] = new self($filepath);
        }
        return $_instances[$filepath];
    }

    private function __construct($filepath) {
        try {
            $content = Api::getInstance()->getFile($filepath);
            if (!empty($content)) {
                $data = json_decode($content);
                if (json_last_error() != JSON_ERROR_NONE) {
                    throw new \UnexpectedValueException(json_last_error_msg(), json_last_error());
                }
                switch ($data->encoding) {
                    case 'base64':
                        $this->content = base64_decode($data->content);
                        unset($data->encoding, $data->content);
                        break;
                    default:
                        throw new \UnexpectedValueException('Unknown encoding - '.$data->encoding, 1);
                }
                $this->data = $data;
            }
        } catch (\RuntimeException $e) {
            if ($e->getCode() != 404) {
                throw $e;
            }
        }
    }

    public function eof() {
        return $this->offset >= $this->data->size;
    }

    public function open($mode) {
        if (!isset($this->data)) {
            return false;
        }
        $this->offset = 0;
        return true;
    }

    public function read($count) {
        $ret = substr($this->content, $this->offset, $count);
        $this->offset += strlen($ret);
        return $ret;
    }

    public function seek($offset, $whence) {
        switch ($whence) {
            case \SEEK_SET:
                if ($offset < $this->data->size && $offset >= 0) {
                     $this->offset = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case \SEEK_CUR:
                if ($offset >= 0) {
                     $this->offset += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case \SEEK_END:
                if ($this->data->size + $offset >= 0) {
                     $this->offset = $this->data->size + $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            default:
                return false;
        }
    }

    public function stat() {
        if (!isset($this->data)) {
            return false;
        }
        $stat = [
            0, // 0	dev	device number - 设备名
            0, // 1	ino	inode number - inode 号码
            // 33206
            33188, // 2	mode	inode protection mode - inode 保护模式
            1, // 3	nlink	number of links - 被连接数目
            0, // 4	uid	userid of owner - 所有者的用户 id
            0, // 5	gid	groupid of owner- 所有者的组 id
            0, // 6	rdev	device type, if inode device - 设备类型，如果是 inode 设备的话
            $this->data->size, // 7	size	size in bytes - 文件大小的字节数
            1548146662, // 8	atime	time of last access (unix timestamp) - 上次访问时间（Unix 时间戳）
            1548148038, // 9	mtime	time of last modification (unix timestamp) - 上次修改时间（Unix 时间戳）
            1548148038, // 10	ctime	time of last change (unix timestamp) - 上次改变时间（Unix 时间戳）
            -1, // 11	blksize	blocksize of filesystem IO * - 文件系统 IO 的块大小
            -1, // 12	blocks	number of blocks allocated - 所占据块的数目
        ];
        $stat['dev'] = $stat[0];
        $stat['ino'] = $stat[1];
        $stat['mode'] = $stat[2];
        $stat['nlink'] = $stat[3];
        $stat['uid'] = $stat[4];
        $stat['gid'] = $stat[5];
        $stat['rdev'] = $stat[6];
        $stat['size'] = $stat[7];
        $stat['atime'] = $stat[8];
        $stat['mtime'] = $stat[9];
        $stat['ctime'] = $stat[10];
        $stat['blksize'] = $stat[11];
        $stat['blocks'] = $stat[12];
        return $stat;
    }

    public function tell() {
        return $this->offset;
    }

}
