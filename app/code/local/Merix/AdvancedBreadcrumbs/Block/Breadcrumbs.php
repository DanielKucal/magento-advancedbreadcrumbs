<?php

class Merix_AdvancedBreadcrumbs_Block_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{

    protected $_allCategories = array();

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('merix/advancedbreadcrumbs.phtml');
        $this->addData(array(
            'cache_lifetime' => 86400,
        ));
    }

    protected function _beforeToHtml()
    {
        if (!$this->_crumbs || !(Mage::registry('current_category') || Mage::registry('current_product'))) {
            return parent::_beforeToHtml();
        }

        $crumbs = array_values($this->_crumbs);
        $keys = array_keys($this->_crumbs);

        $parentIds = array();
        foreach ($keys as $i => $key) {
            if (strpos($key, 'category') !== false) {
                $catId = str_replace('category', '', $key);
                $crumbs[$i]['category_id'] = $catId;
                $parentIds[] = $catId;
            }
        }

        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('id')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('url')
            ->addAttributeToSelect('is_active')
            ->addAttributeToFilter('parent_id', array('in' => $parentIds))
            ->addAttributeToSort('position');
        $categoryToParent = array();
        foreach ($collection as $category) {
            $categoryToParent[$category->getParentId()][] = $category;
        }

        foreach ($crumbs as $i => $crumb) {
            if (!isset($crumb['category_id'])) {
                continue;
            }
            if (!empty($categoryToParent[$crumb['category_id']])) {
                $categoryId = $crumb['category_id'];
                foreach ($categoryToParent[$categoryId] as $child) {
                    $this->_crumbs[$keys[$i]]['subcategories']['category' . $child->getId()] = array(
                        'label' => $child->getName(),
                        'link' => $child->getUrl(),
                        'title' => null,
                        'first' => null,
                        'last' => null,
                        'readonly' => null,
                    );
                }
            }
        }
        return parent::_beforeToHtml();
    }

    protected function _getPath($url)
    {
        $parser = parse_url($url);
        $path = isset($parser['path']) ? $parser['path'] : '';
        $path = explode('/', $path);
        $path[count($path) - 1] = basename($path[count($path) - 1], '.html');
        return array_filter($path);
    }

    public function getCacheKeyInfo()
    {
        return array(
            'BLOCK_TPL',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate(),
            'PATH_' . $this->getRequest()->getRequestString(),
        );
    }
}