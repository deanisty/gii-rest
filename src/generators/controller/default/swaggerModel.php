<?php
/**
 * This is the template for generating a controller class file.
 */

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

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

namespace <?= $generator->getSwaggerModelNamespace() ?>;
<?php
/***
 * @var $model \yii\db\ActiveRecord
 */
$comment = "*\n";
$attributes = $generator->getValidAttributes();
$required = [];
foreach($attributes as $attribute)
{
    $comment .= ' * @SWG\Property(property="'.$attribute['name'].'", type="'.$attribute['type'].'", description="'.$attribute['comment'].'" )'."\n";
    if($attribute['required']) {
        array_push($required, $attribute['name']);
    }
}

?>
/**
 * @SWG\Definition(required={"<?=join('", "', $required)?>"})
 *
 <?=$comment?>
 */
class <?=$generator->getControllerModelName()?> {

}