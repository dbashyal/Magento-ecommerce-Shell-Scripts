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
        $_orderState = $this->getArg('state');
        switch($_orderState){
            case 'new':
                $orderstate = Mage_Sales_Model_Order::STATE_NEW;
                break;
            case 'pending_payment':
                $orderstate = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                break;
            case 'complete':
                $orderstate = Mage_Sales_Model_Order::STATE_COMPLETE;
                break;
            case 'closed':
                $orderstate = Mage_Sales_Model_Order::STATE_CLOSED;
                break;
            case 'holded':
                $orderstate = Mage_Sales_Model_Order::STATE_HOLDED;
                break;
            case 'payment_review':
                $orderstate = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                break;
            case 'cancel':
            case 'canceled':
            case 'cancelled':
                $orderstate = Mage_Sales_Model_Order::STATE_CANCELED;
                break;
            case 'processing':
                $orderstate = Mage_Sales_Model_Order::STATE_PROCESSING;
                break;
            default:
                echo "\nState not found.";
                return false;
        }

        $allowedStatus = array(
            'canceled',
            'cancel_ogone',
            'closed',
            'complete',
            'decline_ogone',
            'fraud',
            'fulfillment',
            'holded',
            'payment_review',
            'paypal_canceled_reversal',
            'paypal_reversed',
            'pending',
            'pending_ogone',
            'pending_payment',
            'pending_paypal',
            'processed_ogone',
            'processing',
            'processing_ogone',
            'ready_to_collect',
            'waiting_authorozation'
        );

        if(!$_orderStatus || !in_array($_orderStatus, $allowedStatus)){
            $_orderStatus = $orderstate;
        }

        foreach($orders as $orderId){
            echo "\nchanging order status for: {$orderId}";
            $order = Mage::getModel("sales/order")->loadByIncrementId($orderId);
            try{
                if(!$order->getId()){
                    echo "\norder not found!\n";
                    continue;
                }
                $order->setData('state', $orderstate);
                $order->setData('status', $_orderStatus);
                //$order->setStatus($_orderStatus);
                $history = $order->addStatusHistoryComment('Changed order state to ' . $orderstate . ' and status to ' . $_orderStatus . ' manually.', $_orderStatus);
                $history->setIsCustomerNotified(false);
                $order->save();
            }catch (Exception $e){
                // -- do something
                echo "\n" . $e->getMessage() . "\n";
            }
        }
        echo "\nDone!!!\n";
        return true;
    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php orderstatus.php --state processing --status ready_to_collect --orders e1000001,e1000002,e1000003

 status is optional

  -h            Short alias for help
  help          This help
USAGE;
    }
}

$shell = new DSE_Shell_Orderstatus();
$shell->run();

echo "\n";
