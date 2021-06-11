<?php

	include_once "app.php";
	include_once "formula25.php";

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (isset($_REQUEST['frac_id']) && ctype_digit ($_REQUEST['frac_id']) && mysqli_num_rows($chrtbl) > 0) {

		$frac_id = filter_var(def($_REQUEST['frac_id'],$link), FILTER_VALIDATE_INT, $filter);

		$query = "	SELECT kills.id_killer,
					kills.faction_id_killer,
					kills.id_victim,
					kills.faction_id_victim,
					date
					FROM serv{$sess}_kills kills";

		$result = mysqli_query($link, $query);
		for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

		$query = "SELECT serv{$sess}_chars.id AS id, serv{$sess}_chars.name AS char_name FROM serv{$sess}_chars";

		$result = mysqli_query($link, $query);

		$allstats = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$allstats[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["char_name"],
				"abuse" => []
			];
		}

		$query = "SELECT serv{$sess}_factions.id AS id, serv{$sess}_factions.name AS faction_name FROM serv{$sess}_factions";

		$result = mysqli_query($link, $query);

		$faction_stats = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$faction_stats[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["faction_name"],
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 1000
			];
		}

		$list_of_faction_kills = [];
		$list_of_faction_deaths = [];

		calculateStatsFactionDetails($allstats, $data_kills, $faction_stats, $list_of_faction_kills, $list_of_faction_deaths);

		$faction_name = $faction_stats[$frac_id]["name"];
		$faction_rait = $faction_stats[$frac_id]["raiting"];

		usort($faction_stats, 'myCmp');

		$content = '<tr><td class="th" colspan="6"><div class="title">Убийства</div></td></tr>';
		if (isset($list_of_faction_kills[$frac_id])) {
			krsort($list_of_faction_kills[$frac_id]);
			foreach ($list_of_faction_kills[$frac_id] as $sfaction) {
				$resreit = round($sfaction['raiting'], 2);
				$content .= "
				<tr>
					<td class='td'>$sfaction[char_name_killer]</td>
					<td class='td2'>►</td>
					<td class='td'>$sfaction[char_name_victim]</td>
					<td class='td'><a href='statistic/pages/frac_info.php?s={$sess}&frac_id={$sfaction['faction_id']}'>$sfaction[faction_name]</td>
					<td class='td1'><img class ='image'src='statistic/images/rating.png'></td>
					<td class='td1'>$resreit</span></td>
				</tr>";
			}
		}
		$content .= '<tr><td class="th" colspan="6"><a name="deaths"></a><div class="title">Смерти</div></td></tr>';
		if (isset($list_of_faction_deaths[$frac_id])) {
			krsort($list_of_faction_deaths[$frac_id]);
			foreach ($list_of_faction_deaths[$frac_id] as $sfaction) {
				$resreit = round($sfaction['raiting'], 2);
				$content .= "
				<tr>
					<td class='td'>$sfaction[char_name_victim]</td>
					<td class='td2'>◄</td>
					<td class='td'>$sfaction[char_name_killer]</td>
					<td class='td'><a href='statistic/pages/frac_info.php?s={$sess}&frac_id={$sfaction['faction_id']}'>$sfaction[faction_name]</td>
					<td class='td1'><img class ='image'src='statistic/images/rating.png'></td>
					<td class='td1'>$resreit</span></td>
				</tr>";
			}
		}
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика фракции <?=$faction_name?></title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>
			<div class="title"><?=$faction_name?></div>
			<div class="title"><?=round($faction_rait - 1000, 2)?></div>
			<div align="center"><a href="#deaths">К смертям →</a></div>
			<div align="center" class="block">
				<table align='center' class='table'>
					<?=$content?>
				</table>
			</div>
		</body>
	</html>
	<?
	}