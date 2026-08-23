#!/usr/bin/env bash

# ==============================================================================
# 🎲 CorRol — Asistente Universal de Despliegue, Instalación y Diagnóstico (Linux/macOS)
# ==============================================================================

set -e

SEGUNDOS_INICIO=$(date +"%s")
HORA_INICIO_HUMANA=$(date +%H:%M:%S)

# Colores ANSI
ROJO=$'\e[31m'
VERDE=$'\e[32m'
AZUL=$'\e[34m'
CIAN=$'\e[36m'
AMARILLO=$'\e[33m'
MAGENTA=$'\033[0;35m'
NC=$'\e[0m'
ROJO_B=$'\e[1;31m'
VERDE_B=$'\e[1;32m'
CIAN_B=$'\e[1;36m'
AMARILLO_B=$'\e[1;33m'
MAGENTA_B=$'\033[1;35m'

pintar() {
    local texto="$1"
    local tipo="${2:-normal}"
    local salto="${3:-1}"
    local color=""
    local etiqueta=""

    case "$tipo" in
        "error")    color="$ROJO_B";     etiqueta="[ERROR] " ;;
        "exito")    color="$VERDE_B";    etiqueta="[OK] " ;;
        "alerta")   color="$AMARILLO_B"; etiqueta="[ALERTA] " ;;
        "menu")     color="$CIAN_B";     etiqueta="[ASISTENTE] " ;;
        "prompt")   color="$MAGENTA_B";  etiqueta="[PREGUNTA] " ;;
        *)          color="$NC";         etiqueta="" ;;
    esac

    if [[ $salto -eq 0 ]]; then
        printf '%b' "${color}${etiqueta}${texto}${NC}"
    else
        if [ "$tipo" = "error" ]; then
            echo -e "${color}${etiqueta}${texto}${NC}" >&2
        else
            echo -e "${color}${etiqueta}${texto}${NC}"
        fi
    fi
}

confirm() {
    read -p "$(pintar "$1 [S/n]: " "prompt" 0)" choice
    case "$choice" in 
        [nN][oO]|[nN]) return 1 ;;
        *) return 0 ;;
    esac
}

banner() {
    echo -e "${CIAN_B}"
    echo "======================================================================"
    echo "   🎲 CorRol — Despliegue Universal Local (Cero a Producción)"
    echo "   Hora de inicio: $HORA_INICIO_HUMANA"
    echo "======================================================================"
    echo -e "${NC}"
}

banner

# ==============================================================================
# PASO 1: DETECCIÓN DE SISTEMA OPERATIVO Y GESTOR DE PAQUETES
# ==============================================================================
REAL_USER=${SUDO_USER:-$USER}
DIR_SCRIPT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
REPO_CORROL_GIT="https://github.com/rocacua/corrol.git"

TIENE_PROYECTO="n"
DIR_PROYECTO=""

es_proyecto_corrol() {
    local ruta="$1"
    [ -f "$ruta/artisan" ] && [ -f "$ruta/composer.json" ] && [ -d "$ruta/app" ]
}

expandir_ruta() {
    local ruta="$1"

    case "$ruta" in
        '~') ruta="$HOME" ;;
        '~/'*) ruta="$HOME/${ruta#\~/}" ;;
    esac

    if command -v realpath &>/dev/null; then
        realpath -m "$ruta"
    else
        readlink -m "$ruta"
    fi
}

if es_proyecto_corrol "$DIR_SCRIPT"; then
    TIENE_PROYECTO="s"
    DIR_PROYECTO="$DIR_SCRIPT"
elif es_proyecto_corrol "$DIR_SCRIPT/corrol"; then
    TIENE_PROYECTO="s"
    DIR_PROYECTO="$DIR_SCRIPT/corrol"
elif es_proyecto_corrol "$PWD"; then
    TIENE_PROYECTO="s"
    DIR_PROYECTO="$PWD"
fi

if [ "$TIENE_PROYECTO" = "s" ]; then
    pintar "Proyecto Laravel CorRol detectado en: $DIR_PROYECTO" "exito"
else
    pintar "No se detectaron los archivos del proyecto en el entorno actual. Se clonará desde GitHub." "alerta"
fi

