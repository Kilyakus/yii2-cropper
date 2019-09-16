<?php
namespace kilyakus\cutter;

class CutterAsset extends \yii\web\AssetBundle
{
    public $depends = [
        'yii\web\JqueryAsset',
        'kilyakus\font\FontAwesomeAsset',
        'kilyakus\toastr\ToastrAsset',
        'kilyakus\switcher\SwitcherAsset',
        'kilyakus\range\RangeAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets';

        $this->js[] = 'js/cropper.js';
        $this->js[] = 'js/cutter.js';

        $this->css[] = 'css/cropper.css';
        $this->css[] = 'css/cutter.css';

        parent::init();
    }
}