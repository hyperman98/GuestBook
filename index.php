<?php 
session_start();

/* Конфигурация базы данных. Добавьте свои данные */
$dbOptions = array(
	'db_host' => 'localhost',
	'db_user' => 'root',
	'db_pass' => '',
	'db_name' => 'guests'
);

require "DB.class.php"; //Подключаем класс для работы с базой данных
require "helper.php"; //Подключаем вспомогательные функции

// Соединение с базой данных
DB::init($dbOptions);

$per_page = 25; //Максимальное число сообщений на одной странице
$num_page = 2;


//Получаем общее число сообщений
$result = DB::query('SELECT COUNT(*) AS numrows FROM users');
$total = $result->fetch_object()->numrows;

$start_row = (!empty($_GET['p']))? intval($_GET['p']): 0;
if($start_row < 0) $start_row = 0;
if($start_row > $total) $start_row = $total;


//Получаем список активных сообщений
$result = DB::query('SELECT * FROM users ORDER BY date DESC LIMIT '.$start_row.','.$per_page);

if (isset($_POST['name_sorting'])) {
    $result = DB::query("SELECT * FROM users ORDER BY name LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['name_sorting_desc'])) {
    $result = DB::query("SELECT * FROM users ORDER BY name DESC LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['date_sorting'])) {
    $result = DB::query("SELECT * FROM users ORDER BY date LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['date_sorting_desc'])) {
    $result = DB::query("SELECT * FROM users ORDER BY date DESC LIMIT ".$start_row.','.$per_page);    
}
else if (isset($_POST['mail_sorting'])) {
    $result = DB::query("SELECT * FROM `users` ORDER BY email LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['mail_sorting_desc'])) {
    $result = DB::query("SELECT * FROM `users` ORDER BY email DESC LIMIT ".$start_row.','.$per_page);
}                    
$items = array();
while($row = $result->fetch_assoc()){
        $row['date'] = format_date($row['date'],'date').'|'.format_date($row['date'],'time');
	$items[] = $row;
}



//Если нажата кнопка "Добавить отзыв"
if(!empty($_POST['submit'])){
	
	$now = time();
		
    $errors = array(); 

    $name = (!empty($_POST['name'])) ? trim(strip_tags($_POST['name'])) : false;
    $user_email = (!empty($_POST['user_email']) && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) ? $_POST['user_email'] : false;        
    $text = (!empty($_POST['text'])) ? trim(strip_tags($_POST['text'])) : false;
    $keystring = (!empty($_POST['keystring'])) ? $_POST['keystring'] : false;
    
    if (empty($name)) $errors[] = '<div class="error">Empty field "Name"</div>'; 
    if (empty($user_email)) $errors[] = '<div class="error">Incorrect field "E-mail"</div>';
    if (empty($text)) $errors[] = '<div class="error">Empty field "Message"</div>'; 
    if (!$keystring || $keystring != $_SESSION['keystring']) $errors[] = '<div class="error">Incorrect Captcha</div>'; 
        
        //Если ошибок нет пишем отзыв в базу
        if(!$errors){
        	
        	//Переводим IP адрес пользователя в безнаковое целое число
        	$user_ip = $_SERVER['REMOTE_ADDR'];
                $browser = $_SERVER['HTTP_USER_AGENT'];
        	
        	DB::query("INSERT INTO users (name,email,message,date,ip, browser) VALUES ('".DB::esc($name)."','".DB::esc($user_email)."','".DB::esc($text)."','".$now."','".$user_ip."','".$browser."')");
        	
        	$_SESSION['time'] = $now;
        	unset($_SESSION['keystring']);//Удаляем капчу из сессии  
			
        	if(DB::getMySQLiObject()->affected_rows == 1){
        		$errors[] = '<div class="error">Success!</div>';
        	}
        	else{
        		$errors[] = '<div class="error">Review not added!</div>';
        	}				
	}
}

?>
<!DOCTYPE html>
<html>
    <head>
	<title>Guest book</title>
	<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script> 
	<script type="text/javascript" src="js/scripts.js"></script> 
    </head>
    <body>
    
	<div class="contentToChange">
	<h1>Reviews</h1>

        <a name="top"></a>
        <div class="noFloat">
    	    <div class="titleText" onclick="show_form()">Make Review
                <a class="add_com_but"><img src="images/show_com.png" alt=""></a>
            </div>    	    
        </div>
	      
        <div class="add_com_block" id="add_com_block" style="display:<?=(!empty($errors))? 'block': 'none'?>;">
        <?=(!empty($errors))? '<div class="errors">'.implode($errors).'</div>': ''?>  
            <form action="index.php" method="post" enctype="multipart/form-data">	
                <label>Name:</label>
                <input class="text" name="name" value="<?=set_value('name');?>" type="text"><br>
		    <label>E-mail:</label>
                    <input class="text" name="user_email" value="<?=set_value('user_email');?>" type="text"><br>
                    <label>Homepage</label>
                    <input class="text" type="text"><br>
		    <label>Message:</label>
                    <textarea cols="15" rows="5" name="text" id="com_text"><?=set_value('text');?></textarea><br>
		    
                    <label>Input digits:</label>
                    <div class="plusClear mb plusOverflow">
                                   <?php require 'captcha.php';?>		  	   
                            <input class="capch" name="keystring" value="" maxlength="6" style="font-size: 16pt;" type="text">
                    </div>
                    <div class="plusClear"><input class="but" name="submit" value="Send" type="submit"></div>

                    <input name="email" value="" type="hidden">
                    <input name="form" value="guestbook" type="hidden">
                        <img class="hide_com" src="images/hide_com.gif" alt="" onclick="show_form();">
            </form>

        </div>
<table class="comments-block">
    <tr>
        <th>Name</th>
        <th>Date</th>
        <th>Email<th>
    </tr>
    <?if(!empty($items)):
    foreach ($items as $item):?>
    <tr>
        <td><?=$item['name'];?></td>
        <td class="date"><?=$item['date'];?></td>
        <td class="com_body"><?=$item['email'];?></td>
    </tr>
    <div id="com-form-wrap"></div>
    <? endforeach;?>
  
    <?else:?>
    <div class="com-item"><h2>No active reviews</h2></div>
    <? endif;?>
    <form method="post">
    <input type="submit" name="name_sorting" value="Sorting by name">
    <input type="submit" name="name_sorting_desc" value="Sorting by name DESC">
    <input type="submit" name="date_sorting" value="Sorting by date">
    <input type="submit" name="date_sorting_desc" value="Sorting by date DESC">
    <input type="submit" name="mail_sorting" value="Sorting by mail">
    <input type="submit" name="mail_sorting_desc" value="Sorting by mail DESC">
    </form>
</table>
<?=pagination($total,$per_page,$num_page,$start_row,'/')?>		
</div>
</body>
</html>

