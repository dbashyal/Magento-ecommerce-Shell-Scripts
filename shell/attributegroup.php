<?php
/*
 * file: /shell/attributegroup.php
 * code compiled by: @dbashyal 'damu'
 * href: http://dltr.org/ 'magento tips and tricks'
 * ref: Mage_Adminhtml_Catalog_Product_AttributeController::saveAction
 */
//enable errors to see if something is wrong
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

define('MAGENTO_ROOT', dirname(getcwd()));

//include Mage.php
require_once MAGENTO_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';

//tell magento that you are running on developer mode, for additional error messages (if any)
Mage::setIsDeveloperMode(true);

//instantiate the application
Mage::app();

$newAttributes      = array();
$newAttributes[]    = array(
                          'attribute_code'  => 'product_right_block_title',
                          'frontend_label'  => 'Product Right Block Title',
                          'backend_type'    => 'int', //datetime,decimal,int,text,varchar,static
                          'frontend_input'  => 'select', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
                        );
$newAttributes[]    = array(
                          'attribute_code'  => 'product_right_block_content',
                          'frontend_label'  => 'Product Right Block Content',
                          'backend_type'    => 'text', //datetime,decimal,int,text,varchar,static
                          'frontend_input'  => 'textarea', //text,textarea,date,boolean,multiselect,select,price,media_image,weee
                        );

$groupName = 'Product Right Block';

$attr = new Mage_Custom_Attribute();
$attr->setGroupName($groupName);

foreach($newAttributes as $a){
    $attr->reset()->setData($a)->run();
}

class Mage_Custom_Attribute {
    protected $_data = array();
    protected $_entityTypeId;
    protected $_group_name      = 'Downloads';
    protected $_frontend_label  = 'Drivers';
    protected $_attribute_code  = 'drivers_downloads';
    protected $_attributeCodes  = array();
    protected $_attributeSets   = array();
    protected $_attributeGroups = array();

    /**
     * @return Mage_Custom_Attribute
     */
    public function reset(){
        $this->_data = array();
        return $this;
    }

    /**
     * @param string|array $key
     * @param string $val
     * @return Mage_Custom_Attribute
     */
    public function setData($key='key', $val='')
    {
        if(is_array($key)){
            foreach($key as $k => $v){
                $this->setData($k, $v);
            }
        } else {
            $this->_data[$key] = $val;
        }
        return $this;
    }

    /**
     * @param string|bool $key
     * @return array|string
     */
    public function getData($key = false){
        if($key){
            if(isset($this->_data[$key])){
                return $this->_data[$key];
            }
            return '';
        }
        return $this->_data;
    }

    /**
     * @param string $GroupName
     */
    public function setGroupName($GroupName)
    {
        $this->_group_name = $GroupName;
    }

    /**
     * @return string
     */
    public function getGroupName()
    {
        return $this->_group_name;
    }

    /**
     * @param string $frontend_label
     */
    public function setFrontendLabel($frontend_label)
    {
        $this->_frontend_label = $frontend_label;
    }

    /**
     * @return string
     */
    public function getFrontendLabel()
    {
        return $this->_frontend_label;
    }

    /**
     * @param string $attribute_code
     */
    public function setAttributeCode($attribute_code)
    {
        $this->_attribute_code = $attribute_code;
    }

    /**
     * @return string
     */
    public function getAttributeCode()
    {
        return $this->_attribute_code;
    }

    public function getEntityTypeId(){
        if(!$this->_entityTypeId){
            $this->_entityTypeId = Mage::getModel('eav/entity')->setType(Mage_Catalog_Model_Product::ENTITY)->getTypeId();
        }
        return $this->_entityTypeId;
    }

    public function run(){
        echo "\nrun:start****\n\n";

        echo "\nstart create group\n";
        $this->createGroup();
        echo "\n\nend create group\n";

        echo "\nstart create attribute\n";
        $this->createAttribute();
        echo "\n\nend create attribute\n";

        echo "\n start assigning attributes to sets\n";
        $this->assignAttributeToSets();
        echo "\n\n end assigning attributes to sets\n";

        /**
         * Clear translation cache because attribute labels are stored in translation
         */
        echo "\n start clearing translation cache\n";
        Mage::app()->cleanCache(array(Mage_Core_Model_Translate::CACHE_TAG));
        echo "\n\n end clearing translation cache";

        echo "\n*\n**\n***\nrun:end";
    }

    public function getAttributeSets(){
        //get all attribute sets
        if(empty($this->_attributeSets)){
            $this->_attributeSets = Mage::getModel('eav/entity_attribute_set')
                ->getResourceCollection()
                //filter only sets for products - that's why you needed the product type ID
                ->addFilter('entity_type_id', $this->getEntityTypeId());
        }
        return $this->_attributeSets;
    }

    public function createGroup($attributeGroupName=''){
        if(empty($attributeGroupName)){
            $attributeGroupName = $this->getGroupName();
        }

        //loop through all the sets
        /*
         * Array('attribute_set_id' => 4, 'entity_type_id' => 4, 'attribute_set_name' => 'Default', 'sort_order' => 1)
         */
        foreach ($this->getAttributeSets() as $set){
            $group = Mage::getModel('eav/entity_attribute_group')
                        ->getCollection()
                        ->addFieldToFilter('attribute_set_id',$set->getID())
                        ->addFieldToFilter('attribute_group_name',$attributeGroupName)
                        ->getFirstItem();
            if($group->getId()){
                $this->_attributeGroups[$set->getId()] = $group->getData();
                $this->_attributeGroups[$set->getId()]['id'] = $group->getId();

                // no need to reinsert
                continue;
            }

            //create an attribute group instance
            $model = Mage::getModel('eav/entity_attribute_group');

            //set the group name
            $model->setAttributeGroupName($attributeGroupName)

                //link to the current set
                ->setAttributeSetId($set->getId())

                //set the order in the set
                //->setSortOrder(100)
            ;

            //save the new group
            $model->save();

            $this->_attributeGroups[$set->getId()] = $model->getData();
            $this->_attributeGroups[$set->getId()]['id'] = $model->getId();
        }
    }

