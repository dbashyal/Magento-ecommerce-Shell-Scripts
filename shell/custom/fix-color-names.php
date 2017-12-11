<?php

/**
 *
 * Copyright (c) 2017.
 * fix-color-names.php
 * Code compiled by: @dbashyal 'damu' on 26/10/17 12:31 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

if(!defined('DS')){
    // include, only if this file is run standalone.
    require_once dirname(dirname(dirname(__FILE__))) . '/app/Mage.php';
    require_once dirname(dirname(__FILE__)) . '/abstract.php';
}

Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin')->setUseSessionInUrl(false);
Mage::unregister('isSecureArea');
Mage::register('isSecureArea', 1);

class Mage_Shell_Lowes_FixColorNames extends Mage_Shell_Abstract
{
    public function run()
    {
        $stores = Mage::app()->getStores();
        $storeIds = array_keys($stores);
        $i = 0;

        //print_r($storeIds);


        // Mage::log(array_keys($stores));

        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode(Mage_Catalog_Model_Product::ENTITY, 'color');

        /** @var $optionsCollection Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection */
        $optionsCollection = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setAttributeFilter($attribute->getId())
            ->setOrder('value', 'asc')
            ->setStoreFilter(0, false);

        $optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav_attribute_option_value');
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        /** @var $option Mage_Eav_Model_Entity_Attribute_Option */
        foreach ($optionsCollection as $option) {
            $value = $option->getValue();
            $optionId = $option->getId();
            $cleanValue = Mage::helper('lowes_configurableswatches')->getCleanOptionLabel($value);
            $hasValidColorCode = Mage::helper('lowes_configurableswatches')->hasValidColorCode($value, $optionId);
            $option->setSortOrder(0);
            $option->save();

            //$qry = $connection->select()->from($optionValueTable)->where('option_id=? and store_id=0', $optionId);
            $qry = $connection->select()->from($optionValueTable)->where('option_id=? and store_id > 0', $optionId)->order('store_id ASC');
            //echo $qry->__toString();
            $rows = $connection->fetchAll($qry);

            $values = array();
            foreach ($storeIds as $storeId) {
                $values[$storeId] = array(
                    'value_id' => 0,
                    'option_id' => $optionId,
                    'store_id' => $storeId,
                    'value' => $cleanValue
                );
            }

            foreach ($rows as $row) {
                if (isset($row['store_id']) && isset($values[$row['store_id']])) {
                    $values[$row['store_id']]['value_id'] = $row['value_id'];
                }
            }

            // insert or update
            foreach ($values as $value){
                if($value['value_id']){
                    // update
                    $qry = "UPDATE `{$optionValueTable}` SET `value` = '{$value['value']}' WHERE `value_id` = {$value['value_id']}";
                } else {
                    // insert
                    $qry = "INSERT INTO `{$optionValueTable}` (`value_id`, `option_id`, `store_id`, `value`) VALUES ('', {$value['option_id']}, {$value['store_id']}, '{$value['value']}')";
                }

                try{
                    $i++;
                    echo "\n{$i}: {$qry}\n";
                    $connection->query($qry);
                } catch (Exception $e){
                    echo "\n" . $e->getMessage() . "\n";
                }
            }
        }
    }
}

$shell = new Mage_Shell_Lowes_FixColorNames();

try {
    // fetch new data
    $shell->run();
} catch (Exception $e) {
    echo $e->__toString();
}

echo "done!\n";
