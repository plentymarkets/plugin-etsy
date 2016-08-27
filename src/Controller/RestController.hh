<?hh //strict

namespace Etsy\Controller;

use Plenty\Plugin\Controller;
use Etsy\Service\Category;

class RestController extends Controller
{
    /**
     * @var Category $category
     */
    private Category $category;

    /**
     * RestController constructor.
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getCategory():string
    {
        return $this->category->getCategory();
    }
}