<?php
/**
 * Set layered nav to show in all categories
 *
 * @copyright      Copyright (c) 2012 dltr.org
 * @author         Damodar Bashyal | dltr.org/contact
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
require_once './abstract.php';

class Mage_Shell_showLayer extends Mage_Shell_Abstract
{
    public $_root = 3;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // this requires a bit of memory
        // so, lets set as 512M
        ini_set('memory_limit', '512M');
    }

    /**
     * Runner
     */
    public function run($id=3)
    {
        $cat = Mage::getModel('catalog/category')->load($id);
        $cat->setData('show_layered_nav', 1);
        $cat->save();
        echo "\n" . str_repeat('-', $cat->getLevel()) . $cat->getName();

        if($cat->hasChildren()){
            foreach($cat->getChildrenCategories() as $child){
                $this->run($child->getId());
            }
        }
    }
}

$shell = new Mage_Shell_showLayer();
$shell->run($shell->_root);