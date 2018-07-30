# bProxmox
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

