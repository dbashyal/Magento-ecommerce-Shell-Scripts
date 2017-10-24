<?php

require_once "../app/Mage.php";
require_once 'abstract.php';
Mage::app('admin')->setUseSessionInUrl(false);
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));

class Mage_Shell_SortCategoriesByName extends Mage_Shell_Abstract
{
    private function sortCategories($level=2, $path='1/2')
    {
        if(!$level || !$path) return false;

        $collection = Mage::getModel('catalog/category')->getCollection()->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection->clear();
        $collection->addAttributeToFilter('path', array('like' => "{$path}/%"));
        $collection->addAttributeToFilter('level', array('eq' => $level));
        $collection->addAttributeToSort('name', 'asc');

        $position = 1;

        foreach ($collection as $category){
            $this->saveCateory($category, $position++);

            $level = $category->getLevel() + 1;
            $path = $category->getPath();
            $this->sortCategories($level, $path);
        }
        return true;
    }

    private function saveCateory($category, $position=0)
    {
        echo "\n |" . str_repeat("-", $category->getLevel()) . $category->getName();
        $category->setPosition($position);
        if($category->getParentId() === 5){
            $category->setIncludeInMenu(0);
        }
        $category->save();
    }

    public function run()
    {
        $this->sortCategories();

        echo "\nSorted\n";
    }
}

// php -f deleteSchoolSubcategory.php -- --size 1000 --page 1

$shell = new Mage_Shell_SortCategoriesByName();
$shell->run();

echo "Done!!!\n";