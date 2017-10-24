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

/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addFieldToFilter('is_school_wear', 1);

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