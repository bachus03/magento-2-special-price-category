<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    CONST PROMOTION_XML_SELECTED_CATEGORY = 'discount_category/settings/category';
    CONST SPECIAL_PRICE_CATEGORY_ON = 'discount_category/settings/enabled';
    /**
     * @var Context
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    public function getCategory(){

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::PROMOTION_XML_SELECTED_CATEGORY, $storeScope);

    }
    public function IsEnabled(){

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::SPECIAL_PRICE_CATEGORY_ON, $storeScope);

    }

    public function getIsSpecialPrice($_product): int
    {

        $orgprice = $_product->getPrice();
        $specialprice = $_product->getSpecialPrice();
        $specialfromdate = $_product->getSpecialFromDate();
        $specialtodate = $_product->getSpecialToDate();
        $today = time();
        if (!$specialprice)
            $specialprice = $orgprice;
        if ($specialprice< $orgprice) {
            if ((is_null($specialfromdate) &&is_null($specialtodate)) || ($today >= strtotime($specialfromdate) &&is_null($specialtodate)) || ($today <= strtotime($specialtodate) &&is_null($specialfromdate)) || ($today >= strtotime($specialfromdate) && $today <= strtotime($specialtodate))) {
                return 1;
            }
        }

        return 0;
    }
}
