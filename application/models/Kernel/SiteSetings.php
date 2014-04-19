<?php
class Application_Model_Kernel_SiteSetings {

	protected $id;
	protected $idPhoto1;
    protected $url1;
    protected $description1;
    protected $photo1 = null;
    
    public function __construct( $id, $idPhoto1, $url1, $description1 ) {
		$this->id =  $id;
		$this->idPhoto1 = $idPhoto1;
        $this->url1 = $url1;
        $this->description1 = $description1;
	}
    
    
     public function getIdPhoto1(){
        return $this->idPhoto1;
    }
    
    public function getPhoto1() {
        if (is_null($this->photo1))
            $this->photo1 = Application_Model_Kernel_Photo::getById($this->idPhoto1);
        return $this->photo1;
    }
    
    public function setPhoto1(Application_Model_Kernel_Photo &$photo1) {
        $this->photo1 = $photo1;
        return $this;
    }
    
    public function setIdPhoto1($idPhoto1){
        $this->idPhoto1 = $idPhoto1;
    }
	
	public function getId() {
		return $this->id;
	}
    
    public function getUrl1() {
        return trim($this->url1);
    }
    
    public function getDescription1() {
        return $this->description1;
    }
    
    public function setDescription1($value) {
        $this->description1 = $value;
    }
    
    public function setUrl1($value) {
       $this->url1 = $value;
    }

    public function validate() {
		if ($this->getName() === '') {
			throw new Exception('Enter block name');
		}
		if (strlen($this->getName()) <= 3) {
			throw new Exception('Block name must me more then 3 letter');
		}
	}
	
	/**
	 * Save block data
	 * @access public
	 * @return void
	 */
	public function save() {
		$data = array(
			'idPhoto1' => $this->idPhoto1,
            'url1' => $this->url1,
            'description1' => $this->description1
		);
		$db = Zend_Registry::get('db');
        $db->update('site_setings', $data, 'id = ' . $this->getId());
	}
	
	public static function getBy() {
		$db = Zend_Registry::get('db');
		$select = $db->select()->from('site_setings');
		$select->where('site_setings.id = 1');
		$select->limit(1);
		if (($block = $db->fetchRow($select)) !== false) {
			return new self( $block->id, $block->idPhoto1, $block->url1, $block->description1 );
		} else {
			throw new Exception('Table NOT FOUND');
		}
	}
	
	
}