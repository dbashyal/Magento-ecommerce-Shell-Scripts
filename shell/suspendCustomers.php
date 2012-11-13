<?php
/**
 * Suspend users who are inactive for more than 10 months
 *
 * @category       w3tut
 * @copyright      Copyright (c) 2012 w3tut.org
 * @author         Damodar Bashyal | w3tut.org/contact
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
require_once '../shell/abstract.php';

class Mage_Shell_suspendCustomers extends Mage_Shell_Abstract
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // this requires a bit of memory
        // so, lets set as 512M
        ini_set('memory_limit', '512M');
    }

    /**
     * Runner
     */
    public function run()
    {
        // lets save all updated accounts to a txt file as a backup
        // so that if we need to check affected users, we can check anytime
        $fileName = 'suspended-' . time() . '.txt';
        Mage::log('Customers Suspended on ' . date('Y-m-d H:i:s', time()), null, $fileName);

        // load customers who logged in more than 10 months ago
        // also, updated account more than 10 months ago, as we may
        // have recently updated their account but hasn't got chance to login yet
        $collection = Mage::getModel('customer/customer')->getCollection();
        $collection->getSelect()->joinRight(array('l'=>'log_customer'), "customer_id=entity_id AND l.login_at >= '" . date('Y-m-d H:i:s', strtotime('10 months ago')) . "'")->group('e.entity_id');
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('updated_at', array(
            'gt' => date('Y-m-d H:i:s', strtotime('10 months ago')),
            'datetime'=>true,
        ));
        $collection->addAttributeToFilter('group_id', array(
            'neq' => 5,
        ));
        /*
        // also, we only want to disable users with some email domains e.g. hotmail etc
        $collection->addAttributeToFilter('email', array(
            'like' => '%@fake-email.com%',
        ));
        */

        // print out the query to see if it's generating right sql statement.
        //echo $collection->printLogQuery(true);die();

        // total users suspended
        $total = 0;

        // lets iterate through customers who haven't logged-in in last 10 months
        // and change their user group to group_id '5' which is for suspended users.
        foreach($collection as $v){
            $v->setData('group_id', 5);
            $v->save();
            $total++;

            // store the information on our log file
            $user = $v->getData('email') . ' - ' . $v->getData('group_id');
            Mage::log($user, null, $fileName);
        }
        // thats the end of the log
        Mage::log('====END====', null, $fileName);

        // if there was no suspended user, lets delete file and keep folder tidy.
        if(!$total){
            unlink(Mage::getBaseDir('log') . DS . $fileName);
        }
    }
}

$shell = new Mage_Shell_suspendCustomers();
$shell->run();