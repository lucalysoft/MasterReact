<?php
/**
 * COutputProcessor class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * COutputProcessor transforms the content into a different format.
 *
 * COutputProcessor captures the output generated by an action or a view fragment
 * and passes it to its {@link onProcessOutput} event handlers for further processing.
 *
 * The event handler may process the output and store it back to the {@link COutputEvent::output}
 * property. By setting the {@link CEvent::handled handled} property of the event parameter
 * to true, the output will not be echoed anymore. Otherwise (by default), the output will be echoed.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.0
 */
class COutputProcessor extends CFilterWidget
{
	/**
	 * Initializes the widget.
	 * This method starts the output buffering.
	 */
	public function init()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	/**
	 * Executes the widget.
	 * This method stops output buffering and processes the captured output.
	 */
	public function run()
	{
		$output=ob_get_clean();
		$this->processOutput($output);
	}

	/**
	 * Processes the captured output.
	 *
	 * The default implementation raises an {@link onProcessOutput} event.
	 * If the event is not handled by any event handler, the output will be echoed.
	 *
	 * @param string the captured output to be processed
	 */
	public function processOutput($output)
	{
		if($this->hasEventHandler('onProcessOutput'))
		{
			$event=new COutputEvent($this,$output);
			$this->onProcessOutput($event);
			if(!$event->handled)
				echo $output;
		}
		else
			echo $output;
	}

	/**
	 * Raised when the output has been captured.
	 * @param COutputEvent event parameter
	 */
	public function onProcessOutput($event)
	{
		$this->raiseEvent('onProcessOutput',$event);
	}
}
