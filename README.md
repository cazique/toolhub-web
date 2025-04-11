```
████████╗ ██████╗  ██████╗ ██╗     ██╗  ██╗██╗   ██╗██████╗     ██╗    ██╗███████╗██████╗ 
╚══██╔══╝██╔═══██╗██╔═══██╗██║     ██║  ██║██║   ██║██╔══██╗    ██║    ██║██╔════╝██╔══██╗
   ██║   ██║   ██║██║   ██║██║     ███████║██║   ██║██████╔╝    ██║ █╗ ██║█████╗  ██████╔╝
   ██║   ██║   ██║██║   ██║██║     ██╔══██║██║   ██║██╔══██╗    ██║███╗██║██╔══╝  ██╔══██╗
   ██║   ╚██████╔╝╚██████╔╝███████╗██║  ██║╚██████╔╝██████╔╝    ╚███╔███╔╝███████╗██████╔╝
   ╚═╝    ╚═════╝  ╚═════╝ ╚══════╝╚═╝  ╚═╝ ╚═════╝ ╚═════╝      ╚══╝╚══╝ ╚══════╝╚═════╝ 
                                                                                           
```

# ToolHub Web v0.2

## Descripción

ToolHub Web es una suite de utilidades técnicas de código abierto para análisis de dominios, IPs y seguridad web. Ofrece una colección de herramientas accesibles a través de una interfaz web moderna, diseñada con Bootstrap 5.

Esta suite está pensada para administradores de sistemas, desarrolladores web, especialistas en SEO y profesionales de ciberseguridad que necesiten realizar análisis rápidos de dominios e IPs sin necesidad de instalar múltiples herramientas o visitar diferentes sitios web.

## Características

ToolHub Web v0.2 incluye las siguientes herramientas:

| Herramienta | Descripción |
|-------------|-------------|
| **WHOIS Lookup** | Consulta la información de registro de cualquier dominio |
| **DNS Records** | Verifica todos los registros DNS (A, AAAA, MX, CNAME, TXT, etc.) |
| **IP Lookup / GeoIP** | Obtén información geográfica y de red para cualquier dominio o IP |
| **HTTP Headers** | Analiza las cabeceras HTTP que devuelve un sitio web |
| **SSL Checker** | Verifica y analiza certificados SSL de cualquier dominio |
| **CMS / Tecnologías** | Detecta qué CMS, frameworks o librerías utiliza un sitio web |
| **Blacklist Checker** | Comprueba si un dominio o IP está en listas negras de spam o malware |

### Nuevas Funcionalidades en v0.2

* **Configuración centralizada**: Archivo `config.php` para gestionar todos los ajustes del sistema
* **Gestión de errores mejorada**: Sistema unificado para manejar y mostrar errores de forma consistente
* **Modo offline**: Funcionamiento parcial cuando no hay conexión a Internet
* **Pruebas unitarias**: Framework para garantizar la estabilidad al añadir nuevas funcionalidades
* **Registro de uso**: Sistema opcional (con consentimiento) para registrar consultas realizadas
* **Estadísticas**: Panel administrativo para visualizar el uso de las herramientas
* **Modo oscuro**: Interfaz adaptable para reducir la fatiga visual
* **Seguridad mejorada**: Implementación de protecciones adicionales y mejores prácticas
* **Experiencia mejorada**: Búsqueda rápida, notificaciones de actualización y más

Consulta [DOCUMENTACION.md](DOCUMENTACION.md) para información detallada sobre estas mejoras.

## Requisitos

