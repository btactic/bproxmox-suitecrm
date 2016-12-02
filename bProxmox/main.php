<?php

require_once('bProxmox/proxmox_api.php');
require_once('bProxmox/virtual_machine.php');

function main() {
    $GLOBALS['log']->fatal("[bProxmox] Entering bProxmox synchronization.");
    $virtual_machine = new VirtualMachine(ProxmoxAPI::get_instance());
    $virtual_machine->sync_all_virtual_machines();
    return true;
}

?>
