# 🎲 CorRol — Gestor Ágil de Campañas y Recursos de Rol

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="280" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Plataforma integral y colaborativa para directores de juego, creadores de mundos y jugadores de rol.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-11.x%2F12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel Framework">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Cloud_Storage-Backblaze_B2_(S3_API)-0082D5?style=flat-square&logo=amazon-s3&logoColor=white" alt="Backblaze B2">
  <img src="https://img.shields.io/badge/Architecture-SOLID%20%7C%20Clean%20Code-4A154B?style=flat-square" alt="Clean Code">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License">
</p>

---

## 📖 Acerca del Proyecto

Los directores de juego (*Game Masters*) y entusiastas de los juegos de rol (como *RuneQuest*, *Dungeons & Dragons*, *La Llamada de Cthulhu* o *Juego de Dioses*) suelen enfrentarse al problema del contenido fragmentado: hojas de personaje en libretas, manuales en PDF, notas de sesión en procesadores de texto, mapas dispersos en foros y enlaces externos repartidos por la red.

**CorRol** nace para dar soporte directo a la comunidad rolera, ofreciendo un entorno centralizado, intuitivo y moderno donde organizar, consultar, crear y compartir recursos de rol sin fricción. La plataforma permite tanto el uso colaborativo abierto como la gestión privada de campañas personales, combinando la subida de archivos pesados en la nube con herramientas de creación interactiva en el navegador.
Más allá de la gestión de manuales y hojas de personaje, CorRol abraza la narrativa visual del rol integrando un visor interactivo de cómics, pensado para explorar novelas gráficas, lore expandido y material visual que enriquece el trasfondo de las campañas.

**Desarrollador** Ricardo Ocaña Gasco

---

## ✨ Características Principales

### 📁 Gestión Híbrida de Recursos (Archivos y Enlaces)
* **Almacenamiento Cloud Desacoplado:** Integración con **Backblaze B2** mediante la API compatible con Amazon S3. Los archivos subidos no saturan el servidor de la aplicación y se gestionan en buckets privados seguros.
* **Soporte de Enlaces Externos:** Posibilidad de indexar recursos externos con verificación automática de disponibilidad HTTP antes del registro.
* **Procesamiento de Archivos Comprimidos (ZIP):** Lectura e inspección en memoria con `ZipArchive` para extraer el árbol de carpetas y ficheros, guardándolo en formato JSON para comprobación visual sin necesidad de descomprimir en el servidor.
* **Monitor de Espacio en Tiempo Real:** Barra visual de cuota compartida que avisa del consumo total y emite alertas de espacio bajo.

### 👓 Visores Multimedia Integrados
* **Documentos PDF:** Visor con **Mozilla PDF.js** con controles de cambio de página, zoom dinámico y fallback a visor nativo del navegador con barra de miniaturas.
* **Visor Interactivo de Cómics:** Lector inmersivo optimizado para cómics y novelas gráficas. Incluye controles de zoom dinámico y ofrece soporte de lectura dual: visualización clásica de página completa o modo de "lectura guiada" (navegación secuencial aislando elementos específicos de la página).
* **Streaming Proxy Seguro:** Transmisión de bytes controlada por backend para evitar restricciones de CORS y validar permisos de privacidad en tiempo real.
* **Documentos Office:** Integración con visor de Microsoft Office mediante URLs firmadas temporales (`temporaryUrl`) de caducidad automática para documentos privados.
* **Imágenes, Audio y Video:** Reproductores y visualizadores adaptativos con extracción automática de dimensiones, metadatos y MIME-types.

### 🛠️ Herramientas de Creación de Contenido
* **📋 Fichas de PNJ / Personajes:** Editor de bloques enriquecidos (**Editor.js**) con soporte de tablas para estadísticas, atributos y plantillas reutilizables.
* **📖 Diarios de Sesión y Crónicas:** Registro estructurado de eventos de campaña en bloques JSON.
* **🗺️ Mapas Interactivos:** Soporte con **Leaflet.js** sobre proyecciones planas (`CRS.Simple`). Permite a los usuarios colocar pines interactivos con títulos y descripciones sobre cualquier imagen estática de mapa.
* **💬 Mapeador de Viñetas (Guided View):** Herramienta visual de edición que permite a los usuarios trazar las coordenadas de las viñetas (mediante recortes poligonales o rectangulares) sobre las páginas de un cómic. Estos metadatos se almacenan en formato JSON, permitiendo al visor recortar y enfocar dinámicamente cada viñeta en pantalla utilizando `clip-path`.
* **Plantillas Rápidas:** Barra lateral con historial de creaciones anteriores del usuario para clonar o editar con un solo clic.

