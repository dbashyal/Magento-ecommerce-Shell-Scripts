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
require_once '../../app/Mage.php';
Mage::app('admin');
require_once '../abstract.php';

$w = Mage::getSingleton('core/resource')->getConnection('core_write');

// let's find entity_type_id
echo "finding entity_type_id..";
$select = "SELECT entity_type_id  FROM `eav_entity_type` WHERE `entity_type_code` = 'catalog_product'";
$subscriber = $w->query($select)->fetchAll();
if (!isset($subscriber[0]['entity_type_id'])) {
    echo 'Could not find entity_type_id for `entity_type_code` = catalog_product';
    die();
}
$entity_type_id = $subscriber[0]['entity_type_id'];
echo ".. {$entity_type_id} found!\n\r";

// then find image(s)'s attribute_id
echo "finding attribute_id..";
$select = "SELECT attribute_id  FROM `eav_attribute` WHERE `frontend_input` = 'media_image' and attribute_code IN ('image','small_image','thumbnail','hover_image','media_gallery')";
$attribute_ids = $w->query($select)->fetchAll();

if (empty($attribute_ids)) {
    echo 'Could not find attribute_id for images';
    die();
}

$attribute_id = array();
foreach ($attribute_ids as $v) {
    $attribute_id[] = $v['attribute_id'];
}
$attribute_ids = implode(',', $attribute_id);
echo ".. {$attribute_ids} found!\n\r";

// find product with images not selected.
echo "finding product with images not selected..";
//$select = "SELECT entity_id,value FROM `catalog_product_entity_varchar` WHERE attribute_id in ({$attribute_ids}) and `entity_type_id`={$entity_type_id} AND (value='no_selection' OR value='' OR value = NULL) group by entity_id";
//$select = "SELECT entity_id,value FROM `catalog_product_entity_varchar` WHERE attribute_id in ({$attribute_ids}) and `entity_type_id`={$entity_type_id} group by entity_id";
$select = "SELECT `v`.`entity_id`, `v`.`value`, `g`.`value` AS `gvalue` FROM `catalog_product_entity_varchar` v
LEFT JOIN `catalog_product_entity_media_gallery` g 
ON `g`.`entity_id`=`v`.`entity_id`
WHERE `v`.`attribute_id` IN ({$attribute_ids}) AND v.`entity_type_id`={$entity_type_id} AND ( g.`value` IS NULL OR v.`value` = 'no_selection' OR v.`value` = '' OR v.`value` IS NULL)
GROUP BY v.`entity_id`
";
$broken = $w->query($select)->fetchAll();
/*
entity_id   value                                           gvalue
5847        /lowes/24754/image/default_Lowes2808MHR.jpg     NULL
*/

$count = 0;
echo ".. found issues with " . count($broken) . " product(s)!\n\r";

class Mage_Shell_repair_images extends Mage_Shell_Abstract
{
    private $_count = 0;
    /**
     * @var $_product Mage_Catalog_Model_Product
     */
    private $_product;
    private $_visibility = array(
        'thumbnail',
        'small_image',
        'image'
    );
    private $_image_path = null;

    private function _get_product_image_path()
    {
        if (empty($this->_image_path)) {
            $this->_image_path = Mage::getBaseDir('media') . '/catalog/product/';
            $this->_image_path = realpath($this->_image_path) . DIRECTORY_SEPARATOR;
        }
        return $this->_image_path;
    }

    public function run($broken = array())
    {
        foreach ($broken as $row) {
            $images = array();
            $id = $row["entity_id"];
            echo 'Id: ' . $id . "\n\r";
            $this->_product = Mage::getModel("catalog/product")->load($id);
            // skip products that are not enabled
            /*if ($this->_product->getStatus() != 1) {
                continue;
            }*/
            echo 'Loaded: ' . $this->_product->getId() . "\n\r";
            $mediaGallery = $this->_product->getMediaGallery();

            // skip products that have no images uploaded or value not set
            if (!count($mediaGallery['images']) && empty($row['value'])) {
                echo 'No Image for: ' . $this->_product->getSku() . ':' . $this->_product->getId() . "\n\r";
                continue;
            }

            $images[$id]['image'] = $row['value'];
            $images[$id]['small_image'] = $row['value'];
            $images[$id]['thumbnail'] = $row['value'];
            $images[$id]['hover_image'] = $row['value'];

            if (count($mediaGallery["images"]) && !empty($mediaGallery["images"][0]["file"])) {
                // $path = $mediaGallery["images"][0]["file"];
                $images[$id]['image'] = $this->getImage('image', $mediaGallery);
                $images[$id]['small_image'] = $this->getImage('small_image', $mediaGallery);
                $images[$id]['thumbnail'] = $this->getImage('thumbnail', $mediaGallery);
                $images[$id]['hover_image'] = $this->getImage('hover_image', $mediaGallery);
            } else if (!count($mediaGallery["images"]) && !empty($row['value'])) {
                try {
                    $image = $this->_get_product_image_path() . trim($row['value'], '/\\');
                    if (is_file($image)) {
                        $this->_product->addImageToMediaGallery($image, $this->_visibility, false, false);
                    } else {
                        echo 'Error: Image not exists! - ' . $image . "\n\r";
                        echo 'For: ' . $this->_product->getSku() . ':' . $this->_product->getId() . "\n\r";
                    }
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . "\n\r";
                    echo 'For: ' . $this->_product->getSku() . ':' . $this->_product->getId() . "\n\r";
                    continue;
                }
            }

            if (!empty($images[$id]['image']) && $images[$id]['image'] != 'no_selection' && $images[$id]['image'] != 'NULL') {
                //foreach($this->_product->getStoreIds() as $storeId) {
                try {
                    $this->_product
                        //->setStoreId(0) //$storeId
                        ->setImage($images[$id]['image'])
                        ->setSmallImage($images[$id]['small_image'])
                        ->setThumbnail($images[$id]['thumbnail'])
                        ->setHoverImage($images[$id]['hover_image'])
                        ->save();
                    $this->_count++;
                    echo 'updated: SKU: ' . $this->_product->getSku() . ':' . $this->_product->getId() . "\n\r";
                } catch (Exception $e) {
                    echo 'Error: ' . $e->getMessage() . "\n\r";
                    echo 'For: ' . $this->_product->getSku() . ':' . $this->_product->getId() . "\n\r";
                    continue;
                }
                //}
            }
        }

        return $this->_count;
    }

    private function getImage($attribute = 'image', $default = array())
    {
        $image = $this->_product->getData('image');
        if (empty($image) || $image == 'no_selection' || $image == 'NULL') {
            $image = $default['images'][0]['file'];
        }
        return $image;
    }
}

$shell = new Mage_Shell_repair_images();
$count = $shell->run($broken);

echo "... {$count} product's images fixed!\n\r";
echo ".... images for " . (count($broken) - $count) . " product(s) not found!\n\r";
echo "........DONE.......\n\r";