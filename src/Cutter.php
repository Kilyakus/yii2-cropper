<?php
namespace kilyakus\cutter;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Inflector;
use yii\bootstrap\Modal;
use kilyakus\web\widgets\ButtonGroup;
use kilyakus\helper\media\Image;

/*
	For model behaviors:

	'image' => [
		'class' => CutterBehavior::className(),
		'attributes' => 'image',
		'baseDir' => '/uploads/' . dirname . '/images',
		'basePath' => '@webroot/uploads/' . dirname . '/images',
	]

	Examples:

	echo $form->field($model, 'image')->widget(Cutter::className(), [
		'cropperOptions' => [
			'aspectRatio' => '16/9',
			'aspectRatioHidden' => true,
			'positionsHidden' => true,
			'sizeHidden' => true,
			'rotateHidden' => true
		]
	])

	Cutter::widget([
		'model' => $model,
		'name' => 'image',
		'cropperOptions' => [
			'aspectRatio' => '16/9',
			'aspectRatioHidden' => true,
			'positionsHidden' => true,
			'sizeHidden' => true,
			'rotateHidden' => true
		]
	])
*/

class Cutter extends \yii\widgets\InputWidget
{
	public $imageContainer = [];

	public $imageOptions;

	public $settingsOptions;

	public $useWindowHeight = true;

	public $cropperOptions = [];

	public $defaultCropperOptions = [
		'rotatable' => true,
		'zoomable' => true,
		'movable' => true,
	];

	public $buttonUploadIcon = 'fa fa-upload';
	public $buttonUploadText;
	public $buttonUploadEllipsis = true;
	public $buttonUploadSrc;

	public $buttonEditIcon = 'fa fa-edit';
	public $buttonEditText;
	public $buttonEditEllipsis = true;

	public $buttonDeleteIcon = 'fa fa-trash';
	public $buttonDeleteText;
	public $buttonDeleteEllipsis = true;
	public $buttonDeleteSrc;

	public $thumbWidth = 640;
	public $thumbHeight;

	public $title;
	public $description;

	public function init()
	{
		parent::init();

		$this->registerTranslations();

		$this->cropperOptions = array_merge($this->cropperOptions, $this->defaultCropperOptions);
	}