### 🔍 Búsqueda y Navegación Multifiltro
* Búsqueda global por texto libre (título, descripción, autor, campaña, juego).
* Filtros avanzados por Juego de Rol, Campaña, Autor Original, Tipo de recurso y Etiquetas (Tags JSON).
* Catálogos directos indexados por **Juegos**, **Campañas**, **Autores** y **Comunidad de Usuarios**.

### 🔒 Privacidad, Permisos y Adopción Comunitaria
* **Acceso Público vs. Privado:** Los usuarios anónimos pueden explorar y publicar recursos públicos; los usuarios registrados pueden alternar visibilidad pública o privada.
* **Adopción de Recursos:** Los recursos aportados de forma anónima pueden ser adoptados por cualquier usuario registrado al editarlos, pasando a su propiedad para su custodia y mantenimiento.
* **Diseño Responsivo Completo:** Interfaz en modo oscuro con **Tailwind CSS**, adaptada con menús colapsables optimizados para móviles, tablets y escritorio.

---

## 🏗️ Arquitectura de Software y Patrones de Diseño

El proyecto ha sido concebido bajo estrictos principios de ingeniería de software para garantizar escalabilidad, legibilidad y desacoplamiento:

```
[ Petición HTTP ] 
       │
       ▼
[ ResourceController / SheetController / MapController ] (Skinny Controllers - SRP)
       │
       ├──► (DIP) [ ResourceUploadService ] 
       │                 ├──► [ Strategy Pattern: FileProcessorInterface ]
       │                 │         ├── ZipProcessor (Extracción ZipArchive)
       │                 │         ├── ImageProcessor (Dimensiones/Resolución)
       │                 │         ├── PdfProcessor
       │                 │         └── DefaultProcessor
       │                 └──► [ Storage Driver: Backblaze B2 (S3 API) ]
       │
       └──► (DIP) [ Repository Pattern: ResourceRepositoryInterface ]
                         │
                         ▼
                   [ ResourceRepository (Eloquent ORM) ]
                         │
                         ▼ (MySQL con prefijo de tablas)
             ┌────────────────────────────────────────┐
             │       Tabla Central: cr_resources      │  <── Relación Polimórfica
             └───────────────────┬────────────────────┘
                                 │
        ┌────────────────────────┼────────────────────────┐
        ▼                        ▼                        ▼
[ cr_resource_files ]   [ cr_resource_sheets ]   [ cr_resource_maps ]
```

### 1. Principios SOLID
* **Single Responsibility Principle (SRP):** Los controladores son delgados (*Skinny Controllers*); solo validan la petición HTTP mediante `FormRequest`, delegan al servicio de aplicación y devuelven la vista.
* **Dependency Inversion Principle (DIP):** Los servicios y controladores dependen de abstracciones (`ResourceRepositoryInterface`, `FileProcessorInterface`), inyectadas a través del *Service Container* de Laravel en `AppServiceProvider`.
* **Open/Closed Principle (OCP):** Nuevos formatos de archivo pueden soportarse implementando `FileProcessorInterface` sin modificar la lógica del servicio ni de los controladores.

### 2. Patrones de Diseño
* **Repository Pattern:** Desacopla la lógica de acceso a datos de la capa de negocio.
* **Strategy Pattern:** Procesamiento polimórfico de archivos en función de su extensión y tipo.
* **Polimorfismo Eloquent (`MorphTo` / `MorphOne`):** Una tabla central `resources` almacena metadatos y unifica el motor de búsqueda, mientras tablas satélite (`resource_files`, `resource_sheets`, `resource_maps`) guardan los atributos específicos de cada formato.

---

## 🚀 Requisitos del Sistema

