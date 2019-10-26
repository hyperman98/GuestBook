<?php 

require "captcha.class.php"; //Подключаем класс капчи

//�?нициализируем капчу
$captcha = new Captcha();

$_SESSION['keystring'] = $captcha->getKeyString();

echo $captcha->draw();

?>