<?php

	/*ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);*/
	
	include "config.php";

	function def($text,$linksql = false) {
		$result = strip_tags($text);
		$result = htmlspecialchars($result);
		if ($linksql)
			$result = mysqli_real_escape_string ($linksql, $result);
		return $result;
	}

	$filter = [
		'options' => [
			'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
			// другие параметры
			'min_range' => 0
		],
		'flags' => FILTER_FLAG_ALLOW_OCTAL,
	];

	$ck = 0;
	if (isset($_REQUEST ['ck'])) {
		$ck = filter_var(def($_REQUEST ['ck']), FILTER_VALIDATE_INT, $filter);
	}
	
	$query = "SELECT name FROM `name_perks`";
		
	$result = mysqli_query($link, $query);
	$perk = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$perk[] = $row["name"];
	}
	
	if ($ck)
		$query = "SELECT id,pidlist FROM serv18_perks WHERE id = (select distinct id_killer from serv18_kills where id_killer = serv18_perks.id AND (select count(id_killer) from serv18_kills where id_killer = serv18_perks.id) >= $ck)";
	else
		$query = "SELECT id,pidlist FROM serv18_perks";
	
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
	
	$content = '<tr><td class="th" colspan="3"></a><div class="title">Трейты</div></td></tr>';
	$content .= '<tbody>';
	$class = 'trait';
	$num = 1;
	foreach($perk as $i => $e) {
		if (array_key_exists($i,$stat))
			$pr = round($stat[$i] / $sum * 100, 2);
		else
			$pr = 0;
		$content .= "
			<tr class='$class'>
				<td>
					<img align ='middle' class ='image_perks' src='images/perks/$num.png'" . 
					($num == 105 ? "onmouseover='this.src = \"images/perks/easter_egg.png\"' onmouseout='this.src = \"images/perks/$num.png\"'" : "") . ">
				</td>
				<td class='td'>$e</td>
				<td class='td'>$pr%</td>
			</tr>";
		if ($i == 15) {
			$content .= '</tbody><tr><td class="th" colspan="3"></a><div class="title">Перки</div></td></tr><tbody>';
			$class = 'perk';
		}
		$num++;
	}
	$content .= '</tbody>';
	
	$content .= "<tr style='background-color:#444444'><td></td><td class='td'>Всего данных</td><td class='td'>$sum</td></tr>";
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
				<div align="center" class="block">Фильтр по убийствам</div><br>
				<div align="center">
					<a href="perks.php" class="button">Всё</a>
					<a href="perks.php?ck=25" class="button">25</a>
					<a href="perks.php?ck=50" class="button">50</a>
					<a href="perks.php?ck=100" class="button">100</a>
					<a href="perks.php?ck=150" class="button">150</a>
					<a href="perks.php?ck=200" class="button">200</a>
				</div>
				<div align="center" class="block">
					<?
					if ($sum > 15) 
						echo "<table align='center' class='table'>".$content."</table>";
					else
						echo "<br>Недостаточно данных для вывода статистики.";
					?>
				</div>
			</div>
			<script>
			var ch = <?=($sum > 15 ? "true" : "false")?>;
			function sortGrid(cls) {
				// Составить массив из TR
				var rowsArray = document.querySelectorAll("." + cls);
				var tbody = rowsArray[0].parentNode;
				rowsArray = Array.from(rowsArray);
				// сортировать
				rowsArray.sort(function(a,b) {
					var compA = a.cells[2].innerText.slice(0,-1);
					var compB = b.cells[2].innerText.slice(0,-1);
					return compB - compA;
				});
				// добавить результат в нужном порядке в TBODY
				// они автоматически будут убраны со старых мест и вставлены в правильном порядке
				for (var i = 0; i < rowsArray.length; i++)
					tbody.appendChild(rowsArray[i]);
			}
			if (ch) {
				sortGrid("trait");
				sortGrid("perk");
			}
			</script>
		</body>
	</html>
<?
	mysqli_close($link);
