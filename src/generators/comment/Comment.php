<?php
/**
 * Created by PhpStorm.
 * User: deanisty
 * Date: 19/5/11
 * Time: 下午10:13
 */

namespace deanisty\generators\comment;

use deanisty\generators\controller\Generator;
use yii\helpers\Inflector;

class Comment
{
    public static function index(Generator $generator)
    {
        $comment = '
    /**
     * @SWG\Get(path="/{{module}}/{{controllerNames}}",
     *     tags={"{{module}}"},
     *     summary="列表",
     *     produces={"application/json"},
     *     @SWG\Response(
     *         response = 200, 
     *         description = "success",
     *         @SWG\Schema(
     *             type = "array",
     *             @SWG\Items(
     *                 ref = "#/definitions/{{modelName}}"
     *             )
     *         )
     *     ),
     *     @SWG\Response(
     *          response = 400,
     *          description = "failed"
     *     )
     * )
     * @return object
     */';

        return self::popularVar($generator, $comment);
    }

    public static function view(Generator $generator)
    {
        $comment = '
    /**
     * @SWG\Get(path="/{{module}}/{{controllerNames}}/{{{primaryKey}}}",
     *     tags={"{{module}}"},
     *     summary="详情",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "path",
     *        name = "{{primaryKey}}",
     *        description = "{{primaryKey}}",
     *        required = true,
     *        type = "integer",
     *     ),
     *
     *     @SWG\Response(
     *         response = 200, 
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/{{modelName}}")
     *     ),
     *     @SWG\Response(
     *          response = 400,
     *          description = "failed"
     *     ),
     * )
     * @return object
     */';

        return self::popularVar($generator, $comment);
    }

    public static function create(Generator $generator)
    {
        $attributes = self::extractProperties($generator)['attributes'];
        $comment = '
    /**
     * @SWG\Post(path="/{{module}}/{{controllerNames}}",
     *     tags={"{{module}}"},
     *     summary="新增",
     *     produces={"application/json"},';
            foreach($attributes as $attribute)
            {
                if($attribute['isPrimaryKey'])
                    continue;
                $comment .= '
     *    @SWG\Parameter(
     *        in = "formData",
     *        name = "'.$attribute['name'].'",
     *        description = "'.$attribute['comment'].'",';
                if($attribute['required']) {
                    $comment .= '
     *        required = true,';
                }
                $comment .= '
     *        type = "'.$attribute['type'].'",
     *     ),';
            }
            $comment .= '
     *     @SWG\Response(
     *         response = 200, 
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/{{modelName}}")
     *     ),
     *     @SWG\Response(
     *          response = 400,
     *          description = "failed"
     *     ),
     * )
     * @return object
     */';
            return self::popularVar($generator, $comment);
    }

    public static function update(Generator $generator)
    {
        $attributes = self::extractProperties($generator)['attributes'];
        $primaryKey = self::extractProperties($generator)['primaryKey'];
        $comment = '
    /**
     * @SWG\Put(path="/{{module}}/{{controllerNames}}/{{{primaryKey}}}",
     *     tags={"{{module}}"},
     *     summary="更新",
     *     produces={"application/json"},';
        foreach($primaryKey as $pk) {
            $comment .= '
     *    @SWG\Parameter(
     *        in = "path",
     *        name = "'.$pk.'",
     *        description = "'.$pk.'",
     *        required = true,
     *        type = "integer",
     *     ),';
        }
            foreach($attributes as $attribute)
            {
                if($attribute['isPrimaryKey'])
                    // 跳过主键
                    continue;

                $comment .= '
     *    @SWG\Parameter(
     *        in = "formData",
     *        name = "'.$attribute['name'].'",
     *        description = "'.$attribute['comment'].'",
     *        type = "'.$attribute['type'].'",
     *     ),';
            }
            $comment .= '
     *     @SWG\Response(
     *         response = 200, 
     *         description = "success",
     *         @SWG\Schema(ref = "#/definitions/{{modelName}}")
     *     ),
     *     @SWG\Response(
     *          response = 400,
     *          description = "failed"
     *     ),
     * )
     * @return object
     */';

            return self::popularVar($generator, $comment);
    }

    public static function delete(Generator $generator)
    {
        $comment = '
    /**
     * @SWG\Delete(path="/{{module}}/{{controllerNames}}/{{{primaryKey}}}",
     *     tags={"{{module}}"},
     *     summary="删除",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *        in = "path",
     *        name = "{{primaryKey}}",
     *        description = "{{primaryKey}}",
     *        required = true,
     *        type = "integer",
     *     ),
     *
     *     @SWG\Response(
     *         response = 204, 
     *         description = "success"
     *     ),
     *     @SWG\Response(
     *          response = 400,
     *          description = "failed"
     *     ),
     * )
     * @return null
     */';
        return self::popularVar($generator, $comment);
    }

    /******************************************protected functions*****************************************************/
    /***
     * replace placeholder with generator property
     * @param $object Generator generator object
     * @param $comment string comment to be popular
     * @return mixed
     */
    protected static function popularVar(Generator $object, $comment)
    {
        preg_match_all("/\{\{(\w+)\}\}/", $comment, $matches);
        foreach($matches[0] as $key => $value)
        {
            $prop = $matches[1][$key];
            $replace = self::extractProperties($object)[$prop];
            if(is_array($replace)) {
                $replace = join(',', $replace);
            }
            $comment = str_replace($value, $replace, $comment);
        }

        $comment .= "\n";

        return $comment;
    }

    /***
     * Extract generator properties to static $properties var
     * @param Generator $generator
     * @return array
     */
    protected static function extractProperties(Generator $generator)
    {
        try {
            $model = $generator->getControllerModel();
            $schema = $model::getTableSchema();
        }catch (\Exception $e) {

        }
        $properties['modelName'] = $generator->getControllerModelName();
        $properties['primaryKey'] = $schema->primaryKey;
        $properties['columns'] = $schema->columns;

        $properties['module'] = $generator->getModule();

        $properties['controllerNames'] = Inflector::pluralize($generator->getControllerId());

        $properties['attributes'] = $generator->getValidAttributes();

        return $properties;
    }
}