if [ "$(uname)" == "Darwin" ]; then
    OS="macOS"
    INSTALL_CMD="brew install"
    SUDO=""
    UPDATE_CMD="brew update"
    WEB_USER="_www"
    APACHE_CONF_DIR="/usr/local/etc/httpd/extra"
    APACHE_RESTART_CMD="sudo apachectl restart"
    APACHE_LOG_DIR="/usr/local/var/log/httpd"
    MYSQL_SERVICE_CMD="brew services start mysql"
else
    if [ -f /etc/os-release ]; then
        . /etc/os-release
    else
        pintar "No se pudo determinar el S.O. (/etc/os-release no existe)." "error"
        exit 1
    fi

    ALL_IDS="${ID} ${ID_LIKE}"
    ALL_IDS="${ALL_IDS,,}"

    if [[ "$ALL_IDS" =~ "ubuntu" || "$ALL_IDS" =~ "debian" || "$ALL_IDS" =~ "linuxmint" || "$ALL_IDS" =~ "pop" ]]; then
        OS="Debian-based"
        INSTALL_CMD="apt-get install -y"
        SUDO="sudo"
        UPDATE_CMD="apt-get update"
        WEB_USER="www-data"
        APACHE_CONF_DIR="/etc/apache2/sites-available"
        APACHE_RESTART_CMD="sudo systemctl restart apache2"
        APACHE_LOG_DIR="/var/log/apache2"
        MYSQL_SERVICE_CMD="sudo systemctl enable --now mariadb 2>/dev/null || sudo systemctl enable --now mysql"
    elif [[ "$ALL_IDS" =~ "fedora" || "$ALL_IDS" =~ "rhel" || "$ALL_IDS" =~ "centos" || "$ALL_IDS" =~ "rocky" ]]; then
        OS="Fedora-based"
        INSTALL_CMD="dnf install -y"
        SUDO="sudo"
        UPDATE_CMD="dnf check-update"
        WEB_USER="apache"
        APACHE_CONF_DIR="/etc/httpd/conf.d"
        APACHE_RESTART_CMD="sudo systemctl restart httpd"
        APACHE_LOG_DIR="/var/log/httpd"
        MYSQL_SERVICE_CMD="sudo systemctl enable --now mariadb 2>/dev/null || sudo systemctl enable --now mysqld"
    elif [[ "$ALL_IDS" =~ "arch" || "$ALL_IDS" =~ "manjaro" || "$ALL_IDS" =~ "endeavouros" ]]; then
        OS="Arch-based"
        INSTALL_CMD="pacman -S --noconfirm"
        SUDO="sudo"
        UPDATE_CMD="pacman -Sy"
        WEB_USER="http"
        APACHE_CONF_DIR="/etc/httpd/conf/extra"
        APACHE_RESTART_CMD="sudo systemctl restart httpd"
        APACHE_LOG_DIR="/var/log/httpd"
        MYSQL_SERVICE_CMD="sudo systemctl enable --now mariadb"
    elif [[ "$ALL_IDS" =~ "suse" || "$ALL_IDS" =~ "opensuse" ]]; then
        OS="SUSE-based"
        INSTALL_CMD="zypper --non-interactive install"
        SUDO="sudo"
        UPDATE_CMD="zypper refresh"
        WEB_USER="apache"
        APACHE_CONF_DIR="/etc/apache2/vhosts.d"
        APACHE_RESTART_CMD="sudo systemctl restart apache2"
        APACHE_LOG_DIR="/var/log/apache2"
        MYSQL_SERVICE_CMD="sudo systemctl enable --now mariadb"
    else
        OS="Desconocido"
        INSTALL_CMD=""
        SUDO="sudo"
        WEB_USER="www-data"
        MYSQL_SERVICE_CMD=""
    fi
fi

pintar "S.O. Detectado: $OS ($PRETTY_NAME)" "exito"

# ==============================================================================
# PASO 2: DIAGNÓSTICO E INSTALACIÓN DE DEPENDENCIAS GLOBALES (MÉTODO LIMPIO)
# ==============================================================================
pintar "➜ Analizando dependencias del sistema para Laravel..." "menu"

