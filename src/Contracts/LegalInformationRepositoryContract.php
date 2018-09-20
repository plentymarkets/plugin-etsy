<?php


namespace Etsy\Contracts;

use Etsy\Models\LegalInformation;


/**
 * Class LegalInformationRepository
 *
 * @package Etsy\Repositories
 */
interface LegalInformationRepositoryContract
{
    /**
     * Searchs legal information.
     * 
     * @param array $filter
     * @return array
     */
    public function search($filter = []);

    /**
     * Gets a LegalInformation.
     * 
     * @param int $id
     * @return LegalInformation|null
     */
    public function get($id);

    /**
     * Saves a new legal information.
     *
     * @param LegalInformation|array $data
     * @return LegalInformation
     */
    public function save($data);

    /**
     * Updates a legal information.
     *
     * @param int $id
     * @param array $data
     * @return LegalInformation
     */
    public function update($id, $data);

    /**
     * Deletes a legal information
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id);
}