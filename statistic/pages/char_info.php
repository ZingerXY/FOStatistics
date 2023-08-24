<?php

include_once "app.php";
include_once "formula25.php";

// Проверка существования таблицы с префиксом
$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

if (isset($_REQUEST['char_id']) && ctype_digit($_REQUEST['char_id']) && mysqli_num_rows($chrtbl) > 0) {
	$char_id = filter_var(def($_REQUEST['char_id'], $link), FILTER_VALIDATE_INT, $filter);

	$query = "SELECT kills.id_killer,
					kills.faction_id_killer,
					kills.weapon_killer,
					kills.id_victim,
					kills.faction_id_victim,
					kills.armor_victim,
					date
					FROM serv{$sess}_kills kills";

	$result = mysqli_query($link, $query);
	for ($data_kills = []; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

	// $query = "SELECT chars.id AS id, chars.name AS char_name FROM serv{$sess}_chars chars";
	$query = "SELECT chars.id AS id, chars.name AS char_name, pidlist AS build FROM serv{$sess}_chars chars LEFT JOIN serv{$sess}_perks AS perks ON chars.id = perks.id";

	$result = mysqli_query($link, $query);

	$allstats = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$allstats[$row["id"]] = [
			"id" => $row["id"],
			"name" => $row["char_name"],
			"kills" => 0,
			"deaths" => 0,
			"raiting" => 0,
			"armorCoefficient" => [],
			"abuse" => [],
			"build" => $row["build"] ? $row["build"] : ''
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

	$query = 'SELECT id, name FROM `serv28_name_perks`';
	$result = mysqli_query($link, $query);
	$perkNames = mysqli_fetch_all($result, MYSQLI_ASSOC);

	$contBuild = "";
	if ($allstats[$char_id]["build"]) {
		$contBuild .= '<div class="title ptitle">Трейты</div>
				<table align="center" class="ptable"><tbody>';

		$perkList = str_split($allstats[$char_id]["build"]);
		$lenpid = count($perkList);
		foreach ($perkList as $key => $value) {
			$value = intval($value);
			if ($value) {
				$id = $perkNames[$key]['id'];
				$contBuild .= "<tr class='perks' data-pid='$id' data-num='$key'>
						<td>
							<img align ='left' class ='image_perks' src='statistic/images/perks/$id.png'>
						</td>
						<td class='td'>" . $perkNames[$key]['name'] . ($value > 1 ? '(' . $value . ')' : '') . "</td>
					</tr>";
			}

			if ($key == '15') {
				$contBuild .= '</tbody></table>
						<table align="center" class="ptable">
							<tr class="perks">
								<td class="th" colspan="2">
									<div class="title">Перки</div>
								</td>
							</tr><tbody>';
				$class = 'perk';
			}
		}
		$contBuild .= '</tbody></table>';
	}
?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta charset="utf-8">
		<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
		<title>Статистика <?= $allstats[$char_id]["name"] ?></title>
		<link rel='stylesheet' href='style.css'>
	</head>

	<body>
		<div class="title"><?= $allstats[$char_id]["name"] ?></div>

		<? if ($contBuild): ?>
		<div>
			<details>
				<summary class="title build">Build</summary>
				<?= $contBuild ?>
			</details>
		</div>
		<? endif; ?>

		<div class="title"><?= round($allstats[$char_id]["raiting"], 2) ?></div>
		<div align="center" class="container">
			<div class="block1">
				<div class="block3">Убийства</div>
				<table align='center' class='table'>
					<?= $contKills ?>
				</table>
			</div>
		</div>
	</body>

	</html>
<?
}
