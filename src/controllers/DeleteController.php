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

    and use "/cutter/delete/image" url for delete images, or add

----------------------------------------------------------------------------

    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'image (or another as more like)' => [
                'class' => 'kilyakus\cutter\actions\DeleteAction',
            ],
        ];
    }

----------------------------------------------------------------------------

    to your controller.




    Or just use:

----------------------------------------------------------------------------

    extends \kilyakus\cutter\controllers\DeleteController

----------------------------------------------------------------------------

    if u like crutches.

*/

class DeleteController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;

    public function actions() {
        return [
            'image' => [
                'class' => 'kilyakus\cutter\actions\DeleteAction',
            ],
        ];
    }
}