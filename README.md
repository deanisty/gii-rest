<p align="center">
    <h1 align="center">Restful extension for Gii</h1>
    <br>
</p>

This extension provides a Restful controller code generator for Gii, based on Yii 2. 
Support swagger API document generator as while.

Installation
------------

add

```
"deanisty/gii-rest": "~1.0.0"
```

to the require-dev section of your `composer.json` file.


Usage
-----

The extension based on Yii 2 and Gii, after installed you should [enable gii extension](https://www.yiiframework.com/doc/guide/2.0/en/start-gii), 
then add the extension as a generator to gii as follows:

```php
return [
    'bootstrap' => ['gii'],
    'modules' => [
        'gii' => [
            'class' => 'yii\gii\Module',
            'allowedIPs' => ['127.0.0.1', '*:*:*'],
            // here comes the addition configure for the extension
            'generators' => [ // generators
                'restController' => [ // our new rest generator
                    'class' => 'deanisty\generators\controller\Generator', // generator class name
                ]
            ]
        ],
        // ...
    ],
    // ...
];
```

You can then access Gii through the following URL:

```
http://localhost/path/to/index.php/gii
```

then you can see our new Restful controller generator:

![gii-rest-home](images/gii-rest-home.png)


Swagger Model
-----

This extension will also generate a swagger model file under the project directory where you controller file lives, 
swagger model file path will follow the rule : `{project_name}/swagger/definitions/{module_name}/{model_name}.php`, 
module_name is the name of module where the controller file belongs, 
and model_name if the name of model you typed in "Model Class" input.


Read More
-----

If you need know more about Swagger comment document details,
view [docs for swagger-php](https://github.com/zircote/swagger-php/blob/2.x/docs/Getting-started.md) for more swagger comment examples.