	public function run()
	{
		if (is_null($this->imageOptions)) {
			$this->imageOptions = [
				'class' => 'img-responsive',
			];
		}

		$this->imageOptions['id'] = Yii::$app->getSecurity()->generateRandomString(10);

		$inputField = Html::getInputId($this->model, $this->attribute) . '-' . $this->id;

		echo Html::beginTag('div', ['id' => $inputField . '-cutter']);

		$image = $_SERVER['DOCUMENT_ROOT'] . $this->model->{$this->attribute};

		$initialSizeWidth = 300;
		$initialSizeHeight = 300;

		if(is_file($image)){
			list($width, $height) = getimagesize($image);
			if($width)
				{
				if( $width > $height ){

				  $adaptedWidth = $initialSizeWidth;
				  $coefficienWidth = $width / $initialSizeWidth;
				  $prevHeight = $height / $coefficienWidth;

				  $adaptedHeight = $initialSizeHeight;
				  $coefficienHeight = $prevHeight / $initialSizeHeight;
				  $adaptedWidth = $initialSizeWidth / $coefficienHeight;

				}else{

				  $adaptedHeight = $initialSizeHeight;
				  $coefficienHeight = $width / $initialSizeHeight;
				  $prevWidth = $height / $coefficienHeight;

				  $adaptedWidth = $initialSizeWidth;
				  $coefficienWidth = $prevWidth / $initialSizeWidth;
				  $adaptedHeight = $initialSizeHeight / $coefficienWidth;

				}
			}

		}

		if(!$adaptedWidth){
			$adaptedWidth = $initialSizeWidth;
		}
		if(!$adaptedHeight){
			$adaptedHeight = $initialSizeHeight;
		}

		if($this->model){
			$inputModel = (new \ReflectionClass($this->model))->getShortName() . '['.$this->attribute.']';
		}
		echo Html::input('hidden',$inputModel);
		echo Html::input('file',$inputModel, null, ['id' => $inputField, 'class' => 'hidden uplaod-image-input', 'href' => $this->buttonUploadSrc]);
		// echo Html::input('file',$inputModel,$this->model->{$this->attribute},['id' => $inputField . '-edit', 'class' => 'hidden']);

		// echo Html::input('hidden', $this->attribute . '-edit', null);

		echo Html::beginTag('div', ['class' => 'preview-pane',]);

		echo Html::beginTag('div', ['id' => $inputField.'-css','class' => 'preview-container', 'style' => '--preview-image:url(\'' . Image::thumb($this->model->{$this->attribute}, $this->thumbWidth, $this->thumbHeight) . '\');']);

		echo $this->getControls($inputField);

		// echo Html::beginTag('div', ['id' => $inputField.'-css','class' => 'btn-group position-absolute']);
		// 	echo Html::tag('label', '<i class="fa fa-edit"></i>' . $this->buttonEditText, ['id' => $inputField . '-edit', 'class' => 'btn btn-cutter']);
		// 	echo Html::tag('label', '<i class="fa fa-upload"></i> ' . $this->buttonUploadText, ['for' => $inputField, 'class' => 'btn btn-cutter']);
		// echo Html::endTag('div');

		// echo Html::beginTag('a', ['class' => $this->model->{$this->attribute} ? 'plugin-box' : '', 'href' => $this->model->{$this->attribute}, 'data-caption' => '<strong>' . $this->title . '</strong><p>' . $this->description . '</p>', 'data-pjax' => '0']);
		
		echo Html::beginTag('a', $this->imageContainer);

		echo Html::img(Image::thumb($this->model->{$this->attribute}, $this->thumbWidth, $this->thumbHeight), [
			'class' => 'preview-image img-responsive img-rounded',
		]);
		echo Html::endTag('a');

		echo Html::endTag('div');
		echo Html::endTag('div');
		
		// if($this->model->{$this->attribute}){
		// 	echo Html::checkbox($this->attribute . '-remove', false, ['class' => 'switch',
		// 		'label' => Yii::t('kilyakus/cutter/cutter', 'REMOVE')
		// 	]);
		// }

		$modelClass = (new \ReflectionClass($this->model))->getShortName();

		$disableOptions =  isset($this->cropperOptions['aspectRatioHidden']) && isset($this->cropperOptions['rotateHidden']) && isset($this->cropperOptions['sizeHidden']) && isset($this->cropperOptions['positionsHidden']);

		Modal::begin([
			// 'header' => Html::tag('h4', Yii::t('kilyakus/cutter/cutter', 'CUTTER')),
			'header' => '<h4 class="text-center">' . Yii::t('kilyakus/cutter/cutter','Upload new photo') . '</h4><p class="text-center text-gray">Вы можете загрузить изображение в формате JPG, GIF или PNG.</p>',
			'closeButton' => [],
			'options' => ['class' => 'modal-cutter'],
			'footer' => $this->getModalFooter(),
			'size' => Modal::SIZE_LARGE,
		]);

		echo Html::beginTag('div', ['class' => 'cropper-body']);

		echo Html::beginTag('div', ['class' => 'cropper-container']);
		echo Html::beginTag('div', ['class' => 'cropper-preview']);
		echo Html::img($this->model->{$this->attribute}, $this->imageOptions);
		echo Html::endTag('div');
		echo $this->getToolbar($inputField);
		echo Html::endTag('div');

		if(!$disableOptions){
			echo Html::beginTag('div', ['class' => 'cropper-properties']);
		}else{
			echo Html::beginTag('div', ['class' => 'hidden']);
		}
		echo Html::beginTag('div', ['class' => 'form-group'.(isset($this->cropperOptions['aspectRatioHidden']) ? ' hidden' : '')]);
		echo Html::label(Yii::t('kilyakus/cutter/cutter', 'ASPECT_RATIO'), $inputField . '-aspectRatio');
		$resolutions = ['' => 'Responsive','16/9' => 'Full HD (16:9)','21/9' => 'Widescreen (21:9)','16/10' => 'Widescreen (16:10)','5/3' => 'Widescreen (5:3)','5/4' => 'Landscape (5:4)','4/3' => 'Landscape (4:3)','4/4' => 'Square (4:4)','12/16' => 'Vertical (12:16)'];
		echo Html::dropDownList(
			$modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][aspectRatio]',
			isset($this->cropperOptions['aspectRatio']) ? $this->cropperOptions['aspectRatio'] : 0,
			$resolutions,
			[
				'id' => $inputField . '-aspectRatio',
				'class' => 'form-control',
				'data-target' => '#'.$inputField.'-aspectRatio'
		]);

