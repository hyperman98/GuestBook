<?php
session_start();

$dbOptions = array(
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',
    'db_name' => 'guests'
);

// Класс для работы с базой данных
require 'Db.php';

// вспомогательные функции
require 'helpers.php';

// Соединение с бд
DB::init($dbOptions);

$per_page = 2;
$num_page = 2;
$result = DB::query('SELECT COUNT(*) AS numrows FROM users ');
$total = $result->fetch_object()->numrows;

//Определяем номер страницы, которую показываем
$start_row = (!empty($_GET['p'])) ? intval($_GET['p']): 0;
if ($start_row < 0) $start_row = 0;
if ($start_row > $total) $start_row = $total;
$result = DB::query('SELECT * FROM users ORDER BY date DESC LIMIT ' .$start_row.','.$per_page);

$items = array();
while ($row = $result->fetch_assoc()){
    $items[] = $row;
}
//Если нажата кнопка "Добавить отзыв"
if(!empty($_POST['submit'])){
    
    $now = time();
    $antiflood = 120;//Время в секундах для блокировки повторной отправки сообщения
        
    $errors = array(); 

    $name = (!empty($_POST['name'])) ? trim(strip_tags($_POST['name'])) : false;
    $user_email = (!empty($_POST['user_email']) && filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL)) ? $_POST['user_email'] : false;        
    $text = (!empty($_POST['text'])) ? trim(strip_tags($_POST['text'])) : false;
    $sex = (!empty($_POST['sex'])) ? intval($_POST['sex']) : 1;
    
    $keystring = (!empty($_POST['keystring'])) ? $_POST['keystring'] : false;

    // ANTIFLOOD
    if (!$antiflood || (!isset($_SESSION['time']) || $now - $antiflood >= $_SESSION['time']) )  {
        
        if (empty($name)) $errors[] = '<div class="error">Вы не заполнили поле "Представьтесь"!</div>'; 
        if (empty($user_email)) $errors[] = '<div class="error">Вы не корректно заполнили поле "Ваш e-mail"!</div>';
        if (empty($text)) $errors[] = '<div class="error">Вы не заполнили поле "Текст"!</div>'; 
        if (!$keystring || $keystring != $_SESSION['keystring']) $errors[] = '<div class="error">Вы не правильно ввели цифры с картинки!</div>'; 
                
        if (!empty($_FILES['image']['tmp_name'])) {            
                        
            $tmp_name = $_FILES['image']['tmp_name'];
            $file_mime = $_FILES['image']['type'];
            
            list($m1, $m2) = explode('/', $file_mime);
            if ($m1 == 'image') {
                $file_ext = strtolower(strrchr($_FILES['image']['name'],'.')); // получаем расширение файла        
                $file_name = uniqid(rand(9999,100000));// генерим уникальное имя
                
                $avatar = $file_name.$file_ext;
                
                if (move_uploaded_file($tmp_name, $appath.$uploaddir.'/'.$avatar)) {
                    
                    chmod($appath.$uploaddir.'/'.$avatar, 0666);
                    
                }
            }        
        }
     
        //Если ошибок нет пишем отзыв в базу
        if(!$errors){
            
            //Переводим IP адрес пользователя в безнаковое целое число
            $user_ip = sprintf("%u", ip2long($_SERVER['REMOTE_ADDR']));
            
            DB::query("INSERT INTO site_guestbook (name,email,message,date,user_ip) VALUES ('".DB::esc($name)."','".DB::esc($user_email)."','".DB::esc($text)."','".DB::esc($sex)."','".DB::esc($avatar)."','".$now."','".$user_ip."')");
            
            $_SESSION['time'] = $now;
            unset($_SESSION['keystring']);//Удаляем капчу из сессии  
            
            if(DB::getMySQLiObject()->affected_rows == 1){
                $errors[] = '<div class="error">Ваш отзыв успешно добавлен!</div>';
            }
            else{
                $errors[] = '<div class="error">Ваш отзыв не добавлен. Попробуйте позже!</div>';
            }
        }                
    }
    else{
        $errors[] = '<div class="error">Подождите '.ceil($antiflood/60).' минут(у,ы) перед отправкой следующего сообщения!</div>'; 
    }
    
}
?>
<!DOCUMENT html>
<html>
    <head>
        <meta charset="utf-8">
        <link href="style.css" rel="stylesheet">
        <script src="scripts/main.js"></script>
        <title>Гостевая книга</title>
    </head>
    <body>
        <div class="contentToChange">
    <h1>Отзывы</h1>

        <a name="top"></a>
        <div class="noFloat">
            <div class="titleText" onclick="show_form()"><a class="add_com_but">оставить отзыв</a>
<!--                <a class="add_com_but"><img src="images/show_com.png" alt=""></a>-->
            </div>
        </div>


     <div class="comments-block">
            <?php if(!empty($items)):foreach($items as $item): ?>
            <a name="comments-<?=$item['id']?>"></a>
            <div class="com-item-pad" id="com_<?=$item['id']?>">

                <div class="com-item">
                    <div class="user_info">>
                        <div class="info_panel">
                            <div class="fl-left">
                                <strong><?=$item['name']?></strong>                  
                                <span class="date"><?=$item['date']?></span>
                            </div>
                        </div>
                        <div class="com_body"><?=$item['message']?></div>
                    </div>                               
                </div>                                                                                   
            </div>
            <div id="com-form-wrap"></div>
            <?php endforeach; else:?>
            <div class="com-item"><h2>На данный момент нет активных отзывов!</h2></div>
            <?php endif;?>
                                                            
        </div>
        
        <?=pagination($total,$per_page,$num_page,$start_row,'/')?>
        
</div>
        <div class="add_com_block" id="add_com_block" style="display:<?=(!empty($errors))? 'block': 'none'?>;">
        <?=(!empty($errors))? '<div class="errors">'.implode($errors).'</div>': ''?>  
            <form action="index.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">    
            <label>Представьтесь:</label>
            <input class="text" name="name" value="<?=set_value('name');?>" type="text">
            <label>Ваш e-mail:</label>
            <input class="text" name="user_email" value="<?=set_value('user_email');?>" type="text">
            <label>Сообщение:</label>
              <textarea cols="15" rows="5" name="text" id="com_text"><?=set_value('text');?></textarea>

            <label>Аватар:</label>
              <input class="file" name="image" type="file">
            <div class="radios">
                  <label for="sex1">Мужчина: </label><input name="sex" id="sex1" class="radio" value="1" checked="checked" type="radio">&nbsp;&nbsp;&nbsp;
                <label for="sex2">Женщина: </label><input name="sex" id="sex2" class="radio" value="2" type="radio">
            </div> 
            
            <label>Введите цифры:</label>
            <div class="plusClear mb plusOverflow">
                 <?php require 'captcha.php';?>                 
                <input class="capch" name="keystring" value="" maxlength="6" style="font-size: 16pt;" type="text">
            </div>
            <div class="plusClear"><input class="but" name="submit" value="Отправить" type="submit"></div>

            <input name="email" value="" type="hidden">
            <input name="form" value="guestbook" type="hidden">
            <img class="hide_com" src="images/hide_com.gif" alt="" onclick="show_form();">
            </form>
        </div>
    </body>
</html>
