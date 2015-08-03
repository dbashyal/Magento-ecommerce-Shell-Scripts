<?php
require_once 'abstract.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);
class DSE_Shell_Orderstatus extends Mage_Shell_Abstract
{
    public function run()
    {
        Mage::register('isSecureArea', 1);
        $orderId = $this->getArg('orders');
        if(empty($orderId)){
            return false;
        }
        echo "\nchanging order status for: {$orderId}";
        $orderstatus = Mage_Sales_Model_Order::STATE_PROCESSING;
        $order = Mage::getModel("sales/order")->loadByIncrementId($orderId);
        try{
            if(!$order->getId()){
                echo "\norder not found!\n";
                return false;
            }
            $order->setData('state', $orderstatus);
            $order->setStatus($orderstatus);
            $history = $order->addStatusHistoryComment('Changed order status to ' . $orderstatus . ' manually.', $orderstatus);
            $history->setIsCustomerNotified(false);
            $order->save();
        }catch (Exception $e){
            // -- do something
        }
        echo "\nDone!!!\n";
        return true;
    }
}

$shell = new DSE_Shell_Orderstatus();
$shell->run();
