<?php

require_once('bProxmox/proxmox_api.php');
require_once('bProxmox/virtual_machine.php');
require_once('bProxmox/server_hard_disc.php');

function main() {
    $GLOBALS['log']->fatal("[bProxmox] Entering bProxmox synchronization.");
    $virtual_machine = new VirtualMachine(ProxmoxAPI::get_instance());
    $virtual_machine->sync_all_virtual_machines();
    $server_hard_disc = new ServerHardDisc(ProxmoxAPI::get_instance());
    $server_hard_disc->sync_all_servers_hard_discs();
    return true;
}

?>
