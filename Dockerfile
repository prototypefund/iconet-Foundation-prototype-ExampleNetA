FROM ubuntu:latest

# Environment variable necessary to not be prompted during setup
ENV DEBIAN_FRONTEND="noninteractive" TZ="Etc/UTC"

RUN \
    apt update && \
    apt upgrade -y && \
    apt install -y apache2 mysql-server composer php php-mysql php-dom

WORKDIR /var/www/prototype-ExampleNetA

COPY apache.conf /etc/apache2/sites-available/netA.conf

RUN \
    a2dissite 000-default.conf && \
    a2ensite netA.conf

# Database init files
COPY netA.sql init_netA.sql
COPY iconet/iconet.sql init_netAiconet.sql

RUN  \
    service mysql start && \
    mysql -e "CREATE USER 'admin'@'localhost' IDENTIFIED BY ''; GRANT ALL ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;" && \
    mysql -e "CREATE DATABASE netA;" && mysql -uroot --database=netA < init_netA.sql && \
    mysql -e "CREATE DATABASE netAiconet;" && mysql -uroot --database=netAiconet < init_netAiconet.sql


# To execute when running docker run.
ENTRYPOINT \
    service apache2 start && \
    service mysql start && \
    composer install && \
    /bin/bash

EXPOSE 80 8001