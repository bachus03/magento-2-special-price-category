<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Cron;
use Magmakers\SpecialPriceCategory\Helper\Data;
use Magmakers\SpecialPriceCategory\Model\ManageProducts;


class RunManageProducts
{

    protected $logger;

    protected $manageProducts;
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $helper
     * @param ManageProducts $manageProducts
     */
    function __construct(\Psr\Log\LoggerInterface $logger,
                         Data $helper,
                         ManageProducts $manageProducts
    )
    {
        $this->manageProducts =$manageProducts;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**IsEnabled
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if($this->helper->IsEnabled()) {
            $this->logger->addInfo("Cronjob RunManageProducts is executed.");
            try {
                $this->manageProducts->AssignProducts();
                $this->manageProducts->RemoveProducts();
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}

