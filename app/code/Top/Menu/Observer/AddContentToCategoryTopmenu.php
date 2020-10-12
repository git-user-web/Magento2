<?php
/**
 * Topmenu catalog observer to add custom additional elements
 *
 * @category  Vendor
 * @package   Vendor\NavigationMenu
 * @author    Your Name <your.name@email.com>
 * @copyright 2017 Vendor
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Top\Menu\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
 
/**
 * Class AddFirstCategoryImageToTopmenu
 * @package Vendor\NavigationMenu
 */
class AddContentToCategoryTopmenu implements ObserverInterface
{
    /**
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected $categoryRepository;
 
    /**
     * AddFirstCategoryImageToTopmenu constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository repository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }
 
    /**
     * @param Observer $observer Observer object
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getTransport();
        $html      = $transport->getHtml();
        $menuTree  = $transport->getMenuTree();
 
        $parentLevel = $menuTree->getLevel();
        $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/execute.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("get");
        $logger->info('here');
 
        $menuId = $menuTree->getId();
 
        if ($childLevel == 1 && $this->isCategory($menuId)) {
            $html .= '<li class="category_image" style=""><img src="'.$this->getCategoryImage($menuId).'"/></li>';
        }
 
        $transport->setHtml($html);
    }
 
    /**
     * Retrieves the category image for the corresponding child
     *
     * @param string $categoryId Category composed ID
     *
     * @return string
     */
    protected function getCategoryImage($categoryId)
    {
        $categoryIdElements = explode('-', $categoryId);
        $category           = $this->categoryRepository->get(end($categoryIdElements));
        $categoryName       = $category->getImageUrl();
 
        return $categoryName;
    }
 
    /**
     * Check if current menu element corresponds to a category
     *
     * @param string $menuId Menu element composed ID
     *
     * @return string
     */
    protected function isCategory($menuId)
    {
        $menuId = explode('-', $menuId);
 
        return 'category' == array_shift($menuId);
    }
}
