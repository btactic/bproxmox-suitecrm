<?php

$job_strings[] = 'bProxmox';

function bProxmox() {
    require_once('bProxmox/main.php');
    return main();
}
