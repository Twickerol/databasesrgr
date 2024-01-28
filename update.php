<?php
function sanitize($item, $sanit_type){
   switch($sanit_type){
       case "string":
           $res=filter_var($item, FILTER_SANITIZE_STRING);
           break;
       case "float":
           $res=filter_var($item, FILTER_SANITIZE_NUMBER_FLOAT);
   }
    return $res;
}
function redirTab($message_1){
    $url = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 's' : '') . '://';
    //$url = $url . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $url = $url . $_SERVER['SERVER_NAME'] .$_SERVER['PHP_SELF']."?m=".$message_1;
        header("Location: {$url}");
}

global $iniFileArray, $iniFile, $dsn, $pdo, $h, $d, $output_rows, $max_index, $buttonPressed,
       $result_table, $message_result;
$iniFile = 'config.ini';
$iniFileArray = parse_ini_file($iniFile);
$redirect_pause=0;

$h = $iniFileArray['host'];
$d = $iniFileArray['db'];
$dsn = "mysql:host=$h;dbname=$d;charset=UTF8";
try {
   $pdo = new PDO($dsn, $iniFileArray['user'], $iniFileArray['pass']);
   if ($pdo) {
       $sql = "SELECT good_id, good_name, manufacturer, current_delivery_price, cur_delivery_price_date
      FROM `goods_reference` ORDER BY `good_id`ASC;";
       $result_table = $pdo->query($sql);
       $output_rows = $result_table->rowCount();
   }
   }catch (PDOException $e) {echo $e->getMessage();}

//Визначення, яка кнопка на формі натиснута
if(isset($_POST['insData'])){$buttonPressed="insData";}
if(isset($_POST['delData'])){$buttonPressed="delData";}
if(isset($_POST['updData'])){$buttonPressed="updData";}
if(isset($_POST['upd_search'])){$buttonPressed="upd_search";}

if(isset($_POST['insert'])) {//Додавання запису в таблицю
   //Отримання максимального індексу таблиці товарів
   $sql = "SELECT max(`good_id`) as MG FROM `goods_reference`";
   $result_search = $pdo->query($sql);
   $row2 = $result_search->fetch(pdo::FETCH_ASSOC);
   $next_record = $row2['MG']+1;
   //Запит на додавання
   $sql1 = $pdo->prepare("INSERT INTO `goods_reference`(`good_id`, `good_name`, `current_delivery_price`,
         `cur_delivery_price_date`, `manufacturer`) VALUES (:next, :good_name, :price, :date_m, :manufacturer)");
   $sql1->execute([':next'=>$next_record, ':good_name'=>$_POST['good_name'], ':price'=>$_POST['price'], ':date_m'=>$_POST['date_m'], ':manufacturer'=>$_POST['manufacturer']]);
   $pdo=null;
   redirTab(1);
}

if(isset($_POST['delete'])) {//Видалення запису з таблиці
   $sql2 = $pdo->prepare("DELETE FROM `goods_reference` WHERE good_id=:good_id");
   $sql2->execute([':good_id'=>$_POST['del_num']]);
   $pdo=null;
   redirTab(2);
}

if(isset($_POST['update'])) {//Коригування запису в таблиці
   //Запит на коригування
   $sql3 = $pdo->prepare("UPDATE `goods_reference` SET `good_name`=:good_name, `manufacturer`=:manufacturer, `current_delivery_price`= :cur_deliv_price, `cur_delivery_price_date`=:date_m WHERE `good_id`=:good_id");
   $sql3->execute([':good_name'=>$_POST['good_name'], ':manufacturer'=>$_POST['manufacturer'], ':cur_deliv_price'=>$_POST['price'], ':date_m'=>$_POST['date_m'], ':good_id'=>$_POST['upd_num']]);
   $pdo=null;
   $message_result="Запис ".$_POST['upd_num']." змінено";
   redirTab(3);
}
?>

