<?php

namespace Etsy\Helper;

use Carbon\Carbon;
use Etsy\Contracts\LegalInformationRepositoryContract;
use Etsy\EtsyServiceProvider;
use Etsy\Models\LegalInformation;
use Illuminate\Database\Eloquent\Collection;
use Plenty\Modules\Item\ItemShippingProfiles\Models\ItemShippingProfiles;
use Plenty\Modules\Item\Variation\Models\Variation;
use Plenty\Modules\Item\VariationSku\Models\VariationSku;
use Plenty\Modules\Market\Helper\Contracts\MarketAttributeHelperRepositoryContract;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Modules\Market\Settings\Models\Settings;
use Plenty\Modules\Order\Shipping\Contracts\ParcelServicePresetRepositoryContract;
use Plenty\Plugin\Application;
use Plenty\Plugin\ConfigRepository;
use Plenty\Modules\Helper\Contracts\UrlBuilderRepositoryContract;
use Plenty\Modules\Item\DataLayer\Models\Record;
use Plenty\Modules\Item\VariationSku\Contracts\VariationSkuRepositoryContract;
use Etsy\Helper\SettingsHelper;
use Plenty\Plugin\Log\Loggable;

/**
 * Class ItemHelper
 */
class ItemHelper
{
	use Loggable;

	/**
	 * @var Application
	 */
	private $app;

	/**
	 * @var VariationSkuRepositoryContract
	 */
	private $variationSkuRepository;

	/**
	 * @var ConfigRepository
	 */
	private $config;

	/**
	 * @var UrlBuilderRepositoryContract
	 */
	private $urlBuilderRepository;

	/**
	 * @var OrderHelper
	 */
	private $orderHelper;

    /**
     * @var array
     */
	private $legalInformationCache = [];
	
    /**
     * @var LegalInformationRepositoryContract
     */
    private $legalInformationRepository;

    /**
     * Possible value for SKU status
     */
    const SKU_STATUS_ACTIVE = 'ACTIVE';

    /**
     * Possible value for SKU status
     */
    const SKU_STATUS_INACTIVE = 'INACTIVE';

    /**
     * Possible value for SKU status
     */
    const SKU_STATUS_ERROR = 'ERROR';

    /**
     * Possible value for SKU status
     */
    const SKU_STATUS_SENT = 'SENT';

    /**
     * ItemHelper constructor.
     *
     * @param Application $app
     * @param UrlBuilderRepositoryContract $urlBuilderRepository
     * @param VariationSkuRepositoryContract $variationSkuRepository
     * @param ConfigRepository $config
     * @param OrderHelper $orderHelper
     * @param LegalInformationRepositoryContract $legalInformationRepository
     */
    public function __construct(
        Application $app,
        UrlBuilderRepositoryContract $urlBuilderRepository,
        VariationSkuRepositoryContract $variationSkuRepository,
        ConfigRepository $config,
        OrderHelper $orderHelper,
        LegalInformationRepositoryContract $legalInformationRepository
    ) {
        $this->app = $app;
        $this->urlBuilderRepository = $urlBuilderRepository;
        $this->variationSkuRepository = $variationSkuRepository;
        $this->config = $config;
        $this->orderHelper = $orderHelper;
        $this->legalInformationRepository = $legalInformationRepository;
    }

    /**
     * Gets the legal information by language.
     * 
     * @param string $lang
     * @return string
     */
	public function getLegalInformation($lang):string 
    {
        if(array_key_exists($lang, $this->legalInformationCache)) {
            return $this->legalInformationCache[$lang];    
        }
        
        $list = $this->legalInformationRepository->search(['lang' => $lang]);
        if(is_array($list) && count($list)) {
            $legalInformation = array_shift($list);
            if($legalInformation instanceof LegalInformation) {
                $this->legalInformationCache[$lang] = (string)$legalInformation->value;
            }
        } else {
            $this->legalInformationCache[$lang] = '';
        }
        
        return $this->legalInformationCache[$lang];
    }

    /**
     * Searches the etsy SKU for given variation. Returns null if none exists yet
     *
     * @param $variationId
     * @return null|VariationSku
     */
    public function getVariationSku($variationId)
    {
        /** @var Collection $skus */
        $skus = $this->variationSkuRepository->search([
            'marketId' => $this->orderHelper->getReferrerId(),
            'variationId' => $variationId
        ]);

        if (isset($skus[0])) {
            return $skus[0];
        }

        return null;
    }

