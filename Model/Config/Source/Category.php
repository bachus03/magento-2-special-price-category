<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\Collection;

class Category implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $categoryCollection;

    /**
     * Category constructor.
     * @param Collection $categoryCollection
     */
    public function __construct(Collection $categoryCollection)
    {
        $this->categoryCollection = $categoryCollection;

    }
    public function toOptionArray(){

        $categories = $this->categoryCollection->addAttributeToSelect('name');

        $categories_option = array();
        foreach ($categories as $category){

            $categories_option[] = ['value' => $category->getId(), 'label' => $category->getName()];
        }
            return $categories_option;
    }

}
