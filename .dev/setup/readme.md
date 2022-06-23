# Moloni Prestashop Dev Enviroment
## Docker installation process
**Make sure you have docker installed in your computer**

- Place the `docker-compose.yaml` file in your folder root for example `C:/_moloni/plugins_pt/prestashop`
- Remember to update the `PRESTASHOP_HOST`, `PRESTASHOP_PASSWORD` and `PRESTASHOP_EMAIL` variables
- Run `docker-compose up -d`
- Clone this repository into the `modules` folder
- Install your dependencies with composer running `composer install`
- Build your assets by running `cd .dev` and `npm run watch`

## Installing xdebug

SSH into the Prestashop docker container and run the following commands
````
docker exec -it -u root [docker_name] bin/bash
apt-get update 
apt-get install autoconf 
apt-get install php-xdebug 
apt-get install nano
nano /opt/bitnami/php/lib/php.ini
````

Add the following to the end of the file

````
zend_extension = xdebug
xdebug.mode = debug
xdebug.idekey=netbeans-xdebug
xdebug.discover_client_host=1
xdebug.client_host=host.docker.internal
````

You can look the PHPStorm sample configuration in the image `phpstorm_xdebug_sample.png`

