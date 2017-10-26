<?php
/**
 *
 * Copyright (c) 2017.
 * showlayer.php
 * Code compiled by: @dbashyal 'damu' on 24/10/17 4:23 PM
 * Visit http://dltr.org for Magento Tips and Tricks
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