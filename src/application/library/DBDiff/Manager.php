<?php

namespace DBDiff;

class Manager {

    private $objects = [];

    public function __construct() {
        $config = \Yaf\Registry::get('config')->backup;
        foreach ($config->objects as $object) {
            $this->objects[] = call_user_func([__NAMESPACE__.'\\Object\\'.$object, 'getInstance']);
        }
        foreach ($config->data as $params) {
            $this->objects[] = new Object\Data($params);
        }
    }

    public function getChanges() {
        $changes = [];
        foreach ($this->objects as $object) {
            $object_changes = $object->getChanges();
            if ($object instanceof Object\Data) {
                if (empty($object_changes)) continue;
                $changes['Data'][] = $object_changes;
            } else {
                if (count($object_changes) == 0) continue;
                $key = $object->getName();
                $changes[$key] = $object_changes;
            }
        }
        return $changes;
    }

    public function getContent() {
        $content = '';
        foreach ($this->objects as $object) {
            $content .= $object->getContenct();
        }
        return $content;
    }

    public function saveChanges() {
        foreach ($this->objects as $object) {
            $object->saveChanges();
        }
    }

}