<?php
/**
 * GiaPhuGroup Co., Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GiaPhuGroup.com license that is
 * available through the world-wide-web at this URL:
 * https://www.giaphugroup.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    PHPCuong
 * @package     PHPCuong_BannerSlider
 * @copyright   Copyright (c) 2018-2019 GiaPhuGroup Co., Ltd. All rights reserved. (http://www.giaphugroup.com/)
 * @license     https://www.giaphugroup.com/LICENSE.txt
 */

namespace PHPCuong\BannerSlider\Ui\Component\Listing\Column\Group;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use PHPCuong\BannerSlider\Model\GroupFactory as BannerGroupFactory;

/**
 * Class Options
 */
class Subbanneroptions implements OptionSourceInterface
{
    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var BannerGroupFactory
     */
    protected $bannerGroupFactory;

    /**
     * @var array
     */
    protected $Subbanneroptions;

    /**
     * @var array
     */
    protected $currentSubbanneroptions = [];

    /**
     * Constructor
     *
     * @param BannerGroupFactory $systemStore
     * @param Escaper $escaper
     */
    public function __construct(BannerGroupFactory $bannerGroupFactory, Escaper $escaper)
    {
        $this->bannerGroupFactory = $bannerGroupFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->Subbanneroptions !== null) {
            return $this->Subbanneroptions;
        }

        $this->Subbanneroptions = $this->getAvailableGroups();

        return $this->Subbanneroptions;
    }

    /**
     * Prepare groups
     *
     * @return array
     */
    private function getAvailableGroups()
    {
        // $collection = $this->bannerGroupFactory->create()->getCollection();
        $result = [];
        $result = [
                        ['value' => 0, 'label' => 'Select...'],
                        ['value' => 1, 'label' => 'Left'],
                        ['value' => 2, 'label' => 'Right'],
                        ['value' => 3, 'label' => 'Center']
                    ];
        return $result;
    }
}
