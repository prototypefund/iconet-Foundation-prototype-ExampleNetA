# Project setup

## Clone the repository

### Via HTTPS:

    git clone https://codeberg.org/iconet-Foundation/prototype-ExampleNetA.git

### Or Via SSH:

Upload your public key in Codeberg under `Settings->Security`. Follow the instructions if you don't already have one.
Download the repository with:

    git clone git@codeberg.org:iconet-Foundation/prototype-ExampleNetA.git

## On Ubuntu

1. php is already installed, install apache2, install mysql, install composer

        sudo apt install apache2 mysql-server composer

2. Make apache2 host your local folder: Create a new file called netA.conf under `/etc/apache2/sites-available/`. You
   need sudo rights:

        sudoedit /etc/apache2/sites-available/netA.conf

3. Change the paths below and paste this:

```apacheconf
<VirtualHost neta.localhost:80>
        ServerAdmin webmaster@neta.localhost
        ServerName neta.localhost

        DocumentRoot YOUR_PATH_TO_SRC
        DirectoryIndex index.php

        LogLevel info
        ErrorLog ${APACHE_LOG_DIR}/error-netA.log
        CustomLog ${APACHE_LOG_DIR}/access-netA.log combined

        <Directory YOUR_PATH_TO_SRC>
            AllowOverride All
            Options +FollowSymLinks
            Require local
            #Require all granted
        </Directory>
</VirtualHost>
```

4. Enable the site and needed modules

        sudo a2ensite netA.conf
        sudo a2enmod rewrite 
        sudo a2enmod headers


5. Add the following line to your `/etc/hosts` file with `sudoedit`

        127.0.0.1       netA

6. Install composer (`sudo apt install composer`) and run `composer install` in the project folder.


7. In your browser visit the site `http://neta.localhost/`. If the page is served without _Forbidden_ or _Not Found_
   erros, skip the next steps and continue setting up the database in step 13.


8. Check apache2's status for errors with

        apachectl -S


9. Error logs are usually located at `/var/log/apache2/error-netA.log`


10. Maybe you need to create the file `/etc/apache2/conf-available/fqdn.conf`. With `sudoedit` paste the following line
    and activate it with `sudo a2enconf /etc/apache2/conf-available/fqdn.conf`

        ServerName localhost


11. If apache is not running, run `sudo systemctl start apache2`, check status under `sudo systemctl status apache2`


12. If you get a permission denied error, make sure that the apache process can access the project directory. Check the
    file system permissions.


13. If Mysql allows the user root only to be accessed when run by sudo, create an admin account with full rights

    In `mysql`:

    ```mysql
    CREATE USER 'admin'@'localhost' IDENTIFIED BY '';
    GRANT ALL ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
    ```

14. Create the databases (enter your mysql credentials):
    ``` bash
    mysql -e "CREATE DATABASE netA;"
    mysql --database=netA < netA.sql
    mysql -e "CREATE DATABASE netAiconet;"
    mysql --database=netAiconet < iconet/iconet.sql
    ```

15. Set the admin credentials in a file named `.env` in the project root. Use `.env.default` as a template. You can skip
    this step, if you used the same ones as in the default file.


16. Start the mysql server with `sudo systemctl start mysql`

## Using Docker

Alternatively, you can create a docker instance which will host a mysql and apache server.
The docker container will serve the local development directory, so external changes are immediately made available. (
This also means the container can rewrite local files. So take care to avoid data loss, when switching between the two
setups.)

```bash
docker-compose up
```

This will also start a reverse proxy (traefik) which redirects https://neta.localhost to the container. For a trusted
TLS connection you will have to create your own certificates (e.g. mkcert) and place them under `certs/cert.crt`
and `certs/cert.key` in the project folder.

You can attach to it with `docker attach neta` (`Ctrl+P` `Ctlr+Q` to detach). Apache logs are located
at `/var/log/apache2/`.

If you plan to only run this project, you can toggle the comments in the docker-compose.yml and access the container
from port 8001 ([http://localhost:8001](http://localhost:8001)).

## XDebug

XDebug is needed for debugging and code coverage analysis of the tests.

1. Install it with `sudo apt install php-xdebug`
2. If php 8.1 is the version you use, `/etc/php/8.1/mods-available/xdebug.ini` should contain at least the first two
   lines:

    ```apache
    zend_extension=xdebug.so
    xdebug.mode=debug

    #xdebug.remote_enable=1
    #xdebug.remote_connect_back = 1
    #xdebug.remote_port = 9000
    #xdebug.scream=0 
    #xdebug.cli_color=1
    #xdebug.show_local_vars=1
    ```

3. This config will be included in your `php.ini` via `sudo phpenmod -v 8.1 xdebug`.
4. Restart apache: `sudo systemctl restart apache2`
5. In phpstorm under `File->Setting->PHP->Debug`, run the IDE's validation script from step 2 in the project folder.






## phpstorm IDE specific:

- For windows: Connect phpstorm to your AMP and
  Mysql: [Follow this tutorial](https://www.jetbrains.com/help/phpstorm/installing-an-amp-package.html)

- You can link the database in `View->Tool Windows->Database`. In this window click `+ -> Datasource -> MySQL`. Fill in the credentials. If you leave the database field empty, you can see all databases.

