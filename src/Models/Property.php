<?php

namespace Etsy\Models;

/**
 * Class Property
 */
class Property
{
    const GROUP_IS_SUPPLY = 1;
    const GROUP_OCCASION = 2;
    const GROUP_WHEN_MADE = 3;
    const GROUP_RECIPIENT = 4;
    const GROUP_WHO_MADE = 5;
    const GROUP_STYLE = 6;
    const GROUP_UNKNOWN = 7;

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