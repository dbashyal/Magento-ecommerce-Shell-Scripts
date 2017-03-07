<?php

require_once "../app/Mage.php";
Mage::app('admin')->setUseSessionInUrl(false);

// populate this array with the order increment ids that you would like to delete
$orderIds = array(145000130,145000131,145000132,145000133,145000134,145000135,145000136,145000137,145000138,145000139);

$collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('increment_id', (array) $orderIds);

foreach($collection as $order){
    echo 'deleting order id : ' . $order->getIncrementId() . '-' . $order->getId() . "\n";
    $order->delete();
}

echo "Done!!!\n";