<!DOCTYPE html>
<html>
<head>

   <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>БД Аптека: зміна даних</title>
   </head>
   <body>
   <h3>Модифікація таблиці товарів бази даних</h3>
   <table style="width:70%">
      <tr>
         <form method="POST">
            <input type='submit' name='insData' value='Додати'>
            <input type='submit' name='delData' value='Видалити'>
            <input type='submit' name='updData' value='Змінити'><br><br>
         </form>
      </tr>
      <tr>
        <?php
        //Виведеення таблиці товарів у вкладку
        if ($output_rows > 0) {
           echo "<b>"."Таблиця товарів"."</b>"."<br>"."<br>";
           echo "<table>";
               while ($row = $result_table->fetch(PDO::FETCH_ASSOC)) {
                  echo "<tr><td>". implode('</td><td>', $row)."</td></tr>";
                }
           echo "</table>";
        }  else {echo "Запит повернув 0 записів";}
        echo "</tr><tr>";
        if ($buttonPressed=="insData"){//Обробка натиснення кнопки Додати---------------
            $_GET['m']=0;
               echo "<br>

               <form method='POST'>
                   <b>Введіть значення</b><br>
                   <input type='text' id='good_name' name='good_name' required>
                   <label for='good_name'> - назва товару</label><br>
                   <input type='text' id='manufacturer' name='manufacturer' required>
                   <label for='manufacturer'> - виробник</label><br>
                   <input type='number' id='price' name='price' step='0.10' min='0.10' required>
                   <label for='price'> - поточна ціна реалізації</label><br>
                   <input type='date' id='date_m' name='date_m' required>
                   <label for='date_m'> - дата ціни реалізації</label><br>
                   <input type='submit' name='insert' value='Додати в перелік'>
               </form><br>";
          }

        if ($buttonPressed=="delData"){//Обробка натиснення кнопки Видалити---------------
            $_GET['m']=0;
            echo "<br>
            <form method='POST'>
               <input type='text' id='del_num' name='del_num' required>
               <label for='date_m'> - номер товару, який треба видалити</label><br>
               <input type='submit' name='delete' value='Видалити'>
            </form>
            <br>";
        }

        if ($buttonPressed=="updData"){//Обробка натиснення кнопки Редагувати---------------
            $_GET['m']=0;
                echo "<br>
                <form method='POST'>
                    <input type='number' id='upd_num' name='upd_num' step='1' required>
                    <label for='upd_num'> - номер товару, який треба відкоригвати</label><br>
                    <input type='submit' name='upd_search' value='Завантажити'><br><br><br>
                    </form>";
                }

        if ($buttonPressed=="upd_search"){
            $_GET['m']=0;
            $sql = "SELECT * FROM `goods_reference` WHERE `good_id` = ".$_POST['upd_num'];
            $result_upd = $pdo->query($sql);
            $row_upd = $result_upd->fetch(pdo::FETCH_ASSOC);

            echo "<br><br>
                <form method='POST'>
                   <input type='text' id='upd_num' name='upd_num' value='". $_POST['upd_num']."' required>
                   <label for='upd_num'> - номер товару, який треба відкоригвати</label><br>
                   <input type='submit' name='upd_search' value='Завантажити'><br><br><br>
                   <b>Відкоригуйте значення</b>><br>

                   <input type='text' id='good_name' name='good_name' value='".$row_upd['good_name']."' required>
                   <label for='good_name'> - назва товару</label><br>

                   <input type='text' id='manufacturer' name='manufacturer' value='". $row_upd['manufacturer']."' required>
                   <label for='manufacturer'> - виробник</label><br>

                   <input type='number' id='price' name='price' value='". $row_upd['current_delivery_price']."' step='0.10' min='0.10' required>
                   <label for='price'> - поточна ціна реалізації</label><br>

                   <input type='date' id='date_m' name='date_m' value='". $row_upd['cur_delivery_price_date']."' required>
                   <label for='date_m'> - дата ціни реалізації</label><br>
                   <input type='submit' name='update' value='Збереги'>
                   </form><br> ";
            }
        ?>

      </tr>
      <tr>
          <?php
          if (isset($_GET['m'])) {
              switch ($_GET['m']) {
                  case 1:
                      $message_result = "Запис додано";
                      break;
                  case 2:
                      $message_result = "Запис видалено";
                      break;
                  case 3:
                      $message_result = "Запис змінено";
              }
              echo "<br><div><b>".$message_result."</b></div>";
          }
          ?>
      </tr>
</table>
</body>
</html>