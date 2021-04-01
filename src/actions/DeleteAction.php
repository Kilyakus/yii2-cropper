<?php
namespace kilyakus\cutter\actions;

use Yii;
use kilyakus\action\BaseAction as Action;

class DeleteAction extends Action
{
	public function run($class, $id, $attribute = 'image')
	{
		if(class_exists($class) && ($model = $class::findOne($id)))
		{
			$_POST[$attribute . '-remove'] = 1;
			$model->{$attribute} = null;
			$model->update();
			// fix
			Yii::$app->db->createCommand('UPDATE ' . $model::tableName() . ' SET ' . $attribute . '=:image WHERE ' . $model::primaryKey()[0] . '=:id', ['id' => $id, 'image' => null])->execute();
		} else {
			$this->error = Yii::t('easyii', 'Not found');
		}
		return $this->formatResponse(Yii::t('easyii', 'Photo deleted'));
	}
}