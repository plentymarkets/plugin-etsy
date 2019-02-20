<?php

namespace Etsy\Models;
use Etsy\EtsyServiceProvider;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class Taxonomy
 */
class Taxonomy extends Model
{
    const TABLE_NAME = 'taxonomies';

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
    public $language;

    /**
     * @var string
     */
    public $nameDe;

    /**
     * @var string
     */
    public $nameEn;

    /**
     * @var string
     */
    public $nameFr;

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
            'language' => $this->language,
            'nameDe'  => $this->nameDe,
            'nameEn'  => $this->nameEn,
            'nameFr'  => $this->nameFr,
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

    /**
     * @return string
     */
    public function getTableName()
    {
        return EtsyServiceProvider::PLUGIN_NAME.'::'.self::TABLE_NAME;
    }

    private function &getVarRef($varName)
    {
        switch ($varName) {
            case 'id':
                return $this->id;

            case 'parentId':
                return $this->parentId;

            case 'language':
                return $this->language;

            case 'nameDe':
                return $this->nameDe;

            case 'nameEn':
                return $this->nameEn;

            case 'nameFr':
                return $this->nameFr;

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