		// echo Html::textInput($this->attribute . '-aspectRatio', isset($this->cropperOptions['aspectRatio']) ? $this->cropperOptions['aspectRatio'] : 0, ['id' => $inputField . '-aspectRatio', 'class' => 'form-control']);
		echo Html::endTag('div');

		echo Html::beginTag('div', ['class' => 'form-group'.(isset($this->cropperOptions['rotateHidden']) ? ' hidden' : '')]);
		echo Html::label(Yii::t('kilyakus/cutter/cutter', 'ANGLE'), $inputField . '-dataRotate');
		echo Html::input('range', $modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataRotate]','',['id' => $inputField . '-dataRotate', 'class' => 'form-control','data-range-value' => 0,'data-range-min' => 0, 'data-range-max' => 360, 'data-range-step' => 1, 'min' => 0, 'max' => 360]);
		// echo Html::textInput($modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataRotate]', '', ['id' => $inputField . '-dataRotate', 'class' => 'form-control']);
		echo Html::endTag('div');

		echo Html::beginTag('div', ['class' => 'row'.(isset($this->cropperOptions['positionsHidden']) ? ' hidden' : '')]);
			echo Html::beginTag('div', ['class' => 'col-xs-12 col-md-6']);
				echo Html::beginTag('div', ['class' => 'form-group']);
					echo Html::label(Yii::t('kilyakus/cutter/cutter', 'POSITION') . ' (X)', $inputField . '-dataX');
					echo Html::input('number', $modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataX]', '', ['id' => $inputField . '-dataX', 'class' => 'form-control']);
				echo Html::endTag('div');
			echo Html::endTag('div');
			echo Html::beginTag('div', ['class' => 'col-xs-12 col-md-6']);
				echo Html::beginTag('div', ['class' => 'form-group']);
					echo Html::label(Yii::t('kilyakus/cutter/cutter', 'POSITION') . ' (Y)', $inputField . '-dataY');
					echo Html::input('number', $modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataY]', '', ['id' => $inputField . '-dataY', 'class' => 'form-control']);
				echo Html::endTag('div');
			echo Html::endTag('div');
		echo Html::endTag('div');

		echo Html::beginTag('div', ['class' => 'row'.(isset($this->cropperOptions['sizeHidden']) ? ' hidden' : '')]);
			echo Html::beginTag('div', ['class' => 'col-xs-12 col-md-6']);
				echo Html::label(Yii::t('kilyakus/cutter/cutter', 'WIDTH'), $inputField . '-dataWidth');
				echo Html::textInput($modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataWidth]', '', ['id' => $inputField . '-dataWidth', 'class' => 'form-control','readonly' => true]);
			echo Html::endTag('div');
			echo Html::beginTag('div', ['class' => 'col-xs-12 col-md-6']);
				echo Html::label(Yii::t('kilyakus/cutter/cutter', 'HEIGHT'), $inputField . '-dataHeight');
				echo Html::textInput($modelClass . '[' . $this->attribute . '-cropping][' . $this->model->primaryKey . '][dataHeight]', '', ['id' => $inputField . '-dataHeight', 'class' => 'form-control','readonly' => true]);
			echo Html::endTag('div');
		echo Html::endTag('div');

		echo Html::endTag('div');
		echo Html::endTag('div');

		Modal::end();

		echo Html::endTag('div');

		$view = $this->getView();

		CutterAsset::register($view);
		
		$view->registerJs("
			toastr.options = {
				'closeButton': true,
				'debug': false,
				'newestOnTop': false,
				'progressBar': true,
				'positionClass': 'toast-bottom-right',
				'preventDuplicates': false,
				'onclick': null,
				'showDuration': '300',
				'hideDuration': '1000',
				'timeOut': '5000',
				'extendedTimeOut': '5000',
				'showEasing': 'swing',
				'hideEasing': 'linear',
				'showMethod': 'fadeIn',
				'hideMethod': 'fadeOut'
			}
			
			$('form[type=cropper] [id*=_button_accept],form[type=cropper] [name*=-remove]').on('click',function(){
				var parent = $($(this).parents('form')).get(0);
				$(parent).submit();
				if($(this).attr('name') && $(this).attr('name').indexOf('-remove') !== -1){
					setTimeout(function(){
						toastr['success']('" . Yii::t('kilyakus/cutter/cutter', 'DELETED') . "');
					},0);
				}else{
					setTimeout(function(){
						toastr['success']('" . Yii::t('kilyakus/cutter/cutter', 'UPLOADING') . "');
					},0);
				}
			})",
		yii\web\View::POS_END,'widget-cutter-toastr-' . $inputField);

