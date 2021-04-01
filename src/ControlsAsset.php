<?php
namespace kilyakus\cutter;

class ControlsAsset extends \yii\web\AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/assets/addons';

        $this->css[] = 'widget-controls.css';

        parent::init();
    }
}