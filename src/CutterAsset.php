<?php
namespace kilyakus\cutter;

class CutterAsset extends \yii\web\AssetBundle
{
    public $depends = [
        'yii\web\JqueryAsset',
        'kilyakus\fontawesome\FontAwesomeAsset',
        'kilyakus\toastr\ToastrAsset',
        'kilyakus\switcher\SwitcherAsset',
        'kilyakus\widget\range\RangeAsset',
        'kilyakus\cutter\ControlsAsset',
    ];

    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets/cutter';

        $this->js[] = 'widget-cropper.js';
        $this->js[] = 'widget-cutter.js';

        $this->css[] = 'widget-cropper.css';
        $this->css[] = 'widget-cutter.css';

        parent::init();
    }
}