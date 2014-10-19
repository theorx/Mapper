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
$ git clone https://github.com/theorx/Mapper.git

$ ./composer.phar update

$ ./composer.phar dump-autoload -o
```


Usage
-----
```php

<?php

Mapper\Mapper::setCacheSaveFunction(function($className, $data, $ttl){
    //Save data to disk or interface to any other caching backend
});

Mapper\Mapper::setCacheReadFunction(function($className, $ttl){
    //Read from disk / database or any other caching backend
});

$metaData = Mapper\Mapper::getMethodMeta('\Path\To\My\Class', 'MyMethodName');
//Returns method's metadata


```

Example output
--------------

```html
Array
(
    [\Tests\Stubs\StubExampleClass::test] => Array
        (
            [parameters] => Array
                (
                    [param1] => Array
                        (
                            [name] => param1
                            [type] =>
                            [isOptional] =>
                            [position] => 0
                            [defaultValue] =>
                        )

                    [param2] => Array
                        (
                            [name] => param2
                            [type] =>
                            [isOptional] =>
                            [position] => 1
                            [defaultValue] =>
                        )

                    [param3] => Array
                        (
                            [name] => param3
                            [type] =>
                            [isOptional] => 1
                            [position] => 2
                            [defaultValue] =>
                        )

                )

            [tags] => Array
                (
                    [@author] => Array
                        (
                            [0] => Lauri Orgla <TheOrX@hotmail.com>
                        )

                    [@param] => Array
                        (
                            [0] => $param1
                            [1] => $param2
                            [2] => null $param3
                        )

                )

        )

    [\Tests\Stubs\StubExampleClass::$primaryProperty] => Array
        (
            [tags] => Array
                (
                    [@custom-tag] => Array
                        (
                            [0] => CustoMTagValue
                            [1] => CustoMTagValue2
                            [2] => CustoMTagValue3
                        )

                    [@var] => Array
                        (
                            [0] => bool
                        )

                )

        )

)
```