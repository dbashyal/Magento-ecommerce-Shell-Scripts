<?php

require_once dirname(dirname(__FILE__)) . '/app/Mage.php';

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin')->setUseSessionInUrl(false);
Mage::register('isSecureArea', 1);


// populate this array with the order increment ids that you would like to delete
$orderIds = range(145000200,145000208);

$collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('increment_id', (array) $orderIds);

/* @var $order Mage_Sales_Model_Order */
foreach($collection as $order){
    // check if has shipment(s)
    if(!$order->hasShipments()){
        echo "\nOrder ID - " . $order->getIncrementId() . ' has no shipments!';
        continue;
    }

    //delete shipment(s)
    $shipments = $order->getShipmentsCollection();
    /* @var $shipment Mage_Sales_Model_Order_Shipment */
    foreach ($shipments as $shipment){
        $shipment->delete();
    }

    // Reset item shipment qty
    $items = $order->getAllVisibleItems();
    /* @var $item Mage_Sales_Model_Order_Item */
    foreach($items as $item){
        $item->setQtyShipped(0);
        $item->save();
    }

    //Reset order state
    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Undo Shipment');
    $order->save();

    echo "\nOrder ID - " . $order->getIncrementId() . ' shipments deleted!';
}

echo "\nDone!!!\n";