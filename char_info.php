<?php

	include_once "app.php";

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (isset($_REQUEST['char_id']) && ctype_digit ($_REQUEST['char_id']) && mysqli_num_rows($chrtbl) > 0) {
		$char_id = filter_var(def($_REQUEST['char_id'],$link), FILTER_VALIDATE_INT, $filter);

		$query = "	SELECT kills.id_killer,
					kills.faction_id_killer,
					kills.weapon_killer,
					kills.id_victim,
					kills.faction_id_victim,
					kills.armor_victim
					FROM serv{$sess}_kills kills";

		$result = mysqli_query($link, $query);
		for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

		$query = "SELECT chars.id AS id, chars.name AS char_name FROM serv{$sess}_chars chars";

		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$data_stat[$row["id"]] = [
					"id" => $row["id"],
					"name" => $row["char_name"],
					"kills" => 0,
					"deaths" => 0,
					"raiting" => 0
				];
		}

		$allstats = $data_stat;
		foreach ($data_kills as $dkills)
		{
			$id_killer = $dkills["id_killer"];
			$faction_id_killer = $dkills["faction_id_killer"];
			$weapon_killer = $dkills["weapon_killer"];
			
			$id_victim = $dkills["id_victim"];
			$faction_id_victim = $dkills["faction_id_victim"];
			$armor_victim = $dkills["armor_victim"];

			if (!isset($allstats[$id_killer],$allstats[$id_victim])) continue;
			if($faction_id_killer != 0 && $faction_id_killer == $faction_id_victim) continue;

			$allstats[$id_killer]["kills"]++;
			$allstats[$id_victim]["deaths"]++;

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];
			$allstats[$id_killer]["raiting"] += ($victim_kills / ($victim_kills + $victim_deaths));

			$list_of_kills[$id_killer][$killer_kills] = [
					"id" => $id_victim,
					"name" => $data_stat[$id_victim]["name"],
					"raiting" => ($victim_kills / ($victim_kills + $victim_deaths)),
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
				];

			$list_of_deaths[$id_victim][$victim_deaths] = [
					"id" => $id_killer,
					"name" => $data_stat[$id_killer]["name"],
					"raiting" => ($killer_deaths / ( $killer_deaths + $killer_kills)),
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
				];
		}
	?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика <?=$data_stat[$char_id]["name"]?></title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>			<div class="title"><?=$data_stat[$char_id]["name"]?></div>
			<div class="title"><?=round($allstats[$char_id]["raiting"],2)?></div>
			<div align="center" class="container">
				<div class="block1">
					<div class="block3">Убийства</div>
					<table align='center' class='table'>
						<?php if (isset($list_of_kills[$char_id])): ?>
							<?php krsort($list_of_kills[$char_id]); ?>
							<?php foreach ($list_of_kills[$char_id] as $schar): ?>
							<?php $resreit = round($schar['raiting'], 2); ?>
							<tr>
								<td class='td1'><img class ='image'src='images/kill.png'></td>
								<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=<?=$schar['weapon']?>'></td>
								<td class='td'><a href='char_info.php?s=<?=$sess?>&char_id=<?$schar['id']?>'><?=$schar['name']?></td>
								<td class='td2'><img class ='image'src='images/death.png'></td>
								<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=<?=$armor?>'></td>
								<td class='td2_char_info'><img class ='image'src='images/rating.png'></td>
								<td class='td1'>+<?=$resreit?></span></td>
							</tr>
						<?php endfor; endif; ?>
					</table>
				</div>
				<div class="block1">
					<div class="block4">Смерти</div>
					<table align='center' class='table'>
						<?php if (isset($list_of_deaths[$char_id])): ?>
							<?php krsort($list_of_deaths[$char_id]); ?>
							<?php foreach ($list_of_deaths[$char_id] as $schar): ?>
							<?php $resreit = round($schar['raiting'], 2); ?>
							<tr>
								<td class='td1'><img class ='image'src='images/kill.png'></td>
								<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=<?=$schar['weapon']?>'></td>
								<td class='td'><a href='char_info.php?s=<?=$sess?>&char_id=<?$schar['id']?>'><?=$schar['name']?></td>
								<td class='td2'><img class ='image'src='images/death.png'></td>
								<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=<?=$armor?>'></td>
								<td class='td2_char_info'><img class ='image'src='images/rating.png'></td>
							</tr>
						<?php endfor; endif; ?>
					</table>
				</div>
			</div>
		</body>
	</html>
	<?
	}
