<?php
//die('The purpose of this file is achieved.');
require_once '../app/Mage.php';
Mage::app('admin');
require_once 'abstract.php';

class Mage_Shell_Update_Attributes extends Mage_Shell_Abstract
{
    private $_count = 0;
    public $_size = 100;
    public $_page = 1;
    private $_stores = array();

    public function run(){
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->addAttributeToSelect('rw_google_base_skip_submi');
        //$collection->addAttributeToFilter('status', array('eq' => '1'));
        //$collection->addAttributeToFilter('rw_google_base_skip_submi', array('neq' => 1)); // not working as this attribute not associated to all products.
        $collection->setPage($this->_page, $this->_size);
        $collection = $collection->load();
        $count = count($collection);

        echo 'Found total ' . $count . ' products in page '.$this->_page.' !!!' . "\n\r\n\r\n\r\n\r\n\r\n\r";

        //var_dump($collection->getSelectSql(true));

        $pid = array();
        foreach($collection as $product){
            echo (++$this->_count) . '> updating ... [' . $product->getSku() . '] ... ';

            $val = $product->getData('rw_google_base_skip_submi');

            if($val){
                echo "has val [{$val}] so skipped!!!\n\r\n\r";
            } else {
                $pid[$product->getSku()] = $product->getId();
                //$product->setData('rw_google_base_skip_submi', '1')->save();
                echo '... stored for bulk save!!!'."\n\r\n\r";
            }
        }
        if(count($pid)){
            echo 'saving SKUs ['.implode(',', array_keys($pid)).'] ...';
            Mage::getSingleton('catalog/product_action')->updateAttributes($pid, array('rw_google_base_skip_submi' => 1), 0);
            echo '... saved!!!'."\n\r\n\r";
        }

        return ($this->_page > $collection->getLastPageNumber() ? 0 : $count );
    }

    public function getStoreIds(){
        if(empty($this->_stores)){
            foreach(Mage::app()->getStores() as $store){
                $this->_stores[$store->getData('store_id')] = $store->getData('store_id');
            }
        }
        return implode(',', $this->_stores);
    }
}

/*let's set indexers to manual, so saving is faster*/
$processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_MANUAL));
$processes->walk('save');

$shell = new Mage_Shell_Update_Attributes();
$_count = 0;
while($count = $shell->run()){
    $shell->_page++;
    $_count += $count;
    $count = $shell->run();
}

echo "... {$_count} product's updated!\n\r";

/*Now set them back to auto save*/
$processes = Mage::getSingleton('index/indexer')->getProcessesCollection();
$processes->walk('setMode', array(Mage_Index_Model_Process::MODE_REAL_TIME));
$processes->walk('save');

echo 'Reindexing all...' . "\n\r";
$processes->walk('reindexAll');
echo 'Reindexing complete...' . "\n\r";

echo "........DONE.......\n\r";
