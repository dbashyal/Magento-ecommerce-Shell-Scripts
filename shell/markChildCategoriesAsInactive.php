<?php
/**
 *
 * Copyright (c) 2017.
 * markChildCategoriesAsInactive.php
 * Code compiled by: @dbashyal 'damu' on 4/10/17 10:41 AM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

PHP_SAPI == 'cli' or die('<h1>:P</h1>');
ini_set('memory_limit','2048M');
set_time_limit(0);
error_reporting(E_ALL | E_STRICT);

require_once 'abstract.php';
class Mage_Shell_markChildCategoriesAsInactive extends Mage_Shell_Abstract
{
    public function run()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */
        $collection = Mage::getModel('catalog/category')->getCollection();
        $collection->setStore(5);
        $collection->addAttributeToFilter('is_active', 1);
        $collection->addAttributeToFilter('path', array('like' => "1/2/5/%"));

        $collection->setDataToAll('is_active', 0);
        $collection->save();
    }
}


$app = new Mage_Shell_markChildCategoriesAsInactive();
$app->run();
