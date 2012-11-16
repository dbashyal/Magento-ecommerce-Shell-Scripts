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

        // load customers who hasn't logged in for more than 10 months
        // or, created account more than 10 months ago but never logged in
        // have recently updated their account but hasn't got chance to login yet
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        /*$sql = "select * from (
                    select e.*,l.login_at
                        from customer_entity as e
                        left join log_customer as l
                        on l.customer_id=e.entity_id
                        group by e.entity_id
                        order by l.login_at desc
                ) as l
                where (
                    l.login_at <= '".date('Y-m-d H:i:s', strtotime('10 months ago'))."'
                    or (
                        l.created_at <= '".date('Y-m-d H:i:s', strtotime('10 months ago'))."'
                        and
                        l.login_at is NULL
                        )
                    )
                and
                group_id != 5";*/
        $sql = "SELECT e.*,MAX(l.login_at) as login_at
        FROM `customer_entity` e
        LEFT JOIN `log_customer` l on e.entity_id=l.customer_id
        GROUP BY l.customer_id
        HAVING created_at < '".date('Y-m-d H:i:s', strtotime('10 months ago'))."'
        and (login_at< '".date('Y-m-d H:i:s', strtotime('10 months ago'))."' or login_at is null)
        and group_id != 5
        ORDER BY `e`.`entity_id` ASC";
        $result = $read->fetchAll($sql);

        // print the query to see if it's generating right sql statement.
        //Mage::log(print_r($sql, true));die();

        // total users suspended
        $total = 0;

        // lets iterate through the result
        // and change their user group to group_id '5' which is for suspended users.
        foreach($result as $v){
            $v = Mage::getModel('customer/customer')->load($v['entity_id']);
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