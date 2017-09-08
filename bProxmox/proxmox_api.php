<?php

//require_once 'bProxmox/vendor/autoload.php';
require_once 'bProxmox/ProxmoxVE/vendor/autoload.php';
use ProxmoxVE\Proxmox;

class ProxmoxAPI {

    private static $instance = NULL;
    private $proxmox;

    private function __construct($config_file = 'bProxmox/config/proxmox_api.ini') {
        if (file_exists($config_file)) {
            $this->config = parse_ini_file($config_file);
            $this->proxmox = new Proxmox($this->config);
        } else {
            $GLOBALS['log']->fatal("[bProxmox] Impossible to access '$config_file'.");
        }
    }

    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new ProxmoxAPI();
        }
        return self::$instance->proxmox;
    }

}

?>