		$view->registerJs('
			if($(document).find("input[type=range]").length != 0){
			  $("input[type=range]").range({
				tooltip_position: "bottom",
				formatter: function(value) {
				  return value;
				}
			  });
			}
			$(document).ready(function(e){
				var tooltips_range = $(".tooltip-main[role=presentation]");
				for (var i = 0; i < tooltips_range.length; i++) {
					$(tooltips_range[i]).addClass("in");
				}
			});
		');

		$options = [
			'inputField' => $inputField,
			'useWindowHeight' => $this->useWindowHeight,
			'cropperOptions' => $this->cropperOptions
		];

		$options = Json::encode($options);

		$view->registerJs('jQuery("#' . $inputField . '").cutter(' . $options . ');',$view::POS_READY,'widget-cutter-'.$inputField);

		$adaptedHeight = $this->thumbHeight ? $this->thumbHeight : $adaptedHeight;

		if($this->thumbHeight != null){
			$view->registerCss('
				#'.$inputField.'-css.preview-container,
				#'.$inputField.'-css.preview-container .preview-image {min-height:' . $this->thumbHeight . 'px;}
			');
		}

		$view->registerCss('
			#'.$inputField.'-css.preview-container {max-width:' . $adaptedWidth . 'px;max-height:' . $adaptedHeight . 'px;}
		');
	}

	public function registerTranslations()
	{
		Yii::$app->i18n->translations['kilyakus/cutter/*'] = [
			'class' => 'yii\i18n\PhpMessageSource',
			'sourceLanguage' => 'en-US',
			'basePath' => '@vendor/kilyakus/yii2-widget-cutter/src/messages',
			'fileMap' => [
				'kilyakus/cutter/cutter' => 'cutter.php',
			],
		];

		if($this->buttonUploadText === null){
			$this->buttonUploadText = Yii::t('kilyakus/cutter/cutter', 'DOWNLOAD');
		}

		if($this->buttonUploadEllipsis === true){
			$this->buttonUploadText .= ' ...';
		}

		if(!$this->buttonUploadSrc){
			$this->buttonUploadSrc = \yii\helpers\Url::to(['/cutter/upload/image', 'class' => get_class($this->model), 'id' => $this->model->primaryKey, 'attribute' => $this->attribute]);
		}

		if($this->buttonEditText === null){
			$this->buttonEditText = Yii::t('kilyakus/cutter/cutter', 'EDIT');
		}

		if($this->buttonEditEllipsis === true){
			$this->buttonEditText .= ' ...';
		}

		if($this->buttonDeleteText === null){
			$this->buttonDeleteText = Yii::t('kilyakus/cutter/cutter', 'REMOVE');
		}

		if($this->buttonDeleteEllipsis === true){
			$this->buttonDeleteText .= ' ...';
		}

		if(!$this->buttonDeleteSrc){
			$this->buttonDeleteSrc = \yii\helpers\Url::to(['/cutter/delete/image', 'class' => get_class($this->model), 'id' => $this->model->primaryKey, 'attribute' => $this->attribute]);
		}
	}

	private function getControls($inputField)
	{
		$view = $this->getView();

		return Html::beginTag('div', ['class' => 'controls-container']) .

		Html::checkbox(null, null, ['class' => 'controls-toggler']) . 
		Html::beginTag('label', ['for' => 'controls-toggler']) .
		Html::tag('i', null, ['class' => 'fa fa-cog']) .
		Html::endTag('label') .

		Html::beginTag('ul') . 

			Html::beginTag('li', ['class' => 'controls-item']) .
			Html::tag('label', Html::tag('i', null, ['class' => $this->buttonUploadIcon]), ['for' => $inputField, 'class' => 'uplaod-image-button', 'data-toggle' => 'kt-tooltip', 'data-placement' => 'left', 'data-original-title' => $this->buttonUploadText]) .
			Html::endTag('li') .

			(
				$this->model->{$this->attribute} ?
				Html::beginTag('li', ['class' => 'controls-item']) .
				Html::tag('label', Html::tag('i', null, ['class' => $this->buttonEditIcon]), ['id' => $inputField . '-edit', 'data-toggle' => 'kt-tooltip', 'data-placement' => 'left', 'data-original-title' => $this->buttonEditText]) .
				Html::endTag('li') .

				Html::beginTag('li', ['class' => 'controls-item']) .
				Html::a(Html::tag('i', null, ['class' => $this->buttonDeleteIcon]), $this->buttonDeleteSrc, ['class' => 'delete-photo', 'data-confirm' => Yii::t('kilyakus/cutter/cutter', 'REMOVE'), 'data-toggle' => 'kt-tooltip', 'data-placement' => 'left', 'data-original-title' => $this->buttonDeleteText]) .
				Html::endTag('li') : ''
			) .

		Html::endTag('ul') .
		Html::endTag('div');
	}

	private function getToolbar($inputField)
	{
		return Html::beginTag('div', [
			'class' => 'btn-toolbar'
		]) .
		ButtonGroup::widget([
			'encodeLabels' => false,
			'vertical' => true,
			'buttons' => [
				[
					'label' => '<i class="fa fa-arrows-alt"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'setDragMode',
						'data-option' => 'move',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'DRAG_MODE_MOVE'),
					]
				],
				[
					'label' => '<i class="fa fa-cut"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'setDragMode',
						'data-option' => 'crop',
						'class' => 'btn btn-cutter',
						'data-title' => Yii::t('kilyakus/cutter/cutter', 'DRAG_MODE_CROP'),
					]
				],
			],
		]) .
		ButtonGroup::widget([
			'encodeLabels' => false,
			'vertical' => true,
			'buttons' => [
				[
					'label' => '<i class="fa fa-vector-square"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'crop',
						'class' => 'btn btn-cutter',
						'data-title' => Yii::t('kilyakus/cutter/cutter', 'CROP'),
					]
				],
				[
					'label' => '<i class="fa fa-compress"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'reset',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'REFRESH'),
					]
				],
				[
					'label' => '<i class="fa fa-expand"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'clear',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'REMOVE'),
					]
				],
			],
		]) .
		ButtonGroup::widget([
			'encodeLabels' => false,
			'vertical' => true,
			'buttons' => [
				[
					'label' => '<i class="fa fa-search-plus"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'zoom',
						'data-option' => '0.1',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'ZOOM_IN'),
					],
					'visible' => $this->cropperOptions['zoomable']
				],
				[
					'label' => '<i class="fa fa-search-minus"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'zoom',
						'data-option' => '-0.1',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'ZOOM_OUT'),
					],
					'visible' => $this->cropperOptions['zoomable']
				],
				[
					'label' => '<i class="fa fa-redo icon-flipped"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'rotate',
						'data-option' => '-45',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'ROTATE_LEFT'),
					],
					'visible' => $this->cropperOptions['rotatable']
				],
				[
					'label' => '<i class="fa fa-redo"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'rotate',
						'data-option' => '45',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'ROTATE_RIGHT'),
					],
					'visible' => $this->cropperOptions['rotatable']
				],
			],
		]) .
		ButtonGroup::widget([
			'encodeLabels' => false,
			'vertical' => true,
			'buttons' => [
				[
					'label' => '<i class="glyphicon glyphicon-glyphicon glyphicon-resize-full"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'setAspectRatio',
						'data-target' => '#' . $inputField . '-aspectRatio',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'SET_ASPECT_RATIO'),
					]
				],
				[
					'label' => '<i class="glyphicon glyphicon-upload"></i>',
					'options' => [
						'type' => 'button',
						'data-method' => 'setData',
						'class' => 'btn btn-cutter',
						'title' => Yii::t('kilyakus/cutter/cutter', 'SET_DATA'),
					]
				],
			],
			'options' => [
				'class' => 'hidden'
			]
		]) .
		Html::endTag('div');
	}

	private function getModalFooter()
	{
		return Html::button(Yii::t('kilyakus/cutter/cutter', 'CANCEL'), [
			'id' => $this->imageOptions['id'] . '_button_cancel', 'class' => 'btn btn-danger'
		]) . Html::button(Yii::t('kilyakus/cutter/cutter', 'ACCEPT'), [
			'id' => $this->imageOptions['id'] . '_button_accept', 'class' => 'btn btn-primary'
		]);
	}
}