* **PHP:** $\ge$ 8.3 con extensiones: `pdo_mysql`, `mbstring`, `xml`, `zip`, `fileinfo`, `gd`, `openssl`, `curl`.
* **Base de Datos:** MySQL 8.0+ / MariaDB 10.4+.
* **Gestor de Dependencias:** [Composer](https://getcomposer.org/).
* **Almacenamiento Cloud (Opcional para subidas directas):** Cuenta de [Backblaze B2](https://www.backblaze.com/) o compatible con Amazon S3.

---

## 💻 Instalación y Puesta en Marcha Local

### Opción A: Despliegue Automatizado e Interactivo (Recomendado en Linux)

El proyecto incluye un script Bash (`deploy.sh`) que realiza la instalación, verificación de dependencias, configuración de base de datos, compilación de assets con Vite y la reparación automática de permisos/AppArmor en servidores Apache/Linux:

```bash
chmod +x deploy.sh
./deploy.sh
```

El script se encargará de:
1. Detectar e instalar dependencias faltantes (`composer`, `npm`, `mysql`).
2. Configurar el archivo `.env` e importar archivos `.sql` si es necesario.
3. Generar la clave de aplicación y compilar el frontend con Vite (`npm run build`).
4. Resolver problemas de permisos en `/home/` y AppArmor redirigiendo las vistas compiladas a `/tmp/corrol_views`.
5. Crear symlinks web y limpiar cachés de Laravel.

---

### Opción B: Instalación Manual paso a paso

#### 1. Clonar el repositorio
```bash
git clone https://github.com/rocacua/corrol.git
cd corrol
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
Copia el archivo de ejemplo y genera la clave de cifrado:
```bash
cp .env.example .env
php artisan key:generate
```

Edita el archivo `.env` con los datos de tu base de datos y tus credenciales de Backblaze B2:
```env
APP_NAME="CorRol"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=corrol_db
DB_USERNAME=root
DB_PASSWORD=
DB_PREFIX=cr_

# Backblaze B2 (API S3)
BACKBLAZE_KEY_ID=tu_key_id
BACKBLAZE_APPLICATION_KEY=tu_application_key
BACKBLAZE_REGION=eu-central-003
BACKBLAZE_BUCKET=tu-bucket
BACKBLAZE_ENDPOINT=https://s3.eu-central-003.backblazeb2.com
```

### 4. Ejecutar migraciones
```bash
php artisan migrate
```

### 5. Iniciar el servidor de desarrollo
```bash
php artisan serve
```
La aplicación estará disponible en `http://localhost:8000`.

---

## 🔧 Resolución de Problemas Frecuentes en Linux / Apache

Si despliegas CorRol bajo Apache en un entorno local de desarrollo (por ejemplo `http://rocanyaweb.local/corrol`), es posible encontrar los siguientes errores comunes:

### 1. `tempnam(): file created in the system's temporary directory` (Error HTTP 500)
* **Causa:** Apache (`www-data`) no tiene permisos para escribir en `/home/usuario/.../storage/framework/views` debido a restricciones del módulo de seguridad **AppArmor** o permisos de travesía en carpetas personales.
* **Solución:**
  1. Define en tu archivo `.env`: `VIEW_COMPILED_PATH=/tmp/corrol_views`
  2. Crea la carpeta y dale permisos: `mkdir -p /tmp/corrol_views && chmod 777 /tmp/corrol_views`
  3. Limpia las vistas: `php artisan view:clear && php artisan config:clear`

### 2. `ViteManifestNotFoundException` (Falta `manifest.json`)
* **Causa:** Los assets de Vite no han sido compilados.
* **Solución:** Ejecuta `npm install && npm run build`.

### 3. Error de Escritura en `laravel.log` (`Monolog StreamHandler`)
* **Causa:** Propietario de la carpeta `storage/logs` incorrecto.
* **Solución:** `sudo chown -R $USER:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache`.

---

## 🗄️ Estructura de la Base de Datos

Todas las tablas utilizan el prefijo configurable `cr_` para convivir de forma aislada en entornos de hosting compartido:

* **`cr_resources`**: Tabla principal polimórfica (título, descripción, privacidad, tipo, juego, campaña, autor, etiquetas JSON, metadatos de cómic interactivo, relaciones `resourceable`).
* **`cr_resource_files`**: Registros de ficheros (ruta/URL, peso, MIME-type, si es externo, árbol JSON de ZIPs y dimensiones de imagen).
* **`cr_resource_sheets`**: Fichas de personaje, diarios y campañas en formato JSON estructurado (Editor.js).
* **`cr_resource_maps`**: Mapas interactivos (URL de imagen base y array JSON de pines X/Y con descripciones).
* **`cr_users`**: Usuarios registrados y autenticación.

---

## 🌐 Despliegue en Servidores Compartidos (Apache / SFTP)

CorRol está optimizado para funcionar en servidores compartidos sin necesidad de acceso a terminal Apache:

1. **Aislamiento de Código:** La carpeta del proyecto puede ubicarse fuera del directorio web público (`public_html` o `public`).
2. **Enlace Simbólico:** Se expone únicamente la carpeta `corrol/public` en el directorio web mediante un enlace simbólico (`symlink`), garantizando la máxima seguridad del código fuente.
3. **Optimización para Producción:**
   ```bash
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

---

## 📜 Licencia

Este proyecto está bajo la licencia [MIT](LICENSE). Siéntete libre de utilizarlo, modificarlo y adaptarlo para tus propias partidas y proyectos roleros.

---

<p align="center">
  Hecho con 🎲 y dedicación para la comunidad de rol.
</p>
