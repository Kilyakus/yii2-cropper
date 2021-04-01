<?php
namespace kilyakus\cutter;

use yii\base\Module as BaseModule;

class CutterModule extends BaseModule
{
    public $urlPrefix = 'cutter';

    public $dbConnection = 'db';

    public function getDb()
    {
        return \Yii::$app->get($this->dbConnection);
    }
}