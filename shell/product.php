<?php
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
                and
                i.attribute_id=91
                and
                i.value > 1
                Group by value
                having count > 1
                ");
        foreach($results as $row) {
            //print_r($row);
            //echo "SELECT `entity_id` FROM `catalog_product_entity_varchar` where `store_id` = 2 and ((`attribute_id` = 86 and value = '{$row[value]}') or (`attribute_id` = 87 and value = '{$row[value]}.html'))\n";
            $entities = $conn->fetchAll("SELECT `entity_id` FROM `catalog_product_entity_varchar` where `store_id` = 2 and ((`attribute_id` = 86 and value = '{$row[value]}') or (`attribute_id` = 87 and value = '{$row[value]}.html'))");
            foreach($entities as $entity_id) {
                $_products[$entity_id['entity_id']] = $entity_id['entity_id'];
            }
        }

        if(count($_products)){
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('url_path');
            $collection->addAttributeToFilter(array(
                array(
                    'attribute' => 'entity_id',
                    'in'        => $_products,
                    )
            ));

            foreach($collection as $_product){
                $urlKey = $_product->getUrlKey();
                $urlPath = $_product->getUrlPath();
                if(substr($urlPath, -5) == '.html'){
                    $urlPath = substr($urlPath, 0, -5);
                    $id = substr($urlPath, (strrpos($urlPath, '-', 0)+1));
                    if($id == $_product->getId()){
                        $urlPath = substr($urlPath, 0, (strrpos($urlPath, '-', 0)));
                    }
                }
                if(empty($urlKey)){
                    $urlKey = $urlPath;
                }
                $urlPath = $urlPath . '-' . $_product->getId() . '.html';
                $_product->setUrlPath($urlPath);
                $_product->setUrlKey($urlKey);
                $_product->save();
                echo $urlPath . " - SAVED!!! \n";
            }
        }
    }
}

$shell = new Mage_Shell_fixproduct();
$shell->run();
echo 'done!!!';
echo "\n";