<?php
/*
 * @author dbashyal dltr.org
 */
require_once 'abstract.php';

/**
 * Class Mage_Shell_Tclean
 */
class Mage_Shell_TMoveOrder extends Mage_Shell_Abstract
{
    public function run()
    {
        $store = Mage::app()->getStore(1);
        $fromCustomer = Mage::getModel('customer/customer')->setStore($store)->loadByEmail('fromemail@gmail.com');
        $toCustomer = Mage::getModel('customer/customer')->setStore($store)->loadByEmail('toemail@hotmail.com');

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

$shell = new Mage_Shell_TMoveOrder();
$shell->run();
