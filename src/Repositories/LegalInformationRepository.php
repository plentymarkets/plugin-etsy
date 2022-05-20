<?php

namespace Etsy\Repositories;

use Etsy\Contracts\LegalInformationRepositoryContract;
use Etsy\Models\LegalInformation;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

/**
 * Class LegalInformationRepository
 *
 * @package Etsy\Repositories
 */
class LegalInformationRepository implements LegalInformationRepositoryContract
{
    /**
     * @var DataBase
     */
    private $database;

    /**
     * LegalInformationHelper constructor.
     *
     * @param DataBase $dataBase
     */
	public function __construct(DataBase $dataBase)
	{
	    $this->database = $dataBase;
	}

    /**
     * Searchs legal information
     * 
     * @param array $filter
     * @return LegalInformation[]
     */
    public function search($filter = [])
    {
        $query = $this->database->query(LegalInformation::class);
            
        foreach($filter as $key => $value) {
            $query->where($key, '=', $value);
        }
        
        /** @var LegalInformation[] $result */
        $result = $query->get();
        return $result;
    }
    
    /**
     * Gets a legal information.
     * 
     * @param int $id
     * @return LegalInformation|null
     */
    public function get($id)
    {
        /** @var LegalInformation $legaInformation */
        $legaInformation = $this->database->find(LegalInformation::class, $id);
        return $legaInformation;
    }

    /**
     * Saves a new legal information.
     *
     * The language and text has to be specified.
     *
     * @param array $data
     * @return LegalInformation
     */
    public function save($data)
    {
        if(isset($data['lang'])) {
            $legalInformations = $this->search(['lang' => $data['lang']]);
            if(is_array($legalInformations) && count($legalInformations) >= 1) {
                $legalInformation = array_shift($legalInformations);
                return $this->update($legalInformation->id, $data);
            }
        }

        $result = null;
        if(is_array($data)) {
            /** @var LegalInformation $legalInformation */
            $legalInformation = pluginApp(LegalInformation::class);
            $legalInformation->fill($data);

            if(!isset($legalInformation->createdAt)) {
                $legalInformation->createdAt = date('Y-m-d H:i:s');
            }

            if(!isset($data['updatedAt'])) {
                $legalInformation->updatedAt = date('Y-m-d H:i:s');
            }

            /** @var LegalInformation $result */
            $result = $this->database->save($legalInformation);
        }
        
        return $result;
    }

    /**
     * Updates a legal information.
     * 
     * @param int $id
     * @param array $data
     * @return LegalInformation
     */
	public function update($id, $data)
    {
        /** @var LegalInformation $legalInformation */
        $legalInformation = $this->get($id);
        if($legalInformation instanceof LegalInformation) {
            if(!is_null($data['id'])) {
                unset($data['id']);
            }

            $legalInformation->fill($data);
            if(!isset($data['updatedAt'])) {
                $legalInformation->updatedAt = date('Y-m-d H:i:s');
            }
            
            $legalInformation = $this->database->save($legalInformation);
            return $legalInformation;
        } else {
            return $this->save($data);
        }
    }

    /**
     * Deletes a legal information.
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        /** @var LegalInformation $legalInformation */
        $legalInformation = pluginApp(LegalInformation::class);
        $legalInformation->id = $id;
        return $this->database->delete($legalInformation);
    }
}
