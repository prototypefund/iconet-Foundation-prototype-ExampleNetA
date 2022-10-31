# Project setup

## Clone the repository

### Via HTTPS:

    git clone https://codeberg.org/iconet-Foundation/prototype-ExampleNetA.git

### Or Via SSH:

Upload your public key in Codeberg under `Settings->Security`. Follow the instructions if you dont already have one.
Download the repository with:

    git clone git@codeberg.org:iconet-Foundation/prototype-ExampleNetA.git

## On Ubuntu

1. php-7.4 is already installed, install apache2, install mysql, install composer

        sudo apt install apache2 mysql-server composer

2. Make apache2 host your local folder: Create a new file called netA.conf under `/etc/apache2/sites-available/` You
   need sudo rights:

        sudoedit /etc/apache2/sites-available/netA.conf

3. Change the paths below and paste this:

        <VirtualHost netA:80>
            ServerAdmin webmaster@localhost
            ServerName netA
            ServerAlias www.netA

            DocumentRoot /home/YOURUSERNAME/your/path/to/prototype-ExampleNetA
            DirectoryIndex index.php

            LogLevel info
            ErrorLog ${APACHE_LOG_DIR}/error-netA.log
            CustomLog ${APACHE_LOG_DIR}/access-netA.log combined

            <Directory /home/YOURUSERNAME/your/path/to/prototype-ExampleNetA>
                AllowOverride All
                Options +FollowSymLinks
                Require local
                #Require all granted
            </Directory>
        </VirtualHost>


4. Enable the site

        sudo a2ensite netA.conf


5. Add the following line to your `/etc/hosts` file with `sudoedit`

        127.0.0.1       netA

6. Install composer (`sudo apt install composer`) and run `composer install` in the project folder.


7. In your browser visit the site `netA/`

8. Not working? Check apache2's status for errors with

        apachectl -S


9. Maybe you need to create the file `/etc/apache2/conf-available/fqdn.conf`. With `sudoedit` paste the following line and activate it with `sudo a2enconf /etc/apache2/conf-available/fqdn.conf`

        ServerName localhost


10. If apache is not running run `sudo systemctl start apache2`, check status under `sudo systemctl status apache2`
   If mysql not running run `sudo service mysql start`


11. If Mysql allows the user root only to be accessed when run by sudo, create an admin account with full rights and no
    pw.
    in mysql:
    `CREATE USER 'admin'@'localhost' IDENTIFIED BY '';`
    `GRANT ALL ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;`

Run following steps in mysql not with root but with admin

12. Start the mysql server with `sudo systemctl start mysql`
13. Run `netA.sql`

## IDE specific:

- Connect phpstorm to your AMP and
  Mysql: [Follow this tutorial](https://www.jetbrains.com/help/phpstorm/installing-an-amp-package.html)

- Create Database "social":

        mysql -uroot -p
        create database social;

- Link IDE Database social@localhost

