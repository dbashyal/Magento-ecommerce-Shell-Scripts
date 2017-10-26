<?php
/**
 *
 * Copyright (c) 2017.
 * tclean.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */


require_once 'abstract.php';

/**
 * Class Mage_Shell_Tclean
 */
class Mage_Shell_Tclean extends Mage_Shell_Abstract
{

    const XML_LOG_CLEAN_DAYS    = 'system/log/clean_after_day';

    /**
     * @param string $str
     */
    private function log($str=''){ Mage::log($str, null, 'tclean.log'); }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    private function _getCoreRead(){ return Mage::getSingleton('core/resource')->getConnection('core_read');}

    /**
     * @return Varien_Db_Adapter_Interface
     */
    private function _getCoreWrite(){ return Mage::getSingleton('core/resource')->getConnection('core_write');}

    /**
     * @param $date
     * @param bool $includeTime
     * @return null|string
     */
    public function formatDate($date, $includeTime=true){ return Varien_Date::formatDate($date, $includeTime); }

    public function run()
    {
        if ($clean = $this->getArg('clean')) {
            echo "\nStart::tclean\n";

            $time = Mage::getStoreConfig(self::XML_LOG_CLEAN_DAYS) * 60 * 60 * 24;
            $timeLimit = $this->formatDate(Mage::getModel('core/date')->gmtTimestamp() - $time);

            $clean = explode(',', $clean);

            if(in_array('log_url', $clean)){
                // clean log/url_info table
                $this->log('Start::cleanLogUrl');
                $this->cleanLogUrl($timeLimit);
                $this->log('END::cleanLogUrl');
            }

            if(in_array('log_url_info', $clean)){
                // clean log/url_info table
                $this->log('Start::cleanLogUrlInfo');
                $this->cleanLogUrlInfo();
                $this->log('END::cleanLogUrlInfo');
            }

            echo "\nEND::tclean\n";
        } else {
            echo $this->usageHelp();
        }
    }

    /**
     * @param string $table
     * @return string
     */
    private function getTable($table='')
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    /**
     * @param $timeLimit
     * @return $this
     */
    private function cleanLogUrl($timeLimit)
    {
        $readAdapter    = $this->_getCoreRead();
        $writeAdapter    = $this->_getCoreWrite();

        $i = 0;
        $clean = true;
        while ($clean) {
            echo "\n" . (++$i) . ": starting to clean log urls.";
            $select = $readAdapter->select()
                ->from(
                    array('url_table' => $this->getTable('log/url_table')),
                    array('url_id'))
                ->where('url_table.visitor_id IS NULL AND url_table.visit_time < ?', $timeLimit)
                ->order(array('url_table.visit_time ASC'))
                ->limit(100);

            $urlIds = $readAdapter->fetchCol($select);

            if (!$urlIds) {
                break;
            }


            $condition = array('url_id IN (?)' => $urlIds);

            // remove visitors from log/url
            $deleted = $writeAdapter->delete($this->getTable('log/url_table'), $condition);

            echo "\n\t - finished cleaning {$deleted} log url.";

            $clean = $deleted && $i < 1000; // max of 1000 iterations per run
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function cleanLogUrlInfo()
    {
        $readAdapter    = $this->_getCoreRead();
        $writeAdapter    = $this->_getCoreWrite();

        $i = 0;
        $deleted = 0;
        $clean = true;
        while ($clean) {
            echo "\n" . (++$i) . ": starting to clean log url info.";
            $select = $readAdapter->select()
                ->from(
                    array('url_info_table' => $this->getTable('log/url_info_table')),
                    array('url_id'))
                ->joinLeft(
                    array('url_table' => $this->getTable('log/url_table')),
                    'url_info_table.url_id = url_table.url_id',
                    array())
                ->where('url_table.url_id IS NULL')
                ->limit(100);

            $urlIds = $readAdapter->fetchCol($select);

            if (!$urlIds) {
                break;
            }

            try
            {
                $deleted = $writeAdapter->delete(
                    $this->getTable('log/url_info_table'),
                    array('url_id IN (?)' => $urlIds)
                );
            } catch(Exception $e){
                echo $e->getMessage();
            }

            echo "\n\t - finished cleaning {$deleted} log url info.";

            $clean = $deleted && $i < 1000; // max of 1000 iterations per run
        }

        return $this;
    }

    /**
     * Retrieve Usage Help Message
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f tclean.php -- [options]
  clean             Clean Logs
  status            Display statistics per log tables
  help              This help

USAGE;
    }
}

$shell = new Mage_Shell_Tclean();
$shell->run();
