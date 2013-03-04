<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use yii\base\Component;

/**
 * Validator is the base class for all validators.
 *
 * Child classes should override the [[validateAttribute()]] method to provide the actual
 * logic of performing data validation. Child classes may also override [[clientValidateAttribute()]]
 * to provide client-side validation support.
 *
 * Validator declares a set of [[builtInValidators|built-in validators] which can
 * be referenced using short names. They are listed as follows:
 *
 * - `boolean`: [[BooleanValidator]]
 * - `captcha`: [[CaptchaValidator]]
 * - `compare`: [[CompareValidator]]
 * - `date`: [[DateValidator]]
 * - `default`: [[DefaultValueValidator]]
 * - `double`: [[NumberValidator]]
 * - `email`: [[EmailValidator]]
 * - `exist`: [[ExistValidator]]
 * - `file`: [[FileValidator]]
 * - `filter`: [[FilterValidator]]
 * - `in`: [[RangeValidator]]
 * - `integer`: [[NumberValidator]]
 * - `match`: [[RegularExpressionValidator]]
 * - `required`: [[RequiredValidator]]
 * - `string`: [[StringValidator]]
 * - `unique`: [[UniqueValidator]]
 * - `url`: [[UrlValidator]]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Validator extends Component
{
	/**
	 * @var array list of built-in validators (name => class or configuration)
	 */
	public static $builtInValidators = array(
		'boolean' => 'yii\validators\BooleanValidator',
		'captcha' => 'yii\validators\CaptchaValidator',
		'compare' => 'yii\validators\CompareValidator',
		'date' => 'yii\validators\DateValidator',
		'default' => 'yii\validators\DefaultValueValidator',
		'double' => 'yii\validators\NumberValidator',
		'email' => 'yii\validators\EmailValidator',
		'exist' => 'yii\validators\ExistValidator',
		'file' => 'yii\validators\FileValidator',
		'filter' => 'yii\validators\FilterValidator',
		'in' => 'yii\validators\RangeValidator',
		'integer' => array(
			'class' => 'yii\validators\NumberValidator',
			'integerOnly' => true,
		),
		'match' => 'yii\validators\RegularExpressionValidator',
		'number' => 'yii\validators\NumberValidator',
		'required' => 'yii\validators\RequiredValidator',
		'string' => 'yii\validators\StringValidator',
		'unique' => 'yii\validators\UniqueValidator',
		'url' => 'yii\validators\UrlValidator',
	);

	/**
	 * @var array list of attributes to be validated.
	 */
	public $attributes;
	/**
	 * @var string the user-defined error message. Error message may contain some placeholders
	 * that will be replaced with the actual values by the validator.
	 * The `{attribute}` and `{value}` are placeholders supported by all validators.
	 * They will be replaced with the attribute label and value, respectively.
	 */
	public $message;
	/**
	 * @var array list of scenarios that the validator should be applied.
	 */
	public $on = array();
	/**
	 * @var array list of scenarios that the validator should not be applied to.
	 */
	public $except = array();
	/**
	 * @var boolean whether this validation rule should be skipped if the attribute being validated
	 * already has some validation error according to some previous rules. Defaults to true.
	 */
	public $skipOnError = true;
	/**
	 * @var boolean whether to enable client-side validation. Defaults to null, meaning
	 * its actual value inherits from that of [[\yii\web\ActiveForm::enableClientValidation]].
	 */
	public $enableClientValidation;

	/**
	 * Validates a single attribute.
	 * Child classes must implement this method to provide the actual validation logic.
	 * @param \yii\base\Model $object the data object to be validated
	 * @param string $attribute the name of the attribute to be validated.
	 */
	abstract public function validateAttribute($object, $attribute);

	/**
	 * Creates a validator object.
	 * @param string $type the validator type. This can be a method name,
	 * a built-in validator name, a class name, or a path alias of the validator class.
	 * @param \yii\base\Model $object the data object to be validated.
	 * @param array|string $attributes list of attributes to be validated. This can be either an array of
	 * the attribute names or a string of comma-separated attribute names.
	 * @param array $params initial values to be applied to the validator properties
	 * @return Validator the validator
	 */
	public static function createValidator($type, $object, $attributes, $params = array())
	{
		if (!is_array($attributes)) {
			$attributes = preg_split('/[\s,]+/', $attributes, -1, PREG_SPLIT_NO_EMPTY);
		}
		$params['attributes'] = $attributes;

		if (isset($params['on']) && !is_array($params['on'])) {
			$params['on'] = preg_split('/[\s,]+/', $params['on'], -1, PREG_SPLIT_NO_EMPTY);
		}

		if (isset($params['except']) && !is_array($params['except'])) {
			$params['except'] = preg_split('/[\s,]+/', $params['except'], -1, PREG_SPLIT_NO_EMPTY);
		}

		if (method_exists($object, $type)) {
			// method-based validator
			$params['class'] = __NAMESPACE__ . '\InlineValidator';
			$params['method'] = $type;
		} else {
			if (isset(self::$builtInValidators[$type])) {
				$type = self::$builtInValidators[$type];
			}
			if (is_array($type)) {
				foreach ($type as $name => $value) {
					$params[$name] = $value;
				}
			} else {
				$params['class'] = $type;
			}
		}

		return \Yii::createObject($params);
	}

	/**
	 * Validates the specified object.
	 * @param \yii\base\Model $object the data object being validated
	 * @param array|null $attributes the list of attributes to be validated.
	 * Note that if an attribute is not associated with the validator,
	 * it will be ignored.
	 * If this parameter is null, every attribute listed in [[attributes]] will be validated.
	 */
	public function validate($object, $attributes = null)
	{
		if (is_array($attributes)) {
			$attributes = array_intersect($this->attributes, $attributes);
		} else {
			$attributes = $this->attributes;
		}
		foreach ($attributes as $attribute) {
			if (!($this->skipOnError && $object->hasErrors($attribute))) {
				$this->validateAttribute($object, $attribute);
			}
		}
	}

	/**
	 * Returns the JavaScript needed for performing client-side validation.
	 *
	 * You may override this method to return the JavaScript validation code if
	 * the validator can support client-side validation.
	 *
	 * The following JavaScript variables are predefined and can be used in the validation code:
	 *
	 * - `attribute`: the name of the attribute being validated.
	 * - `value`: the value being validated.
	 * - `messages`: an array used to hold the validation error messages for the attribute.
	 *
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the name of the attribute to be validated.
	 * @return string the client-side validation script. Null if the validator does not support
	 * client-side validation.
	 * @see enableClientValidation
	 * @see \yii\web\ActiveForm::enableClientValidation
	 */
	public function clientValidateAttribute($object, $attribute)
	{
		return null;
	}

	/**
	 * Returns a value indicating whether the validator is active for the given scenario and attribute.
	 *
	 * A validator is active if
	 *
	 * - the validator's `on` property is empty, or
	 * - the validator's `on` property contains the specified scenario
	 *
	 * @param string $scenario scenario name
	 * @return boolean whether the validator applies to the specified scenario.
	 */
	public function isActive($scenario)
	{
		return !in_array($scenario, $this->except, true) && (empty($this->on) || in_array($scenario, $this->on, true));
	}

	/**
	 * Adds an error about the specified attribute to the model object.
	 * This is a helper method that performs message selection and internationalization.
	 * @param \yii\base\Model $object the data object being validated
	 * @param string $attribute the attribute being validated
	 * @param string $message the error message
	 * @param array $params values for the placeholders in the error message
	 */
	public function addError($object, $attribute, $message, $params = array())
	{
		$params['{attribute}'] = $object->getAttributeLabel($attribute);
		$params['{value}'] = $object->$attribute;
		$object->addError($attribute, strtr($message, $params));
	}

	/**
	 * Checks if the given value is empty.
	 * A value is considered empty if it is null, an empty array, or the trimmed result is an empty string.
	 * Note that this method is different from PHP empty(). It will return false when the value is 0.
	 * @param mixed $value the value to be checked
	 * @param boolean $trim whether to perform trimming before checking if the string is empty. Defaults to false.
	 * @return boolean whether the value is empty
	 */
	public function isEmpty($value, $trim = false)
	{
		return $value === null || $value === array() || $value === ''
			|| $trim && is_scalar($value) && trim($value) === '';
	}
}
