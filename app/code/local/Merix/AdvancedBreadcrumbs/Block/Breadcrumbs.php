<?php

class Merix_AdvancedBreadcrumbs_Block_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{

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
        $crumbs = array_values($this->_crumbs);
        $keys = array_keys($this->_crumbs);

        foreach($crumbs as $i => $crumb)
        {
            $category = Mage::getResourceModel('catalog/category_collection')
                ->addFieldToFilter('name', $crumb['label'])
                ->getFirstItem();

            $this->_crumbs[$keys[$i]]['subcategories'] = array();

            if($i > 0){
                $category
                    ->getChildrenCategories()
                    ->addFieldToFilter('name', $crumbs[$i-1]['label'])
                    ->getFirstItem();
            }
            if($category->getData()){
                $children = $category->getChildrenCategories();

                foreach($children as $child)
                {
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
        $path[count($path)-1] = basename($path[count($path)-1], '.html');
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