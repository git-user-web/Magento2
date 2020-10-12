<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Top\Menu\Block\Html;

use Magento\Backend\Model\Menu;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/**
 * Html page top menu block
 *
 * @api
 * @since 100.0.2
 */
class Topmenu extends \Magento\Theme\Block\Html\Topmenu
{
    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     */
    protected function _getHtml(
        Node $menuTree,
        $childrenWrapClass,
        $limit,
        array $colBrakes = []
    ) {
        $html = '';

        $children = $menuTree->getChildren();
        $childLevel = $this->getChildLevel($menuTree->getLevel());
        $this->removeChildrenWithoutActiveParent($children, $childLevel);

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/temp.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info("Info ----- Id");
        $logger->info($childLevel);
        if ($childLevel == 1) {
            $countChildren = count($children);
        }
        /** @var Node $child */
        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter === 1);
            $child->setIsLast($counter === $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel === 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $this->setCurrentClass($child, $outermostClass);
            }

            if ($this->shouldAddNewColumn($colBrakes, $counter)) {
                $html .= '</ul></li><li class="column"><ul>';
            }
            $logger->info("childLevel". $childLevel);
            $logger->info("counter". $counter);

            if ($childLevel != 0 && $counter == 1) {
                if ($childLevel == 2) {
                    $html .= '<div class="div-left sub-section">';
                } else {
                    $html .= '<div class="div-left">';
                }
            }
            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                $child->getName()
            ) . '</span></a>' . $this->_addSubMenu(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            ) . '</li>';
            if ($childLevel == 2 && $counter % 6 == 0) {
                $html .= '</div><div class="div-left sub-section">';
            }
            $counter++;
        }
        if ($childLevel != 0 && (!empty($countChildren) &&  $countChildren!= 6)) {
            // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/templog.log');
            // $logger = new \Zend\Log\Logger();
            // $logger->addWriter($writer);
            // $logger->info("Info ----- Id");
            // $logger->info($child->getId());
            // $logger->info(str_replace("category-node-","",$child->getId()));
            $categoryId = str_replace("category-node-","",$child->getId());

            $objectManager   = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory');
            $category        = $categoryFactory->create()->load($categoryId);
            $parent = $category->getParentIds();
            $count = count($parent);
            $baseCategoryId = $parent[$count - 1];

            $cateinstance = $objectManager->create('Magento\Catalog\Model\CategoryFactory');
            $allcategoryproduct = $cateinstance->create()->load($baseCategoryId)->getProductCollection()->addAttributeToSelect('*');

            $fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
            $mediaPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
            $html .='</div>';
            $imageCount = 6 - $countChildren;
            foreach ($allcategoryproduct as $key => $product) {
                $_product = $objectManager->get('Magento\Catalog\Model\Product')->load($product->getId());
                $imageHelper  = $objectManager->get('\Magento\Catalog\Helper\Image');
                $image_url = $imageHelper->init($_product, 'product_page_image_small')->setImageFile($_product->getImage())->resize(200, 300)->getUrl();
                $priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); 
                if ($imageCount != 0) {
                    // $html .= '<span class="sub-span"><b>Trending</b></span>';
                    $html .= '<div class="div-right">';
                    // $html .= '<div class="first-box">
                    //     <img src="'. $image_url .'" />
                    //     <span class="text-block"><b>'. $product->getName() .'</b></span>
                    //     <span class="text-block">'. $priceHelper->currency($product->getPrice(), true, false) .'</span>
                    // </div></div>';
                    $html .= '<div class="first-box">
                        <img src="'. $image_url .'" />
                    </div></div>';
                }
                if ($key == 2) {
                    // $html .= '<div class="second-box">
                    //     <img src="'. $image_url .'" />
                    //     <span class="text-block"><b>'. $product->getName() .'</b></span>
                    //     <span class="text-block">'. $priceHelper->currency($product->getPrice(), true, false) .'</span>
                    // </div></div>';
                }
                $imageCount--;
            }
        }
        
        if (is_array($colBrakes) && !empty($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }
        $logger->info("last");
        $logger->info($html);

        return $html;
    }

    /**
     * Remove children from collection when the parent is not active
     *
     * @param Collection $children
     * @param int $childLevel
     * @return void
     */
    private function removeChildrenWithoutActiveParent(Collection $children, int $childLevel): void
    {
        /** @var Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                $children->delete($child);
            }
        }
    }

    /**
     * Retrieve child level based on parent level
     *
     * @param int $parentLevel
     *
     * @return int
     */
    // private function getChildLevel($parentLevel): int
    // {
    //     return $parentLevel === null ? 0 : $parentLevel + 1;
    // }

    /**
     * Check if new column should be added.
     *
     * @param array $colBrakes
     * @param int $counter
     * @return bool
     */
    private function shouldAddNewColumn(array $colBrakes, int $counter): bool
    {
        return count($colBrakes) && $colBrakes[$counter]['colbrake'];
    }

    //  private function setCurrentClass(Node $child, string $outermostClass): void
    // {
    //     $currentClass = $child->getClass();
    //     if (empty($currentClass)) {
    //         $child->setClass($outermostClass);
    //     } else {
    //         $child->setClass($currentClass . ' ' . $outermostClass);
    //     }
    // }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param Node $item
     * @return string
     */
    // protected function _getRenderedMenuItemAttributes(Node $item)
    // {
    //     $html = '';
    //     foreach ($this->_getMenuItemAttributes($item) as $attributeName => $attributeValue) {
    //         $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
    //     }
    //     return $html;
    // }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    // protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    // {
    //     $html = '';
    //     if (!$child->hasChildren()) {
    //         return $html;
    //     }

    //     $colStops = [];
    //     if ($childLevel == 0 && $limit) {
    //         $colStops = $this->_columnBrake($child->getChildren(), $limit);
    //     }

    //     $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass . '">';
    //     $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
    //     $html .= '</ul>';

    //     return $html;
    // }

    /**
     * Retrieve child level based on parent level
     *
     * @param int $parentLevel
     *
     * @return int
     */
    private function getChildLevel($parentLevel): int
    {
        return $parentLevel === null ? 0 : $parentLevel + 1;
    }
}
