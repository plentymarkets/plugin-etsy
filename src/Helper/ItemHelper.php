<?php

namespace Etsy\Helper;

use Etsy\Contracts\LegalInformationRepositoryContract;
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
	 * Get the stock based on the settings.
	 *
	 * @param Record $item
	 *
	 * @return int
	 */
	public function getStock(Record $item)
	{
		if($item->variationBase->limitOrderByStockSelect == 2)
		{
			$stock = 999;
		}
		elseif($item->variationBase->limitOrderByStockSelect == 1 && $item->variationStock->stockNet > 0)
		{
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				$stock = intval($item->variationStock->stockNet);
			}
		}
		elseif($item->variationBase->limitOrderByStockSelect == 0)
		{
			if($item->variationStock->stockNet > 999)
			{
				$stock = 999;
			}
			else
			{
				if($item->variationStock->stockNet > 0)
				{
					$stock = intval($item->variationStock->stockNet);
				}
				else
				{
					$stock = 0;
				}
			}
		}
		else
		{
			$stock = 0;
		}

		return $stock;
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
        if(count($list)) {
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
		try
		{
			$this->variationSkuRepository->delete($skuId);
		}
		catch(\Exception $ex)
		{
			$this->getLogger(__FUNCTION__)->debug('Etsy::item.skuRemovalError', [
				'skuId' => $skuId,
				'error' => $ex->getMessage(),
			]);
		}
	}

	public function deleteListingsSkus($listingId, $marketId) {
	    $skus = $this->variationSkuRepository->search([
	        'marketId' => $marketId,
            'parentSku' => $listingId
        ]);

	    foreach ($skus as $sku) {
	        $this->deleteSku($sku->id);
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