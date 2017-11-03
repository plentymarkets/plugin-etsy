<?php

namespace Etsy\Models;

/**
 * Class Property
 */
class Property
{
    /**
     * @var int
     */
    public $id;

    public $groupId;

    public $name;

    public $groupName;

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
            'id'        => $this->id,
            'groupId'   => $this->groupId,
            'name'      => $this->name,
            'groupName' => $this->groupName,
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

            case 'groupId':
                return $this->groupId;

            case 'name':
                return $this->name;

            case 'groupName':
                return $this->groupName;
        }
    }
}