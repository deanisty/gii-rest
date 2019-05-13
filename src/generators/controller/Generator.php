<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace deanisty\generators\controller;

use Yii;
use yii\db\ActiveRecord;
use yii\gii\CodeFile;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * This generator will generate a controller and one or a few action view files.
 *
 * @property array $actionIDs An array of action IDs entered by the user. This property is read-only.
 * @property string $controllerFile The controller class file path. This property is read-only.
 * @property string $controllerID The controller ID. This property is read-only.
 * @property string $controllerNamespace The namespace of the controller class. This property is read-only.
 * @property string $controllerSubPath The controller sub path. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Generator extends \yii\gii\Generator
{
    /**
     * @var string the controller class name
     */
    public $controllerClass;
    /**
     * @var string the controller's view path
     */
    public $viewPath;
    /***
     * @var string the model class path
     */
    public $modelClass;
    /**
     * @var string the base class of the controller
     */
    public $baseClass = 'yii\rest\ActiveController';
    /**
     * @var string list of action IDs separated by commas or spaces
     */
    public $actions = 'index,view,create,update,delete';
    /***
     * @var string current executing action name
     */
    public $currentAction = '';


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Restful Controller Generator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'This generator helps you to quickly generate a new restful controller class with
            '. $this->actions . ' controller actions. And also generate swagger comments as while.';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['controllerClass', 'actions', 'baseClass'], 'filter', 'filter' => 'trim'],
            [['controllerClass', 'baseClass', 'modelClass', ], 'required'],
            ['controllerClass', 'match', 'pattern' => '/^[\w\\\\]*Controller$/', 'message' => 'Only word characters and backslashes are allowed, and the class name must end with "Controller".'],
            ['controllerClass', 'validateNewClass'],
            [['baseClass', 'modelClass'], 'match', 'pattern' => '/^[\w\\\\]*$/', 'message' => 'Only word characters and backslashes are allowed.'],
            ['actions', 'match', 'pattern' => '/^[a-z][a-z0-9\\-,\\s]*$/', 'message' => 'Only a-z, 0-9, dashes (-), spaces and commas are allowed.'],
            ['modelClass', 'validateClass', 'params' => ['extends' => yii\db\ActiveRecord::class,]],
            ['baseClass', 'validateClass', 'params' => ['extends' => 'yii\rest\ActiveController',]],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'baseClass' => 'Base Class',
            'controllerClass' => 'Controller Class',
            'modelClass' => 'Model Class',
            'actions' => 'Action IDs',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function requiredTemplates()
    {
        return [
            'controller.php',
            'swaggerModel.php',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function stickyAttributes()
    {
        return ['baseClass'];
    }

    /**
     * {@inheritdoc}
     */
    public function hints()
    {
        return [
            'controllerClass' => 'This is the name of the controller class to be generated. You should
                provide a fully qualified namespaced class (e.g. <code>app\controllers\PostController</code>),
                and class name should be in CamelCase ending with the word <code>Controller</code>. Make sure the class
                is using the same namespace as specified by your application\'s controllerNamespace property.',
            'actions' => 'Provide one or multiple action IDs to generate empty action method(s) in the controller. Separate multiple action IDs with commas or spaces.
                Action IDs should be in lower case. For example:
                <ul>
                    <li><code>index</code> generates <code>actionIndex()</code></li>
                    <li><code>create-order</code> generates <code>actionCreateOrder()</code></li>
                </ul>',
            'modelClass' => 'Specify the name of the model class the generating controller will use. You should
                provide a fully qualified namespaced class (e.g. <code>manage\modules\education\models\ClassRecord</code>). 
                Make sure the model class is exists and is subclass or descendant of <code>\yii\db\ActiveRecord</code>.',
            'baseClass' => 'This is the class that the new controller class will extend from. Please make sure the class extends from <code>\yii\rest\ActiveController</code>.',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function successMessage()
    {
        return 'The controller has been generated successfully.' . $this->getLinkToTry();
    }

    /**
     * This method returns a link to try controller generated
     * @see https://github.com/yiisoft/yii2-gii/issues/182
     * @return string
     * @since 2.0.6
     */
    private function getLinkToTry()
    {
        if (strpos($this->controllerNamespace, Yii::$app->controllerNamespace) !== 0) {
            return '';
        }

        $actions = $this->getActionIDs();
        if (in_array('index', $actions, true)) {
            $route = $this->getControllerSubPath() . $this->getControllerID() . '/index';
        } else {
            $route = $this->getControllerSubPath() . $this->getControllerID() . '/' . reset($actions);
        }
        return ' You may ' . Html::a('try it now', Yii::$app->getUrlManager()->createUrl($route), ['target' => '_blank', 'rel' => 'noopener noreferrer']) . '.';
    }

    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $files = [];

        $files[] = new CodeFile(
            $this->getControllerFile(),
            $this->render('controller.php')
        );
        // generate swagger model
        $files[] = new CodeFile(
            $this->getSwaggerModelFile(),
            $this->render('swaggerModel.php')
        );

        return $files;
    }

    /**
     * Normalizes [[actions]] into an array of action IDs.
     * @return array an array of action IDs entered by the user
     */
    public function getActionIDs()
    {
        $actions = array_unique(preg_split('/[\s,]+/', $this->actions, -1, PREG_SPLIT_NO_EMPTY));
//        sort($actions);

        return $actions;
    }

    /***
     * @return object the db model object needs by controller
     */
    public function getControllerModel()
    {
        return new $this->modelClass;
    }

    public function getControllerModelName()
    {
        return StringHelper::basename($this->modelClass);
    }

    /***
     * @return string extract module name from controller class namespace
     */
    public function getModule()
    {
        $module = '';
        preg_match("#\\\\modules\\\\(\w+)\\\\#i", $this->getControllerNamespace(), $match);
        if(count($match) > 1) {
            $module = $match[1];
        }

        return $module;
    }
    /**
     * @return string the controller class file path
     */
    public function getControllerFile()
    {
        return Yii::getAlias('@' . str_replace('\\', '/', $this->controllerClass)) . '.php';
    }

    /***
     * @return string the swagger model file path
     */
    public function getSwaggerModelFile()
    {
        $controllerPath = $this->getControllerFile();
        $moduleName = $this->getModule();
        $modelName = $this->getControllerModelName();
        if($moduleName !== '') {
            preg_match("/^(.*)\/modules\//", $controllerPath, $matches);
            $filePath = $matches[1] . '/swagger/definitions/' . $moduleName . '/' .
                $modelName . '.php';
        } else {
            preg_match("/^(.*)\/controllers\//", $controllerPath, $matches);
            $filePath = $matches[1] . '/swagger/definitions/' . $moduleName . '/' .
                $modelName . '.php';
        }

        return $filePath;
    }

    /***
     * @return string get swagger definition namespace base on controller class name
     */
    public function getSwaggerModelNamespace()
    {
        $controllerNamespace = $this->getControllerNamespace();
        $moduleName = $this->getModule();
        if($moduleName !== '') {
            preg_match("/^(.*)\\\\modules\\\\/", $controllerNamespace, $matches);
            $namespace = $matches[1] . '\\swagger\\definitions\\' . $moduleName;
        } else {
            preg_match("/^(.*)\\\\controllers\\\\/", $controllerNamespace, $matches);
            $namespace = $matches[1] . '\\swagger\\definitions\\' . $moduleName;
        }

        return $namespace;
    }

    /**
     * @return string the controller ID
     */
    public function getControllerID()
    {
        $name = StringHelper::basename($this->controllerClass);
        return Inflector::camel2id(substr($name, 0, strlen($name) - 10));
    }

    /**
     * This method will return sub path for controller if it
     * is located in subdirectory of application controllers dir
     * @see https://github.com/yiisoft/yii2-gii/issues/182
     * @since 2.0.6
     * @return string the controller sub path
     */
    public function getControllerSubPath()
    {
        $subPath = '';
        $controllerNamespace = $this->getControllerNamespace();
        if (strpos($controllerNamespace, Yii::$app->controllerNamespace) === 0) {
            $subPath = substr($controllerNamespace, strlen(Yii::$app->controllerNamespace));
            $subPath = ($subPath !== '') ? str_replace('\\', '/', substr($subPath, 1)) . '/' : '';
        }
        return $subPath;
    }

    /**
     * @return string the namespace of the controller class
     */
    public function getControllerNamespace()
    {
        $name = StringHelper::basename($this->controllerClass);
        return ltrim(substr($this->controllerClass, 0, - (strlen($name) + 1)), '\\');
    }

    /***
     * @return array valid table attribute to be used by front user
     */
    public function getValidAttributes()
    {
        /***
         * @var $model ActiveRecord
         */
        $model = $this->getControllerModel();
        try {
            $schema = $model::getTableSchema();
        } catch(\Exception $e) {

        }
        $columns = $schema->columns;
        // maybe can filter by scenario
        $scenario = '';
        try {
            $modelReflection = new \ReflectionClass($model);
            $scenario = $modelReflection->getConstant('SCENARIO_'.strtoupper($this->currentAction));
        } catch (\Exception $e) {

        }
        // set scenario when create
        if(!empty($scenario) && $this->currentAction == 'create') {
            $model->scenario = $scenario;
        }
        $attributes = $model->activeAttributes();

        $validAttributes = array();
        foreach($columns as $column)
        {
            $attribute = array();
            if(in_array($column->name, $attributes)) {
                // column is allow to display
                $attribute['name'] = $column->name;
                $attribute['type'] = $column->phpType;
                $attribute['comment'] = $column->comment;
                $attribute['required'] = $model->isAttributeRequired($column->name);
                $attribute['isPrimaryKey'] = $column->isPrimaryKey;

                array_push($validAttributes, $attribute);
            }
        }

        return $validAttributes;
    }
}
