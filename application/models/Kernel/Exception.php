<?php
class Application_Model_Kernel_Exception extends Exception implements ArrayAccess, Iterator {
	protected $_list = array();

	public function __construct() {
	}

	public function offsetExists($index) {
	  return isset($this->_list[$index]);
	}

	public function offsetGet($index) {
	  return $this->_list[$index];
	}

	public function offsetSet($index, $value) {
	  if (isset($index)) {
	 $this->_list[$index] = $value;
	  }
	  else {
	 $this->_list[] = $value;
	  }
	}

	public function offsetUnset($index) {
	  unset($this->_list[$index]);
	}

	public function current() {
	  return current($this->_list);
	}

	public function key() {
	  return key($this->_list);
	}

	public function next() {
	  return next($this->_list);
	}

	public function rewind() {
	  return reset($this->_list);
	}

	public function valid() {
	  return (bool) $this->current();
	}
	
	public function getMessages(){
		return $this->_list;
	}
 }
 ?>
