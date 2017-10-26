<?php
/**
 *
 * Copyright (c) 2017.
 * inventory.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

// it will fix duplicate url_paths for products
require_once('../app/Mage.php');
Mage::app('admin');
require_once 'abstract.php';
class Mage_Shell_fixproduct extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        $_products = array();
        $ids = range(13354,13400);
        $i = 1;
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('entity_id');
        $collection->addAttributeToFilter(array(
            array(
                'attribute' => 'entity_id',
                'in'        => $ids,
                )
        ));

        $storeIds = 2;
        foreach($collection as $_product){
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId());
            $qty = (int)$stockItem->getData('qty');
            $newQty = 0;
            $is_in_stock = 0;
            if($qty > 5){
                $newQty = $qty - 5;
                $is_in_stock = 1;
            }

            $stockItem->setData('is_in_stock', $is_in_stock);
            $stockItem->setData('qty', $newQty);
            $stockItem->save();

            echo ($i++) . '> ' . $_product->getSku() .':'. "{$newQty} - {$qty}" . " - SAVED!!! \n";
        }
    }
}

$shell = new Mage_Shell_fixproduct();
$shell->run();
echo 'done!!!';
echo "\n";