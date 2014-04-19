<?php

class IndexController extends Zend_Controller_Action {

    public function preDispatch() {
        $this->view->category = Application_Model_Kernel_Category::getStructCategories(true);
        $this->view->pageType = true;
        $this->view->menu = 'main';
    }

    public function indexAction() {

        $this->view->idPage = (int) $this->_getParam('idPage');

        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();

        $this->view->publicList = Application_Model_Kernel_Product::getList(false, false, true, true, false, Application_Model_Kernel_Page::STATUS_SHOW, false, false, 15, true, false);

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }

    public function productAction() {

        $this->view->idPage = (int) $this->_getParam('idPage');

        $this->view->page = Application_Model_Kernel_Product::getByIdPage($this->view->idPage); //->getContent()->getFields();
        $this->view->contentPage = $this->view->page->getContent()->getFields();
        
        $this->view->products = Application_Model_Kernel_Product::getList('RAND()', '', true, true, false, 1, false, false, 4, false, 'products.idProduct <> '.$this->view->page->getIdProduct());

        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText();
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }
    
    public function fafAction() {

        $this->view->idPage = (int)$this->_getParam('idPage');
        $this->view->contentPage = Application_Model_Kernel_Page_ContentPage::getByPageId($this->view->idPage)->getContent()->getFields();
        
        $this->view->title = $this->view->contentPage['title']->getFieldText();
        $this->view->keywords = $this->view->contentPage['keywords']->getFieldText(); 
        $this->view->description = $this->view->contentPage['description']->getFieldText();
    }

}