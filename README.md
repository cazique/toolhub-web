```
████████╗ ██████╗  ██████╗ ██╗     ██╗  ██╗██╗   ██╗██████╗     ██╗    ██╗███████╗██████╗ 
╚══██╔══╝██╔═══██╗██╔═══██╗██║     ██║  ██║██║   ██║██╔══██╗    ██║    ██║██╔════╝██╔══██╗
   ██║   ██║   ██║██║   ██║██║     ███████║██║   ██║██████╔╝    ██║ █╗ ██║█████╗  ██████╔╝
   ██║   ██║   ██║██║   ██║██║     ██╔══██║██║   ██║██╔══██╗    ██║███╗██║██╔══╝  ██╔══██╗
   ██║   ╚██████╔╝╚██████╔╝███████╗██║  ██║╚██████╔╝██████╔╝    ╚███╔███╔╝███████╗██████╔╝
   ╚═╝    ╚═════╝  ╚═════╝ ╚══════╝╚═╝  ╚═╝ ╚═════╝ ╚═════╝      ╚══╝╚══╝ ╚══════╝╚═════╝ 
                                                                                           
```

# ToolHub Web v0.1

## Descripción

ToolHub Web es una suite de utilidades técnicas de código abierto para análisis de dominios, IPs y seguridad web. Ofrece una colección de herramientas accesibles a través de una interfaz web moderna, diseñada con Bootstrap 5.

Esta suite está pensada para administradores de sistemas, desarrolladores web, especialistas en SEO y profesionales de ciberseguridad que necesiten realizar análisis rápidos de dominios e IPs sin necesidad de instalar múltiples herramientas o visitar diferentes sitios web.

## Características

ToolHub Web v0.1 incluye las siguientes herramientas:

| Herramienta | Descripción |
|-------------|-------------|
| **WHOIS Lookup** | Consulta la información de registro de cualquier dominio |
| **DNS Records** | Verifica todos los registros DNS (A, AAAA, MX, CNAME, TXT, etc.) |
| **IP Lookup / GeoIP** | Obtén información geográfica y de red para cualquier dominio o IP |
| **HTTP Headers** | Analiza las cabeceras HTTP que devuelve un sitio web |
| **SSL Checker** | Verifica y analiza certificados SSL de cualquier dominio |
| **CMS / Tecnologías** | Detecta qué CMS, frameworks o librerías utiliza un sitio web |
| **Blacklist Checker** | Comprueba si un dominio o IP está en listas negras de spam o malware |

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
   ```

4. Accede a la suite a través de tu navegador visitando la URL donde hayas alojado los archivos.

## Estructura de archivos

```
toolhub-web/
├── index.php                  # Dashboard principal
├── README.md                  # Este archivo
├── includes/                  # Carpeta con todas las herramientas
│   ├── whois.php             # Herramienta WHOIS Lookup
│   ├── dns.php               # Herramienta DNS Records
│   ├── iplookup.php          # Herramienta IP Lookup / GeoIP
│   ├── headers.php           # Herramienta HTTP Headers
│   ├── ssl.php               # Herramienta SSL Checker
│   ├── tech.php              # Herramienta detector de tecnologías
│   └── blacklist.php         # Herramienta Blacklist Checker
└── assets/                    # Recursos estáticos
    ├── css/
    │   └── style.css         # Estilos personalizados
    └── js/
        └── script.js         # Scripts personalizados
```

## Personalización

Puedes personalizar ToolHub Web modificando los siguientes archivos:

- `assets/css/style.css` para cambiar el estilo visual.
- `assets/js/script.js` para agregar funcionalidad JavaScript adicional.
- `index.php` para agregar o reordenar las herramientas en el dashboard.

## Limitaciones conocidas

- La herramienta WHOIS requiere que el comando `whois` esté instalado en el servidor.
- El Blacklist Checker actualmente muestra resultados simulados. Para entornos de producción, se recomienda implementar consultas reales a servidores DNSBL.
- La detección de tecnologías/CMS se realiza de forma básica analizando patrones en el código HTML. No es tan completa como soluciones comerciales especializadas.

## Seguridad

Todas las entradas de usuario son validadas y sanitizadas para prevenir ataques XSS e inyección de comandos. Sin embargo, se recomienda implementar medidas de seguridad adicionales si planeas utilizar esta suite en un entorno de producción público.

## Próximas funcionalidades

Para futuras versiones se planea incluir:

- Historial de consultas realizadas
- Exportación de resultados en diferentes formatos
- Comparativa entre dominios
- Integración con APIs externas para análisis más detallados
- Analizador de velocidad y rendimiento web
- Panel de administración y configuración

## Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar ToolHub Web, puedes:

1. Hacer fork del repositorio
2. Crear una rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`)
3. Realizar tus cambios y hacer commit (`git commit -am 'Añadir nueva funcionalidad'`)
4. Subir los cambios a tu fork (`git push origin feature/nueva-funcionalidad`)
5. Crear un Pull Request

## Licencia

Este proyecto se distribuye bajo licencia MIT. Ver archivo `LICENSE` para más detalles.

## Contacto

Si tienes preguntas o sugerencias, puedes abrir un issue en este repositorio o contactar al autor a través de GitHub.

---

Desarrollado por [cazique](https://github.com/cazique)
