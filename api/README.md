Undercity for Sylvanas
======================

Undercity provides services for [Sylvanas](https://github.com/redspy/Sylvanas). it is developed using php. database is mysql.



###Installation###

Clone the repository and then copy propel.xml.dist to propel.xml. propel.xml containes DB connection information and pathes where schema.xml exists and generated model classes are stored.

Run these command line to initialize external libraries and generate database model classes.

    $ openssl genrsa -out keys/private.pem 2048
    $ openssl rsa -in keys/private.pem -out keys/public.pem -outform pem -pubout
    
    $ composer install
    $ ./vendor/bin/propel sql:build
    $ ./vendor/bin/propel sql:insert
    $ ./vendor/bin/propel model:build
    $ ./vendor/bin/propel config:convert --output-dir=config
    $ composer dump-autoload
    
    
    
    $ npm install -g jasmine-node   
    
    
###API###
GET undercity/api/images/:id

POST undercity/api/images

response
format: json
image: number

POST undercity/api/images/:id

DELETE undercity/api/images/:id