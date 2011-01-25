<?php

class SessionManager extends SComponent {

	protected $_sessions = array();
	
	public function __construct($bot) {
		parent::__construct($bot);
        S::logger()->log("Session Manager starting...", 'session_manager');
	}    

	public function pushHandler($handler, $user) {
        if (array_key_exists($user, $this->_sessions)) {
            S::logger()->log("Pushing new handler for $user...");
            $this->_sessions[$user]->pushHandler($handler);
            return true;
        } else {
            return false;
        }
	}

    public function popHandler($user) {
        if (array_key_exists($user, $this->_sessions)) {
            $this->_sessions[$user]->popHandler();
            return true;
        } else {
            return false;
        }
    }

    public function SessionStart($user) {
        if (!array_key_exists($user, $this->_sessions)) {
            $this->_sessions[$user] = new Session($user);
            $this->_sessions[$user]->pushHandler(array(S::bot(), 'Parse'));
            return $this->_sessions[$user];
        } else {
            return null;
        }
    }

    public function SessionExists($user) {
        return array_key_exists($user, $this->_sessions);
    }

    public function sessionDestroy($user) {
        if (array_key_exists($user, $this->_sessions)) {
            ## ?
            unset($this->_sessions[$user]);
            return true;
        } else {
            return false;
        }
    }

    public function callHandler($event) {
        S::logger()->log("Call Handler", 'session_manager');
        $user = $event->sender;
        //$this->sessionGC(1);
        if (!array_key_exists($user, $this->_sessions)) {
            S::logger()->log("Starting session...");
            $this->SessionStart($user);
        }

        S::logger()->log("Call handler for $user");
        $this->_sessions[$user]->callHandler($event);
    }

    protected function sessionGC($p) {
        foreach ($this->_sessions as $user=>$s) {
            if ($s->isExpired()) {
                $this->sessionDestroy($user);
            }
        }
    }
}