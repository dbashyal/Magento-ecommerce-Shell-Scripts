<?php
/**
 *
 * Copyright (c) 2017.
 * deleteSchoolSubcategory.php
 * Code compiled by: @dbashyal 'damu' on 23/10/17 10:07 AM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

require_once "../app/Mage.php";
require_once 'abstract.php';
Mage::app('admin')->setUseSessionInUrl(false);


class Mage_Shell_DeleteSchoolSubcategory extends Mage_Shell_Abstract
{
    private $_cur_page = 1;
    private $_page_size = 1;

    private function getPageSize()
    {
        $size = (int)$this->getArg('size');
        if($size){
            $this->_page_size = $size;
        }
        return $this->_page_size;
    }

    private function getCurrentPage()
    {
        $page = (int)$this->getArg('page');
        if($page){
            $this->_cur_page = $page;
        }
        return $this->_cur_page;
    }

    public function run()
    {
        // SELECT * FROM `catalog_category_flat_store_1` WHERE `path` LIKE '1/2/5/%' and level >= 4 group by name
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->clear();
        $collection->addAttributeToFilter('path', array('like' => '1/2/5/%'))
            ->addAttributeToFilter('level', array('gteq' => '4'))
            ->addAttributeToSelect('name')
            ->addAttributeToSort('entity_id', 'desc')
            ->setPage($this->getCurrentPage(), $this->getPageSize())
            ->load()
        ;

        //echo $collection->getSize();

        echo "\n" . $collection->getSelect()->__toString() . "\n";

        //return;

        foreach($collection as $k => $category){
            echo "\n{$k}:";
            echo $category->getName();
            $category->delete();
        }
    }
}

// php -f deleteSchoolSubcategory.php -- --size 1000 --page 1

$shell = new Mage_Shell_DeleteSchoolSubcategory();
$shell->run();

echo "Done!!!\n";