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
        $collection->addAttributeToFilter('status', array('eq' => '1'));
        //$collection->addAttributeToFilter('rw_google_base_skip_submi', array('neq' => 1)); // not working as this attribute not associated to all products.
        $collection->setPage($this->_page, $this->_size);
        $collection = $collection->load();
        $count = count($collection);

        echo 'Found total ' . $count . ' products in page '.$this->_page.' !!!' . "\n\r\n\r\n\r\n\r\n\r\n\r";

        //var_dump($collection->getSelectSql(true));

        foreach($collection as $product){
            echo (++$this->_count) . '> updating ... [' . $product->getSku() . '] ... ';

            $val = $product->getData('rw_google_base_skip_submi');

            echo "has val [{$val}] so ";

            if($val){
                echo 'skipped';
            } else {
                $product->setData('rw_google_base_skip_submi', '1')->save();
                echo 'saved';
            }

            echo "!!!\n\r\n\r";
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

$shell = new Mage_Shell_Update_Attributes();
$_count = 0;

while($count = $shell->run()){
    $shell->_page++;
    $_count += $count;
    $count = $shell->run();
}

echo "... {$_count} product's updated!\n\r";
echo "........DONE.......\n\r";
