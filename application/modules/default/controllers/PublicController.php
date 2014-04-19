<?php

class PublicController extends Zend_Controller_Action {

    public function preDispatch() {
        $this->view->menu = 'public';
    }

    public function showAction() {

        $this->view->idPage = (int) $this->_getParam('idPage');

        $this->view->product = Application_Model_Kernel_Product::getByIdPage($this->view->idPage);

        $this->view->contentPage = $this->view->product->getContent()->getFields();

        $this->view->category = Application_Model_Kernel_Category::getStructCategories(true);

        $this->view->categoryText = $this->view->product->getCategoryTextByProduct($this->view->category);

        $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, false, false, 2, true, 'products.idProduct <> '.$this->view->product->getIdProduct());
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }

    public function indexAction() {

        $this->view->idPage = (int) $this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
        
        $this->view->category = Application_Model_Kernel_Category::getStructCategories(true);
        
        
        
        $wheres = false;
        $order = false;
        $orderType = false;
        
        $this->view->idCategory = (int)$this->_getParam('idCategory');
        $this->view->sort = (int)$this->_getParam('sort');
        $this->view->pageNum = (int)$this->_getParam('pageNum');
        
        if( $this->view->pageNum == 0 ){
            $this->view->pageNum = 1;
        } else {
            $this->view->headCanonical = '<link rel="canonical" href="http://wifi-hardware.com/public"/>';
        }
        if( $this->view->sort == 1 ){
            
            $order = ' countComm ';
            $orderType = ' DESC ';
            
            $this->view->headCanonical = '<link rel="canonical" href="http://wifi-hardware.com/public"/>';
        }
        if( $this->view->idCategory != 0 ){
            $wheres = '';
            $wheres .= ' categorie_product.idCategorie = '.$this->view->idCategory;
            $this->view->headCanonical = '<link rel="canonical" href="http://wifi-hardware.com/public/'.$this->view->idCategory.'"/>';
            
            $this->view->categoryContent = Application_Model_Kernel_Category::getById($this->view->idCategory)->getContent()->getFields();
            
            $this->view->title = $this->view->categoryContent['title']->getFieldText();
            $this->view->keywords = $this->view->categoryContent['keywords']->getFieldText();
            $this->view->description = $this->view->categoryContent['description']->getFieldText();
        }
        $this->view->publicList = Application_Model_Kernel_Product::getList($order, $orderType, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, $this->view->pageNum, Application_Model_Kernel_Product::ITEM_ON_PAGE, false, true, $wheres);
    }

}