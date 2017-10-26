<?php
/**
 *
 * Copyright (c) 2017.
 * quote-to-order.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

require_once 'abstract.php';
require_once 'utility.php';

class Quote_To_Order_Shell extends Mage_Shell_Abstract
{
    public function run()
    {
        $quotes = $this->getArg('ids');
        if($quotes){
            $quotes = explode(',', $quotes);
        }
        if(!is_array($quotes) || !count($quotes)){
            echo "\n -- please provide quote id(s) if multiple, separated with comma. e.g. 27090439";
            return $this;
        }

        $collection = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter('entity_id', array('in' => $quotes));

        foreach($collection as $quote){
            $id = $quote->getId();
            echo "\n-- checking quote id: " . $id;
            $order = Mage::getModel('sales/order')->load($id, 'quote_id');
            $order_id = $order->getId();
            if($order_id){
                echo "\n --- Order already exists for this quote.";
                $delete = $this->getArg('delete-existing');
                if($delete){
                    echo " ... deleting order id {$order_id} now.";
                    Mage::getModel('dseorderxml/sales_service_quote')->deleteOrder($order);
                    echo ".....done.";
                } else {
                    echo "\n --- Order already exists for this quote. See: " . $order_id;
                    return $this;
                }
            }
            echo "\n  : No existing order found for this quote id.";
            echo "\n  : I'll now try to convert this into order.";

            $model = Mage::getModel('dseorderxml/sales_service_quote', $quote);

            $order = $model->submitOrder();

            if($order && $order instanceof Mage_Sales_Model_Order){
                echo "\n  : Generated Order: " . $order->getId();
            } else {
                echo "\n..... Failed creating order.";
            }

            echo "\n.....\n";
        }
        return $this;
	}
}


print "\n";
$shell = new Quote_To_Order_Shell();
try {
	$shell->run();
} catch( Exception $e) {
	echo $e->getMessage();
}

print "\n\n";
