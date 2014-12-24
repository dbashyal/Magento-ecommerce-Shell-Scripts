<?php
/*
 * @author: Damodar Bashyal (@dbashyal)
 * tested on: Magento Enterprise Edition
 */
class Dse_Orderxml_Model_Sales_Service_Quote extends Mage_Sales_Model_Service_Quote
{
    public function submitOrder()
    {
        $this->_deleteNominalItems();
        $this->_validate();
        $quote = $this->_quote;
        $isVirtual = $quote->isVirtual();

        $transaction = Mage::getModel('core/resource_transaction');
        if ($quote->getCustomerId()) {
            $transaction->addObject($quote->getCustomer());
        }
        $transaction->addObject($quote);

        $quote->reserveOrderId();
        if ($isVirtual) {
            $order = $this->_convertor->addressToOrder($quote->getBillingAddress());
        } else {
            $order = $this->_convertor->addressToOrder($quote->getShippingAddress());
        }
        $order->setBillingAddress($this->_convertor->addressToOrderAddress($quote->getBillingAddress()));
        if ($quote->getBillingAddress()->getCustomerAddress()) {
            $order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
        }
        if (!$isVirtual) {
            $order->setShippingAddress($this->_convertor->addressToOrderAddress($quote->getShippingAddress()));
            if ($quote->getShippingAddress()->getCustomerAddress()) {
                $order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
            }
        }
        $payment = $quote->getPayment();
        $paymentData = $payment->getData();
        $payment->setMethod('paynow');

        $order->setPayment($this->_convertor->paymentToOrderPayment($payment));

        $message = 'NOTE:: Order manually submitted from quote.';
        $message .= "\n Original Data: ";
        foreach($paymentData as $k => $v){
            if(!empty($v)){
                if(!is_array($v) && !is_object($v)){
                    $message .= "\n {$k}: {$v}";
                } else if(is_array($v)){
                    $message .= "\n {$k}:\n";
                    foreach($v as $k2 => $v2){
                        if(!empty($v2)){
                            if(!is_array($v2) && !is_object($v2)){
                                $message .= "\n {$k2}: {$v2}";
                            }
                        }
                    }
                }
            }
        }
        $order->addStatusHistoryComment($message, $order->getStatus())->setIsCustomerNotified(false);

        foreach ($this->_orderData as $key => $value) {
            $order->setData($key, $value);
        }

        foreach ($quote->getAllItems() as $item) {
            $orderItem = $this->_convertor->itemToOrderItem($item);
            if ($item->getParentItem()) {
                $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
            }
            $order->addItem($orderItem);
        }

        $order->setQuote($quote);

        $transaction->addObject($order);
        $transaction->addCommitCallback(array($order, 'place'));
        $transaction->addCommitCallback(array($order, 'save'));

        /**
         * We can use configuration data for declare new order status
         */
        Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$quote));
        Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));
        try {
            $transaction->save();
            $this->_inactivateQuote();
            Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order'=>$order, 'quote'=>$quote));
        } catch (Exception $e) {

            if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
                // reset customer ID's on exception, because customer not saved
                $quote->getCustomer()->setId(null);
            }

            //reset order ID's on exception, because order not saved
            $order->setId(null);
            /** @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getItemsCollection() as $item) {
                $item->setOrderId(null);
                $item->setItemId(null);
            }

            Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
            throw $e;
        }
        Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
        $this->_order = $order;
        return $order;
    }

    /*
     * @author: Marius (http://magento.stackexchange.com/a/21744/3906)
     * */
    public function deleteOrder(Mage_Sales_Model_Order $order){
        $invoices = $order->getInvoiceCollection();
        foreach ($invoices as $invoice){
            //delete all invoice items
            $items = $invoice->getAllItems();
            foreach ($items as $item) {
                $item->delete();
            }
            //delete invoice
            $invoice->delete();
        }
        $creditnotes = $order->getCreditmemosCollection();
        foreach ($creditnotes as $creditnote){
            //delete all creditnote items
            $items = $creditnote->getAllItems();
            foreach ($items as $item) {
                $item->delete();
            }
            //delete credit note
            $creditnote->delete();
        }
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment){
            //delete all shipment items
            $items = $shipment->getAllItems();
            foreach ($items as $item) {
                $item->delete();
            }
            //delete shipment
            $shipment->delete();
        }
        //delete all order items
        $items = $order->getAllItems();
        foreach ($items as $item) {
            $item->delete();
        }
        //delete payment - not sure about this one
        $order->getPayment()->delete();
        //delete quote - this can be skipped
        /*if ($order->getQuote()) {
            foreach ($order->getQuote()->getAllItems() as $item) {
                $item->delete();
            }
            $order->getQuote()->delete();
        }*/
        //delete order
        $order->delete();

        return $this;
    }
}
