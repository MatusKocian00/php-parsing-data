<?php
include "eat.php";
include "freeFood.php";

$fileCreated = false;

// Read the JSON file
if(!file_exists('./storage/fileDelikanti.json')){
    touch('./storage/fileDelikanti.json');
    $fp = fopen('./storage/fileDelikanti.json', 'w');
    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => []];
    fwrite($fp, json_encode($data));
    fclose($fp);

    $fileCreated = true;
}

// Read the JSON file
$json = file_get_contents('./storage/fileDelikanti.json');

$interval = 0;
$timeDifference = 0;

// Decode the JSON file
$json_data = json_decode($json,true);

$foods = $json_data['data'];
$foodsEat = $jedla;

$foodsPrinted = $foods;
$foodsEatPrinted = $foodsEat;
$freeFoodsPrinted = $freeFood;

$interval = date_diff( DateTime::createFromFormat( 'U', $json_data['timestamp'] ), new DateTime());
$timeDifference = (new DateTime())->getTimestamp() - $json_data['timestamp'];

if($timeDifference > 800 || $fileCreated) {
    $ch = curl_init();

// set url
    curl_setopt($ch, CURLOPT_URL, "https://www.delikanti.sk/prevadzky/3-jedalen-prif-uk/");

//return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// $output contains the output string
    $output = curl_exec($ch);

// close curl resource to free up system resources
    curl_close($ch);

    $dom = new DOMDocument();

    @$dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;
    $tables = $dom->getElementsByTagName('table');

    $rows = $tables->item(0)->getElementsByTagName('tr');
    $index = 0;
    $dayCount = 0;

    $foods = [];
    $foodCount = $rows->item(0)->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

    foreach ($rows as $row) {

        if($row->getElementsByTagName('th')->item(0)){
            $foodCount = $row->getElementsByTagName('th')->item(0)->getAttribute('rowspan');

            $day = trim($rows->item($index)->getElementsByTagName('th')->item(0)->getElementsByTagName('strong')->item(0)->nodeValue);

            $th = $rows->item($index)->getElementsByTagName('th')->item(0);

            foreach($th->childNodes as $node)
                if(!($node instanceof \DomText))
                    $node->parentNode->removeChild($node);

            $date = trim($rows->item($index)->getElementsByTagName('th')->item(0)->nodeValue);


            array_push($foods, ["date" => $date, "day" => $day, "menu" => []]);

            for($i = $index; $i <  $index + intval($foodCount); $i++)
            {
                if($foods[$dayCount])
                    array_push($foods[$dayCount]["menu"], trim($rows->item($i)->getElementsByTagName('td')->item(1)->nodeValue));
            }
            $index += intval($foodCount);
            $dayCount++;
        }

    }

    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $foods];

    $fp = fopen('./storage/fileDelikanti.json', 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles/style.css">
    <title>JEDLO</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>
<body>
<section class="w-full h-full">
<table class="table">
    <thead class="thead-dark">
        <tr>
            <th  style='cursor: pointer' onclick="showAll()">All week</th>
            <?php
            $index = 0;

            foreach ($foodsEat as $item){
                if(isset($item['day'])) {
                    echo "<th id='$index' style='cursor: pointer' onclick='headerClick($index)'>";
                    echo $item['day'];
                    echo "<br/>";
                    echo $item['date'];
                    echo "</th>";
                }
                $index++;
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th>Eat</th>
            <?php
                $index = 0;

                foreach ($foodsEatPrinted as $item){
                    if(isset($item['menu'])){
                        echo "<td style='padding: 10px; width: 14%'><div id='col-eat-$index'>".implode("<hr/>",$item['menu'])."</div></td>";
                    }

                    $index++;
                }
            ?>
        </tr>
        <tr>
            <th>Delikanti</th>
            <?php
                $index = 0;

                foreach ($foodsPrinted as $item){
                    if(isset($item['menu'])){
                        echo "<td style='padding: 10px'><div id='col-del-$index'>".implode("<hr/>",$item['menu'])."</div></td>";
                    }

                    $index++;
                }
            ?>
        </tr>
        <tr>
            <th>FreeFood</th>
            <?php
                $index = 0;
                foreach ($freeFoodsPrinted as $item){
                    if(isset($item['menu'])){
                        echo "<td id='col-$index' style='padding: 10px'><div id='col-kol-$index'>".implode("<hr/>",$item['menu'])."</div></td>";
                    }
                    $index++;
                }
            ?>
        </tr>
    </tbody>
</table>
</section>

<script>
    const headerClick = (id) => {
        console.log(id)

        for (let i = 0; i < 7; i++){
            if(i !== id) {
                document.querySelector("#col-eat-" + i).style.display = 'none';
                document.querySelector("#col-del-" + i).style.display = 'none';
                document.querySelector("#col-kol-" + i).style.display = 'none';
            } else {
                document.querySelector("#col-eat-" + i).style.display = 'table-cell';
                document.querySelector("#col-del-" + i).style.display = 'table-cell';
                document.querySelector("#col-kol-" + i).style.display = 'table-cell';
            }
        }
    }

    const showAll = () =>{
        for (let i = 0; i < 7; i++){
            document.querySelector("#col-eat-" + i).style.display = 'table-cell';
            document.querySelector("#col-del-" + i).style.display = 'table-cell';
            document.querySelector("#col-kol-" + i).style.display = 'table-cell';
        }
    }
</script>
</body>
</html>