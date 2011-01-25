<?php 
class Session extends SComponent {
    public $user;    
    protected $_handlers = array(),
              $_lastAccess,
              $_durability = 60; //100 seconds

	public function __construct($user) {
        parent::__construct(S::bot());
        $this->user = $user;
        $this->_lastAccess = time();
	}

    public function callHandler($event) {
        call_user_func(end($this->_handlers), $event);
    }
    
	public function pushHandler($handler) {
        array_push($this->_handlers, $handler);
        return true;
	}

	public function popHandler() {
        return array_pop($this->_handlers);
	}

    public function isExpired() {
        return abs($this->_lastAccess - time()) > $this->_durability;
    }
}
