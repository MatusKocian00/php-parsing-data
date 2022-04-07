<?php
$fileCreated = false;

// Read the JSON file
if(!file_exists('./storage/kozlovna.json')){
    touch('./storage/kozlovna.json');
    $fp = fopen('./storage/kozlovna.json', 'w');
    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => []];
    fwrite($fp, json_encode($data));
    fclose($fp);

    $fileCreated = true;
}

// Read the JSON file
$json = file_get_contents('./storage/kozlovna.json');
// Decode the JSON file
$json_data = json_decode($json,true);
$kozlovna = $json_data['data'];
$interval = date_diff( DateTime::createFromFormat( 'U', $json_data['timestamp'] ), new DateTime());
$timeDifference = (new DateTime())->getTimestamp() - $json_data['timestamp'];

if($timeDifference > 800 || $fileCreated) {
    $ch = curl_init();
// set url
    curl_setopt($ch, CURLOPT_URL, "http://www.zilinskakozlovna.sk/menu");
//return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// $output contains the output string
    $output = curl_exec($ch);
// close curl resource to free up system resources
    curl_close($ch);

    $dom = new DOMDocument();

    @$dom->loadHTML($output);
    $dom->preserveWhiteSpace = false;

    $kozlovna = [
        ["date" => date('d.m.Y', strtotime('monday this week')), "day" => "Pondelok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('tuesday this week')), "day" => "Utorok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('wednesday this week')), "day" => "Streda", "menu" => []],
        ["date" => date('d.m.Y', strtotime('thursday this week')), "day" => "Štvrtok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('friday this week')), "day" => "Piatok", "menu" => []],
        ["date" => date('d.m.Y', strtotime('saturday this week')), "day" => "Sobota", "menu" => []],
        ["date" => date('d.m.Y', strtotime('sunday this week')), "day" => "Nedela", "menu" => []]
    ];

    $kmenu = $dom->getElementById("obedovemenu");
    $kmenu = $kmenu->getElementsByTagName('div')->item(0);

//    echo '<pre>' , var_dump($kmenu) , '</pre>';
    $dayIndex = -2;
    $counter = 0;

//    echo '<pre>' , var_dump($kmenu->childNodes->item(2)) , '</pre>';
    foreach ($kmenu->childNodes as $day) {
        foreach ($day->childNodes as $menuItem) {
            if ($menuItem && ($dayIndex >= 0) && ($dayIndex <= 5) && $counter > 2 && $counter != 5) {
                // echo '<pre>', var_dump($menuItem->childNodes->item(0)->nodeValue), '</pre>';
                $popis = trim($menuItem->childNodes->item(0)->nodeValue);
                $cena = trim($menuItem->childNodes->item(1)->nodeValue);
                array_push($kozlovna[$dayIndex]["menu"], "$popis: $cena");

            }
            $counter++;
//            $index++;
        }
        $counter = 0;
        $dayIndex++;
    }

    $data = ["timestamp" => (new DateTime())->getTimestamp(), "data" => $kozlovna];
    $fp = fopen('./storage/kozlovna.json', 'w');
    fwrite($fp, json_encode($data));
    fclose($fp);
}