    function createAttribute($data = array()){
        $frontend_label = $this->getFrontendLabel();
        $attribute_code = $this->getData('attribute_code');
        if(isset($this->_attributeCodes[$attribute_code])){
            return;
        }

        $data_default = array(
            'is_global'                     => '2', /*0=storeview,1=global,2=website*/
            'attribute_code'                => $attribute_code,
            'frontend_input'                => $this->getData('frontend_input'),
            'default_value'                 => '',
            'default_value_text'            => '',
            'default_value_yesno'           => '0',
            'default_value_date'            => '',
            'default_value_textarea'        => '',
            'is_unique'                     => '0',
            'is_required'                   => '0',
            'frontend_class'                => '',
            'is_searchable'                 => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable'                 => '0',
            'is_used_for_promo_rules'       => '0',
            'is_html_allowed_on_front'      => '1',
            'is_visible_on_front'           => '1',
            'used_in_product_listing'       => '0',
            'used_for_sort_by'              => '0',
            'is_configurable'               => '0',
            'is_filterable'                 => '0',
            'is_filterable_in_search'       => '0',
            'backend_type'                  => $this->getData('backend_type'),
        );

        $data = array_merge($data_default, $data);

        $data['frontend_label'] = array('0' => $this->getData('frontend_label'));

        /* @var $model Mage_Catalog_Model_Entity_Attribute */
        $model = Mage::getModel('catalog/resource_eav_attribute');

        /* @var $helper Mage_Catalog_Helper_Product */
        $helper = Mage::helper('catalog/product');

        //validate attribute_code
        if (isset($data['attribute_code'])) {
            $validatorAttrCode = new Zend_Validate_Regex(array('pattern' => '/^[a-z][a-z_0-9]{1,254}$/'));
            if (!$validatorAttrCode->isValid($data['attribute_code'])) {
                Mage::log(Mage::helper('catalog')->__('Attribute code is invalid. Please use only letters (a-z), numbers (0-9) or underscore(_) in this field, first character should be a letter.'));
                return;
            }
        }

        //validate frontend_label
        if (!isset($data['frontend_label'])) {
           Mage::log(Mage::helper('catalog')->__('Attribute Text is required.'));
        }

        //validate frontend_input
        if (isset($data['frontend_input'])) {
            /** @var $validatorInputType Mage_Eav_Model_Adminhtml_System_Config_Source_Inputtype_Validator */
            $validatorInputType = Mage::getModel('eav/adminhtml_system_config_source_inputtype_validator');
            if (!$validatorInputType->isValid($data['frontend_input'])) {
                foreach ($validatorInputType->getMessages() as $message) {
                    Mage::log($message);
                }
                return;
            }
        }

        $data['source_model'] = $helper->getAttributeSourceModelByInputType($data['frontend_input']);
        $data['backend_model'] = $helper->getAttributeBackendModelByInputType($data['frontend_input']);

        if (!isset($data['is_configurable'])) {
            $data['is_configurable'] = 0;
        }
        if (!isset($data['is_filterable'])) {
            $data['is_filterable'] = 0;
        }
        if (!isset($data['is_filterable_in_search'])) {
            $data['is_filterable_in_search'] = 0;
        }

        if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
        }

        if(!isset($data['apply_to'])) {
            $data['apply_to'] = array();
        }

        //filter
        $data = Mage::helper('tgeneral/catalog')->filterPostData($data);
        $model->addData($data);

        $entityTypeId = $this->getEntityTypeId();
        $model->setEntityTypeId($entityTypeId);
        $model->setIsUserDefined(1);

        try {
            $model->save();

            $this->_attributeCodes[$attribute_code] = $model->getData();
            $this->_attributeCodes[$attribute_code]['id'] = $model->getId();
        } catch (Exception $e) {
            Mage::log($e->getMessage());
        }
        return;
    }

    public function assignAttributeToSets(){
        $attr = $this->_attributeCodes[$this->getData('attribute_code')];
        foreach ($this->getAttributeSets() as $set){
            $setId = $set->getId();
            //$set = Mage::getModel('eav/entity_attribute_set')->load($set->getId());
            $groupId = $this->_attributeGroups[$setId]['id'];
            $newItem = Mage::getModel('eav/entity_attribute');
            $newItem->setEntityTypeId($this->getEntityTypeId()) // catalog_product eav_entity_type id ( usually 10 )
                      ->setAttributeSetId($setId) // Attribute Set ID
                      ->setAttributeGroupId($groupId) // Attribute Group ID ( usually general or whatever based on the query i automate to get the first attribute group in each attribute set )
                      ->setAttributeId($attr['id']) // Attribute ID
                      //->setSortOrder(10) // Sort Order for the attribute in the tab form edit
                      ->save()
            ;
            echo "Attribute ".$this->getData('attribute_code')." Added to Attribute Set ".$set->getAttributeSetName()." in Attribute Group ".$this->_attributeGroups[$setId]['attribute_group_name']."<br>\n";
        }
    }
}
