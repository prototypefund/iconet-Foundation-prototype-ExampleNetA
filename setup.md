IDE specific:
Connect phpstorm to your AMP and Mysql: follow this tutorial
https://www.jetbrains.com/help/phpstorm/installing-an-amp-package.html

Create Database "social":
mysql -uroot -p
create database social;

Link IDE Database social@localhost

Run social.sql on linked database. 