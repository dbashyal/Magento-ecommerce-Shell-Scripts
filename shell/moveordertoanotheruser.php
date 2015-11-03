<?php
/*
 * author : Damodar Bashyal
 * url: http://twitter.com/dbashyal
 * usage: php moveordertoanotheruser.php
 */
require_once 'abstract.php';

class DSE_Shell_MpveOrderToAnotherUser extends Mage_Shell_Abstract
{
    public function run()
    {
        //$customer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1));
        $fromCustomer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1))->loadByEmail('fromCustomer@gmail.com');
        $toCustomer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1))->loadByEmail('toCustomer@gmail.com');

        $orderEmailCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_email',$fromCustomer->getEmail());
        $orderIdCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id',$fromCustomer->getId());

        foreach($orderEmailCollection as $order){
            $order->setCustomerId($toCustomer->getId());
            $order->setCustomerEmail($toCustomer->getEmail());
            $order->save();
        }

        foreach($orderIdCollection as $order){
            $order->setCustomerId($toCustomer->getId());
            $order->setCustomerEmail($toCustomer->getEmail());
            $order->save();
        }
    }
}

$shell = new DSE_Shell_MpveOrderToAnotherUser();
$shell->run();
