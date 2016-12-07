<?php

require_once('bProxmox/proxmox_api.php');
require_once('bProxmox/virtual_machine.php');
require_once('bProxmox/server_hard_disc.php');
require_once('bProxmox/storage.php');

function main() {
    $GLOBALS['log']->fatal("[bProxmox] Entering bProxmox synchronization.");
    $storage = new Storage(ProxmoxAPI::get_instance());
    $storage->sync_all_storages();
    $virtual_machine = new VirtualMachine(ProxmoxAPI::get_instance());
    $virtual_machine->sync_all_virtual_machines();
    $server_hard_disc = new ServerHardDisc(ProxmoxAPI::get_instance());
    $server_hard_disc->sync_all_servers_hard_discs();
    $GLOBALS['log']->fatal("[bProxmox] bProxmox synchronization finished.");
    return true;
}

?>
