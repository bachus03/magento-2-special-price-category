# Magento 2 Module  SpecialPriceCategory

Module add functionality which automatically track products with special price and puts them int category
## Main Functionalities

 - script runs through the all products and add them to selected category when they have special price, or their children has special price.
 - script also remove products from a category when special price is no longer available.


## Configuration

 - Turn off/onn(discount_category/settings/enabled)

 - category (discount_category/settings/category)


## Specifications

 - Cronjob
	- magmakers_specialpricecategory_runmanageproducts

 - Observer
	- catalog_product_save_after > Magmakers\SpecialPriceCategory\Observer\Catalog\ProductSaveAfter

    