    /**
     * Sets the updated timestamp for an sku if it exists. Returns false if the variation has no sku for etsy yet
     *
     * @param $variationId
     * @return bool
     */
    public function updateVariationSkuTimestamp($variationId)
    {
        $sku = $this->getVariationSku($variationId);

        if ($sku) {
            $sku->save();
            return true;
        }

        return false;
    }

    /**
     * @param $variationId
     * @return bool
     */
    public function updateVariationSkuStockTimestamp($variationId)
    {
        $sku = $this->getVariationSku($variationId);
        $this->getLogger(__FUNCTION__)
            ->addReference('variationId', $variationId)
            ->report(EtsyServiceProvider::PLUGIN_NAME . '::item.itemExportListings', [
                'function' => 'updateVariationSkuStockTimestamp',
                'sku' => $sku
            ]);
        if (!$sku) return false;

        $sku->stockUpdatedAt = date('Y-m-d H:i:s');
        $sku->save();

        return true;
    }

	/**
	 * @param int $sku
	 * @param int $variationId
	 */
	public function generateSku($sku, $variationId)
	{
		/** @var VariationSku $sku */
		$variationSku = $this->variationSkuRepository->generateSku($variationId, $this->orderHelper->getReferrerId(), 0, $sku, true, true);

		if($variationSku instanceof VariationSku)
		{
			$this->variationSkuRepository->update([
				                                      'additionalInformation' => $sku,
				                                      'status'                => 'ACTIVE',
			                                      ], $variationSku->id);
		}

	}

    public function updateVariationSkuStatus($variationId, $status = self::SKU_STATUS_INACTIVE)
    {
        $skus = $this->variationSkuRepository->search([
            'variationId' => $variationId,
            'marketId' => $this->orderHelper->getReferrerId()
        ]);

        $this->getLogger(__FUNCTION__)
            ->addReference('variationId', $variationId)
            ->addReference('status', $status)
            ->report('UpdateVariationSkuStatus', [
                'function' => 'updateVariationSkuStatus',
                'skus' => $skus,
                'referrerId' => $this->orderHelper->getReferrerId()
            ]);

        if (is_array($skus) && count($skus) < 1) return false;


        foreach ($skus as $sku) {
            $sku->status = $status;
            $this->variationSkuRepository->update($sku->toArray(), $sku->id);
        }

        return true;
    }

    public function generateParentSku($listingId, $variationData)
    {
        $etsySku = $listingId.'-'.$variationData['id'];

        $variationSku = $this->variationSkuRepository->generateSkuWithParent($variationData, $this->orderHelper->getReferrerId(), 0, $etsySku, $listingId, true, true);

        return $variationSku->sku;
	}

	/**
	 * Deletes an SKU.
	 *
	 * @param int $skuId
	 */
	public function deleteSku(int $skuId)
	{
        $this->variationSkuRepository->delete($skuId);
	}

	public function deleteListingsSkus($listingId, $marketId) {
	    $skus = $this->variationSkuRepository->search([
	        'marketId' => $marketId,
            'parentSku' => $listingId
        ]);

	    foreach ($skus as $sku) {
	        try {
                $this->deleteSku($sku->id);
            } catch(\Exception $ex)
            {
                $this->getLogger(__FUNCTION__)->debug('Etsy::item.skuRemovalError', [
                    'skuId' => $sku->id,
                    'listingId' => $listingId,
                    'error' => $ex->getMessage()
                ]);
            }
        }
    }

	/**
	 * Get the Etsy property.
	 *
	 * @param Record $record
	 * @param string $propertyKey
	 * @param string $lang
	 *
	 * @return mixed
	 */
	public function getProperty(Record $record, $propertyKey, $lang)
	{
		/** @var SettingsCorrelationFactory $settingsCorrelationFactory */
		$settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

		foreach($record->itemPropertyList as $itemProperty)
		{
			$settings = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_PROPERTY)->getSettingsByCorrelation(SettingsHelper::PLUGIN_NAME, $itemProperty['propertyId']);

			if($settings instanceof Settings && isset($settings->settings['mainPropertyKey']) && isset($settings->settings['propertyKey']) && isset($settings->settings['propertyKey'][ $lang ]) && $settings->settings['mainPropertyKey'] == $propertyKey)
			{
				return $settings->settings['propertyValueKey'][ $lang ];
			}
		}

