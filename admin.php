<?php
session_start();
/* Конфигурация базы данных. Добавьте свои данные */
$dbOptions = array(
	'db_host' => 'localhost',
	'db_user' => 'root',
	'db_pass' => '',
	'db_name' => 'guests'
);

require "DB.class.php";
require 'helper.php';

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
$result = DB::query('SELECT * FROM users WHERE is_block = 1 ORDER BY date DESC LIMIT '.$start_row.','.$per_page);

if (isset($_POST['name_sorting'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY name LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['name_sorting_desc'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY name DESC LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['date_sorting'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY date LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['date_sorting_desc'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY date DESC LIMIT ".$start_row.','.$per_page);    
}
else if (isset($_POST['mail_sorting'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY email LIMIT ".$start_row.','.$per_page);
}
else if (isset($_POST['mail_sorting_desc'])) {
    $result = DB::query("SELECT * FROM users WHERE is_block = 1 ORDER BY email DESC LIMIT ".$start_row.','.$per_page);
}                    
$items = array();
while($row = $result->fetch_assoc()){
        $row['date'] = format_date($row['date'],'date').' '.format_date($row['date'],'time');
	$items[] = $row;
}

if (isset($_POST['logout'])) {
    unset($_SESSION['user']);
}


?>
<!DOCTYPE html>
<html>
    <head>
        <link href="styles/style.css" rel="stylesheet">  
    </head>
    <body>
       <div class="contentToChange">
           <h1>Block reviews</h1>
<?if (!empty($_SESSION['user'])) { echo '<p>Здравствуйте, администратор</p><form method="post"><input type="submit" name="logout" value="Выйти"></form>';}?>
        <table class="comments-block">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Date</th>
        <th>Email<th>
    </tr>
    <?if(!empty($items)):
        foreach ($items as $item):
        if (isset($_POST[$item['id']])) {
                DB::query("UPDATE users SET is_block = 0 WHERE id = ".$item['id']);
            }
        
        ?>
    <tr <?=$item['is_block'] == 0 ? "hidden" : ""?>>
        <td><?=$item['id']?></td>
        <td><?=$item['name'];?></td>
        <td class="date"><?=$item['date'];?></td>
        <td class="com_body"><?=$item['email'];?></td>   
        <td><?if (!empty($_SESSION['user'])) {?><form method="post"><input type="submit" name="<?=$item['id']?>" value="Разблокировать"/></form><?}?></td>
    </tr>
    <div id="com-form-wrap"></div>
    <? endforeach;?>
  
    <?else:?>
    <div class="com-item"><h2>No active reviews</h2></div>
    <? endif;?>
    <br>
    <form method="post">
    <input type="submit" name="name_sorting" value="Sorting by name">
    <input type="submit" name="name_sorting_desc" value="Sorting by name DESC">
    <input type="submit" name="date_sorting" value="Sorting by date">
    <input type="submit" name="date_sorting_desc" value="Sorting by date DESC">
    <input type="submit" name="mail_sorting" value="Sorting by mail">
    <input type="submit" name="mail_sorting_desc" value="Sorting by mail DESC">
    </form>
</table>
       </div>
    </body>
</html>