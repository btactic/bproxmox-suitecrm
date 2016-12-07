<?php

require_once('bProxmox/bean_utils.php');
require_once('bProxmox/utils.php');

class Storage {

    public function __construct($proxmox_api_instance) {
        $this->proxmox = $proxmox_api_instance;
    }

    public function sync_all_storages() {
        $storages = $this->proxmox->get('/storage');
        if (isset($storages['errors'])) {
            $GLOBALS['log']->fatal("[bProxmox] Error retrieving storages list.");
        } else {
            foreach ($storages['data'] as $storage) {
                $this->sync_storage($storage);
            }
        }
    }

    public function sync_storage($storage) {
        if ($storage['type'] == 'lvm' || $storage['type'] == 'iscsi') {
            $keys_values = array();
            $keys_values['name'] = $storage['storage'];
            $bean = retrieve_record_bean('btc_Discos_duros', $keys_values);
            $bean->name = $storage['storage'];
            $bean->tipo = $storage['type'];
            $bean->save();
        }
    }

}

?>
