<?php
/**
 * Change order status manually | programatically through shell
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
class DSE_Shell_Orderstatus extends Mage_Shell_Abstract
{
    public function run()
    {
        Mage::register('isSecureArea', 1);
        $orders = $this->getArg('orders');
        if(empty($orders)){
            return false;
        }
        $orders = explode(',', $orders);
        $_orderStatus = $this->getArg('status');
        switch($_orderStatus){
            case 'new':
                $orderstatus = Mage_Sales_Model_Order::STATE_NEW;
                break;
            case 'pending_payment':
                $orderstatus = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                break;
            case 'complete':
                $orderstatus = Mage_Sales_Model_Order::STATE_COMPLETE;
                break;
            case 'closed':
                $orderstatus = Mage_Sales_Model_Order::STATE_CLOSED;
                break;
            case 'holded':
                $orderstatus = Mage_Sales_Model_Order::STATE_HOLDED;
                break;
            case 'payment_review':
                $orderstatus = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                break;
            case 'cancel':
            case 'canceled':
            case 'cancelled':
                $orderstatus = Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case 'processing':
            default:
                $orderstatus = Mage_Sales_Model_Order::STATE_PROCESSING;
        }
        foreach($orders as $orderId){
            echo "\nchanging order status for: {$orderId}";
            $order = Mage::getModel("sales/order")->loadByIncrementId($orderId);
            try{
                if(!$order->getId()){
                    echo "\norder not found!\n";
                    continue;
                }
                $order->setData('state', $orderstatus);
                $order->setStatus($orderstatus);
                $history = $order->addStatusHistoryComment('Changed order status to ' . $orderstatus . ' manually.', $orderstatus);
                $history->setIsCustomerNotified(false);
                $order->save();
            }catch (Exception $e){
                // -- do something
            }
        }
        echo "\nDone!!!\n";
        return true;
    }
}

$shell = new DSE_Shell_Orderstatus();
$shell->run();
