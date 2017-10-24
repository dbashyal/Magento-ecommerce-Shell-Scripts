<?php

require_once dirname(dirname(__FILE__)) . '/app/Mage.php';

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin')->setUseSessionInUrl(false);
Mage::register('isSecureArea', 1);

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToSelect(array('sku', 'category_name', 'category_id'));
$collection->addAttributeToFilter('type_id', 'configurable');
$collection->addAttributeToFilter('sku', array('like' => 'SCO-CLRDSCRUNCH%'));
//$collection->addAttributeToFilter('category_name', 'N/A');

/* @var $product Mage_Catalog_Model_Product */
foreach($collection as $product){
    echo "\nSKU - " . $product->getSku();
    echo "\nCN - " . $product->getCategoryName();
    echo "\ndata - " . var_dump($product->getData());
}

echo "\nDone!!!\n";
