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
            $keys_values['name'] = $vm['name'];;
            $bean = retrieve_record_bean('btc_Maquinas_virtuales', $keys_values);
            $bean->name = $vm['name'];
            $bean->cpu = $vm['cpus'];
            $server = $node['node'];
            $bean->ram = $vm_info['memory'];
            $bean->mvid = $vm['vmid'];
            $bean->mac = isset($vm_info['net0']) ? $this->get_mac($vm_info['net0']) : "";
            /*list($virtios, $satas, $ides, $scsis) = $this->get_storage($vm_info);
            foreach ($virtios as $virtio) {
                //
            }*/
            $this->relate_vm_with_ips($bean, $bean->mac);
            $this->relate_vm_with_server($bean, $server);
            $bean->estado_vm = 'Vigente';
            $bean->save();
        }
    }

    private function get_storage($vm_info) {
        $virtios = Array();
        $satas = Array();
        $ides = Array();
        $scsis = Array();
        foreach ($vm_info as $key => $value) {
            if (preg_match('/media=cdrom/', $value)) continue;
            $value = explode(',', $value);
            if (preg_match('/^virtio[0-9][0-9]*$/', $key)) {
                $virtios[] = $this->parse_storage($value);
            } else if (preg_match('/^sata[0-9][0-9]*$/', $key)) {
                $satas[] = $this->parse_storage($value);
            } else if (preg_match('/^ide[0-9][0-9]*$/', $key)) {
                $ides[] = $this->parse_storage($value);
            } else if (preg_match('/^scsi[0-9][0-9]*$/', $key)) {
                $scsis[] = $this->parse_storage($value);
            }
        }
        return Array($virtios, $satas, $ides, $scsis);
    }

    private function parse_storage($storage_info) {
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
            $server_bean->load_relationship('btc_servidores_btc_maquinas_virtuales');
            $server_bean->btc_servidores_btc_maquinas_virtuales->add($vm_bean);
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

}

?>
