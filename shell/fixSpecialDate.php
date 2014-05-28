<?php
/*
 * This file is used to unset special date range
 */
require_once('app/Mage.php');

Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

echo "\n\nstart\n\n";

$collection = Mage::getModel('catalog/product')
    ->getCollection()
    ->addAttributeToSelect('*')
    ->addAttributeToFilter(
        array(
            array('attribute' => 'special_from_date', 'like' => '%70%'),
            array('attribute' => 'special_to_date', 'like' => '%70%'),
        )
    )
    //->setPageSize(5)
;

$i = $collection->count();
foreach($collection as $product){
    echo $i-- . ":" . $product->getSku() . "\n";
    echo "special_from_date:" . $product->getData('special_from_date') . "\n";
    echo "special_to_date:" . $product->getData('special_to_date') . "\n";
    echo "... Fixed!!! \n\n";

    $product->setData('special_from_date', '');
    $product->setData('special_to_date', '');
    $product->save();
}
echo "done\n\n";
