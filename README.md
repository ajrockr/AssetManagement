# Asset Management

This is a tool developed for internal use. Built with the Symfony framework, this software allows you to intake assets, assign users, track repairs and store them in a virtual representation of physical storage.

## Installation
I run our instance off of an ubuntu 22.04 server with NGINX and MariaDB. If you want to do the same, the supplied [install.sh](https://github.com/ajrockr/AssetManagement/blob/master/install.sh) file will install all necessary packages and enable NGINX and MariaDB.

> [!WARNING]
> I had errors when building the Node packages if I wasn't on NodeJS 20.

You can also copy the server configuration files from [Symfony](https://symfony.com/doc/current/setup/web_server_configuration.html).

Move the repository or just clone it into a directory under /var/www/
```shell
$ git clone https://github.com/ajrockr/assetmanagement.git assetmanagement
```
Make sure to change permissions and ownership.
```shell
$ sudo chown -R www-data:www-data assetmanagement
$ sudo chmod -R 0777 assetmanagement
```

You will then need to build your database url string:
```dotenv
DATABASE_URL="mysql://db_user:db_pass@host:3306?db_name?serverVersion=16.6.1-mariadb&charget=utf8mb4
```

> [!NOTE]
> The server version needs to be exactly how your chosen database provides it. For instance, mine is:
> 
```shell
$ mariadb --version
10.6.16-MariaDB
```

Once that is done, install the project dependencies.
```shell
$ composer install --no-dev --optimize-autoloader
```