instalar_paquetes_faltantes() {
    local faltan_herramientas=0

    # Comprobar Apache
    if [ ! -x /usr/sbin/apache2 ] && [ ! -x /usr/sbin/httpd ] && ! command -v apache2 &>/dev/null && ! command -v httpd &>/dev/null; then
        pintar "Apache no detectado." "alerta"
        faltan_herramientas=1
    fi

    # Comprobar PHP
    if ! command -v php &>/dev/null; then
        pintar "PHP no detectado." "alerta"
        faltan_herramientas=1
    fi

    # Comprobar Composer
    if ! command -v composer &>/dev/null; then
        pintar "Composer no detectado." "alerta"
        faltan_herramientas=1
    fi

    # Comprobar NodeJS / NPM
    if ! command -v node &>/dev/null || ! command -v npm &>/dev/null; then
        pintar "Node/NPM no detectado." "alerta"
        faltan_herramientas=1
    fi

    # Comprobar MySQL / MariaDB Server/Client
    if ! command -v mysql &>/dev/null && ! command -v mariadb &>/dev/null; then
        pintar "MySQL/MariaDB no detectado en el sistema." "alerta"
        faltan_herramientas=1
    fi

    if [ "$faltan_herramientas" -eq 1 ]; then
        if confirm "¿Deseas instalar automáticamente la pila completa (Apache, PHP, Composer, Node/NPM, MariaDB/MySQL Server)?"; then
            pintar "Instalando paquetes requeridos según tu distribución..." "menu"
            if [ "$OS" = "Debian-based" ]; then
                $SUDO apt-get update
                $SUDO apt-get install -y apache2 mariadb-server mariadb-client php php-cli php-mysql php-sqlite3 php-gd php-xml php-mbstring php-curl php-zip php-bcmath unzip curl git nodejs npm
            elif [ "$OS" = "Fedora-based" ]; then
                $SUDO dnf install -y httpd mariadb-server mariadb php php-cli php-mysqlnd php-pdo php-sqlite3 php-gd php-xml php-mbstring php-pecl-zip php-bcmath unzip curl git nodejs npm
            elif [ "$OS" = "Arch-based" ]; then
                $SUDO pacman -Sy --noconfirm apache mariadb php php-apache php-gd php-intl unzip curl git nodejs npm
                $SUDO mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql 2>/dev/null || true
            elif [ "$OS" = "SUSE-based" ]; then
                $SUDO zypper --non-interactive install apache2 mariadb mariadb-client php8 php8-mysql php8-gd php8-xmlreader php8-mbstring php8-zip php8-curl unzip curl git nodejs npm
            elif [ "$OS" = "macOS" ]; then
                brew install httpd php composer node mysql
            fi

            # Iniciar e iniciar el servicio MySQL / MariaDB
            if [ -n "$MYSQL_SERVICE_CMD" ]; then
                pintar "➜ Activando y arrancando el servicio de Base de Datos..." "menu"
                eval "$MYSQL_SERVICE_CMD" 2>/dev/null || true
            fi
        else
            pintar "Continuando bajo tu propia responsabilidad con las herramientas actuales..." "alerta"
        fi
    else
        pintar "✓ Todas las herramientas de sistema necesarias están instaladas." "exito"
    fi

    # Forzar el arranque del servicio de MySQL en todo momento
    if [ -n "$MYSQL_SERVICE_CMD" ]; then
        eval "$MYSQL_SERVICE_CMD" 2>/dev/null || true
    fi

    # Composer fallback si no está en el path global
    if ! command -v composer &> /dev/null; then
        if [ ! -f "/usr/local/bin/composer" ]; then
            pintar "Descargando Composer localmente..." "menu"
            curl -sS https://getcomposer.org/installer | php 2>/dev/null
            chmod +x composer.phar
            $SUDO mv composer.phar /usr/local/bin/composer 2>/dev/null || true
        fi
    fi
}

instalar_paquetes_faltantes

# ==============================================================================
# PASO 3: CONFIGURACIÓN DE RUTA DEL PROYECTO Y URL
# ==============================================================================
echo ""
pintar "=== CONFIGURACIÓN DE UBICACIÓN DEL PROYECTO Y URL ===" "menu"

RUTA_ORIGEN="$DIR_PROYECTO"

# Definir la ruta por defecto según si se detectó el proyecto o no
if [ "$TIENE_PROYECTO" = "s" ]; then
    RUTA_DEFECTO="$DIR_PROYECTO"
else
    # Si no tiene proyecto, por defecto es un directorio "corrol" en el directorio actual
    RUTA_DEFECTO="$PWD/corrol"
fi

