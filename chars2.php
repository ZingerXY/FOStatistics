<?php

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

include "config.php";

$start = microtime(true);

$query = "	SELECT  serv18_kills.id_killer,
					(select serv18_chars.name from serv18_chars where serv18_chars.id=serv18_kills.id_killer) AS killer_name,
					serv18_kills.faction_id_killer,
					(select serv18_factions.name from serv18_factions where serv18_factions.id=serv18_kills.faction_id_killer) AS killer_name_faction,
					serv18_kills.id_victim,
					(select serv18_chars.name from serv18_chars where serv18_chars.id=serv18_kills.id_victim) AS victim_name,
					serv18_kills.faction_id_victim,
					(select serv18_factions.name from serv18_factions where serv18_factions.id=serv18_kills.faction_id_victim) AS victim_name_faction
			FROM serv18_kills";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

$time1 = microtime(true) - $start;
$start = microtime(true);

$query = "	SELECT 	serv18_chars.id AS id,
					serv18_chars.name AS char_name,		
					(SELECT count(id_killer) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_killer) AS kills,
					(SELECT count(id_victim) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_victim) AS deth
			FROM serv18_chars
			WHERE (SELECT count(id_killer) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_killer) > 0 OR (SELECT count(id_victim) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_victim) > 0";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
while($row = mysqli_fetch_assoc($result)) {
	$data_stat[$row["id"]] = ["id" => $row["id"], "name" => $row["char_name"], "kills" => $row["kills"], "deth" => $row["deth"]];
}

$time2 = microtime(true) - $start;
$start = microtime(true);

$statchar = $data_stat;

$statchars = [];
foreach ($statchar as $id => $stat) {
	$raiting = 0;
	foreach ($data_kills as $dkills) {
		if($id == $dkills["id_killer"] && isset($dkills["id_victim"]) && isset($statchar[$dkills["id_victim"]])) {
			$info = $statchar[$dkills["id_victim"]];
			$kills = $info["kills"];
			$score = 0;
			if($kills > 0) {
				$deth = $info["deth"];
				$score = ($kills / ($kills + $deth));
			}
			$raiting += $score;
		}
		if($id == $dkills["id_victim"] && isset($dkills["id_killer"]) && isset($statchar[$dkills["id_killer"]])) {
			$info = $statchar[$dkills["id_killer"]];
			$deth = $info["deth"];
			$score = 0;
			if($deth > 0) {
				$kills = $info["kills"];
				$score = -($deth / ($kills + $deth));
			}
			$raiting += $score;
		}		
	}
	$stat["raiting"] = $raiting;
	$statchars[] = $stat;
}

$time3 = microtime(true) - $start;
$start = microtime(true);

usort($statchars, 'myCmp'); 

$time4 = microtime(true) - $start;

function myCmp($a, $b)
{
	return ($b["raiting"]*1000) - ($a["raiting"]*1000);
}
?>
<html>
	<head>
	<style type="text/css">
		body {
			background-color: #444444;
			color: #e2e2e2;;
		}
		a {
			text-decoration: none;
			color: #4bff00;
		}
		#title {
			font-size: 20px;
			margin-left: 10px;
		}
		#table {
			border-collapse: collapse;
		}
		.td {
			border: 1px solid #aaa;
			padding: 2px 6px;
		}
		.green {
			color: #34c734;
			font-weight: bold;
		}
		.red {
			color: #d82828;
			font-weight: bold;
		}
		.bold {
			font-weight: bold;
		}
		
	</style>
	</head>
	<body>
	<script>
	console.log("Первый запрос: <?=$time1?>");
	console.log("Второй запрос: <?=$time2?>");
	console.log("Обработка данных: <?=$time3?>");
	console.log("Сортировка: <?=$time4?>");
	</script>
	<table id='table'>
	<tr>
		<th class='td'>№</th>
		<th class='td'>Name</th>
		<th class='td'>Kills</th>
		<th class='td'>Deaths</th>
		<th class='td'>Rating</th>
	</tr>
	<?
	$num = 1;
	foreach($statchars as $id => $schar)
	{
		$resreit = round($schar['raiting'], 3);
		?>
		<tr>
			<td class='td'><?=$num++?></td>
			<td class='td'><a href='char_info.php?char_id=<?=$schar['id']?>' title='<?=$schar['id']?>'><?=$schar['name']?></a></td>
			<td class='td'><?=$schar['kills']?></td>
			<td class='td'><?=$schar['deth']?></td>
			<td class='td'><span class="<?=($resreit<0?"red":"green")?>"><?=$resreit?></span></td>
		</tr>
		<?
	}
	?>
	</table>
	<script>
	(function(){
		// Сортировка таблицы colNum колонка от 0, table таблица, sort порядок 1 или 2
		function sortGrid(colNum, table, sort) {
			var tbody = table.tBodies[0];
			var grid = tbody.parentNode;
			// Составить массив из TR
			var rowsArray = [].slice.call(tbody.rows);
			rowsArray.splice(0, 1);
			// сортировать
			//rowsArray.sort((a,b) => b.cells[colNum].innerHTML - a.cells[colNum].innerHTML );
			rowsArray.sort(function(a,b) {			
				var compA = a.cells[colNum].innerText;
				var compB = b.cells[colNum].innerText;
				if(isNaN(Number.parseInt(compA)))
					return (compA < compB) ? -1 : (compA > compB) ? 1 : 0;
				else
					return compB - compA;
			});
			if(sort == 2)
				rowsArray.reverse();
			// Убрать tbody из большого DOM документа для лучшей производительности
			grid.removeChild(tbody);
			// добавить результат в нужном порядке в TBODY
			// они автоматически будут убраны со старых мест и вставлены в правильном порядке
			for (var i = 0; i < rowsArray.length; i++) {
				tbody.appendChild(rowsArray[i]);
			}
			grid.appendChild(tbody);
		}
		var table = document.querySelector("#table");
		var th = table.querySelectorAll("th.td");
		th.forEach(function(elem){
			elem.style.cursor = "pointer";
			elem.dataset["sort"] = 0;
			elem.onclick = function() {				
				for(var i = 0; i < th.length; i++) {
					if(th[i] != this) {
						th[i].dataset.sort = 0;
					}
					var arrow = th[i].querySelector("span");
					if(arrow)
						arrow.remove();
				}
				if(this.dataset.sort == 1) {
					this.dataset.sort = 2;
					this.innerHTML += "<span> ↑</span>";
				}
				else {
					this.dataset.sort = 1;
					this.innerHTML += "<span> ↓</span>";
				}
				sortGrid(this.cellIndex, table, this.dataset.sort);
			}			
		});	
	})();
	</script>
	</body>
</html>