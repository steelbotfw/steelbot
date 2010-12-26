<?php 
class Timer extends SComponent {
	protected $_time,
		  $_callbacks = array();

	public function __construct($time) {
		$this->_time = $time;
	}

	public function attachHandler($callback) {
		$hash = $this->hashObject($callback);
		return $hash;	
	}

	protected function hashObject($obj) {

	}
}
