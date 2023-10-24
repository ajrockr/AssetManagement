#!/bin/bash

# Check if the script is being run as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root"
  exit 1
fi

# Define the expected PHP version
EXPECTED_PHP_VERSION="8.2"

# Function to check the PHP version and make sure it's PHP 8.2
check_php_version() {
    php_version=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
    if [ "$php_version" != "$EXPECTED_PHP_VERSION" ]; then
        echo "This script requires PHP $EXPECTED_PHP_VERSION, but PHP $php_version is installed."
        exit 1
    fi
}

# Function to prompt for MySQL username and password
prompt_for_mysql_credentials() {
    read -p "Enter a MySQL username: " mysql_user
    read -s -p "Enter a password for the MySQL user: " mysql_password
    echo
    read -p "Enter the name of the database (default: assetmanagement): " db_name
    db_name=${db_name:-assetmanagement}  # Use "assetmanagement" as the default if no name provided
}

# Function to prompt for Nginx site configuration and SSL certificate details
prompt_for_nginx_site() {
    read -p "Enter the domain name for the Nginx site (e.g., example.com): " domain_name
    read -p "Enter the path to the SSL certificate file (e.g., /etc/nginx/ssl/certificate.crt, leave empty for self-signed): " ssl_certificate
    read -p "Enter the path to the SSL certificate key file (e.g., /etc/nginx/ssl/certificate.key, leave empty for self-signed): " ssl_certificate_key
}

# Function to generate a self-signed SSL certificate
generate_self_signed_cert() {
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/nginx/ssl/selfsigned.key -out /etc/nginx/ssl/selfsigned.crt
    ssl_certificate="/etc/nginx/ssl/selfsigned.crt"
    ssl_certificate_key="/etc/nginx/ssl/selfsigned.key"
}

# Install necessary packages based on Linux distribution
install_packages() {
    local package_manager
    if [ -x "$(command -v apt)" ]; then
        package_manager="apt"
    elif [ -x "$(command -v dnf)" ]; then
        package_manager="dnf"
    elif [ -x "$(command -v yum)" ]; then
        package_manager="yum"
    elif [ -x "$(command -v pacman)" ]; then
        package_manager="pacman"
    else
        echo "Unsupported package manager. Please install required packages manually."
        exit 1
    fi

    case "$package_manager" in
        "apt")
            apt update
            apt install -y nginx php-fpm mysql-server openssl
            apt install -y php-cli php-curl php-mbstring php-xml php-zip php-json
            ;;
        "dnf"|"yum")
            dnf_command="dnf"
            if [ -x "$(command -v yum)" ]; then
                dnf_command="yum"
            fi
            $dnf_command install -y nginx php-fpm mariadb-server openssl
            $dnf_command install -y php-cli php-curl php-mbstring php-xml php-zip php-json
            ;;
        "pacman")
            pacman -Syu --noconfirm nginx php-fpm mariadb openssl
            pacman -S --noconfirm php php-curl php-intl php-gd php-zip php-sqlite
            ;;
    esac
}

# Install Composer from getcomposer.org
install_composer() {
    EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
    INSTALL_DIR="/usr/local/sbin"
    COMPOSER_SETUP="composer-setup.php"

    # Download Composer with wget
    echo "Downloading Composer..."
    wget -q https://getcomposer.org/installer -O "$COMPOSER_SETUP"

    # Verify the checksum
    ACTUAL_CHECKSUM=$(php -r "echo hash_file('sha384', '$COMPOSER_SETUP');")

    if [ "$ACTUAL_CHECKSUM" == "$EXPECTED_SIGNATURE" ]; then
        echo "Checksum verified."
    else
        echo "Checksum verification failed. Aborting installation."
        rm "$COMPOSER_SETUP"
        exit 1
    fi

    # Move Composer to the installation directory
    echo "Installing Composer..."
    php "$COMPOSER_SETUP" --install-dir="$INSTALL_DIR" --filename=composer

    # Clean up
    echo "Cleaning up..."
    rm "$COMPOSER_SETUP"

    # Verify the installation
    if [ -x "$INSTALL_DIR/composer" ]; then
        echo "Composer is installed in $INSTALL_DIR"
    else
        echo "Composer installation failed."
        exit 1
    fi
}

# Function to secure MySQL with user-provided credentials
secure_mysql() {
    # Set a password for the MySQL root user if not already set
    mysqladmin -u root password "${mysql_password}"
    # Create the database
    mysql -e "CREATE DATABASE IF NOT EXISTS ${db_name};"
    # Create the MySQL user and grant privileges to the database
    mysql -e "CREATE USER '${mysql_user}'@'localhost' IDENTIFIED WITH 'mysql_native_password' BY '${mysql_password}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${db_name}.* TO '${mysql_user}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    mysql_secure_installation
}

# Function to configure Nginx site with SSL
configure_nginx_site() {
    cat <<EOF > "/etc/nginx/sites-available/$domain_name"
server {
    listen 80;
    server_name ${domain_name};
    return 301 https://\$host\$request_uri;
}

server {
    listen 443 ssl;
    server_name ${domain_name};
    ssl_certificate ${ssl_certificate};
    ssl_certificate_key ${ssl_certificate_key};

    # Enable strong security protocols and ciphers
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers off;
    ssl_ciphers 'TLS_AES_128_GCM_SHA256:TLS_AES_256_GCM_SHA384:DHE-RSA-AES128-GCM-SHA256';

    # Enable OCSP stapling for better security
    ssl_stapling on;
    ssl_stapling_verify on;

    # Diffie-Hellman parameters for DHE key exchange
    ssl_dhparam /etc/nginx/ssl/dhparam.pem;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";

    location / {
        root /var/www/html/${domain_name};
        index index.php index.html;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Adjust the socket path for PHP 8.2
    }
}
EOF
    # Symbolic link for enabling the site and reloading Nginx
    ln -s "/etc/nginx/sites-available/$domain_name" "/etc/nginx/sites-enabled/"
    systemctl reload nginx
}

# Function to copy everything in the current directory to /var/www/html/${domain_name}
copy_files_to_webroot() {
    cp -r . "/var/www/html/$domain_name"
}

# Function to run Composer install in the site directory
run_composer_install() {
    cd "/var/www/html/$domain_name"
    composer install --no-dev --optimize-autoloader --prefer-source
}

# Main script execution
install_packages
check_php_version
prompt_for_mysql_credentials
prompt_for_nginx_site
[ -z "$ssl_certificate" ] && [ -z "$ssl_certificate_key" ] && generate_self_signed_cert
install_composer
secure_mysql
configure_nginx_site
copy_files_to_webroot
run_composer_install

# Start and enable services
systemctl start nginx
systemctl enable nginx
systemctl start php-fpm
systemctl enable php-fpm

# End of the script
