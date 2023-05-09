<style>
    /* Сообщения об ошибках и поля с ошибками выводим с красным бордюром. */
    .error {
        border: 2px solid red;
    }
</style>
<?php

/**
 * Файл login.php для не авторизованного пользователя выводит форму логина.
 * При отправке формы проверяет логин/пароль и создает сессию,
 * записывает в нее логин и id пользователя.
 * После авторизации пользователь перенаправляется на главную страницу
 * для изменения ранее введенных данных.
 **/

// Отправляем браузеру правильную кодировку,
// файл login.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// Начинаем сессию.
session_start();

// В суперглобальном массиве $_SESSION хранятся переменные сессии.
// Будем сохранять туда логин после успешной авторизации.
if (!empty($_SESSION['login'])) {
    // Если есть логин в сессии, то пользователь уже авторизован.
    // TODO: Сделать выход (окончание сессии вызовом session_destroy()
    //при нажатии на кнопку Выход).
    // Делаем перенаправление на форму.
    header('Location: ./');
}

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    $errors = array();
    $errors['login'] = !empty($_COOKIE['login_error']);
    $errors['pass'] = !empty($_COOKIE['password_error']);

    if (!empty($errors['login'])) {
        setcookie('login_error', '', 100000);
        $messages['login'] = '<p class="msg">Такого аккаунта не существует</p>';
    }
    if (!empty($errors['pass'])) {
        setcookie('password_error', '', 100000);
        $messages['pass'] = '<p class="msg">Вы не заполнили пароль</p>';
    }
    if (!empty($messages)) {
        foreach ($messages as $message) {
            print($message);
        }
    }
?>

    <form action="" method="post">
        <input <?php  if (empty($errors['login'])) print 'class="error"'?> name="login" />
        <input <?php  if (empty($errors['pass'])) print 'class="error"'?> name="pass" />
        <input type="submit" value="Войти" />
    </form>

<?php
}
// Иначе, если запрос был методом POST, т.е. нужно сделать авторизацию с записью логина в сессию.
else {
    $errors = FALSE;
    $login = $_POST['login'];
    $password = $_POST['pass'];
    if (empty($login)) {
        setcookie('login_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }
    if (empty($password)) {
        setcookie('password_error', '1', time() + 24 * 60 * 60);
        $errors = TRUE;
    }

    if ($errors) {
        header('Location: login.php');
        exit();
    }
    // TODO: Проверть есть ли такой логин и пароль в базе данных.
    // Выдать сообщение об ошибках.
    $user = 'u52802';
    $pass = '7560818';
    $db = new PDO('mysql:host=localhost;dbname=u52802', $user, $pass, [PDO::ATTR_PERSISTENT => true]);
    $stmt = $db->prepare('SELECT user_id FROM user WHERE (login = ?) AND (password = ?) ');
    $stmt->execute([$login, md5($password)]);
    var_dump($_POST['login']);
    var_dump($_POST['pass']);
    var_dump(md5($_POST['pass']));
    var_dump($stmt->rowCount());
    if ($stmt->rowCount() > 0) {
        $_SESSION['login'] = $_POST['login'];
        $stmt = $db->prepare("SELECT app_id FROM user WHERE login = ?");
        $stmt->execute([$login]);
        var_dump($stmt->fetchColumn());
        $_SESSION['uid'] = $stmt->fetchColumn();
    }
    else{
        setcookie('login_error', '1', time() + 24 * 60 * 60);
//         header('Location: login.php');
//         exit();
    }
//     header('Location: ./');
}