read -p "$(pintar "Ruta donde deseas instalar/ubicar el proyecto [$RUTA_DEFECTO]: " "prompt" 0)" RUTA_DESTINO
RUTA_DESTINO="${RUTA_DESTINO:-$RUTA_DEFECTO}"
# Convertir a ruta absoluta
RUTA_DESTINO=$(expandir_ruta "$RUTA_DESTINO")

# Intentar crear el directorio si no existe
if [ ! -d "$RUTA_DESTINO" ]; then
    pintar "➜ Creando directorio de destino: $RUTA_DESTINO..." "menu"
    if ! mkdir -p "$RUTA_DESTINO" 2>/dev/null; then
        pintar "No se tienen permisos para crear la ruta '$RUTA_DESTINO'." "alerta"
        if confirm "¿Deseas crearlo usando privilegios elevados (sudo)?"; then
            $SUDO mkdir -p "$RUTA_DESTINO"
            $SUDO chown -R "$REAL_USER" "$RUTA_DESTINO"
        else
            pintar "No se pudo crear la ruta de instalación. Abortando despliegue." "error"
            exit 1
        fi
    fi
fi

# Acción según si ya tiene el proyecto descargado localmente o hay que clonarlo
if [ "$TIENE_PROYECTO" = "s" ]; then
    # Si la ruta elegida es diferente de donde se encuentra el script con el proyecto, copiar los archivos
    if [ "$RUTA_DESTINO" != "$RUTA_ORIGEN" ]; then
        pintar "➜ Copiando archivos de CorRol hacia $RUTA_DESTINO..." "menu"
        $SUDO chown -R "$REAL_USER" "$RUTA_DESTINO" 2>/dev/null || true
        if command -v rsync &>/dev/null; then
            rsync -a --delete --exclude '.git/' "$RUTA_ORIGEN/" "$RUTA_DESTINO/"
        else
            find "$RUTA_ORIGEN" -mindepth 1 -maxdepth 1 ! -name '.git' -exec cp -a {} "$RUTA_DESTINO/" \;
        fi
    fi
else
    # No tiene el proyecto: Realizar git clone directo del repositorio en la ruta destino
    pintar "➜ No se encontró el proyecto de manera local. Clonando CorRol desde GitHub..." "menu"
    if es_proyecto_corrol "$RUTA_DESTINO"; then
        pintar "El proyecto ya existe en la carpeta destino; se reutilizará." "alerta"
    elif [ -n "$(find "$RUTA_DESTINO" -mindepth 1 -maxdepth 1 -print -quit 2>/dev/null)" ]; then
        pintar "La carpeta destino no está vacía y no contiene un proyecto CorRol válido." "error"
        exit 1
    else
        # Asegurarse de tener git instalado en caliente si falta
        if ! command -v git &>/dev/null; then
            pintar "Instalando git..." "menu"
            if [ "$OS" = "Debian-based" ]; then
                $SUDO apt-get update && $SUDO apt-get install -y git
            elif [ "$OS" = "Fedora-based" ]; then
                $SUDO dnf install -y git
            elif [ "$OS" = "Arch-based" ]; then
                $SUDO pacman -Sy --noconfirm git
            elif [ "$OS" = "SUSE-based" ]; then
                $SUDO zypper --non-interactive install git
            fi
        fi
        git clone "$REPO_CORROL_GIT" "$RUTA_DESTINO"
        pintar "✓ Clonado completado con éxito." "exito"
    fi
fi

cd "$RUTA_DESTINO" || {
    pintar "No se puede acceder al directorio '$RUTA_DESTINO'. Saliendo." "error"
    exit 1
}
pintar "Directorio de trabajo activo: $(pwd)" "exito"

