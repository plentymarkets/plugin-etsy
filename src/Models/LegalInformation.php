<?php

namespace Etsy\Models;

use Etsy\EtsyServiceProvider;
use Plenty\Modules\Plugin\DataBase\Contracts\Model;

/**
 * Class LegalInformation
 *
 * @property int $id
 * @property string $lang
 * @property string $value
 * @property string $createdAt
 * @property string $updatedAt
 * 
 * @package Etsy\Models
 */
class LegalInformation extends Model
{
    const TABLE_NAME = 'legal_information';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $lang;
    
    /** 
     * @var string 
     */
    public $value;

    /**
     * @var string
     */
    public $createdAt;

    /**
     * @var string
     */
    public $updatedAt;

    /**
     * @var string
     */
    protected $primaryKeyFieldName = 'id';

    /**
     * @var string
     */
    protected $primaryKeyFieldType = self::FIELD_TYPE_INT;

    /**
     * @var array
     */
    protected $textFields = [
        'value'
    ];
    
    /**
     * @return string
     */
    public function getTableName()
    {
        return EtsyServiceProvider::PLUGIN_NAME.'::'.self::TABLE_NAME;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'lang' => $this->lang,
            'value' => $this->value,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
    
    /**
     * @param array $data
     */
    public function fill(array $data)
    {
        if($data['id']) {
            $this->id = $data['id'];    
        }

        if($data['lang']) {
            $this->lang = $data['lang'];
        }

        if(strlen($data['value'])) {
            $this->value = (string)$data['value'];
        } else {
            $this->value = '';
        }

        if($data['createdAt']) {
            $this->createdAt = $data['createdAt'];
        }

        if($data['updatedAt']) {
            $this->updatedAt = $data['updatedAt'];
        }
    }

    /**
     * @return string
     */
    function jsonSerialize()
    {
        return json_encode($this->toArray());
    }
}