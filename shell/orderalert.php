<?php
/**
 *
 * Copyright (c) 2017.
 * orderalert.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

require_once 'abstract.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
umask(0);
class DSE_Shell_Orderalert extends Mage_Shell_Abstract
{
    public function run()
    {
        Mage::register('isSecureArea', 1);
        $orderstatus = array(
            Mage_Sales_Model_Order::STATE_COMPLETE,
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_CANCELED
        );

        $collection = Mage::getModel("sales/order")->getCollection();
        $collection->addAttributeToFilter('state', array('nin' => $orderstatus));
        $fromDate = date('Y-m-d H:i:s', strtotime("5 years ago"));
        $toDate = date('Y-m-d H:i:s', strtotime("3 days ago"));
        $collection->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
        $collection->addAttributeToSort('entity_id', 'asc')->setPageSize(2000)->setCurPage(1);

        $orders = "<table border='1' cellpadding='2' style='border-collapse: collapse'><tr><th>ID</th><th>Status</th><th>Date</th></tr>";
        $sendEmail = false;
        $storeOrders = array();
        foreach($collection as $order){
            $sendEmail = true;
            $storeOrders[$order->getStoreName()][] = "<tr><td>" . $order->getIncrementId() . "</td><td>" . $order->getStatus() . "</td><td>" . $order->getCreatedAt() . "</td></tr>";
        }
        foreach($storeOrders as $name => $_store_order){
            $orders .= "\n<tr><th colspan='3'>" . str_replace("\n", ',', $name) . "</th></tr>\n" . implode("\n", $_store_order);
        }
        $orders .= "\n</table>";

        if($sendEmail){
            echo "\nsending email\n";
            $this->sendEmail($orders);
        }
        echo "\nDone!!!\n";
        return true;
    }

    public function sendEmail($str = '') {
        $sender = Mage::getStoreConfig('s3_files_section/email/sender_email', Mage::app()->getStore());
        $recipient = array(
            'damodar@example.com.au'
        );
        $mail = new Zend_Mail();
        $mail
            ->setBodyHtml("<b>Let's update these old orders with correct status:</b><br /><br />". $str ."<br />")
            ->setFrom($sender, 'Magento Old Orders Cleanup Alert')
            ->addTo($recipient)
            ->setSubject('Old Orders Need Your Attention!');

        if(!empty($str)){
            if($mail->send()){
                echo "\nsent\n";
            } else {
                echo "\nsending failed\n";
            }
        }
    }
}

$shell = new DSE_Shell_Orderalert();
$shell->run();
