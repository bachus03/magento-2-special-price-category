<?php

declare(strict_types=1);

namespace Magmakers\SpecialPriceCategory\Model;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magmakers\SpecialPriceCategory\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\ProductFactory;

class ManageProducts {


    /**
     * @var Data|Magmakers\SpecialPriceCategory\Helper\Data
     */
    private $helper;
    /**
     * @var CategoryLinkManagementInterface
     */
    private $CategoryLinkRepository;
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var Configurable
     */
    private $configurable;
    /**
     * @var Grouped
     */
    private $grouped;
    /**
     * @var Type
     */
    private $bundle;


    /**
     * ManageProducts constructor.
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param CategoryLinkManagementInterface $CategoryLinkRepository
     * @param CategoryFactory $categoryFactory
     * @param Configurable $configurable
     * @param Grouped $grouped
     * @param Type $bundle
     * @param ProductFactory $productloader
     */
    public function __construct(CollectionFactory $collectionFactory,
                                Data $helper,
                                CategoryLinkManagementInterface $CategoryLinkRepository,
                                CategoryFactory $categoryFactory,
                                Configurable $configurable,
                                Grouped $grouped,
                                Type $bundle

    ){
        $this->helper = $helper;
        $this->CategoryLinkRepository = $CategoryLinkRepository;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->bundle = $bundle;

    }

    /**
     * @return string
     */

    public function AssignProducts():void{
        $now = date('Y-m-d');
        $specialPriceProductsCollection = $this->collectionFactory->create()->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->addAttributeToFilter('special_price', ['neq' => ''])
            ->addAttributeToSelect('sku')
            ->addAttributeToFilter([['attribute' => 'special_from_date',
                'lteq' => date('Y-m-d G:i:s', strtotime($now)),
                'date' => true, ], ['attribute' => 'special_to_date',
                'gteq' => date('Y-m-d G:i:s', strtotime($now)),
                'date' => true,]]);

        foreach($specialPriceProductsCollection as $product) {

            $this->AssignProductOrParentProduct($product);

        }
    }

    /**
     * @param Product $product
     */
    public function AssignProductOrParentProduct($product){

        try{
            $this->AssignProduct($product);

            $parentsProductsCollection = $this->collectionFactory->create()->addAttributeToSelect('sku')->addAttributeToFilter('entity_id', array('in' => $this->getParentProducts((int)$product->getId())));
            if(isset($parentsProductsCollection)){

                foreach($parentsProductsCollection as $ParentProduct){
                    $this->AssignProduct($ParentProduct);
                }
            }
        } catch (\Exception $e) {

            $this->logger->info($e->getMessage());
        }
    }

    /**
     * @param Product $product
     */
    public function AssignProduct(Product $product){

        if($product->getVisibility()!=1) {

            $new_category_id = array($this->helper->getCategory());
            $categories = array_merge($product->getCategoryIds(), $new_category_id);
            $this->CategoryLinkRepository->assignProductToCategories($product->getSku(), $categories);

        }

    }

    /**
     * @param int $productId
     * @return array
     */
    public function getParentProducts(int $productId): array
    {
        $allParentIds = array();
        $parentIds_conf = $this->configurable->getParentIdsByChild($productId);
        $parentIds_grouped = $this->grouped->getParentIdsByChild($productId);
        $parentIds_bundle = $this->bundle->getParentIdsByChild($productId);
        $allParentIds = array_merge(array_merge($parentIds_conf,$parentIds_grouped), $parentIds_bundle);
        return $allParentIds;
    }

    public function getChildProducts(int $productId){

        $allChildIds = array();
        $ChildIds_conf = $this->configurable->getChildrenIds($productId);
        $ChildIds_grouped = $this->grouped->getChildrenIds($productId);
        $ChildIds_bundle = $this->bundle->getChildrenIds($productId);
        $allChildIds = array_merge(array_merge($ChildIds_conf,$ChildIds_grouped), $ChildIds_bundle);
        return $allChildIds;

    }


    public function RemoveProducts():void{
        try{
        $SpecialPriceCategory = $this->helper->getCategory();
        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('special_from_date')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('special_price')
            ->addCategoriesFilter(['eq' => $SpecialPriceCategory]);
            if(count($collection)>0) {
                foreach ($collection as $product) {

                    $this->RemoveProductOrParent($product);
                }
            }
        } catch (\Exception $e) {

            $this->logger->info($e->getMessage());
        }
    }


    /**
     * @param Product $product
     * @return bool
     */
    public function RemoveProductOrParent(Product $product): bool
    {

        if (!($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE || $product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)) {
            $remove = true;
            $ChildProducts = $this->getChildProducts((int)$product->getId());
            if (count($ChildProducts) > 0) {
                $ChildProductsCollection = $this->collectionFactory->create()->addAttributeToSelect('sku')->addAttributeToFilter('entity_id', array('in' => array_filter($ChildProducts)));
                foreach ($ChildProductsCollection as $ChildProduct) {
                    if ($this->helper->getIsSpecialPrice($ChildProduct)) {
                        $remove = false;
                        break;
                    }
                }
                if ($remove) {

                    $this->RemoveProduct($product);
                    return true;
                }
            }
        } else {

            if (!$this->helper->getIsSpecialPrice($product)) {

                $this->RemoveProduct($product);


                $ParentProducts = $this->getParentProducts((int)$product->getId());
                if (count($ParentProducts) > 0) {
                    foreach ($ParentProducts as $ParentProduct) {
                        $removeParent = true;
                        $ChildProducts = $this->getChildProducts((int)$ParentProduct);
                        $ChildProductsCollection = $this->collectionFactory->create()->addAttributeToSelect('sku')->addAttributeToFilter('entity_id', array('in' => array_filter($ChildProducts)));
                        foreach ($ChildProductsCollection as $childProduct) {
                            if ($this->helper->getIsSpecialPrice($childProduct)) {
                                $removeParent = false;
                                break;
                            }
                        }
                    }
                    if($removeParent){

                        foreach($this->collectionFactory->create()->addAttributeToSelect('sku')->addAttributeToFilter('entity_id', array('in' => $ParentProduct)) as $parentProductObject){

                            $this->RemoveProduct($parentProductObject);
                            return true;
                        }
                    }
                }
            }
        }

        return false;


    }

    /**
     * @param $product
     */
    public function RemoveProduct($product){

        $categories = $product->getCategoryIds();
        if (!is_array($categories)) {
            $categories = array($categories);
        }
        $new_categories = array();
        foreach ($categories as $category) {
            if ($category != $this->helper->getCategory()) {
                $new_categories[] = $category;
            }
        }
        if($categories = $new_categories){
            $this->CategoryLinkRepository->assignProductToCategories($product->getSku(), $new_categories);
        }

    }
}
