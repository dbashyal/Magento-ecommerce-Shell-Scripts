<?php
//die('The purpose of this file is achieved.');
require_once '../app/Mage.php';
Mage::app();

$w = Mage::getSingleton('core/resource')->getConnection('core_write');

// let's find entity_type_id
echo "finding entity_type_id..";
$select = "SELECT entity_type_id  FROM `eav_entity_type` WHERE `entity_type_code` = 'catalog_product'";
$subscriber = $w->query($select)->fetchAll();
if(!isset($subscriber[0]['entity_type_id'])){
    echo 'Could not find entity_type_id for `entity_type_code` = catalog_product';
    die();
}
$entity_type_id = $subscriber[0]['entity_type_id'];
echo ".. {$entity_type_id} found!\n\r";

// then find image(s)'s attribute_id
echo "finding attribute_id..";
$select = "SELECT attribute_id  FROM `eav_attribute` WHERE `frontend_input` = 'media_image' and attribute_code IN ('image','small_image','thumbnail')";
$attribute_ids = $w->query($select)->fetchAll();

if(empty($attribute_ids)){
    echo 'Could not find attribute_id for images';
    die();
}

$attribute_id = array();
foreach($attribute_ids as $v){
    $attribute_id[] = $v['attribute_id'];
}
$attribute_ids = implode(',',$attribute_id);
echo ".. {$attribute_ids} found!\n\r";

// find product with images not selected.
echo "finding product with images not selected..";
$select = "SELECT entity_id,value FROM `catalog_product_entity_varchar` WHERE attribute_id in ({$attribute_ids}) and `entity_type_id`={$entity_type_id} AND (value='no_selection' OR value='' OR value = NULL) group by entity_id";
$broken = $w->query($select)->fetchAll();
$images = array();
$count = 0;
echo ".. found issues with ".count($broken)." product(s)!\n\r";
foreach($broken as $row){
    $id = $row["entity_id"];
    $product = Mage::getModel("catalog/product")->load($id);
    $mediaGallery = $product->getMediaGallery();

    if ($mediaGallery["images"][0]["file"] != '' || $mediaGallery["images"][0]["file"] != NULL) {
        $path = $mediaGallery["images"][0]["file"];
        $images[$id]['images'] = $mediaGallery["images"][0]["file"];
        $images[$id]['small_image'] = $mediaGallery["images"][0]["file"];
        $images[$id]['thumbnail'] = $mediaGallery["images"][0]["file"];

    } else {
        $images[$id]['images'] = $row['value'];
        $images[$id]['small_image'] = $row['value'];
        $images[$id]['thumbnail'] = $row['value'];
    }

    if(!empty($images[$id]['images']) && $images[$id]['images'] != 'no_selection' && $images[$id]['images'] != 'NULL')
    {
        //foreach($product->getStoreIds() as $storeId) {
            try{
                $product
                    ->setStoreId(0) //$storeId
                    ->setImage($images[$id]['images'])
                    ->setSmallImage($images[$id]['small_image'])
                    ->setThumbnail($images[$id]['thumbnail'])
                    ->save()
                ;
                $count++;
                echo 'updated: SKU: ' . $product->getSku() . "\n\r";
            } catch(Exception $e) {
                echo $e->getMessage();
            }
        //}
    }
}
echo "... {$count} product's images fixed!\n\r";
echo ".... images for ".(count($broken) - $count)." product(s) not found!\n\r";
echo "........DONE.......\n\r";