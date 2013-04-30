<?php
/**
 * @var $this \yii\base\View
 * @var $content string
 */
use yii\helpers\Html;
?>
<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?php echo Html::encode($this->title); ?></title>
	<?php echo Html::cssFile("css/bootstrap.min.css", array('media' => 'screen')); ?>
	<?php $this->head(); ?>
</head>
<body>
	<div class="container">
		<h1>Welcome</h1>
		<?php $this->beginBody(); ?>
		<?php echo $content; ?>
		<?php $this->endBody(); ?>
	</div>
</body>
</html>
<?php $this->endPage(); ?>