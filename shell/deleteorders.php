<?php

require_once "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

// populate this array with the order increment ids that you would like to delete
$orderIds = range(145000223,145000322);

$collection = Mage::getModel('sales/order')->getCollection();//->addFieldToFilter('increment_id', (array) $orderIds);

foreach($collection as $order){
    echo 'deleting order id : ' . $order->getIncrementId() . '-' . $order->getId() . "\n";
    $order->delete();
}

echo "Done!!!\n";