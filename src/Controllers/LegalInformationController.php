<?php

namespace Etsy\Controllers;

use Etsy\Contracts\LegalInformationRepositoryContract;
use Plenty\Data\SimpleRestResponse;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Class LegalInformationController
 *
 * @package Etsy\Controllers
 */
class LegalInformationController extends Controller
{
    /**
     * @var LegalInformationRepositoryContract
     */
    private $legalInformationRepository;

    /**
     * LegalInformationController constructor.
     *
     * @param LegalInformationRepositoryContract $repository
     */
    public function __construct(LegalInformationRepositoryContract $repository)
    {
        $this->legalInformationRepository = $repository;
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return array
     */
    public function search(Request $request)
    {
        return $this->legalInformationRepository->search($request->get('filter'));
    }

    /**
     * @param int $id
     * @return \Etsy\Models\LegalInformation|null
     */
    public function get(int $id)
    {
        return $this->legalInformationRepository->get($id);
    }

    /**
     * @param Request $request
     * @return \Etsy\Models\LegalInformation
     */
    public function save(Request $request)
    {
        return $this->legalInformationRepository->save($request->get('data'));
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Etsy\Models\LegalInformation
     */
    public function update($id, Request $request)
    {
        return $this->legalInformationRepository->update($id, $request->get('data'));
    }

    /**
     * @param int $id
     * @param Response $response
     * @return Response
     */
    public function delete(int $id, Response $response)
    {
        return $response->make($this->legalInformationRepository->delete($id));
    }
    
    
}