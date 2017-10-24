<?php
/*
 * WARNING: This will actually delete products.
 **/
require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin')->setUseSessionInUrl(false);
Mage::register('isSecureArea', 1);

/* @var $category Mage_Catalog_Model_Category */
$category = Mage::getModel('catalog/category')->load(5);
$collection = $category->getProductCollection();

$count = $collection->getSize();
if($count){
    /* @var $product Mage_Catalog_Model_Product */
    foreach($collection as $product){
        $sku = "{$count}. SKU - " . $product->getSku();
        try{
            $product->delete();
        }catch (Exception $e){
            echo "\n-Error: " . $e->getMessage();
        }
        echo "\n-Deleted! {$sku}";
        --$count;
    }
}
echo "\nDone!!!\n";