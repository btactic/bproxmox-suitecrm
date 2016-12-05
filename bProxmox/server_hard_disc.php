<?php

require_once('bProxmox/bean_utils.php');
require_once('bProxmox/utils.php');

class ServerHardDisc {

    public function __construct($proxmox_api_instance) {
        $this->proxmox = $proxmox_api_instance;
    }

    public function sync_all_servers_hard_discs() {
        $nodes = $this->proxmox->get('/nodes');
        if (isset($nodes['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving nodes list.");
        } else {
            foreach ($nodes['data'] as $node) {
                $this->sync_all_server_hard_discs($node);
            }
        }
    }

    public function sync_all_server_hard_discs($node) {
        $node_hdds = $this->proxmox->get('/nodes/'.$node['node']."/disks/list");
        if (isset($node_hdds['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving HDD list of node '".$node."'.");
        } else {
            foreach ($node_hdds['data'] as $hdd) {
                $this->sync_hdd($node, $hdd);
            }
        }
    }

    public function sync_hdd($node, $hdd) {
        $server = $node['node'];
        $keys_values = array();
        $keys_values['name'] = $hdd['serial'];
        $bean = retrieve_record_bean('btc_Discos_duros', $keys_values);
        $bean->name = $hdd['serial'];
        $bean->clase = $hdd['type'];
        $bean->device = $hdd['devpath'];
        $bean->capacidad_gb = bytes_to_gibibytes($hdd['size']);
        $bean->tipo = 'fisico';
        $bean->bus = 'sata';
        $bean->save();
        $this->relate_hdd_with_server($bean, $server);
    }

    private function relate_hdd_with_server($hdd_bean, $servername) {
        $keys_values = array();
        $keys_values['name'] = $servername;
        $server_bean = retrieve_record_bean('btc_Servidores', $keys_values);
        if (!empty($server_bean->id)) {
            $server_bean->load_relationship('btc_discos_duros_btc_servidores');
            $server_bean->btc_discos_duros_btc_servidores->add($hdd_bean);
        }
    }

}

?>
