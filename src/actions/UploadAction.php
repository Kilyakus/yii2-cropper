<?php
namespace kilyakus\cutter\actions;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\Json;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Palette\Color\RGB as RGBColor;
use kilyakus\action\BaseAction as Action;
use kilyakus\helper\media\Image as ImageProcessor;

class UploadAction extends Action
{
	public $expansion = '.png';

    public $baseDir;

    public $basePath;

    public $quality = 100;

	public function run($class, $id, $attribute = 'image')
	{
		if(class_exists($class) && ($model = $class::findOne($id)))
		{
			if(!empty($_FILES))
			{
				$model->{$attribute} = $_FILES;

				if($model->save()){
                    $success = [
                        'message' => Yii::t('easyii', 'Photo uploaded'),
                        'photo' => [
                            'id' => $model->primaryKey,
                            'image' => $model->{$attribute},
                            'thumb' => ImageProcessor::thumb($model->{$attribute}, $class::PHOTO_THUMB_WIDTH, $class::PHOTO_THUMB_HEIGHT),
                            'status' => $class::status($model),
                        ]
                    ];
                }
                else{
                    @unlink(Yii::getAlias('@webroot') . str_replace(Url::base(true), '', $model->image));
                    $this->error = Yii::t('easyii', 'Create error. {0}', $model->formatErrors());
                }
			}

		} else {
			$this->error = Yii::t('easyii', 'Not found');
		}
		return $this->formatResponse($success);
	}
}