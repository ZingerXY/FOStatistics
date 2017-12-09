<?php
include "config.php";

/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

$query = "	SELECT  serv18_kills.id_killer,
					(select serv18_chars.name from serv18_chars where serv18_chars.id=serv18_kills.id_killer) AS killer_name,
					serv18_kills.faction_id_killer,
					(select serv18_factions.name from serv18_factions where serv18_factions.id=serv18_kills.faction_id_killer) AS killer_name_faction,
					serv18_kills.id_victim,
					(select serv18_chars.name from serv18_chars where serv18_chars.id=serv18_kills.id_victim) AS victim_name,
					serv18_kills.faction_id_victim,
					(select serv18_factions.name from serv18_factions where serv18_factions.id=serv18_kills.faction_id_victim) AS victim_name_faction
			FROM serv18_kills
			WHERE faction_id_killer <> 0 AND faction_id_victim <> 0";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

$query = "	SELECT 	serv18_factions.id AS id,
			serv18_factions.name AS frac_name,		
			(SELECT count(id_killer) FROM serv18_kills WHERE serv18_kills.faction_id_killer = serv18_factions.id AND serv18_kills.faction_id_victim <> 0) AS kills,
			(SELECT count(id_victim) FROM serv18_kills WHERE serv18_kills.faction_id_victim = serv18_factions.id AND serv18_kills.faction_id_killer <> 0) AS deth
	FROM serv18_factions
	WHERE (SELECT count(id_killer) FROM serv18_kills WHERE serv18_factions.id=serv18_kills.faction_id_killer) > 0 OR (SELECT count(id_victim) FROM serv18_kills WHERE serv18_factions.id=serv18_kills.faction_id_victim) > 0 ";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$data_stat=[];

while($row = mysqli_fetch_assoc($result)) {
	$data_stat[$row["id"]] = ["id" => $row["id"], "name" => $row["frac_name"], "kills" => $row["kills"], "deth" => $row["deth"]];
}

$query = "	SELECT 	serv18_chars.id AS id,
					serv18_chars.name AS char_name,		
					(SELECT count(id_killer) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_killer) AS kills,
					(SELECT count(id_victim) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_victim) AS deth
			FROM serv18_chars
			WHERE (SELECT count(id_killer) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_killer) > 0 OR (SELECT count(id_victim) FROM serv18_kills WHERE serv18_chars.id=serv18_kills.id_victim) > 0";
$result = mysqli_query($link, $query) or die(mysqli_error($link));
$data_stat_char=[];

while($row = mysqli_fetch_assoc($result)) {
	$data_stat_char[$row["id"]] = ["id" => $row["id"], "name" => $row["char_name"], "kills" => $row["kills"], "deth" => $row["deth"]];
}

$statfrac = [];

foreach ($data_stat as $id => $stat) {
	$raiting = 0;
	if($id != 0) {
		foreach ($data_kills as $dkills) {
			if($id == $dkills["faction_id_killer"] AND $dkills["faction_id_victim"] != 0) {			
				$info = $data_stat_char[$dkills["id_victim"]];	
				$kills = $info["kills"];
				$score = 0;
				if($kills > 0) {
					$deth = $info["deth"];
					$score = ($kills / ($kills + $deth));
				}
				$raiting += $score;
			}
			if($id == $dkills["faction_id_victim"] AND $dkills["faction_id_killer"] != 0) {
				$info = $data_stat_char[$dkills["id_killer"]];	
				$deth = $info["deth"];
				$score = 0;
				if($deth > 0) {
					$kills = $info["kills"];
					$score = -($deth / ($kills + $deth));
				}
				$raiting += $score;
			}
			
		}
		if($id == 243161)
			$stat["name"] = "ЖЖЖЖЖЖЖЖЖЖ...";
		$stat["raiting"] = $raiting;
		$statfrac[] = $stat;
	}
}

usort($statfrac, 'myCmp'); 

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
foreach($statfrac as $id => $sfrac)
{
	$resreit = round($sfrac['raiting'], 3);
	?>
	<tr>
		<td class='td'><?=$num++?></td>
		<td class='td'><a href='frac_info.php?frac_id=<?=$sfrac['id']?>'><?=$sfrac['name']?></a></td>
		<td class='td'><?=$sfrac['kills']?></td>
		<td class='td'><?=$sfrac['deth']?></td>
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