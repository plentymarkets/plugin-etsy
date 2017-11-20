<?php

namespace Etsy\Models;

/**
 * Class Category
 */
class Category
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $parentId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var array
     */
    public $children;

    /**
     * @var boolean
     */
    public $isLeaf;

    /**
     * @var int
     */
    public $level;

    /**
     * @var array
     */
    public $path;

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'id'       => $this->id,
            'parentId' => $this->parentId,
            'name'     => $this->name,
            'children' => $this->children,
            'isLeaf'   => $this->isLeaf,
            'level'    => $this->level,
            'path'     => $this->path,
        ];
    }

    public function fillByAttributes($attributes)
    {
        foreach ($attributes as $attr => $val) {
            if (array_key_exists($attr, $this->jsonSerialize())) {
                $ref = &$this->getVarRef($attr);
                $ref = $val;
            }
        }
    }

    private function &getVarRef($varName)
    {
        switch ($varName) {
            case 'id':
                return $this->id;

            case 'parentId':
                return $this->parentId;

            case 'name':
                return $this->name;

            case 'children':
                return $this->children;

            case 'isLeaf':
                return $this->isLeaf;

            case 'level':
                return $this->level;

            case 'path':
                return $this->path;
        }
    }
}