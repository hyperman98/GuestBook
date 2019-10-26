<?
session_start();
$dbOptions = array(
	'db_host' => 'localhost',
	'db_user' => 'root',
	'db_pass' => '',
	'db_name' => 'guests'
);

require 'DB.class.php';

DB::init($dbOptions);


if (isset($_POST['submit'])) {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $query = DB::query("SELECT * FROM users WHERE email='".$login."' AND password='".$password."'");
    while ($row = $query->fetch_assoc()) {
        $dblogin = $row['email'];
        $dbpassword = $row['password'];
        $is_admin = $row['is_admin'];
    }          
    if (($login == $dblogin) && ($password == $dbpassword) && $is_admin == 1) {
        $_SESSION['user'] = $login;
        header("Location: admin.php");
    } else {
        echo 'Вы ввели неверные данные';
      }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link href="styles/admin.css" rel="stylesheet" >
    </head>
    <body>
        <section class="container">
            <div class="login">
              <h1>Войти в личный кабинет</h1>
              <form method="post">
                <p><input type="text" name="login" value="" placeholder="Логин или Email"></p>
                <p><input type="text" name="password" value="" placeholder="Пароль"></p>
                <p class="submit"><input type="submit" name="submit" value="Войти"></p>
              </form>
            </div>
        </section>
    </body>
</html>