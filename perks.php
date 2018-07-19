<?php

	/*ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);*/
	
	include "config.php";
	
	$query = "SELECT name FROM `name_perks`";
		
	$result = mysqli_query($link, $query);
	$perk = [];
	while ($row = mysqli_fetch_assoc($result)) {		
		$perk[] = $row["name"];
	}
	
	$query = "SELECT * FROM `serv18_perks`";
	$result = mysqli_query($link, $query);
	
	$stat = [];
	$sum = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		$res = str_split($row["pidlist"]);
		foreach($res as $i => $e) {
			if($e > 0) {
				if(!array_key_exists($i,$stat))
					$stat[$i] = 1;
				else
					$stat[$i]++;
			}
		}		
		$sum++;
	}
	
	$content = '<tr><td class="th" colspan="2"></a><div class="title">Трейты</div></td></tr>';
	foreach($perk as $i => $e) {
		if(array_key_exists($i,$stat))
			$pr = round($stat[$i] / $sum * 100, 2);
		else
			$pr = 0;
		$content .= "<tr><td class='td'>$e</td><td class='td'>$pr %</td></tr>";
		if($i == 15)
			$content .= '<tr><td class="th" colspan="2"></a><div class="title">Перки</div></td></tr>';
	}
		
	$content .= "<tr><td class='td'>Всего данных</td><td class='td'>$sum</td></tr>";
?>
	<!DOCTYPE html>
	<html>
		<head>
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика взятия перков</title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>
			<div = class="container">
				<div class="block1">
					<table align='center' id='table' class='table'>
						<?=$content?>
					</table>
				</div>
			</div>
		</body>
	</html>
<?
	mysqli_close($link);
