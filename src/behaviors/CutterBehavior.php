<?php

namespace kilyakus\cutter\behaviors;

use Yii;
use yii\helpers\Json;
use yii\image\ImageDriver;
use yii\imagine\Image;
use Imagine\Image\Box;
use Imagine\Image\Point;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Palette\Color\RGB as RGBColor;

class CutterBehavior extends \yii\behaviors\AttributeBehavior
{
    public $expansion = '.png';

    public $attributes;

    public $baseDir;

    public $basePath;

    public $quality = 100;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeUpload',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpload',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    public function beforeUpload()
    {
        if (is_array($this->attributes) && count($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $this->upload($attribute);
            }
        } else {

            $this->upload($this->attributes);
        }
    }

    public function upload($attribute)
    {
        if ($uploadImage = UploadedFile::getInstance($this->owner, $attribute) ) {
            if (!$this->owner->isNewRecord) {
                $this->delete($attribute);
            }
            $cropping = $_POST[$attribute . '-cropping'];

            $croppingFileName = md5($uploadImage->name . $this->quality . Json::encode($cropping));
            $croppingFileExt = $this->expansion;

            $croppingFileBasePath = Yii::getAlias($this->basePath);

            if (!is_dir($croppingFileBasePath)) {
                mkdir($croppingFileBasePath, 0755, true);
            }
            $croppingFilePath = Yii::getAlias($this->basePath);

            if (!is_dir($croppingFilePath)) {
                mkdir($croppingFilePath, 0755, true);
            }

            $croppingFile = $croppingFilePath . DIRECTORY_SEPARATOR . $croppingFileName . $croppingFileExt;

            if(!empty($uploadImage->tempName)) {

                switch ($uploadImage->type) { 
                    case "image/webp": 
                        $temp = imagecreatefromwebp($uploadImage->tempName);
                        $temp = imagejpeg($temp, $uploadImage->tempName, 100);
                        break; 
                }

                $this->crop($uploadImage->tempName, $cropping, $croppingFile);

                $src = str_replace('\\', '/', Yii::getAlias($this->baseDir) . DIRECTORY_SEPARATOR . $croppingFileName . $croppingFileExt);

                list($width, $height) = getimagesize($uploadImage->tempName);
                if($width > 1920){
                    $maxWidth = 1920;
                    $maxHeight = ($height/$width)*$maxWidth;
                    Image::getImagine()->open($_SERVER['DOCUMENT_ROOT'] . $src)->thumbnail(new Box($maxWidth, $maxHeight))->save($_SERVER['DOCUMENT_ROOT'] . $src, ['quality' => 100]);
                }

                $this->owner->{$attribute} = $src;
            }
        } elseif (isset($_POST[$attribute . '-remove']) && $_POST[$attribute . '-remove']) {
            $this->delete($attribute);
        } elseif (isset($this->owner->oldAttributes[$attribute])) {

            if(($cropping = $_POST[$attribute . '-cropping']) && !empty($cropping['dataX'])){

                $oldFile = $_SERVER['DOCUMENT_ROOT'] . $this->owner->oldAttributes[$attribute];

                $pathInfo = pathinfo($oldFile);

                $croppingFileName = md5($pathInfo['filename'] . $this->quality . Json::encode($cropping));
                $croppingFileExt = '.' . $pathInfo['extension'];

                $croppingFileBasePath = Yii::getAlias($this->basePath);

                if (!is_dir($croppingFileBasePath)) {
                    mkdir($croppingFileBasePath, 0755, true);
                }
                $croppingFilePath = Yii::getAlias($this->basePath);

                if (!is_dir($croppingFilePath)) {
                    mkdir($croppingFilePath, 0755, true);
                }

                $croppingFile = $croppingFilePath . DIRECTORY_SEPARATOR . $croppingFileName . $croppingFileExt;

                $this->crop($oldFile, $cropping, $croppingFile);

                $this->owner->{$attribute} = str_replace('\\', '/', Yii::getAlias($this->baseDir) . DIRECTORY_SEPARATOR . $croppingFileName . $croppingFileExt);

            }else{

                $this->owner->{$attribute} = $this->owner->oldAttributes[$attribute];

            }
        }
    }

    public function crop($uploadImage, $cropping, $croppingFile)
    {
        if($cropping['dataX'] !== '' && $cropping['dataY'] !== '' && $cropping['dataWidth'] !== '' && $cropping['dataHeight'] !== ''){
            $imageTmp = Image::getImagine()->open($uploadImage);
            $imageTmp->rotate($cropping['dataRotate']);

            $palette = new RGB();
            $color = $palette->color('fff', 0);

            $image = Image::getImagine()->create($imageTmp->getSize(), $color);
            $image->paste($imageTmp, new Point(0, 0));

            $point = new Point($cropping['dataX'], $cropping['dataY']);
            $box = new Box($cropping['dataWidth'], $cropping['dataHeight']);

            $image->crop($point, $box);
            $image->save($croppingFile, ['quality' => $this->quality]);
        }
    }

    public function beforeDelete()
    {
        if (is_array($this->attributes) && count($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $this->delete($attribute);
            }
        } else {
            $this->delete($this->attributes);
        }
    }

    public function delete($attribute)
    {
        $name_image = $this->owner->oldAttributes[$attribute];
        if(!empty($name_image)) {
            $mack = Yii::getAlias($this->basePath) . DIRECTORY_SEPARATOR . $name_image . '*';
            @array_map("unlink", glob($mack));
        }
    }

    public function getImgOrigin($attribute=false)
    {
        if(!is_array($this->attributes)) {
            $attribute = $this->attributes;
        }
        return $this->baseDir.'/'.$this->owner->$attribute.$this->expansion;
    }

    public function getImg($size=500, $attribute=false)
    {
        if(!is_array($this->attributes)) {
            $attribute = $this->attributes;
        }
        return self::getImgUrl($this->owner->$attribute, $size);
    }

    public function getImgUrl($img, $size=500)
    {
        $image = $this->baseDir.'/'.$img.'_'.$size.'x'.$size.$this->expansion;
        $image_path = $this->basePath.'/'.$img.'_'.$size.'x'.$size.$this->expansion;
        if(file_exists($image_path)) {
            return $image;
        } else {
            $file = $this->basePath.'/'.$img.$this->expansion;
            if(!file_exists($file)) {
                return false;
            }
            $image = new ImageDriver(['driver' => 'GD']);
            $image = $image->load($file);
            $image->resize($size,$size);
            $image->save($this->basePath.'/'.$img.'_'.$size.'x'.$size.$this->expansion, 100);
            return $this->baseDir.'/'.$img.'_'.$size.'x'.$size.$this->expansion;
        }
    }

    public static function getImageUrl($basePath, $baseDir, $img, $size=500, $expansion='.png')
    {
        $image = $baseDir.'/'.$img.'_'.$size.'x'.$size.$expansion;
        $image_path = Yii::getAlias($basePath).'/'.$img.'_'.$size.'x'.$size.$expansion;
        if(file_exists($image_path)) {
            return $image;
        } else {
            $file = $basePath.'/'.$img.$expansion;
            if(!file_exists($file)) {
                return false;
            }
            $image = new ImageDriver(['driver' => 'GD']);
            $image = $image->load($file);
            $image->resize($size,$size);
            $image->save($basePath.'/'.$img.'_'.$size.'x'.$size.$expansion, 100);
            return $baseDir.'/'.$img.'_'.$size.'x'.$size.$expansion;
        }
    }
}