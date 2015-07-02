Undercity for Sylvanas
======================

Undercity provides services for [Sylvanas](https://github.com/redspy/Sylvanas). it is developed using php. database is mysql.

copy propel.xml.dist to propel.xml
change DB configuration & path where Propel expects to find schema.xml & path where Propel should generate models in propel.xml

$ composer install
$ ./vendor/bin/propel sql:build
$ ./vendor/bin/propel sql:insert
$ ./vendor/bin/propel model:build
$ ./vendor/bin/propel config:convert --output-dir=config
$ composer dump-autoload