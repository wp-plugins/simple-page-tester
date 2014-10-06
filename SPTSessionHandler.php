<?php

/* A wrapper class to handle session data required for tracking visitors in SPT.
** Compatible with Native PHP sessions and the WP Session Manager plugin by
** Eric Mann for those on servers without $_SESSION support. */
final class SPTSessionHandler {
    private $_sessionHandlerType;
    private $_sessionStarted;

    function __construct() {
        /* if WP Session Manager plugin is active, then use that instead of
        ** native PHP sessions */
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        if (is_plugin_active('wp-session-manager/wp-session-manager.php')) {
            $this->_sessionHandlerType = 'wp_session';
        } else {
            $this->_sessionHandlerType = 'native';
        }

        $this->startSession();
    }

    public function startSession() {
        if ($this->_sessionHandlerType == 'native') {
            session_start();
            $this->_sessionStarted = true;
        } else if ($this->_sessionHandlerType == 'wp_session') {
            global $wp_session;
            $wp_session = WP_Session::get_instance();
            wp_session_start();
            $this->_sessionStarted = true;
        } else {
            error_log('Attempt to start session failed. Invalid session handler type.');
            return;
        }
    }

    public function endSession() {
        if ($this->_sessionHandlerType == 'native') {
            session_write_close();
            $this->_sessionStarted = false;
        } else if ($this->_sessionHandlerType == 'wp_session') {
            wp_session_write_close();
            $this->_sessionStarted = false;
        }
    }

    public function getData($key) {
        if (!$this->_sessionStarted) {
            error_log('Session wasn\'t started on attempt to get key [' . $key . ']');
            return;
        }

        if ($this->_sessionHandlerType == 'native') {
            $value = $_SESSION[$key];
        } else if ($this->_sessionHandlerType == 'wp_session') {
            global $wp_session;
            $value = $wp_session[$key];
        } else {
            error_log('Session handler type was invalid during attempt to get key [' . $key . ']');
            return;
        }

        return $value;
    }

    public function setData($key, $value) {
        if (!$this->_sessionStarted) {
            error_log('Session wasn\'t started on attempt to set key [' . $key . ']');
            return;
        }

        if ($this->_sessionHandlerType == 'native') {
            $_SESSION[$key] = $value;
        } else if ($this->_sessionHandlerType == 'wp_session') {
            global $wp_session;
            $wp_session[$key] = $value;
        } else {
            error_log('Session handler type was invalid during attempt to set key [' . $key . ']');
            return;
        }
    }

    public function unsetData($key) {
        if (!$this->_sessionStarted) {
            error_log('Session wasn\'t started on attempt to set key [' . $key . ']');
            return;
        }

        if ($this->_sessionHandlerType == 'native') {
            unset($_SESSION[$key]);
        } else if ($this->_sessionHandlerType == 'wp_session') {
            global $wp_session;
            unset($wp_session[$key]);
        } else {
            error_log('Session handler type was invalid during attempt to set key [' . $key . ']');
            return;
        }

    }
}

global $sptSession;

if (!isset($sptSession)) {
    $sptSession = new SPTSessionHandler();
}

?>
