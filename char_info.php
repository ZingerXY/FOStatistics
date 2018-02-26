<?php
// Отладка
/*ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);*/

include "config.php";

// защита БД от SQL иньекций
function def($text,$linksql = false) {
	$result = strip_tags($text);
	$result = htmlspecialchars($result);
	if($linksql)
		$result = mysqli_real_escape_string ($linksql, $result);
	return $result;
}

$filter = array(
	'options' => array(
		'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
		// другие параметры
		'min_range' => 0
	),
	'flags' => FILTER_FLAG_ALLOW_OCTAL,
);

$sess = 18;
if(isset($_GET['s'])) {
	$sess = filter_var(def($_GET['s']), FILTER_VALIDATE_INT, $filter);
}
// Проверка существования таблицы с префиксом
$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

if(isset($_REQUEST['char_id']) && ctype_digit ($_REQUEST['char_id']) && mysqli_num_rows($chrtbl) > 0) {
	$char_id = def($_REQUEST['char_id'],$link);
	
	$query = "	SELECT 	serv{$sess}_chars.id AS id,
						serv{$sess}_chars.name AS char_name,		
						(SELECT count(id_killer) FROM serv{$sess}_kills WHERE serv{$sess}_chars.id=serv{$sess}_kills.id_killer LIMIT 1) AS kills,
						(SELECT count(id_victim) FROM serv{$sess}_kills WHERE serv{$sess}_chars.id=serv{$sess}_kills.id_victim LIMIT 1) AS deth
				FROM serv{$sess}_chars
				WHERE (SELECT count(id_killer) FROM serv{$sess}_kills WHERE serv{$sess}_chars.id=serv{$sess}_kills.id_killer LIMIT 1) > 0 OR (SELECT count(id_victim) FROM serv{$sess}_kills WHERE serv{$sess}_chars.id=serv{$sess}_kills.id_victim LIMIT 1) > 0";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	$data_stat=[];

	while($row = mysqli_fetch_assoc($result)) {
		$data_stat[$row["id"]] = ["id" => $row["id"], "name" => $row["char_name"], "kills" => $row["kills"], "deth" => $row["deth"]];
	}

	$query = "SELECT DISTINCT serv{$sess}_kills.id_killer AS killer_id,
					(select serv{$sess}_chars.name from serv{$sess}_chars where serv{$sess}_chars.id=serv{$sess}_kills.id_killer LIMIT 1) AS killer_name,
					serv{$sess}_kills.faction_id_killer,
					(select serv{$sess}_factions.name from serv{$sess}_factions where serv{$sess}_factions.id=serv{$sess}_kills.faction_id_killer LIMIT 1) AS killer_name_faction,
					serv{$sess}_kills.id_victim AS victim_id,		
					(select count(id_victim) from serv{$sess}_kills where id_killer = '$char_id' AND serv{$sess}_kills.id_victim=victim_id LIMIT 1) AS counts_kills,
					(select serv{$sess}_chars.name from serv{$sess}_chars where serv{$sess}_chars.id=serv{$sess}_kills.id_victim LIMIT 1) AS victim_name,
					serv{$sess}_kills.faction_id_victim,
					(select serv{$sess}_factions.name from serv{$sess}_factions where serv{$sess}_factions.id=serv{$sess}_kills.faction_id_victim LIMIT 1) AS victim_name_faction
			FROM serv{$sess}_kills
			WHERE id_killer = '$char_id'
			ORDER BY counts_kills DESC";	
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	
	$reschar = mysqli_query($link, "SELECT id,name FROM serv{$sess}_chars WHERE id = '$char_id'");
	$char_info = mysqli_fetch_assoc($reschar);
	
	$player_name = "";
	$player_frac = "";
	$player_kill = 0;
	$player_dead = 0;
	if($char_info) {
		$player_name = $char_info["name"];
	} else {
		$player_name = "<span class='red'>delete</span>";
	}
	if(isset($data_stat[$char_id])) {
		$player_kill = $data_stat[$char_id]["kills"];
		$player_dead = $data_stat[$char_id]["deth"];
	}
	$kills = "";
	$raitingkills = 0;
	while($row = mysqli_fetch_assoc($result)) {
		if($row["faction_id_killer"] == 243161)
			$row["killer_name_faction"] = "ЖЖЖЖЖЖЖЖЖЖ...";
		if($row["faction_id_victim"] == 243161)
			$row["victim_name_faction"] = "ЖЖЖЖЖЖЖЖЖЖ...";
		$victim_id = $row["victim_id"];
		$score = 0;
		$formula = "";
		if(isset($data_stat[$victim_id])) {
			$info = $data_stat[$victim_id];
			$killss = $info["kills"];
			if($killss > 0) {
				$deth = $info["deth"];
				$score = ($killss / ($killss + $deth)) * $row['counts_kills'];
				$formula = "($killss / ($killss + $deth))" . ($row['counts_kills'] > 1 ? " * ".$row['counts_kills'] : "");
			}
		}
		$raitingkills += $score;
		$player_frac = $row["killer_name_faction"];
		$victim_name = $row["victim_name"];
		if(!$victim_name)
			$victim_name = "<span class='red'>delete</span>";
		$kills .= 	"<tr>
						<td class='td' title='".$row['victim_id']."'><a href='char_info.php?s={$sess}&char_id=".$row['victim_id']."'>".$victim_name."</a></td>
						<td class='td' title='".$row['faction_id_victim']."'>".$row['victim_name_faction']."</td>	
						<td class='td'>".$row['counts_kills']."</td>
						<td class='td green' title='$formula'>".round($score, 3)."</td>
					</tr>";
	}
	$kills .= 	"<tr>
					<td colspan='3'></td>
					<td class='td green'>".round($raitingkills, 3)."</td>
				</tr>";

	$query = "SELECT DISTINCT serv{$sess}_kills.id_killer AS killer_id,
					(select serv{$sess}_chars.name from serv{$sess}_chars where serv{$sess}_chars.id=serv{$sess}_kills.id_killer LIMIT 1) AS killer_name,
					serv{$sess}_kills.faction_id_killer,
					(select serv{$sess}_factions.name from serv{$sess}_factions where serv{$sess}_factions.id=serv{$sess}_kills.faction_id_killer LIMIT 1) AS killer_name_faction,
					serv{$sess}_kills.id_victim AS victim_id,		
					(select count(id_victim) from serv{$sess}_kills where id_killer = killer_id AND serv{$sess}_kills.id_victim=$char_id LIMIT 1) AS counts_kills,
					(select serv{$sess}_chars.name from serv{$sess}_chars where serv{$sess}_chars.id=serv{$sess}_kills.id_victim LIMIT 1) AS victim_name,
					serv{$sess}_kills.faction_id_victim,
					(select serv{$sess}_factions.name from serv{$sess}_factions where serv{$sess}_factions.id=serv{$sess}_kills.faction_id_victim LIMIT 1) AS victim_name_faction
			FROM serv{$sess}_kills
			WHERE id_victim = $char_id
			ORDER BY counts_kills DESC";
	$result = mysqli_query($link, $query) or die(mysqli_error($link));
	$victim = "";
	$raitingvictim = 0;
	while($row = mysqli_fetch_assoc($result)) {
		if($row["faction_id_killer"] == 243161)
			$row["killer_name_faction"] = "ЖЖЖЖЖЖЖЖЖЖ...";
		if($row["faction_id_victim"] == 243161)
			$row["victim_name_faction"] = "ЖЖЖЖЖЖЖЖЖЖ...";
		$killer_id = $row["killer_id"];
		$score = 0;
		$formula = "";
		if(isset($data_stat[$killer_id])) {
			$info = $data_stat[$killer_id];	
			$deth = $info["deth"];
			if($deth > 0) {
				$killss = $info["kills"];
				$score = -($deth / ($killss + $deth)) * $row['counts_kills'];
				$formula = "-($deth / ($killss + $deth))" . ($row['counts_kills'] > 1 ? " * ".$row['counts_kills'] : "");
			}
		}
		//$score = -(($info["deth"] == 0 ? 1 : $info["deth"]) / $info["kills"]) * $row['counts_kills'];
		$raitingvictim += $score;
		$killer_name = $row["killer_name"];
		if(!$killer_name)
			$killer_name = "<span class='red'>delete</span>";
		$victim .= 	"<tr>
						<td class='td' title='".$row['killer_id']."'><a href='char_info.php?s={$sess}&char_id=".$row['killer_id']."'>".$killer_name."</a></td>
						<td class='td' title='".$row['faction_id_killer']."'>".$row["killer_name_faction"]."</td>
						<td class='td'>".$row['counts_kills']."</td>
						<td class='td red' title='$formula'>".round($score, 3)."</td>
					</tr>";
	}
	$victim .= 	"<tr>
					<td colspan='3'></td>
					<td class='td red'>".round($raitingvictim, 3)."</td>
				</tr>";
				
	$fullreit = round(($raitingkills+$raitingvictim), 3);
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
		.th {
			padding-bottom: 3px;
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
	<div id="title">
		<a href="chars2.php?s=<?=$sess?>">Character statistics</a>	
		<div>Character: <?=$player_name?></div>
		<div>Faction: <?=($player_frac?$player_frac:"-")?></div>		
		<div>Kills: <?=$player_kill?></div>
		<div>Deaths: <?=$player_dead?></div>
		<div>Rating: <span class="<?=($fullreit<0?"red":"green")?>"><?=$fullreit?></span></div>
	</div>
	<hr>
	<table id='table'>
	<?
	if($player_kill > 0) {
	?>
	<tr>
		<th></th>
		<th class="th">Kills:</th>
	</tr>
	<tr>
		<th class='td'>Victim</th>
		<th class='td'>Faction</th>
		<th class='td'>Count</th>
		<th class='td'>Rating</th>
	</tr>
	<?=$kills?>
	<?
	}
	if($player_dead > 0) {
	?>
	<tr>
		<th></th>
		<th class="th">Deaths:</th>
	</tr>
	<tr>
		<th class='td'>Killer</th>
		<th class='td'>Faction</th>
		<th class='td'>Count</th>
		<th class='td'>Rating</th>
	</tr>
	<?=$victim?>
	<?
	}
	?>
	</table>
	</body>
</html>
<?
}