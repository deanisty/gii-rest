<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use deanisty\generators\comment\Comment;

/* @var $this yii\web\View */
/* @var $generator deanisty\generators\controller\Generator */

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

?>
<?php foreach ($generator->getActionIDs() as $action): ?>
    <?php
    $generator->currentAction = $action;
    echo Comment::$action($generator)
    ?>
    public function action<?= Inflector::id2camel($action) ?>(<?= in_array($action, ['view', 'delete']) ? '$id' : '' ?>)
    {

    }
<?php endforeach; ?>
}
