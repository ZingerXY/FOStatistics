<?php

include_once "app.php";
include_once "formula25.php";

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (isset($_REQUEST['char_id']) && ctype_digit ($_REQUEST['char_id']) && mysqli_num_rows($chrtbl) > 0) {
		$char_id = filter_var(def($_REQUEST['char_id'],$link), FILTER_VALIDATE_INT, $filter);

		$query = "	SELECT kills.id_killer,
					kills.faction_id_killer,
					kills.weapon_killer,
					kills.id_victim,
					kills.faction_id_victim,
					kills.armor_victim,
					date
					FROM serv{$sess}_kills kills";

		$result = mysqli_query($link, $query);
		for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

		$query = "SELECT chars.id AS id, chars.name AS char_name FROM serv{$sess}_chars chars";

		$result = mysqli_query($link, $query);

		$allstats = [];
		while ($row = mysqli_fetch_assoc($result)) {
			$allstats[$row["id"]] = [
					"id" => $row["id"],
					"name" => $row["char_name"],
					"kills" => 0,
					"deaths" => 0,
					"raiting" => 1000,
					"abuse" => []
				];
		}

		$list_of_kills = [];
		$list_of_deaths = [];
		calculateStatsDetails($allstats, $data_kills, $list_of_kills, $list_of_deaths);

		$contKills = "";
		$contDeaths = "";
		if (isset($list_of_kills[$char_id])) {
			krsort($list_of_kills[$char_id]);
			foreach ($list_of_kills[$char_id] as $schar) {
				$resreit = round($schar['raiting'], 2);
				$armor = $schar['armor'] ?: 558;
				$date = $schar['date'];
				$contKills .= "
				<tr>
					<td class='td1'><img class ='image'src='statistic/images/kill.png'></td>
					<td class='td2_char_info'><img class ='image_item' src='https://fonlinew.ru/getinfo.php?picid={$schar['weapon']}'></td>
					<td class='td'><a href='statistic/pages/char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
					<td class='td2'><img class ='image'src='statistic/images/death.png' title='$date'></td>
					<td class='td2_char_info'><img class ='image_item' src='https://fonlinew.ru/getinfo.php?picid=$armor'></td>
					<td class='td2_char_info'><img class ='image' src='statistic/images/rating.png'></td>
					<td class='td1'>+$resreit</span></td>
				</tr>";
			}
		}
		if (isset($list_of_deaths[$char_id])) {
			krsort($list_of_deaths[$char_id]);
			foreach ($list_of_deaths[$char_id] as $schar) {
				$resreit = round($schar['raiting'], 2);
				$armor = $schar['armor'] ?: 558;
				$date = $schar['date'];
				$contDeaths .= "
				<tr>
					<td class='td1'><img class ='image'src='statistic/images/kill.png'></td>
					<td class='td2_char_info'><img class ='image_item' src='https://fonlinew.ru/getinfo.php?picid={$schar['weapon']}'></td>
					<td class='td'><a href='statistic/pages/char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
					<td class='td2'><img class ='image'src='statistic/images/death.png' title='$date'></td>
					<td class='td2_char_info'><img class ='image_item' src='https://fonlinew.ru/getinfo.php?picid=$armor'></td>
					<td class='td2_char_info'><img class ='image'src='statistic/images/rating.png'></td>
					<td class='td1'>-$resreit</span></td>
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
			<title>Статистика <?=$allstats[$char_id]["name"]?></title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>
			<div class="title"><?=$allstats[$char_id]["name"]?></div>
			<div class="title"><?=round($allstats[$char_id]["raiting"] - 1000, 2)?></div>
			<div align="center" class="container">
				<div class="block1">
					<div class="block3">Убийства</div>
					<table align='center' class='table'>
						<?=$contKills?>
					</table>
				</div>
				<div class="block1">
					<div class="block4">Смерти</div>
					<table align='center' class='table'>
						<?=$contDeaths?>
					</table>
				</div>
			</div>
		</body>
	</html>
	<?
	}
