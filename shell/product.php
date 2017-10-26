<?php
/**
 *
 * Copyright (c) 2017.
 * product.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

// it will fix duplicate url_paths for products
require_once('../app/Mage.php');
Mage::app('admin');
require_once 'abstract.php';
class Mage_Shell_fixproduct extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        $_products = array();
        $i = 1;
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $results = $conn->fetchAll("
                SELECT v.entity_id, v.value, count(*) as count
                FROM
                `catalog_product_entity_varchar` as v,
                `catalog_product_entity_int` as i
                where
                v.attribute_id = 86
                and
                i.entity_id = v.entity_id
                /*and
                i.attribute_id=91
                and
                i.value > 1*/
                Group by value
                having count > 1
                ");
        foreach($results as $row) {
            $entities = $conn->fetchAll("SELECT `entity_id` FROM `catalog_product_entity_varchar` where `store_id` = 2 and ((`attribute_id` = 86 and value = '{$row[value]}') or (`attribute_id` = 87 and value = '{$row[value]}.html'))");
            foreach($entities as $entity_id) {
                $_products[$entity_id['entity_id']] = $entity_id['entity_id'];
            }
        }

        if(count($_products)){
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('url_path');
            $collection->addAttributeToSelect('url_key');
            $collection->addAttributeToFilter(array(
                array(
                    'attribute' => 'entity_id',
                    'in'        => $_products,
                    )
            ));
            $collection->addAttributeToFilter(array(
                array(
                    'attribute' => 'sku',
                    'in'        => array('SSGS12177NBN-FS'),
                    )
            ));

            foreach($collection as $_product){
                $storeIds = $_product->getStoreIds();
                //$storeIds = array_pop($storeIds);

                $urlKey = $_product->getUrlKey();
                $urlPath = $_product->getUrlPath();
                if(substr($urlPath, -5) == '.html'){
                    $urlPath = substr($urlPath, 0, -5);
                    $id = substr($urlPath, (strrpos($urlPath, '-', 0)+1));
                    if($id == $_product->getId()){
                        $urlPath = substr($urlPath, 0, (strrpos($urlPath, '-', 0)));
                        $urlPath = substr($urlPath, 0, (strrpos($urlPath, '-', 0)));
                    }
                }
                if(empty($urlKey)){
                    $urlKey = $urlPath;
                }
                $_product->setUrlKey($urlKey);
                foreach($storeIds as $storeId){
                    $urlPath = $urlPath . '-' . $storeId . '-' . $_product->getId() . '.html';
                    $_product->setUrlPath($urlPath);
                    $_product->save();
                    echo ($i++) . '> ' . $urlPath . " - SAVED!!! \n";
                }
            }
        }
    }
}

$shell = new Mage_Shell_fixproduct();
$shell->run();
echo 'done!!!';
echo "\n";