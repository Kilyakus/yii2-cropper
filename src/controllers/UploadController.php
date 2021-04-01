<?php
namespace kilyakus\cutter\controllers;

/*
    How to use:

    add this to your config:

----------------------------------------------------------------------------

    'cutter' => [
        'class' => 'kilyakus\cutter\CutterModule',
    ],

----------------------------------------------------------------------------

    and use "/cutter/upload/image" url for upload images, or add

----------------------------------------------------------------------------

    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'image (or another as more like)' => [
                'class' => 'kilyakus\cutter\actions\UploadAction',
            ],
        ];
    }

----------------------------------------------------------------------------

    to your controller.




    Or just use:

----------------------------------------------------------------------------

    extends \kilyakus\cutter\controllers\UploadController

----------------------------------------------------------------------------

    if u like crutches.

*/

class UploadController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'image' => [
                'class' => 'kilyakus\cutter\actions\UploadAction',
                'baseDir' => '/uploads/photos/images',
                'basePath' => '@webroot/uploads/photos/images',
                'quality' => 100,
                'expansion' => '.png',
            ],
        ];
    }
}