read -p "$(pintar "URL deseada para CorRol [http://corrol.test/]: " "prompt" 0)" URL_CORROL
URL_CORROL="${URL_CORROL:-http://corrol.test/}"

# Extraer el host y el subpath (ej. rocanyaweb.local y /corrol)
DOMINIO_LIMPIO=$(echo "$URL_CORROL" | sed -e 's|^[^/]*//||' -e 's|/.*||')
SUB_PATH=$(echo "$URL_CORROL" | sed -e "s|http[s]*://${DOMINIO_LIMPIO}||")

# Registrar en /etc/hosts si no existe
if ! grep -q "$DOMINIO_LIMPIO" /etc/hosts 2>/dev/null; then
    pintar "➜ Mapeando $DOMINIO_LIMPIO en /etc/hosts..." "menu"
    echo "127.0.0.1   $DOMINIO_LIMPIO" | $SUDO tee -a /etc/hosts > /dev/null
    pintar "Dominio $DOMINIO_LIMPIO añadido a /etc/hosts." "exito"
fi

# Configurar Apache para que el dominio apunte al public/ de Laravel.
configurar_apache() {
    local ruta_conf
    local apachectl_bin=""
    local subpath="${SUB_PATH:-/}"
    local nombre_sitio="corrol-${DOMINIO_LIMPIO//[^a-zA-Z0-9_.-]/_}.conf"

    if [ -x /usr/sbin/apache2ctl ]; then
        apachectl_bin="/usr/sbin/apache2ctl"
    elif [ -x /usr/sbin/apachectl ]; then
        apachectl_bin="/usr/sbin/apachectl"
    elif command -v apache2ctl &>/dev/null; then
        apachectl_bin="$(command -v apache2ctl)"
    elif command -v apachectl &>/dev/null; then
        apachectl_bin="$(command -v apachectl)"
    fi

    if [ -z "$apachectl_bin" ]; then
        pintar "Apache no está instalado; se omite su configuración." "alerta"
        return 0
    fi

    if [ "$subpath" != "/" ]; then
        pintar "URL con subruta detectada ($subpath); se usará el enlace simbólico del directorio público." "menu"
        return 0
    fi

    if [ "$OS" = "Debian-based" ]; then
        ruta_conf="$APACHE_CONF_DIR/$nombre_sitio"
    elif [ "$OS" = "Fedora-based" ] || [ "$OS" = "Arch-based" ] || [ "$OS" = "SUSE-based" ]; then
        ruta_conf="$APACHE_CONF_DIR/$nombre_sitio"
    elif [ "$OS" = "macOS" ]; then
        ruta_conf="$APACHE_CONF_DIR/$nombre_sitio"
    else
        pintar "Distribución no reconocida; se omite la configuración automática de Apache." "alerta"
        return 0
    fi

    pintar "➜ Configurando Apache: $DOMINIO_LIMPIO -> $(pwd)/public..." "menu"
    $SUDO tee "$ruta_conf" > /dev/null <<EOF
# CorRol - generado por deploy.sh
<VirtualHost *:80>
    ServerName $DOMINIO_LIMPIO
    DocumentRoot $(pwd)/public

    DirectoryIndex index.php
    RewriteEngine On
    RewriteRule ^/(build|assets)(/|$) - [END]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    Alias /build/ $(pwd)/public/build/
    Alias /assets/ $(pwd)/public/assets/

    <Directory "$(pwd)/public">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <Directory "$(pwd)/public/build">
        Require all granted
    </Directory>
    <Directory "$(pwd)/public/assets">
        Require all granted
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/corrol_error.log
    CustomLog \${APACHE_LOG_DIR}/corrol_access.log combined
</VirtualHost>
EOF

    if [ "$OS" = "Debian-based" ]; then
        if [ -x /usr/sbin/a2enmod ]; then
            $SUDO /usr/sbin/a2enmod rewrite >/dev/null
        elif command -v a2enmod &>/dev/null; then
            $SUDO "$(command -v a2enmod)" rewrite >/dev/null
        fi
        if [ -x /usr/sbin/a2ensite ]; then
            $SUDO /usr/sbin/a2ensite "$nombre_sitio" >/dev/null
        elif command -v a2ensite &>/dev/null; then
            $SUDO "$(command -v a2ensite)" "$nombre_sitio" >/dev/null
        fi
        if [ ! -e "/etc/apache2/sites-enabled/$nombre_sitio" ]; then
            pintar "No se pudo activar $nombre_sitio en sites-enabled." "error"
            return 1
        fi
    fi

    if ! $SUDO "$apachectl_bin" configtest 2>&1; then
        pintar "La configuración de Apache no es válida; se conserva el archivo para revisarlo." "error"
        return 1
    fi

    if ! eval "$APACHE_RESTART_CMD"; then
        pintar "No se pudo reiniciar Apache automáticamente." "alerta"
        return 1
    fi
    pintar "Configuración de Apache aplicada correctamente." "exito"
}

configurar_apache

# ==============================================================================
# PASO 4: CONFIGURACIÓN DEL ARCHIVO .ENV
# ==============================================================================
echo ""
pintar "=== CONFIGURACIÓN DEL ENTORNO LARAVEL (.env) ===" "menu"

if [ ! -f ".env" ]; then
    if [ -f ".env.development" ]; then
        cp .env.development .env
        pintar "Copiado .env.development -> .env" "exito"
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        pintar "Copiado .env.example -> .env" "exito"
    else
        touch .env
    fi
fi

actualizar_env() {
    local clave="$1"
    local valor="$2"
    local valor_sed
    valor_sed=$(printf '%s' "$valor" | sed 's/[\\&|]/\\&/g')

    if grep -q "^${clave}=" .env; then
        sed -i "s|^${clave}=.*|${clave}=${valor_sed}|" .env
    elif grep -q "^# *${clave}=" .env; then
        sed -i "s|^# *${clave}=.*|${clave}=${valor_sed}|" .env
    else
        printf '\n%s=%s\n' "$clave" "$valor" >> .env
    fi
}

# Inyección de URL y parche definitivo para vistas de Blade (/tmp)
actualizar_env "APP_URL" "$URL_CORROL"

if ! grep -q "VIEW_COMPILED_PATH" .env; then
    echo -e "\n# Vistas compiladas en /tmp para evitar restricciones de AppArmor/SELinux en /home/" >> .env
    echo "VIEW_COMPILED_PATH=/tmp/corrol_views" >> .env
    pintar "Añadida directiva VIEW_COMPILED_PATH=/tmp/corrol_views al .env" "exito"
fi

# ==============================================================================
# PASO 5: BASE DE DATOS (MYSQL)
# ==============================================================================
echo ""
pintar "=== CONFIGURACIÓN DE BASE DE DATOS ===" "menu"

if confirm "¿Deseas configurar o importar la Base de Datos ahora?"; then
    read -p "$(pintar "Host MySQL [127.0.0.1]: " "prompt" 0)" DB_HOST
    DB_HOST="${DB_HOST:-127.0.0.1}"

    read -p "$(pintar "Nombre de Base de Datos [corrol]: " "prompt" 0)" DB_NAME
    DB_NAME="${DB_NAME:-corrol}"

    read -p "$(pintar "Usuario MySQL [admin]: " "prompt" 0)" DB_USER
    DB_USER="${DB_USER:-admin}"

    echo -n "$(pintar "Contraseña MySQL [admin]: " "prompt" 0)"
    read -s DB_PASS
    echo ""
    DB_PASS="${DB_PASS:-admin}"

    read -p "$(pintar "Ruta a archivo .sql inicial (presiona Intro si no tienes): " "prompt" 0)" SQL_PATH

    # Actualizar variables en el archivo .env
    actualizar_env "DB_CONNECTION" "mysql"
    actualizar_env "DB_HOST" "$DB_HOST"
    actualizar_env "DB_PORT" "3306"
    actualizar_env "DB_DATABASE" "$DB_NAME"
    actualizar_env "DB_USERNAME" "$DB_USER"
    actualizar_env "DB_PASSWORD" "$DB_PASS"

    pintar "➜ Comprobando / Creando base de datos '$DB_NAME' en MySQL..." "menu"
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
        DB_LISTA="mysql"
    else
        pintar "No se pudo conectar a MySQL con esas credenciales. Verifica que el servicio MySQL esté activo." "alerta"
        pintar "➜ Se usará SQLite local para completar el despliegue." "menu"
        actualizar_env "DB_CONNECTION" "sqlite"
        actualizar_env "DB_DATABASE" "$(pwd)/database/database.sqlite"
        DB_LISTA="sqlite"
        SQL_PATH=""
        mkdir -p database
        touch database/database.sqlite
    fi

    if [ "$DB_LISTA" = "mysql" ] && [ -n "$SQL_PATH" ] && [ -f "$SQL_PATH" ]; then
        pintar "➜ Importando archivo SQL: $SQL_PATH..." "menu"
        mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$SQL_PATH" 2>/dev/null && pintar "Base de datos importada con éxito." "exito" || pintar "Aviso: Error al importar el archivo SQL." "alerta"
    fi
fi

if grep -q '^DB_CONNECTION=sqlite' .env; then
    mkdir -p database
    touch database/database.sqlite
fi

# ==============================================================================
# PASO 6: INSTALACIÓN DE COMPOSER Y COMPILACIÓN DE ASSETS CON VITE
# ==============================================================================
echo ""
pintar "=== DEPENDENCIAS PHP Y COMPILACIÓN FRONTEND ===" "menu"

pintar "➜ Instalando paquetes de Composer..." "menu"
composer install --no-interaction

pintar "➜ Generando clave de cifrado Laravel (APP_KEY)..." "menu"
php artisan key:generate --force

pintar "➜ Instalando paquetes NPM y compilando frontend con Vite..." "menu"
npm install --silent &>/dev/null || npm install
npm run build

# ==============================================================================
# PASO 7: REPARACIÓN EXHAUSTIVA DE PERMISOS, APPARMOR Y SELINUX
# ==============================================================================
echo ""
pintar "=== REPARACIÓN DE PERMISOS Y DIRECTORIOS TEMPORALES ===" "menu"

# 1. Crear directorios internos de Laravel
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs
touch storage/logs/laravel.log

# 2. Directorio temporal del sistema para Blade (/tmp/corrol_views)
mkdir -p /tmp/corrol_views
$SUDO chmod -R 777 /tmp/corrol_views 2>/dev/null || chmod -R 777 /tmp/corrol_views

# 3. Permisos en storage y bootstrap/cache
pintar "➜ Asignando grupo $WEB_USER y permisos de escritura en storage..." "menu"
$SUDO chown -R $REAL_USER:$WEB_USER storage bootstrap/cache 2>/dev/null || true
$SUDO chmod -R 777 storage bootstrap/cache 2>/dev/null || chmod -R 777 storage bootstrap/cache

# 4. SQLite necesita escribir el archivo y su directorio para crear journals
if grep -q '^DB_CONNECTION=sqlite' .env && [ -f database/database.sqlite ]; then
    pintar "➜ Asignando permisos de escritura para SQLite a $WEB_USER..." "menu"
    $SUDO chown "$REAL_USER:$WEB_USER" database/database.sqlite 2>/dev/null || true
    $SUDO chmod 664 database/database.sqlite 2>/dev/null || chmod 664 database/database.sqlite
    $SUDO chown "$REAL_USER:$WEB_USER" database 2>/dev/null || true
    $SUDO chmod 775 database 2>/dev/null || chmod 775 database
fi

# 5. Permisos de travesía en carpetas superiores (+x)
CURRENT_PATH="$(pwd)"
pintar "➜ Asegurando permisos de travesía (+x) en la ruta del proyecto..." "menu"
PARENT_DIR="$CURRENT_PATH"
while [ "$PARENT_DIR" != "/" ] && [ "$PARENT_DIR" != "." ]; do
    chmod +x "$PARENT_DIR" 2>/dev/null || true
    PARENT_DIR=$(dirname "$PARENT_DIR")
done

# 6. Políticas SELinux para distribuciones RedHat/Fedora
if [ "$OS" = "Fedora-based" ] && command -v setsebool &>/dev/null; then
    pintar "➜ Aplicando políticas de SELinux para Apache y Laravel..." "menu"
    $SUDO setsebool -P httpd_can_network_connect_db 1 2>/dev/null || true
    $SUDO setsebool -P httpd_enable_homedirs 1 2>/dev/null || true
    $SUDO setsebool -P httpd_tmp_exec 1 2>/dev/null || true
fi

# 7. Enlace simbólico del almacenamiento público de Laravel
pintar "➜ Generando enlace simbólico de almacenamiento public/storage..." "menu"
php artisan storage:link --force || true

# 8. Symlink opcional en el directorio público del servidor web
if confirm "¿Deseas crear un enlace simbólico en un directorio público web (ej: /public/corrol)?"; then
    read -p "$(pintar "Ruta destino del directorio público (ej: /home/ricardo/workspace/ocanyaweb/ricardo/public/): " "prompt" 0)" PUBLIC_WEB_DIR
    PUBLIC_WEB_DIR_INPUT="$PUBLIC_WEB_DIR"
    if [ -L "$PUBLIC_WEB_DIR_INPUT" ]; then
        pintar "La ruta pública es un enlace simbólico; indica el directorio público real." "error"
        PUBLIC_WEB_DIR=""
    else
        PUBLIC_WEB_DIR=$(expandir_ruta "$PUBLIC_WEB_DIR_INPUT")
    fi
    LINK_NAME="corrol"
    SUB_PATH_NAME="${SUB_PATH#/}"
    SUB_PATH_NAME="${SUB_PATH_NAME%/}"
    if [ -n "$SUB_PATH_NAME" ]; then
        LINK_NAME="$SUB_PATH_NAME"
    fi
    if [ -d "$PUBLIC_WEB_DIR" ]; then
        LINK_TARGET="$PUBLIC_WEB_DIR/$LINK_NAME"
        if [ "$LINK_TARGET" = "$PUBLIC_WEB_DIR" ]; then
            pintar "El nombre del enlace no es válido; se conserva el directorio público." "error"
        elif ln -sfn "$(pwd)/public" "$LINK_TARGET" 2>/dev/null || $SUDO ln -sfn "$(pwd)/public" "$LINK_TARGET"; then
            pintar "Enlace simbólico creado: $LINK_TARGET -> $(pwd)/public" "exito"
            if [ "$OS" = "Debian-based" ] && [ -d /etc/apache2/conf-available ]; then
                CONF_NAME="corrol-${DOMINIO_LIMPIO//[^a-zA-Z0-9_.-]/_}-path.conf"
                $SUDO tee "/etc/apache2/conf-available/$CONF_NAME" > /dev/null <<EOF
<Directory "$PUBLIC_WEB_DIR">
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF
                if [ -x /usr/sbin/a2enconf ]; then
                    $SUDO /usr/sbin/a2enconf "$CONF_NAME" >/dev/null
                    $SUDO /usr/sbin/apache2ctl configtest >/dev/null
                    $SUDO systemctl reload apache2
                fi
            fi
        else
            pintar "No se pudo crear el enlace simbólico en '$LINK_TARGET'." "error"
        fi
    else
        pintar "La ruta proporcionada no existe. Omitiendo enlace." "alerta"
    fi
fi

# 9. Limpiar cachés
pintar "➜ Limpiando cachés de Laravel..." "menu"
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# ==============================================================================
# PASO 8: PRUEBA DE CONECTIVIDAD HTTP FINAL
# ==============================================================================
echo ""
pintar "=== AUDITORÍA Y VERIFICACIÓN HTTP FINAL ===" "menu"

CODIGO_HTTP=0
CODIGO_ASSET=0
if command -v curl &>/dev/null; then
    CODIGO_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "$URL_CORROL" 2>/dev/null || true)
    CODIGO_HTTP="${CODIGO_HTTP:-0}"

    ASSET_RELATIVE=$(php -r '$manifest = json_decode(file_get_contents("public/build/manifest.json"), true); echo $manifest["resources/css/app.css"]["file"] ?? "";' 2>/dev/null || true)
    if [ -n "$ASSET_RELATIVE" ]; then
        ASSET_URL="${URL_CORROL%/}/build/$ASSET_RELATIVE"
        CODIGO_ASSET=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "$ASSET_URL" 2>/dev/null || true)
        CODIGO_ASSET="${CODIGO_ASSET:-0}"
        if [ "$CODIGO_ASSET" -ne 200 ]; then
            pintar "Asset principal no accesible: $ASSET_URL (HTTP $CODIGO_ASSET)." "alerta"
        fi
    fi
fi

SEGUNDOS_FIN=$(date +"%s")
TIEMPO_TOTAL=$((SEGUNDOS_FIN - SEGUNDOS_INICIO))

echo "------------------------------------------------------------------------------"
if [ "$CODIGO_HTTP" -eq 200 ] || [ "$CODIGO_HTTP" -eq 301 ] || [ "$CODIGO_HTTP" -eq 302 ]; then
    if [ "$CODIGO_ASSET" -eq 200 ]; then
        pintar "🎉 ¡DESPLIEGUE COMPLETADO Y AUDITADO EXITOSAMENTE (Web: $CODIGO_HTTP, asset: $CODIGO_ASSET)!" "exito"
    else
        pintar "La web responde ($CODIGO_HTTP), pero un asset CSS/JS no responde correctamente (Código: $CODIGO_ASSET)." "alerta"
    fi
else
    pintar "⚠️ Despliegue finalizado en ${TIEMPO_TOTAL}s. Si tu servidor Apache requiere ajustes adicionales, comprueba la URL:" "alerta"
fi
echo "   URL de acceso: $URL_CORROL"
echo "------------------------------------------------------------------------------"