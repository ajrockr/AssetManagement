#!/bin/bash

# Detect if script was run as ROOT or with SUDO
if [ "$EUID" -ne 0 ]; then
    echo "This script must be run as root or with sudo."
    exit 1
fi

# Update and install necessary packages
echo "Updating the system and installing neccessary dependencies."
apt update -y && apt upgrade -y
apt install acl unzip nginx mariadb-server mariadb-client php8.1 php8.1-intl php8.1-fpm php8.1-mysql php-common php8.1-cli php8.1-common php8.1-opcache php8.1-readline php8.1-mbstring php8.1-xml php8.1-gd php8.1-curl php8.1-zip git nodejs npm -y

# Allow Nginx through the firewall
echo "Allowing NGINX through the firewall."
ufw allow 'Nginx Full'
ufw reload

# Start and enable MariaDB sql server
echo "Enabling the MariaDB server on startup."
systemctl start mariadb
systemctl enable mariadb

# Start and enable PHP
echo "Enabling PHP8.1 FPM on startup."
systemctl start php8.1-fpm
systemctl enable php8.1-fpm

# https://symfony.com/doc/current/setup/file_permissions.html
#HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
#sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
#sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:$(whoami):rwX var
