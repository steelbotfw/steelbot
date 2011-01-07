<?php

class SComponent {

	public function __construct($bot) {
		$config = $this->config();
		
	}

	public function config() {
		return array();
	}

	public function __get($property) {
		$method = 'get'.ucfirst($property);
		if (method_exists($this, $method)) {
			return $this->$method();
		}
	}

	public function __set($property, $value) {
		$method = 'set'.$property;
		if (method_exists($this, $method)) {
			return $this->$method($value);
		}
	}

}
