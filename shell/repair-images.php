<?php
/**
 *
 * Copyright (c) 2017.
 * repair-images.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
 *
 */

//die('The purpose of this file is achieved.');
require_once '../app/Mage.php';
Mage::app('admin');
require_once 'abstract.php';

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
$select         = "SELECT attribute_id  FROM `eav_attribute` WHERE `frontend_input` = 'media_image' and attribute_code IN ('image','small_image','thumbnail','hover_image')";
$attribute_ids  = $w->query($select)->fetchAll();

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
//$select = "SELECT entity_id,value FROM `catalog_product_entity_varchar` WHERE attribute_id in ({$attribute_ids}) and `entity_type_id`={$entity_type_id} AND (value='no_selection' OR value='' OR value = NULL) group by entity_id";
$select = "SELECT entity_id,value FROM `catalog_product_entity_varchar` WHERE attribute_id in ({$attribute_ids}) and `entity_type_id`={$entity_type_id} group by entity_id";
$broken = $w->query($select)->fetchAll();
$images = array();
$count  = 0;
echo ".. found issues with ".count($broken)." product(s)!\n\r";

class Mage_Shell_repair_images extends Mage_Shell_Abstract
{
    private $_count = 0;
    private $_product;

    public function run($broken = array()){
        foreach($broken as $row){
            $id             = $row["entity_id"];
            $this->_product = Mage::getModel("catalog/product")->load($id);
            // skip products that are not enabled
            if($this->_product->getStatus() != 1){
                continue;
            }
            $mediaGallery = $this->_product->getMediaGallery();

            // skip products that have no images uploaded
            if(!count($mediaGallery['images'])){
                continue;
            }

            if ($mediaGallery["images"][0]["file"] != '' || $mediaGallery["images"][0]["file"] != NULL) {
                $path                       = $mediaGallery["images"][0]["file"];
                $images[$id]['image']       = $this->getImage('image', $mediaGallery);
                $images[$id]['small_image'] = $this->getImage('small_image', $mediaGallery);
                $images[$id]['thumbnail']   = $this->getImage('thumbnail', $mediaGallery);
                $images[$id]['hover_image'] = $this->getImage('hover_image', $mediaGallery);

            } else {
                $images[$id]['image']       = $row['value'];
                $images[$id]['small_image'] = $row['value'];
                $images[$id]['thumbnail']   = $row['value'];
                $images[$id]['hover_image'] = $row['value'];
            }

            /*echo print_r($images, true);
            echo print_r($mediaGallery, true);
            break;die;*/

            if(!empty($images[$id]['image']) && $images[$id]['image'] != 'no_selection' && $images[$id]['image'] != 'NULL')
            {
                //foreach($this->_product->getStoreIds() as $storeId) {
                    try{
                        $this->_product
                            //->setStoreId(0) //$storeId
                            ->setImage($images[$id]['image'])
                            ->setSmallImage($images[$id]['small_image'])
                            ->setThumbnail($images[$id]['thumbnail'])
                            ->setHoverImage($images[$id]['hover_image'])
                            ->save();
                        $this->_count++;
                        echo 'updated: SKU: ' . $this->_product->getSku() . "\n\r";
                    } catch(Exception $e) {
                        echo $e->getMessage();
                    }
                //}
            }
        }

        return $this->_count;
    }

    private function getImage($attribute = 'image', $default=array()){
        $image = $this->_product->getData('image');
        if(empty($image) || $image == 'no_selection' || $image == 'NULL'){
            $image = $default['images'][0]['file'];
        }
        return $image;
    }
}

$shell = new Mage_Shell_repair_images();
$count = $shell->run($broken);

echo "... {$count} product's images fixed!\n\r";
echo ".... images for ".(count($broken) - $count)." product(s) not found!\n\r";
echo "........DONE.......\n\r";