* Servidor web (Apache, Nginx, etc.)
* PHP 7.4 o superior
* Acceso a la función `shell_exec()` para la herramienta WHOIS
* Extensiones PHP habilitadas: curl, openssl, fileinfo
* Comando `whois` instalado en el servidor (para la herramienta WHOIS)

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/cazique/toolhub-web.git
   ```

2. Sube los archivos a tu servidor web.

3. Asegúrate de que el directorio tenga los permisos adecuados:
   ```bash
   chmod -R 755 toolhub-web
   chmod -R 777 toolhub-web/logs # Solo si quieres activar el registro
   ```

4. Configura los ajustes en `config.php` según tus necesidades.

5. Ejecuta las pruebas unitarias para verificar la instalación:
   ```bash
   ./run_tests.sh
   ```

6. Accede a la suite a través de tu navegador visitando la URL donde hayas alojado los archivos.

## Estructura de archivos

```
toolhub-web/
├── index.php                  # Dashboard principal
├── README.md                  # Este archivo
├── DOCUMENTACION.md           # Documentación detallada de mejoras
├── config.php                 # Configuración centralizada
├── .htaccess                  # Configuración del servidor web
├── includes/                  # Carpeta con todas las herramientas
│   ├── whois.php             # Herramienta WHOIS Lookup
│   ├── dns.php               # Herramienta DNS Records
│   ├── iplookup.php          # Herramienta IP Lookup / GeoIP
│   ├── headers.php           # Herramienta HTTP Headers
│   ├── ssl.php               # Herramienta SSL Checker
│   ├── tech.php              # Herramienta detector de tecnologías
│   ├── blacklist.php         # Herramienta Blacklist Checker
│   ├── error_handler.php     # Sistema de gestión de errores
│   ├── offline_mode.php      # Sistema de modo offline
│   └── usage_logger.php      # Sistema de registro de uso
├── admin/                     # Panel de administración
│   └── stats.php             # Estadísticas de uso
├── logs/                      # Directorio para archivos de registro
│   ├── usage.log             # Registro de uso
│   └── errors.log            # Registro de errores
├── tests/                     # Pruebas unitarias
│   ├── bootstrap.php         # Inicialización para pruebas
│   ├── DomainValidatorTest.php # Pruebas para validación de dominios
│   └── IpValidatorTest.php   # Pruebas para validación de IPs
└── assets/                    # Recursos estáticos
    ├── css/
    │   └── style.css         # Estilos personalizados
    └── js/
        └── script.js         # Scripts personalizados
```

## Personalización

Puedes personalizar ToolHub Web modificando los siguientes archivos:

- `config.php` para cambiar la configuración del sistema.
- `assets/css/style.css` para cambiar el estilo visual.
- `assets/js/script.js` para agregar funcionalidad JavaScript adicional.
- `index.php` para agregar o reordenar las herramientas en el dashboard.

## Limitaciones conocidas

- La herramienta WHOIS requiere que el comando `whois` esté instalado en el servidor.
- El Blacklist Checker actualmente muestra resultados simulados. Para entornos de producción, se recomienda implementar consultas reales a servidores DNSBL.
- La detección de tecnologías/CMS se realiza de forma básica analizando patrones en el código HTML. No es tan completa como soluciones comerciales especializadas.
- El panel de administración usa autenticación básica. Para entornos de producción, se recomienda implementar un sistema de autenticación más robusto.

## Seguridad

Todas las entradas de usuario son validadas y sanitizadas para prevenir ataques XSS e inyección de comandos. Se ha añadido un archivo `.htaccess` con configuraciones de seguridad básicas. Para entornos de producción, se recomienda revisar y ajustar estas configuraciones según las políticas de seguridad específicas.

## Próximas funcionalidades

Para futuras versiones se planea incluir:

- API REST para integración con otras aplicaciones
- Sistema de autenticación robusto con roles de usuario
- Más herramientas de análisis y seguridad
- Soporte para múltiples idiomas
- Caché de resultados para consultas frecuentes
- Informes avanzados con exportación en formatos estándar
- Monitorización programada de dominios y notificaciones

## Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar ToolHub Web, puedes:

1. Hacer fork del repositorio
2. Crear una rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Realizar tus cambios y hacer commit (`git commit -am 'Añadir nueva funcionalidad'`)
4. Subir los cambios a tu fork (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

Por favor, asegúrate de ejecutar las pruebas unitarias antes de enviar tu contribución.

## Licencia

Este proyecto se distribuye bajo licencia MIT. Ver archivo `LICENSE` para más detalles.

## Contacto

Si tienes preguntas o sugerencias, puedes abrir un issue en este repositorio o contactar al autor a través de GitHub.

---

Desarrollado por [cazique](https://github.com/cazique)