		return null;
	}

	/**
	 * Get list of images for current item.
	 *
	 * @param array  $list
	 * @param string $imageSize
	 *
	 * @return array
	 */
	public function getImageList(array $list, $imageSize = 'normal')
	{
		$imageList = [];

		foreach($list as $image)
		{
			if(is_array($image) && array_key_exists('path', $image))
			{
				$imageList[ $image['imageId'] ] = $this->urlBuilderRepository->getImageUrl((string) $image['path'], null, $imageSize, $image['fileType'], $image['type'] == 'external');
			}
		}

		return $imageList;
	}

	/**
	 * Get the Etsy shipping profile id.
	 *
	 * @param Collection $itemShippingProfiles
	 *
	 * @return int|null
	 */
	public function getShippingTemplateId($itemShippingProfiles)
	{
		/** @var ParcelServicePresetRepositoryContract $parcelServicePresetRepo */
		$parcelServicePresetRepo = pluginApp(ParcelServicePresetRepositoryContract::class);

		$parcelServicePresetId = null;
		$currentPriority       = 999;

		$shippingTemplateId = null;

		foreach($itemShippingProfiles as $itemShippingProfile)
		{
		    $itemShippingProfile = $itemShippingProfile->toArray();
			try
			{
				$parcelServicePreset = $parcelServicePresetRepo->getPresetById($itemShippingProfile['profileId']);

				if($parcelServicePreset->priority < $currentPriority && (in_array($this->orderHelper->getReferrerId(), $parcelServicePreset->supportedReferrer) || in_array(- 1, $parcelServicePreset->supportedReferrer)) && $correlatedShipping = $this->getCorrelatedShippingTemplate($parcelServicePreset->id))
				{
					$shippingTemplateId = $correlatedShipping;
					$currentPriority    = $parcelServicePreset->priority;
				}
			}
			catch(\Exception $ex)
			{
				$this->getLogger(__FUNCTION__)->debug('Etsy::item.shippingTemplateError', $ex->getMessage());
			}
		}

		return $shippingTemplateId;
	}

	/**
	 * Find a possible correlation for a given shipping template ID.
	 *
	 * @param int $parcelServicePresetId
	 *
	 * @return int|null
	 */
	private function getCorrelatedShippingTemplate($parcelServicePresetId)
	{
		$settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

		$settings = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_SHIPPING)->getSettingsByCorrelation(SettingsHelper::PLUGIN_NAME, $parcelServicePresetId);

		if($settings instanceof Settings && isset($settings->settings['id']))
		{
			return $settings->settings['id'];
		}

		return null;
	}

	/**
	 * Get the Etsy taxonomy id.
	 *
	 * @param int $categoryId
	 *
	 * @return int|null
	 */
	public function getTaxonomyId(int $categoryId)
	{
		$settingsCorrelationFactory = pluginApp(SettingsCorrelationFactory::class);

		$settings = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->getSettingsByCorrelation(SettingsHelper::PLUGIN_NAME, $categoryId);

		if($settings instanceof Settings && isset($settings->settings['id']))
		{
			return $settings->settings['id'];
		}

		return null;
	}

	/**
	 * Get variation name with attributes.
	 *
	 * @param Record $record
	 * @param string $language
	 *
	 * @return string
	 */
	public function getVariationWithAttributesName($record, $language)
	{
		/** @var MarketAttributeHelperRepositoryContract $marketAttributeHelperRepository */
		$marketAttributeHelperRepository = pluginApp(MarketAttributeHelperRepositoryContract::class);

		return $marketAttributeHelperRepository->getVariationNameAndAttributeNameAndValueCombination($record, $language);
	}

	/**
	 * Get tags. Maximum 13 allowed.
	 *
	 * @param Record $record
	 * @param string $language
	 *
	 * @return string
	 */
	public function getTags(Record $record, $language)
	{
		$tagsText = $record->itemDescription[ $language ]['keywords'];

		$tagsArray = explode(',', $tagsText);

		$list = [];

		foreach($tagsArray as $tag)
		{
			$tag = trim(str_replace(['&', '.', '€'], [' and ', '', ''], $tag));

			if(strlen($tag) <= 20)
			{
				$list[] = $tag;
			}
		}

		$list = $this->array_iunique($list);

		return implode(',', array_slice($list, 0, 13));
	}

	/**
	 * Case insensitive array unique.
	 *
	 * @see http://www.php.net/manual/de/function.array-unique.php#78801
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	private function array_iunique($array)
	{
		$lowered = array_map('strtolower', $array);

		return array_intersect_key($array, array_unique($lowered));
	}
}
