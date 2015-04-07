<?php
/*
 * author: Damodar Bashyal (@dbashyal)
 * url: http://dltr.org (magento tips and tricks)
 * 
 * */
require_once 'abstract.php';
class Quote_Fixer extends Mage_Shell_Abstract
{
    public function run()
    {
        $report             = array();
        $readConnection     = Mage::getSingleton('core/resource')->getConnection('core_read'); /* @var $readConnection Varien_Db_Adapter_Pdo_Mysql */
        $writeConnection    = Mage::getSingleton('core/resource')->getConnection('core_write'); /* @var $writeConnection Varien_Db_Adapter_Pdo_Mysql */
        $quoteTable         = Mage::getSingleton('core/resource')->getTableName('sales/quote');
        $quoteItemTable     = Mage::getSingleton('core/resource')->getTableName('sales/quote_item');

        $select = "SELECT quote_id, COUNT(*) AS total_items FROM {$quoteItemTable} qi INNER JOIN {$quoteTable} q ON qi.quote_id = q.entity_id WHERE q.items_count < 1 GROUP BY qi.quote_id ORDER BY q.entity_id ASC";
        $quotes = $readConnection->fetchAll($select);

        #echo "\n{$select}\n";
        $report[] = 'found: ' . count($quotes) . ' quotes.';
        foreach($quotes as $quote){
            $report[] = '-----';
            $report[] = "- now fixing quote: {$quote[quote_id]}.";
            $update = "UPDATE {$quoteTable} SET items_count = {$quote[total_items]} WHERE entity_id = {$quote[quote_id]}";
            $stmt = $writeConnection->query($update);
            if($stmt->rowCount()){
                $report[] = ' -- FIXED!!!';
            } else {
                $report[] = ' -- FAIL!!!';
            }
        }
        $msg = implode("\n", $report);
        echo $msg;

        return $report;
	}
}


print "\n";
$shell = new Quote_Fixer();
try {
	$shell->run();
} catch( Exception $e) {
	echo $e->getMessage();
}

print "\n\n";
