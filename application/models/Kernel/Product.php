<?php

class Application_Model_Kernel_Product extends Application_Model_Kernel_Page
{

    private $idProduct;
    private $productCategory;
    private $idPhoto1;
    private $photo1 = null;

    private $categoryList = array ();
    private $categoryText = '';

    const ITEM_ON_PAGE = 10;

    /**
     * @var Application_Model_Kernel_Gallery
     */
    public function __construct($idProduct, $idPhoto1, $idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, $position)
    {
        parent::__construct($idPage, $idRoute, $idContentPack, $pageEditDate, $pageStatus, self::TYPE_PROJECT, $position);
        $this->idProduct = $idProduct;
        $this->idPhoto1  = $idPhoto1;
    }

    public function getIdProduct()
    {
        return $this->idProduct;
    }

    public function getIdPhoto1()
    {
        return $this->idPhoto1;
    }

    public function getPhoto1()
    {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);

        return $this->photo1;
    }

    public function setPhoto1(Application_Model_Kernel_Photo &$photo1)
    {
        $this->photo1 = $photo1;

        return $this;
    }

    public function setIdPhoto1($idPhoto1)
    {
        $this->idPhoto1 = $idPhoto1;
    }

    public function save()
    {
        $db = Zend_Registry::get('db');
        $db->beginTransaction();
        try {
            $db->beginTransaction();
            $insert = is_null($this->_idPage);
            $this->savePageData(); //сохраняем даные страницы
            $data = array (
                'idPage'   => $this->getIdPage(),
                'idPhoto1' => $this->idPhoto1
            );
            if ($insert) {
                $db->insert('products', $data);
                $this->idProduct = $db->lastInsertId();
            } else {
                $db->update('products', $data, 'idProduct = ' . intval($this->idProduct));
            }
            $db->commit();
//            $this->clearCache();
        } catch (Exception $e) {
            $db->rollBack();
            Application_Model_Kernel_ErrorLog::addLogRow(Application_Model_Kernel_ErrorLog::ID_SAVE_ERROR, $e->getMessage(), ';product.php');
            throw new Exception($e->getMessage());
        }
    }

    private function clearCache()
    {
        if (!is_null($this->getidProject())) {
            $cachemanager = Zend_Registry::get('cachemanager');
            $cache        = $cachemanager->getCache('product');
            if (!is_null($cache)) {
                $cache->remove($this->getidProduct());
            }
        }
    }

    public function validate($data = false)
    {
        $e = new Application_Model_Kernel_Exception();
        $this->getRoute()->validate($e);
        $this->validatePageData($e);

        if ($data != false) {
            $data->url = trim($data->url);
            if (empty($data->url))
                throw new Exception(' Пустой URL ');
            $langs = Kernel_Language::getAll();
            foreach ($langs as $lang) {
                if (empty($data->content[$lang->getId()]['contentName']))
                    throw new Exception(' Пустой поле "Название" ' . $lang->getFullName());
            }
        }

        if ((bool)$e->current())
            throw $e;
    }

    private static function getSelf(stdClass &$data)
    {
        return new self($data->idProduct, $data->idPhoto1,
                        $data->idPage, $data->idRoute, $data->idContentPack,
                        $data->pageEditDate, $data->pageStatus, $data->position);
    }

    public static function loadCache($id)
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache        = $cachemanager->getCache('project');

        return $cache->load($id);
    }

    public static function getById($idProduct)
    {
        $idProduct = (int)$idProduct;
//		$cachemanager = Zend_Registry::get('cachemanager');
//		$cache = $cachemanager->getCache('department');
//		if (($project = $cache->load($idProject)) !== false) {
//			return $project;
//		} else {
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('products');
        $select->join('pages', 'products.idPage = pages.idPage');
        $select->where('idProduct = ?', $idProduct);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
//				$project->completelyCache();
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
//		}
    }

    public static function getByIdPage($idPage)
    {
        $idPage = intval($idPage);

        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('products');
        $select->join('pages', 'products.idPage = pages.idPage');
        $select->where('pages.idPage = ?', $idPage);
        $select->limit(1);
        if (($productData = $db->fetchRow($select)) !== false) {
            return self::getSelf($productData);
        } else {
            throw new Exception(self::ERROR_INVALID_ID);
        }
    }

    public function completelyCache()
    {
        $cachemanager = Zend_Registry::get('cachemanager');
        $cache        = $cachemanager->getCache('product');
        $cache->load($this->getIdPage());
        $this->getidPhoto1();
        $this->getIdGallery2();
        $this->getRoute();
        $this->getContent();
        $cache->save($this);
    }

    public static function getList($order, $orderType, $content, $route, $searchName, $status, $page, $onPage, $limit, $group = true, $wher = false, $area = false, $nextorder = false)
    {
        $return = new stdClass();
        $db     = Zend_Registry::get('db');
        $select = $db->select()->from('products');
        $select->join('pages', 'pages.idPage = products.idPage');
        $select->join('categorie_product', 'products.idProduct = categorie_product.idProduct');
        $select->joinLeft('comments', '( products.idProduct = comments.idOwner AND comments.commentType = 1 )', array ('countComm' => 'COUNT(comments.idOwner)'));

        //ORDER BY countComm DESC

        if ($route) {
            $select->join('routing', 'pages.idRoute = routing.idRoute');
        }
        if ($content) {
            $select->join('content', 'content.idContentPack = pages.idContentPack');
            $select->where('content.idLanguage = ?', Kernel_Language::getCurrent()->getId());
            if ($searchName) {
                $select->where('content.contentName = ?', $searchName);
            }
        }
        $select->where('pages.pageType = ?', self::TYPE_PROJECT);
        if ($wher) {
            $select->where($wher);
        }
        if ($order && $orderType) {
            if ($order == 'BY' && $orderType == 'RAND') {
                $select->order(new Zend_Db_Expr('RAND()'));
            } else {
                $select->order($order . ' ' . $orderType);
            }
        } else {
            if (!$nextorder) {
                $select->order('pages.idPage DESC');
            }
        }
        if ($nextorder) {
            $select->order($nextorder);
        }
        if ($status !== false)
            $select->where('pages.pageStatus = ?', $status);
        if ($group !== false)
            $select->group('products.idProduct');
        if ($limit !== false)
            $select->limit($limit);
        if ($page !== false) {
            $paginator = Zend_Paginator::factory($select);
            $paginator->setItemCountPerPage($onPage);
            $paginator->setPageRange(40);
            $paginator->setCurrentPageNumber($page);
            $return->paginator = $paginator;
        } else {
            $return->paginator = $db->fetchAll($select);
        }
        $return->data = array ();
        $i            = 0;
        foreach ($return->paginator as $projectData) {
            $return->data[$i] = self::getSelf($projectData);
            if ($route) {
                $url           = new Application_Model_Kernel_Routing_Url($projectData->url);
                $defaultParams = new Application_Model_Kernel_Routing_DefaultParams($projectData->defaultParams);
                $route         = new Application_Model_Kernel_Routing($projectData->idRoute, $projectData->type, $projectData->name, $projectData->module, $projectData->controller, $projectData->action, $url, $defaultParams, $projectData->routeStatus);
                $return->data[$i]->setRoute($route);
            }
            if ($content) {
                $contentLang = new Application_Model_Kernel_Content_Language($projectData->idContent, $projectData->idLanguage, $projectData->idContentPack);
                $contentLang->setFieldsArray(Application_Model_Kernel_Content_Fields::getFieldsByIdContent($projectData->idContent));
                $return->data[$i]->setContent($contentLang);
            }
            $i++;
        }

        return $return;
    }

    public function show()
    {
        $db                = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_SHOW;
        $this->savePageData();
//        $this->clearCache();
    }

    public function hide()
    {
        $db                = Zend_Registry::get('db');
        $this->_pageStatus = self::STATUS_HIDE;
        $this->savePageData();
//        $this->clearCache();
    }

    public function delete()
    {
        $db = Zend_Registry::get('db');
        $db->delete('products', "products.idPage = {$this->_idPage}");
        $this->deletePage();
    }

    public static function changePosition($idPage, $position)
    {

        $db = Zend_Registry::get('db');
        $db->update('products', array ("projectPosition" => $position), 'idPage = ' . (int)$idPage);

        return true;
    }

    public function getListCategoryByIdProduct()
    {

        if (count($this->categoryList) == 0) {
            $db     = Zend_Registry::get('db');
            $return = array ();
            $select = $db->select()->from('categorie_product');
            $select->where('categorie_product.idProduct = ' . (int)$this->idProduct);
            $i = 0;
            if (false !== ($result = $db->fetchAll($select))) {
                foreach ($result as $category) {
                    $object[$i]              = new stdClass();
                    $object[$i]->id          = $category->id;
                    $object[$i]->idCategorie = $category->idCategorie;
                    $object[$i]->idProduct   = $category->idProduct;
                    $return[]                = $object[$i];
                    $i++;
                }
            }
            $this->categoryList = $return;
        } else {
            $return = $this->categoryList;
        }

        return $return;
    }

    public function getFirstCategory()
    {
        $arr = $this->getListCategoryByIdProduct();

        if (count($arr)) {
            return Application_Model_Kernel_Category::getById( $arr[0]->idCategorie );
        }

        return false;
    }

    public function saveCategoryByIdProduct($idCategorys)
    {

        $db = Zend_Registry::get('db');
        $i  = 0;
        foreach ($idCategorys as $key => $value) {
            $data[$i] = array ('idCategorie' => (int)$value, 'idProduct' => (int)$this->idProduct);
            $db->insert('categorie_product', $data[$i]);
            $i++;
        }
        $db->commit();
    }

    public function deleteCatecorys()
    {
        $db = Zend_Registry::get('db');
        $db->delete('categorie_product', "categorie_product.idProduct = " . $this->idProduct);
    }

    public function getCategoryTextByProduct($categorys)
    {
        if (mb_strlen($this->categoryText, 'utf8') < 2) {

            $this->categoryText = '';
            foreach ($categorys as $key => $value) {
                foreach (self::getListCategoryByIdProduct() as $v) {
                    if ($v->idCategorie == $value->getId()) {
                        $this->categoryText .= Application_Model_Kernel_Content_Fields::getFieldByIdContentAndNameField($value->getContent()->getId(), 'contentName')->getFieldText() . ', ';
                    }
                }
            }
            if (mb_strlen($this->categoryText, 'utf8') > 2)
                $this->categoryText = mb_substr($this->categoryText, 0, -2);
        }

        return $this->categoryText;
    }

}

