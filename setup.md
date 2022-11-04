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
<VirtualHost netA:80>
        ServerAdmin webmaster@localhost
        ServerName netA
        ServerAlias www.netA

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

4. Enable the site

        sudo a2ensite netA.conf


5. Add the following line to your `/etc/hosts` file with `sudoedit`

        127.0.0.1       netA

6. Install composer (`sudo apt install composer`) and run `composer install` in the project folder.


7. In your browser visit the site `netA/`. If the page is served without _Forbidden_ or _Not Found_ erros, skip these steps and continue setting up the database in step 11.

8. Check apache2's status for errors with

        apachectl -S


9. Maybe you need to create the file `/etc/apache2/conf-available/fqdn.conf`. With `sudoedit` paste the following line and activate it with `sudo a2enconf /etc/apache2/conf-available/fqdn.conf`

        ServerName localhost


10. If apache is not running, run `sudo systemctl start apache2`, check status under `sudo systemctl status apache2`


11. If Mysql allows the user root only to be accessed when run by sudo, create an admin account with full rights

    In `mysql`:

    ```mysql
    CREATE USER 'admin'@'localhost' IDENTIFIED BY '';
    GRANT ALL ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
    ```

12. Create the databases (enter your mysql credentials):
    ``` bash
    mysql -e "CREATE DATABASE netA;"
    mysql --database=netA < netA.sql
    mysql -e "CREATE DATABASE netAiconet;"
    mysql --database=netAiconet < iconet/iconet.sql
    ```

13. Set the admin credentials in a file named `.env` in the project root. Use `.env.example` as a template.



14. Start the mysql server with `sudo systemctl start mysql`

## Using Docker

Alternatively, you can create a docker instance which will host a mysql and apache server.
The docker container will serve the local development directory, so external changes are immediately made available.

```bash
docker build .
docker run -p 80:80 -ti -u=root -v "$(pwd)":/var/www/prototype-ExampleNetA <docker image id>
```

The server is running on port 80 and a console is made available. Apache logs are located at `/var/log/apache2/`.

## phpstorm IDE specific:

- For windows: Connect phpstorm to your AMP and
  Mysql: [Follow this tutorial](https://www.jetbrains.com/help/phpstorm/installing-an-amp-package.html)

- You can link the database in `View->Tool Windows->Database`. In this window click `+ -> Datasource -> MySQL`. Fill in the credentials. If you leave the database field empty, you can see all databases.

