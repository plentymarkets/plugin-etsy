<?php

namespace Etsy\Controllers;

use Etsy\Contracts\LegalInformationRepositoryContract;
use Etsy\Helper\UpdateOldEtsyListings;
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
        $lang = $request->get('lang');
        if(strlen($lang)) {
            $filter['lang'] = $lang;
        } else {
            $filter = [];
        }
        
        return json_encode($this->legalInformationRepository->search($filter));
    }

    /**
     * @param int $id
     * @return \Etsy\Models\LegalInformation|null
     */
    public function get(int $id)
    {
        return json_encode($this->legalInformationRepository->get($id));
    }

    /**
     * @param Request $request
     * @return \Etsy\Models\LegalInformation
     */
    public function save(Request $request)
    {
//        /** @var UpdateOldEtsyListings $test */
//        $test = pluginApp(UpdateOldEtsyListings::class);
//        $test->replaceOldSkuWithNewSku();
        $result = $this->legalInformationRepository->save((array)json_decode($request->getContent()));
        return json_encode($result);
    }

    /**
     * @param $id
     * @param Request $request
     * @return \Etsy\Models\LegalInformation
     */
    public function update($id, Request $request)
    {
        $result = $this->legalInformationRepository->update($id, (array)json_decode($request->getContent()));
        return json_encode($result);
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