# bProxmox - A Proxmox Suitecrm pseudo-module

## Installation

This is not a regular Suitecrm module.
We are making it public so that it can be useful for others.

However there are not official instructions on how to install it.
This is due to be so custom that it's going to be barely useful for
others unless you start a similar project from scratch.

So... do not ask for installation instructions on issues.

Thank you!

## Composer Packages

To use the files in this repository, you will need to install the required Composer packages.

### Installation of Composer Packages

1. Make sure you have **Composer** installed on your system.
2. Navigate to the next directory of this project.
```bash
cd bProxmox/
```
3. Run the following command to install the required packages:

```bash
php8.2 /usr/bin/composer install
```

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

