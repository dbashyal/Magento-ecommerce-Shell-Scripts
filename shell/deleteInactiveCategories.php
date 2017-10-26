<?php
/**
 *
 * Copyright (c) 2017.
 * deleteInactiveCategories.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:20 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

require_once "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

/* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
$collection = Mage::getModel('catalog/category')->getCollection();
$collection->addAttributeToFilter('is_active', 0);
$collection->addAttributeToSelect('name');

echo $collection->getSize();
foreach($collection as $k => $category){
    echo "\n{$k}:";
    echo $category->getName();
    $category->delete();
}

echo "Done!!!\n";