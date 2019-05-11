<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\controller\Generator */

echo "<?php\n";
?>
/**
 * Created by gii.
 * User: <?= @posix_getpwuid(@posix_getuid())['name'] ."\n" ?>
 * Date: <?= date('Y/m/d') . "\n" ?>
 * Time: <?= date('H:i') . "\n"?>
 */

namespace <?= $generator->getControllerNamespace() ?>;

use <?= $generator->baseClass ?>;

class <?= StringHelper::basename($generator->controllerClass) ?> extends <?= StringHelper::basename($generator->baseClass) . "\n" ?>
{
    public $modelClass = '<?= $generator->modelClass ?>';

    public function actions()
    {
        $actions = parent::actions();

        return $actions;
    }
<?php

// 控制器名
$controller = $generator->getControllerID();

// 获取 model 信息
/***
 * @var $model \yii\db\ActiveRecord
 */
$model = new $generator->modelClass;
$schema = $model::getTableSchema();
$primary_key = $schema->primaryKey;
$columns = $schema->columns;

/**** 获取 module ***/
$module = '';
preg_match("#\\\\modules\\\\(\w+)\\\\#i", $generator->getControllerNamespace(), $match);
if(count($match) > 1) {
    $module = $match[1];
}

?>
<?php foreach ($generator->getActionIDs() as $action): ?>
    <?php
    // 添加注释
    switch($action) {
        case 'index':
        printf('
    /**
     * @SWG\Get(path="/%s/%s",
     *     tags={"%s"},
     *     summary="列表",
     *     produces={"application/json"},
     *     @SWG\Response(response = 200, description = "success"),
     * )
     * @return mixed|null|static
     */', $module, Inflector::pluralize($controller), $module) ;
            break;
        case 'view':
            printf('
    /**
     * @SWG\Get(path="/%s/%s/{%s}",
     *     tags={"%s"},
     *     summary="详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "path",
     *        name = "{%s}",
     *        description = "%s",
     *        required = true,
     *        type = "integer",
     *     ),
     *
     *     @SWG\Response(response = 200, description = "success"),
     * )
     * @return mixed|null|static
     */', $module, Inflector::pluralize($controller), join(',', $primary_key),
                $module, join(',', $primary_key), join(',', $primary_key));
            break;
        case 'create':
            $comment = sprintf('
    /**
     * @SWG\Post(path="/%s/%s",
     *     tags={"%s"},
     *     summary="新增",
     *     produces={"application/json"},',  $module, Inflector::pluralize($controller), $module);
            foreach($columns as $column)
            {
                if($column->isPrimaryKey)
                    continue;
                $comment .= sprintf('
    *     @SWG\Parameter(
     *        in = "formData",
     *        name = "%s",
     *        description = "%s",
     *        required = true,
     *        type = "%s",
     *     ),', $column->name, $model->getAttributeLabel($column->name), $column->phpType);
            }
            $comment .= '
     *     @SWG\Response(response = 200, description = "success"),
     * )
     * @return mixed|null|static
     */';
            echo $comment;
            break;
        case 'update':
            $comment = sprintf('
    /**
     * @SWG\Put(path="/%s/%s/{%s}",
     *     tags={"%s"},
     *     summary="更新",
     *     produces={"application/json"},',  $module, Inflector::pluralize($controller), join(',', $primary_key), $module);
            foreach($columns as $column)
            {
                if($column->isPrimaryKey)
                    continue;
                $comment .= sprintf('
    *     @SWG\Parameter(
     *        in = "formData",
     *        name = "%s",
     *        description = "%s",
     *        required = true,
     *        type = "%s",
     *     ),', $column->name, $model->getAttributeLabel($column->name), $column->phpType);
            }
            $comment .= '
     *     @SWG\Response(response = 200, description = "success"),
     * )
     * @return mixed|null|static
     */';
            echo $comment;
            break;
        case 'delete':
            printf('
    /**
     * @SWG\Delete(path="/%s/%s/{%s}",
     *     tags={"%s"},
     *     summary="删除",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "path",
     *        name = "%s",
     *        description = "%s",
     *        required = true,
     *        type = "integer",
     *     ),
     *
     *     @SWG\Response(response = 204, description = "success"),
     * )
     * @return mixed|null|static
     */', $module, Inflector::pluralize($controller), join(',', $primary_key),
                $module, join(',', $primary_key), join(',', $primary_key));
            break;
        default:
            break;
    }
    ?>

    public function action<?= Inflector::id2camel($action) ?>()
    {

    }
<?php endforeach; ?>
}
