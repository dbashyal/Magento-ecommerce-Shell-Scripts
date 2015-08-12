<?php
/**
 * Send order alerts
 *
 * @copyright      Copyright (c) 2015 dltr.org
 * @author         Damodar Bashyal | dltr.org/contact
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $collection->addAttributeToSort('entity_id', 'asc')->setPageSize(500)->setCurPage(1);

        $orders = "<table border='1' cellpadding='2' style='border-collapse: collapse'><tr><th>ID</th><th>Status</th><th>Date</th></tr>";
        $sendEmail = false;
        foreach($collection as $order){
            $sendEmail = true;
            $orders .= "\n<tr><td>" . $order->getIncrementId() . "</td><td>" . $order->getStatus() . "</td><td>" . $order->getCreatedAt() . "</td></tr>";
        }
        $orders .= "</table>";

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
