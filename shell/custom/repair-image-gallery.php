<?php
/**
 * @author      MagePsycho <info@magepsycho.com>
 * @website     http://www.magepsycho.com
 * @category    Export / Import
 */
require_once '../../app/Mage.php';
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

set_time_limit(0);
ini_set('memory_limit', '1024M');

/***************** UTILITY FUNCTIONS ********************/
function _log($message, $file = 'update_missing_images.log')
{
    Mage::log($message, null, $file);
}

function _getIndex($field)
{
    global $fields;
    $result = array_search($field, $fields);
    if ($result === false) {
        $result = -1;
    }
    return $result;
}

function _getConnection($type = 'core_read')
{
    return Mage::getSingleton('core/resource')->getConnection($type);
}

function _getTableName($tableName)
{
    return Mage::getSingleton('core/resource')->getTableName($tableName);
}

function _getAttributeId($attribute_code = 'price')
{
    $connection = _getConnection('core_read');
    $sql = "SELECT attribute_id
                FROM " . _getTableName('eav_attribute') . "
            WHERE
                entity_type_id = ?
                AND attribute_code = ?";
    $entity_type_id = _getEntityTypeId();
    return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
}

function _getEntityTypeId($entity_type_code = 'catalog_product')
{
    $connection = _getConnection('core_read');
    $sql = "SELECT entity_type_id FROM " . _getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
    return $connection->fetchOne($sql, array($entity_type_code));
}

function _getIdFromSku($sku)
{
    $connection = _getConnection('core_read');
    $sql = "SELECT entity_id FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne($sql, array($sku));
}

function _checkIfSkuExists($sku)
{
    $connection = _getConnection('core_read');
    $sql = "SELECT COUNT(*) AS count_no FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    $count = $connection->fetchOne($sql, array($sku));
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function _checkIfRowExists($productId, $attributeId, $value)
{
    $tableName = _getTableName('catalog_product_entity_media_gallery');
    $connection = _getConnection('core_read');
    $sql = "SELECT COUNT(*) AS count_no FROM " . _getTableName($tableName) . " WHERE entity_id = ? AND attribute_id = ?  AND value = ?";
    $count = $connection->fetchOne($sql, array($productId, $attributeId, $value));
    if ($count > 0) {
        return true;
    } else {
        return false;
    }
}

function _insertRow($productId, $attributeId, $value)
{
    $connection = _getConnection('core_write');
    $tableName = _getTableName('catalog_product_entity_media_gallery');

    $sql = "INSERT INTO " . $tableName . " (attribute_id, entity_id, value) VALUES (?, ?, ?)";
    $connection->query($sql, array($attributeId, $productId, $value));
}

function _updateMissingImages($count, $productId)
{
    $connection = _getConnection('core_read');
    $smallImageId = _getAttributeId('small_image');
    $imageId = _getAttributeId('image');
    $thumbnailId = _getAttributeId('thumbnail');
    $mediaGalleryId = _getAttributeId('media_gallery');

    //getting small, base, thumbnail images from catalog_product_entity_varchar for a product
    $sql = "SELECT * FROM " . _getTableName('catalog_product_entity_varchar') . " WHERE attribute_id IN (?, ?, ?) AND entity_id = ? AND `value` != 'no_selection'";
    $rows = $connection->fetchAll($sql, array($imageId, $smallImageId, $thumbnailId, $productId));
    if (!empty($rows)) {
        foreach ($rows as $_image) {
            //check if that images exist in catalog_product_entity_media_gallery table or not
            if (!_checkIfRowExists($productId, $mediaGalleryId, $_image['value'])) {
                //insert that image in catalog_product_entity_media_gallery if it doesn't exist
                _insertRow($productId, $mediaGalleryId, $_image['value']);
                /* Output / Logs */
                $missingImageUpdates = $count . '> Updated:: $productId=' . $productId . ', $image=' . $_image['value'];
                echo $missingImageUpdates . "<br />\n";
                _log($missingImageUpdates);
            }
        }
        $separator = str_repeat('=', 100);
        _log($separator);
        echo $separator . "<br />\n";
    }
}

/***************** UTILITY FUNCTIONS ********************/
// find all products with missing gallery images
$message = "<hr />\n";
$imageId = _getAttributeId('image');
$connection = _getConnection('core_read');
$sql = "SELECT 
g.*, e.sku, e.entity_id AS id, p.value AS image, p.attribute_id 
FROM 
`catalog_product_entity_media_gallery` g 
RIGHT JOIN 
catalog_product_entity e 
ON 
g.entity_id = e.entity_id
LEFT JOIN 
catalog_product_entity_varchar p 
ON 
e.entity_id = p.entity_id
WHERE 
p.attribute_id=? 
AND g.value IS NULL
AND p.value IS NOT NULL
AND p.value != ''";
$rows = $connection->fetchAll($sql, array($imageId));

if (!empty($rows)) {
    foreach ($rows as $k => $row) {
        try {
            _updateMissingImages($k, $row['id']);
            $message .= $k . '> Success:: Updating Images of Sku (' . $row['sku'] . ')' . "<br />\n";
        } catch (Exception $e) {
            $message .= $k . '> Error:: While Upating Images of Sku (' . $row['sku'] . ') => ' . $e->getMessage() . "<br />\n";
        }
    }
}

echo $message;
