## Mapper ##

Mapper is a library for getting metadata from a class.


#Features#
----------

- Get metadata for a whole class ( Mapper\Mapper::getMap($className) )
- Get metadata for a property from given class ( Mapper\mapper::getPropertyMeta($className, $propertyName) )
- Get metadata for a method from given class ( Mapper\Mapper::getMethodMeta($className, $methodName) )
- Define your own callback for writing cache ( Mapper\Mapper::setCacheSaveFunction($saveMethod) )
- Define your own callback for reading cache ( Mapper\Mapper::setCacheReadFunction($readFunction) )

#Author#
--------

- Lauri Orgla

#Requirements#
------------

- PHP 5.5 with Reflection module
- theorx/reflectionist from packagist.org ( comes automatically with composer )

Installation
------------
```sh
$ git clone

$ ./composer.phar update

$ ./composer.phar dump-autoload -o
```


Usage
-----
```php

```

Example input
-------------
```php

```

Example output
--------------

```php

```