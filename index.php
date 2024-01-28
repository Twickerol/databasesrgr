<?php
function sqlPassword($input) {
    $pass = strtoupper(sha1(sha1($input, true)));
    $pass = '*' . $pass;
    return $pass;
}
global $pdo1, $connected, $iniFileArray, $iniFile, $echo_connection;
$iniFile = 'config.ini';

//Регулярні вирази для пошуку фраз в файлі config.ini
$regUser='/user\\s=\\s\'[a-zA-Z0-9._-]*\'/i';
$regPass='/pass\\s=\\s\'[\*a-zA-Z0-9._-]*\'/i';

$echo_connection="";

//Регулярні вирази для перевірки введених ім'я користувача ы пароля
$regValidateUsername="/^[A-Za-z][A-Za-z0-9_-]{3,20}$/i";
$refValidatePass="/^[A-Za-z][a-zA-Z0-9,._-]{3,30}$/i";
$isUsernameValid=true;
$isPasswordValid=true;

$iniFileArray = parse_ini_file($iniFile);
if (empty($iniFileArray['user'])) {$connected=0;}
    else {$connected=1;}
//Обробка натиснення кнопки з'єднання з БД
if(isset($_POST['connectButton'])) {//З'єднання з БД та запис user, pass в config.ini

    $isUsernameValid=preg_match($regValidateUsername, $_POST['user_id']);
    $isPasswordValid=preg_match($refValidatePass, $_POST['password']);

    if ($connected == 0) {
        if (($isUsernameValid) and ($isPasswordValid)) {
            $h = $iniFileArray['host'];
            $d = $iniFileArray['db'];
            $dsn = "mysql:host=$h;dbname=$d;charset=UTF8";
            try {
                $pdo1 = new PDO($dsn, $_POST['user_id'], sqlPassword($_POST['password']));
                if ($pdo1) {
                    $echo_connection="Connected to the " . $iniFileArray['db'] . " database successfully!<br>";
                    //echo "Connected to the " . $iniFileArray['db'] . " database successfully!<br>";
                    $userReplace = "user = '" . $_POST['user_id'] . "'";
                    $passReplace = "pass = '" . sqlPassword($_POST['password']) . "'";
                    file_put_contents($iniFile, preg_replace($regUser, $userReplace, file_get_contents($iniFile)));
                    file_put_contents($iniFile, preg_replace($regPass, $passReplace, file_get_contents($iniFile)));
                    $connected = 1;
                }
            } catch (PDOException $e) {
                //echo $e->getMessage();
                $echo_connection=$e->getMessage();
            }
        }
    }
    else { //Від'єднання від БД та видалення user, pass з config.ini
        $pdo1=null;
        $connected=0;
        $userReplace = "user = ''";
        $passReplace = "pass = ''";
        file_put_contents($iniFile, preg_replace($regUser, $userReplace, file_get_contents($iniFile)));
        file_put_contents($iniFile, preg_replace($regPass, $passReplace, file_get_contents($iniFile)));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>БД Аптека</title>
</head>
<body>
    <h2>Операції з базою даних</h2>

    <table style="width:100%">
        <tr>
            <td style="width:10%">
                <form method="post">
                    <label><b>Введіть облікові дані</b></label><br>
                    <input type="text" size="30" name="user_id"
                        <?php
                            if ($isUsernameValid) {echo 'placeholder="Користувач (4-20 симв.)"';}
                                else {echo 'placeholder="Введіть ім\'я ще раз"';}
                            if ($connected==0) {echo " required";}
                            ?>><br>
                    <input type="password" size="30" name="password"
                        <?php
                            if ($isPasswordValid) {echo 'placeholder="Пароль (4-30 симв.)"';}
                                else {echo 'placeholder="Введіть пароль ще раз"';}
                            if ($connected==0) {echo " required";}
                            ?>><br>
                    <input type="submit" name="connectButton" value=
                    <?php
                       if ($connected==0) {echo "З'єднатись";}
                          else {echo "Роз'єднати";}
                    ?>><br><?php
                                echo $echo_connection;
                                ?><br><br>
                </form>
            </td>
            <td style="width:30%">
                <form action="select.php" target="_blank" method="POST">
                    <label for="personalButton">Вивід даних про робітників та товари</label>
                    <input type="submit" name="personalButton" value="Перейти"
                           <?php if ($connected==0) {echo "disabled";}?>><br><br>
                </form>
                <form action="update.php?m=0" target="_blank" method="POST">
                    <label for="goodsButton">Модифікація БД</label>
                    <input type="submit" name="goodsButton" value="Перейти"
                            <?php if ($connected==0) {echo "disabled";}?>><br><br>
                </form>

            </td>
        </tr>
    </table>
</body>
</html>