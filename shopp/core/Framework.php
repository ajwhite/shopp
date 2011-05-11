<?php
/**
 * Framework
 *
 * Library of abstract design pattern templates
 *
 * @author Jonathan Davis
 * @version 1.0
 * @copyright Ingenesis Limited, May  5, 2011
 * @license GNU GPL version 3 (or later) {@see license.txt}
 * @package
 * @since 1.0
 * @subpackage
 **/

class RegistryManager implements Iterator {

	private $_list = array();
	private $_keys = array();
	private $_false = false;

	public function __construct() {
        $this->_position = 0;
	}

	public function add ($key,$entry) {
		$this->_list[$key] = $entry;
		$this->rekey();
	}

	public function update ($key,$entry) {
		if (!$this->exists($key)) return false;
		$entry = array_merge($this->_list[$key],$entry);
		$this->_list[$key] = $entry;
	}

	public function &get ($key) {
		if ($this->exists($key)) return $this->_list[$key];
		else return $_false;
	}

	public function exists ($key) {
		return array_key_exists($key,$this->_list);
	}

	public function remove ($key) {
		if (!$this->exists($key)) return false;
		unset($this->_list[$key]);
		$this->rekey();
	}

	private function rekey () {
		$this->_keys = array_keys($this->_list);
	}

	function current () {
		return $this->_list[ $this->keys[$this->_position] ];
	}

	function key () {
		return $this->keys[$this->_position];
	}

	function next () {
		++$this->_position;
	}

	function rewind () {
		$this->_position = 0;
	}

	function valid () {
		return (
			array_key_exists($this->_position,$this->_keys)
			&& array_key_exists($this->keys[$this->_position],$this->_list)
		);
	}

}

?>