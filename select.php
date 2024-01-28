<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>БД Аптека: вибір інформації</title>
</head>
<body>
<h3>Вивід даних з бази</h3>
<table style="width:70%">
    <tr>
        <form method="POST">
            <input type="radio" id="type1" value="person" name="type">Вивід даних працівників<br>
            <input type="radio" id="type2" value="goods" name="type" checked>Перелік товарів
            <input type="checkbox" id="goods_ware" value="warehouse_goods" name="warehouse_goods">(тільки наявні на складі)<br><br>
            <input type='submit' name='selectBDButton' value='Вибрати з БД'><br><br><br>
        </form>
    </tr>
    <tr>
        <?php
        global $pdo1, $iniFileArray, $iniFile;
        $iniFile = 'config.ini';
        $iniFileArray = parse_ini_file($iniFile);//Обробка натиснення кнопки виборки з БД
        if(isset($_POST['selectBDButton'])) {
            $h = $iniFileArray['host'];
            $d = $iniFileArray['db'];
            $dsn = "mysql:host=$h;dbname=$d;charset=UTF8";
            try {
                $pdo = new PDO($dsn, $iniFileArray['user'], $iniFileArray['pass']);
                if ($pdo) {
                    //Перевірка радіо кнопок, checkbox та формування відповідного SQL запиту
                    $radioVal = $_POST["type"];

                    $output_column = 0;
                    switch($radioVal){
                        case "person":
                            //SQL запит щодо працівників
                            $outputTitle = 'Дані щодо працівників';
                            $sql = "SELECT `personal`.`full_name`, `position_reference`.`position_name`
                                FROM `personal`
                                LEFT JOIN `position_assignment` ON `position_assignment`.`personal_id` = `personal`.`personal_id`
                                LEFT JOIN `position_reference` ON `position_assignment`.`position_id` = `position_reference`.`position_id`
                                ORDER by position_assignment.position_id ";
                            $output_column = 2;
                            break;
                        case "goods":
                            if (isset($_POST['warehouse_goods'])){
                                //SQL запит щодо товарів, наявних на складі
                                $outputTitle = 'Дані щодо товарів, наявних на складі';
                                $sql = "SELECT `goods_reference`.`good_name`, `goods_reference`.`manufacturer`, `warehouse_goods`.`quantity`, `goods_reference`.`current_delivery_price`
                                      FROM `goods_reference`, `warehouse_goods`
                                      WHERE `warehouse_goods`.`good_id` = `goods_reference`.`good_id`
                                      ORDER BY `goods_reference`.`current_delivery_price` DESC;";
                                $output_column = 4;
                            } else {
                                //SQL запит щодо повного переліку товарів
                                $outputTitle = 'Дані щодо повного переліку товарів';
                                $sql = "SELECT `good_name`, `manufacturer`, `current_delivery_price`
                                      FROM `goods_reference`
                                      ORDER BY `good_id` ASC;";
                                $output_column = 3;
                            }
                    }
                    $result = $pdo->query($sql);
                    $output_rows = $result->rowCount();
                    if ($output_rows > 0) {
                        echo "<b>".$outputTitle."</b>"."<br>"."<br>";
                        $i = 1;
                        ?><table><?php
                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            ?><tr>
                                <td><?php echo $i."."?></td>
                                <td><?php echo implode('</td><td>', $row); ?></td>
                              </tr><?php
                              $i = $i + 1;
                        }
                        ?></table><?php
                    } else {echo "Запит повернув 0 записів";}
                }
            }
            catch (PDOException $e) {echo $e->getMessage();}
        }
        ?>
    </tr>
</table>
</body>
</html>