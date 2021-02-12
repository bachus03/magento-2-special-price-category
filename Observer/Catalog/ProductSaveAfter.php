<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Observer\Catalog;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magmakers\SpecialPriceCategory\Helper\Data;
use Magmakers\SpecialPriceCategory\Model\ManageProducts;
use Magento\Framework\Message\ManagerInterface;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var CategoryLinkManagementInterface
     */
    private $CategoryLinkRepository;
    /**
     * @var ManageProducts
     */
    private $manageProducts;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Execute observer
     *
     * @param Data $helper
     * @param ManageProducts $manageProducts
     * @param ManagerInterface $messageManager
     */
    public function __construct(Data $helper,
                                ManageProducts $manageProducts,
                                ManagerInterface $messageManager
    )
    {
        $this->helper = $helper;
        $this->manageProducts = $manageProducts;
        $this->messageManager = $messageManager;
    }

    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {

        if($this->helper->IsEnabled()) {

            $product = $observer->getEvent()->getProduct();

            if ($this->helper->getIsSpecialPrice($product)) {
                $this->manageProducts->AssignProductOrParentProduct($product);
                $this->messageManager->addSuccessMessage(__('Product was added to promotion category'));
            } else {
                if($this->manageProducts->RemoveProductOrParent($product)){
                    $this->messageManager->addSuccessMessage(__('Product was removed from promotion category'));
                }

            }
        }

    }
}

