<?php //strict
namespace Etsy\Controllers;

use Plenty\Modules\Category\Contracts\CategoryBranchRepositoryContract;
use Plenty\Modules\Category\Contracts\CategoryRepositoryContract;
use Plenty\Modules\Category\Models\Category;
use Plenty\Modules\Market\Settings\Contracts\SettingsRepositoryContract;
use Plenty\Modules\Market\Settings\Factories\SettingsCorrelationFactory;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Etsy\Contracts\EtsyTaxonomyRepositoryContract;
use Plenty\Plugin\Http\Response;
use Plenty\Repositories\Contracts\PaginationResponseContract;

class TaxonomyController extends Controller
{
	/**
	 * @var EtsyTaxonomyRepositoryContract
	 */
	private $etsyTaxonomyRepository;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * TaxonomyController constructor.
	 *
	 * @param EtsyTaxonomyRepositoryContract $etsyTaxonomyRepository
	 * @param Request                        $request
	 */
	public function __construct(EtsyTaxonomyRepositoryContract $etsyTaxonomyRepository, Request $request)
	{
		$this->etsyTaxonomyRepository = $etsyTaxonomyRepository;
		$this->request                = $request;
	}

	/**
	 * @param int $id
	 *
	 * @return array
	 */
	public function imported($id)
	{
		$taxonomies = $this->etsyTaxonomyRepository->all($id, (string) $this->request->get('language', 'de'));

		return $taxonomies;
	}

	/**
	 * Get categories.
	 *
	 * @return PaginationResponseContract
	 */
	public function categories()
	{
		/** @var CategoryBranchRepositoryContract $categoryBranchRepo */
		$categoryBranchRepo = pluginApp(CategoryBranchRepositoryContract::class);

		/** @var PaginationResponseContract $categories */
		$categories = $categoryBranchRepo->get($this->request->get('page', 1), $this->request->get('itemsPerPage', 25));

		$list = [];

		foreach($categories->getResult() as $categoryBranchData)
		{
			$categoryBranch = [];

			for($i = 1; $i <= 6; $i++)
			{
				$key = 'category' . $i . 'Id';

				if(isset($categoryBranchData[$key]) && is_int($categoryBranchData[$key]))
				{
					$name = $this->getCategoryName($categoryBranchData[$key]);

					if(strlen($name))
					{
						$categoryBranch[] = $name;
					}

				}
			}

			if(count($categoryBranch))
			{
				$list[] = [
					'categoryId' => $categoryBranchData['categoryId'],
					'name'       => implode(' » ', $categoryBranch),
				];
			}
		}

		$categories->setResult($list);

		return $categories;
	}

	/**
	 * Get the taxonomy correlations.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 *
	 * @return array
	 */
	public function correlations(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		$list = [];

		$correlations = $settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->all('EtsyIntegrationPlugin');

		foreach($correlations as $correlationData)
		{
			$settings = pluginApp(SettingsRepositoryContract::class)->get($correlationData['settingsId']);

			if(isset($settings->settings['id']))
			{
				$list[] = [
					'taxonomyId' => $settings->settings['id'],
					'categoryId' => $correlationData['categoryId'],
				];
			}
		}

		return $list;
	}

	/**
	 * Correlate taxonomy IDs with category IDs.
	 *
	 * @param SettingsCorrelationFactory $settingsCorrelationFactory
	 */
	public function correlate(SettingsCorrelationFactory $settingsCorrelationFactory)
	{
		pluginApp(SettingsRepositoryContract::class)->deleteAll('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_CATEGORY);

		$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->clear('EtsyIntegrationPlugin');

		foreach($this->request->get('correlations', []) as $correlationData)
		{
			if(isset($correlationData['taxonomyId']) && $correlationData['taxonomyId'] && isset($correlationData['categoryId']) && $correlationData['categoryId'])
			{
				$taxonomyData = $this->getTaxonomyData($correlationData['taxonomyId'], $this->request->get('lang', 'de'));

				$settings = pluginApp(SettingsRepositoryContract::class)->create('EtsyIntegrationPlugin', SettingsCorrelationFactory::TYPE_CATEGORY, $taxonomyData);

				$settingsCorrelationFactory->type(SettingsCorrelationFactory::TYPE_CATEGORY)->createRelation($settings->id, $correlationData['categoryId']);
			}
		}

		return pluginApp(Response::class)->make('', 204);
	}

	/**
	 * Get the taxonomy data.
	 *
	 * @param int    $taxonomyId
	 * @param string $lang
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	private function getTaxonomyData(int $taxonomyId, string $lang):array
	{
		/** @var EtsyTaxonomyRepositoryContract $etsyTaxonomyRepo */
		$etsyTaxonomyRepo = pluginApp(EtsyTaxonomyRepositoryContract::class);

		$taxonomy = $etsyTaxonomyRepo->findById($taxonomyId, $lang); // TODO language!

		if(!is_array($taxonomy) || count($taxonomy) <= 0)
		{
			throw new \Exception('Not data found for the given taxonomy ID');
		}

		$taxonomy['language'] = $lang;

		return $taxonomy;
	}

	/**
	 * Get the category name.
	 *
	 * @param int $id
	 * @param string $lang
	 *
	 * @return string
	 */
	private function getCategoryName(int $id, string $lang = 'de'):string
	{
		/** @var CategoryRepositoryContract $categoryRepo */
		$categoryRepo = pluginApp(CategoryRepositoryContract::class);

		/** @var Category $category */
		$category = $categoryRepo->get($id, $lang);

		return $category->details->first()->name;
	}
}
