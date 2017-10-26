<?php
/**
 *
 * Copyright (c) 2017.
 * moveordertoanotheruser.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

require_once 'abstract.php';

class Technooze_Shell_Move_Order_To_Different_Customer extends Mage_Shell_Abstract
{
    public $_selected_orders = array();
    public $_from_email = null;
    public $_to_email = null;

    public function run()
    {
        if( empty($this->_from_email) || empty($this->_to_email) ){
            echo "\nPlease provide from and to email addresses!\n";
            return;
        }

        //$customer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1));
        $fromCustomer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1))->loadByEmail($this->_from_email);
        $toCustomer = Mage::getModel('customer/customer')->setStore(Mage::app()->getStore(1))->loadByEmail($this->_to_email);

        if( !$toCustomer->getId() || !$fromCustomer->getId() ){
            echo "\nPlease check from and to email and try again! Make sure both customers exists!\n";
            return;
        }

        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->addFieldToFilter(
            array('customer_email', 'customer_id'),
            array(
                array('eq' => $fromCustomer->getEmail()),
                array('eq' => $fromCustomer->getId())
            )
        );

        if(!empty($this->_selected_orders)){
            $collection->addFieldToFilter('increment_id', array('in' => $this->_selected_orders));
        }

        //echo $collection->getSelect()->__toString() . "\n";


        foreach($collection as $order){
            $order->setCustomerId($toCustomer->getId());
            $order->setCustomerEmail($toCustomer->getEmail());
            $order->save();
        }

        return $this;
    }
}

$shell = new Technooze_Shell_Move_Order_To_Different_Customer();
/* @todo move to args */
$shell->_from_email = 'damodarbashyal@example.com';
$shell->_to_email = 'dbashyal@example.com';
$shell->_selected_orders = array(145000234);
$shell->run();

echo "\ndone!!!\n";
