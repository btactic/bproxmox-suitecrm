<?php

require_once('bProxmox/bean_utils.php');

class VirtualMachine {

    public function __construct($proxmox_api_instance) {
        $this->proxmox = $proxmox_api_instance;
    }

    public function sync_all_virtual_machines() {
        $nodes = $this->proxmox->get('/nodes');
        if (isset($nodes['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving nodes list.");
        } else {
            foreach ($nodes['data'] as $node) {
                $this->sync_all_virtual_machines_of_node($node);
            }
        }
    }

    public function sync_all_virtual_machines_of_node($node) {
        $node_vms = $this->proxmox->get('/nodes/'.$node['node']."/qemu");
        if (isset($node_vms['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving VM list of node '".$node."'.");
        } else {
            foreach ($node_vms['data'] as $vm) {
                $this->sync_virtual_machine($node, $vm);
            }
        }
    }

    public function sync_virtual_machine($node, $vm) {
        $vm_info = $this->proxmox->get('/nodes/'.$node['node']."/qemu/".$vm['vmid']."/config");
        if (isset($vm_info['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving info of VM '".$vm."'.");
        } else {
            $vm_info = $vm_info['data'];
            $keys_values = array();
            $keys_values['vmid'] = $vm['vmid'];;
            $bean = retrieve_record_bean('btc_Maquinas_virtuales', $keys_values);
            $bean->name = $vm['name'];
            $bean->cpu = $vm['cpus'];
            $server = $node['node'];
            $bean->ram = $vm_info['memory'];
            $bean->vmid = $vm['vmid'];
            $bean->mac = isset($vm_info['net0']) ? $this->get_mac($vm_info['net0']) : "";
            if (isset($vm_info['description'])) $bean->description = $vm_info['description'];
            $bean->estado_vm = 'Vigente';
            $bean->save();
            $this->relate_vm_with_ips($bean, $bean->mac);
            $this->relate_vm_with_server($bean, $server);
            $this->sync_virtual_machine_storages($bean, $vm_info);
        }
    }

    private function sync_virtual_machine_storages($vm_bean, $vm_info) {
        $storages = $this->get_storage($vm_info);
        foreach ($storages as $storage) {
            $this->sync_virtual_machine_storage($vm_bean, $storage);
        }
    }

    private function sync_virtual_machine_storage($vm_bean, $storage) {
        $keys_values = array();
        $keys_values['name'] = $storage['id'];
        $hdd_bean = retrieve_record_bean('btc_Discos_duros', $keys_values);
        $hdd_bean->tipo = 'virtual';
        $hdd_bean->name = $storage['id'];
        //$hdd_bean->clase = '';
        $hdd_bean->bus = $storage['bus'];
        $hdd_bean->cache = isset($storage['cache']) ? $storage['cache'] : 'default';
        $hdd_bean->no_backup = (isset($storage['backup']) && $storage['backup'] == 0) ? '1' : '0';
        $hdd_bean->device = $storage['device'];
        $hdd_bean->discard = isset($storage['discard']) ? $storage['discard'] : '0';
        $hdd_bean->io_thread = isset($storage['iothread']) ? $storage['iothread'] : '0';
        $hdd_bean->capacidad_gb = to_gigabytes($storage['size']);
        $hdd_bean->save();
        $this->relate_hdd_with_storage($hdd_bean);
        $vm_bean->load_relationship('btc_discos_duros_btc_maquinas_virtuales');
        $vm_bean->btc_discos_duros_btc_maquinas_virtuales->add($hdd_bean);
    }

    private function get_storage($vm_info) {
        $storages = Array();
        foreach ($vm_info as $key => $value) {
            if (preg_match('/media=cdrom/', $value)) continue;
            if (preg_match('/^virtio[0-9][0-9]*$/', $key)) {
                $bus = 'virtio';
            } else if (preg_match('/^sata[0-9][0-9]*$/', $key)) {
                $bus = 'sata';
            } else if (preg_match('/^ide[0-9][0-9]*$/', $key)) {
                $bus = 'ide';
            } else if (preg_match('/^scsi[0-9][0-9]*$/', $key)) {
                $bus = 'scsi';
            } else continue;
            $storage = $this->parse_storage($value);
            $storage['bus'] = $bus;
            $storage['device'] = $key;
            $storages[] = $storage;
        }
        return $storages;
    }

    private function parse_storage($storage_info) {
        $storage_info = explode(',', $storage_info);
        $info = Array("id" => $storage_info[0]);
        unset($storage_info[0]);
        foreach($storage_info as $atribute) {
            $atribute = $this->parse_atribute($atribute);
            if (isset($atribute['key'])) {
                $info[$atribute['key']] = $atribute['value'];
            }
        }
        return $info;
    }

    private function parse_atribute($atribute_info) {
        if (preg_match('/^(.*)=(.*)$/', $atribute_info, $atribute)) {
            return Array(
                "key" => $atribute[1],
                "value" => $atribute[2]
            );
        } else {
            return Array();
        }
    }

    private function get_mac($iface_info) {
        if (preg_match('/([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})/',
                $iface_info, $result)) {
            return $result[0];
        }
    }

    private function relate_vm_with_server($vm_bean, $servername) {
        $keys_values = array();
        $keys_values['name'] = $servername;
        $server_bean = retrieve_record_bean('btc_Servidores', $keys_values);
        if (!empty($server_bean->id)) {
            $server_bean->load_relationship('btc_maquinas_virtuales_btc_servidores');
            $server_bean->btc_maquinas_virtuales_btc_servidores->add($vm_bean);
        }
    }

    private function relate_vm_with_ips($vm_bean, $mac) {
        $select = "SELECT id";
        $from = "FROM btc_ip";
        $where = "WHERE mac = '".$mac."' AND deleted = 0";
        $sql = $select." ".$from." ".$where;
        $result = $GLOBALS['db']->query($sql);
        while($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $keys_values = array();
            $keys_values['id'] = $row['id'];
            $ip_bean = retrieve_record_bean('btc_IP', $keys_values);
            $vm_bean->load_relationship('btc_maquinas_virtuales_btc_ip');
            $vm_bean->btc_maquinas_virtuales_btc_ip->add($ip_bean);
        }
    }

    private function relate_hdd_with_storage($hdd_bean) {
        $keys_values = array();
        $keys_values['name'] = explode(':', $hdd_bean->name)[0];
        $storage_bean = retrieve_record_bean('btc_Discos_duros', $keys_values);
        $hdd_bean->load_relationship('btc_discos_duros_btc_discos_duros');
        $hdd_bean->btc_discos_duros_btc_discos_duros->add($storage_bean);
    }

}

?>
