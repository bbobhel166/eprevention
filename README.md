symfony
=======

A Symfony project created on January 23, 2017, 9:28 pm.

To INSTALL oppbtp projet : 

- composer install
- php bin/console doctrine:database:create
- php bin/console doctrine:migrations:diff
- php bin/console doctrine:migrations:migrate

Load FIXTURE (do not do this on PROD !!!) :
- php bin/console doctrine:fixtures:load


To TEST oppbtp projet (phpunit) : 
- php vendor/phpunit/phpunit/phpunit



