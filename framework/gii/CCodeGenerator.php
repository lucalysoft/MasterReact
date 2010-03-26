<?php
/**
 * CCodeGenerator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCodeGenerator is the base class for code generator classes.
 *
 * CCodeGenerator is a controller that predefines several actions for code generation purpose.
 * Derived classes mainly need to configure the {@link codeModel} property
 * override the {@link getSuccessMessage} method. The former specifies which
 * code model (extending {@link CCodeModel}) that this generator should use,
 * while the latter should return a success message to be displayed when
 * code files are successfully generated.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.gii
 * @since 1.1.2
 */
class CCodeGenerator extends Controller
{
	/**
	 * @var string the layout to be used by the generator. Defaults to 'generator'.
	 */
	public $layout='generator';
	/**
	 * @var array a list of available code templates (name=>path)
	 */
	public $templates=array();
	/**
	 * @var string the code model class. This can be either a class name (if it can be autoloaded)
	 * or a path alias referring to the class file.
	 * Child classes must configure this property with a concrete value.
	 */
	public $codeModel;

	private $_viewPath;

	/**
	 * The code generation action.
	 * This is the action that displays the code generation interface.
	 * Child classes mainly need to provide the 'index' view for collecting user parameters
	 * for code generation.
	 */
	public function actionIndex()
	{
		$model=$this->prepare();
		if($model->files!=array() && isset($_POST['generate'], $_POST['answers']))
		{
			$model->answers=$_POST['answers'];
			$model->status=$model->save() ? CCodeModel::STATUS_SUCCESS : CCodeModel::STATUS_ERROR;
		}

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * The code preview action.
	 * This action shows up the specified generated code.
	 */
	public function actionCode()
	{
		$model=$this->prepare();
		if(isset($_GET['id']) && isset($model->files[$_GET['id']]))
		{
			$this->renderPartial('/common/code', array(
				'file'=>$model->files[$_GET['id']],
			));
		}
		else
			throw new CHttpException(404,'Unable to find the code you requested.');
	}

	/**
	 * The code diff action.
	 * This action shows up the difference between the newly generated code and the corresponding existing code.
	 */
	public function actionDiff()
	{
		Yii::import('gii.components.TextDiff');

		$model=$this->prepare();
		if(isset($_GET['id']) && isset($model->files[$_GET['id']]))
		{
			$file=$model->files[$_GET['id']];
			if(!in_array($file->type,array('php', 'txt','js','css')))
				$diff=false;
			else if($file->operation===CCodeFile::OP_OVERWRITE)
				$diff=TextDiff::compare(file_get_contents($file->path), $file->content);
			else
				$diff='';

			$this->renderPartial('/common/diff',array(
				'file'=>$file,
				'diff'=>$diff,
			));
		}
		else
			throw new CHttpException(404,'Unable to find the code you requested.');
	}

	/**
	 * Returns the view path of the generator.
	 * The "views" directory under the directory containing the generator class file will be returned.
	 * @return string the view path of the generator
	 */
	public function getViewPath()
	{
		if($this->_viewPath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_viewPath=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'views';
		}
		return $this->_viewPath;
	}

	/**
	 * @param string the view path of the generator.
	 */
	public function setViewPath($value)
	{
		$this->_viewPath=$value;
	}

	/**
	 * Renders the common interface for code generation.
	 * This includes the template selector, the submit buttons and the code preview table.
	 * @param CCodeModel the current code model
	 * @param CActiveForm the form
	 */
	public function renderGenerator($model,$form)
	{
		$this->renderPartial('/common/generator', array(
			'model'=>$model,
			'form'=>$form,
		));
	}

	/**
	 * @param CCodeModel the current code model
	 * @return string the message to be displayed when the newly generated code is saved successfully.
	 */
	public function getSuccessMessage($model)
	{
		return 'The code has been generated successfully.';
	}

	/**
	 * @param CCodeModel the current code model
	 * @return string the message to be displayed when some error occurred during code file saving.
	 */
	public function getErrorMessage($model)
	{
		return 'There was some error when generating the code. Please check the following messages.';
	}

	/**
	 * Prepares the code model.
	 */
	protected function prepare()
	{
		if($this->codeModel===null)
			throw new CException(get_class($this).'.codeModel property must be specified.');
		$modelClass=Yii::import($this->codeModel,true);
		$model=new $modelClass;
		$model->templates=$this->templates;
		if(isset($_POST[$modelClass]))
		{
			$model->attributes=$_POST[$modelClass];
			$model->status=CCodeModel::STATUS_PREVIEW;
			if($model->validate())
				$model->prepare();
		}
		return $model;
	}
}