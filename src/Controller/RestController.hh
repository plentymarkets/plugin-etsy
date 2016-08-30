<?hh //strict

namespace Etsy\Controller;

use Plenty\Plugin\Controller;
use Etsy\Service\Category;

class RestController extends Controller
{
    /**
     * @return string
     */
    public function getCategory(Category $category):string
    {
        return $category->getCategory();
    }
}