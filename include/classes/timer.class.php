<?php 
class Timer extends SComponent {
	protected $_time,
		      $_callback,
		      $_parameters = array();

	public function __construct($time, $callback, $parameters = array()) {
		$this->_time = $time;
		$this->_callback = $callback;
		$this->_parameters = $parameters;
	}

	public function timeEvent($time) {
		if ($time >= $this->_time) {
			call_user_func_array($this->_callback, $this->_parameters);
			return true;
		}
		return false;
	}

	public function setCallback($callback) {
		$this->_callback = $callback;
	}

	public function setParameters($parameters) {
		$this->_parameters = $parameters;
	}

}
