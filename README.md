# bProxmox - A Proxmox Suitecrm pseudo-module

## Installation

This is not a regular Suitecrm module.
We are making it public so that it can be useful for others.

However there are not official instructions on how to install it.
This is due to be so custom that it's going to be barely useful for
others unless you start a similar project from scratch.

So... do not ask for installation instructions on issues.

Thank you!

## Custom modules

This module is intended to work with some of our custom Suitecrm modules:

- `btc_Maquinas_virtuales`
- `btc_Discos_duros`
- `btc_Servidores`
- `btc_IP`

which have not been made public.

## Features

- Fetch Proxmox VPS
- Fetch Proxmox Hard Disks
- Fetch Proxmox IPs
- Fetch Proxmox Nodes

and some more features.

# OLD README from 2017 - bProxmox
Integración de los servicios de Proxmox con SuiteCRM.

## Instrucciones de instalación
Copiar las carpetas `custom` y `bProxmox` en el directorio raíz de SuiteCRM.

Ejecutar:
```
cd bProxmox
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

Lanzar una `Reparación y Reconstrucción Rápida`.

