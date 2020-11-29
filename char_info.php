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
					kills.armor_victim,
					date
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
					"raiting" => 1000,
					"abuse" => []
				];
		}

		$allstats = $data_stat;
		foreach ($data_kills as $dkills) {
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			if (isNotStatChar($id_killer) || isNotStatChar($id_victim)) {
				continue;
			}

			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];

			$weapon_killer = $dkills["weapon_killer"];
			$armor_victim = $dkills["armor_victim"];

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];			

			if (!isset($allstats[$id_killer], $allstats[$id_victim])) { 
				continue;
			}

			if($faction_id_killer != 0 && $faction_id_killer == $faction_id_victim) {
				continue;
			}

			$allstats[$id_killer]["kills"]++;
			$allstats[$id_victim]["deaths"]++;

			//Берем текущие рейтинги килера и жертвы
			$Ra = $allstats[$id_killer]["raiting"];
			$Rb = $allstats[$id_victim]["raiting"];

			//Передаем их в функцию расчета рейтинга
			$raiting = EloRating($Ra, $Rb);

			//Изменяем рейтинги игроков
			$add_killer_raiting = $raiting["killer_raiting"];
			$add_victim_raiting = $raiting["victim_raiting"];
			
			if ( isset($armor_c[$armor_victim]) ) {
				$add_killer_raiting = ($add_killer_raiting * $armor_c[$armor_victim]);
				$add_victim_raiting = ($add_victim_raiting / $armor_c[$armor_victim]);
			}

			$date_kill = $dkills["date"];
			$unix_date_kill = strtotime($date_kill);

			// Берем ранее добавленный массив с абузами киллера для текущей жертвы если он есть
			$old_abuse = isset($allstats[$id_killer]['abuse'][$id_victim]) ? $allstats[$id_killer]['abuse'][$id_victim] : [];
			// Смотрим размер текущего массива абузов киллера
			$abuse_count = count($old_abuse);
			if ($abuse_count > 0) { // Если размер больше нуля выбираем из него последнюю запись и сравниваем с текущей
				$abuse_date = $old_abuse[$abuse_count - 1];
				$interval = $unix_date_kill - $abuse_date;
				if ($interval < (3600*3)) { // Если с последнего убийства этой жертвы прошло меньше 3х часов добавляем запись
					$allstats[$id_killer]['abuse'][$id_victim][] = $unix_date_kill;
				} else { // Иначе очищаем массив абузов для этой жертвы
					$allstats[$id_killer]['abuse'][$id_victim] = [];
				}
			} else { // Если равен 0 просто добавляем запись
				$allstats[$id_killer]['abuse'][$id_victim][] = $unix_date_kill;
			}

			/*	Если в массиве абузов больше 4 записей для этой жертвы и киллер получает 
				за жертву больше чем теряет жертва, килер получает 0, жертва теряет 0 */
			if (count($allstats[$id_killer]['abuse'][$id_victim]) > 4) {
				$add_victim_raiting = 0;
				$add_killer_raiting = 0;
			}

			$allstats[$id_killer]["raiting"] += $add_killer_raiting;
			$allstats[$id_victim]["raiting"] += $add_victim_raiting;

			$list_of_kills[$id_killer][$killer_kills] = [
					"id" => $id_victim,
					"name" => $data_stat[$id_victim]["name"],
					"raiting" => $add_killer_raiting,
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
					"date" => $date_kill
				];

			$list_of_deaths[$id_victim][$victim_deaths] = [
					"id" => $id_killer,
					"name" => $data_stat[$id_killer]["name"],
					"raiting" => $add_victim_raiting,
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
					"date" => $date_kill
				];
		}

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
					<td class='td1'><img class ='image'src='images/kill.png'></td>
					<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid={$schar['weapon']}'></td>
					<td class='td'><a href='char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
					<td class='td2'><img class ='image'src='images/death.png' title='$date'></td>
					<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=$armor'></td>
					<td class='td2_char_info'><img class ='image' src='images/rating.png'></td>	
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
					<td class='td1'><img class ='image'src='images/kill.png'></td>
					<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid={$schar['weapon']}'></td>
					<td class='td'><a href='char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
					<td class='td2'><img class ='image'src='images/death.png' title='$date'></td>
					<td class='td2_char_info'><img class ='image_item' src='http://fonlinew.ru/getinfo.php?picid=$armor'></td>
					<td class='td2_char_info'><img class ='image'src='images/rating.png'></td>
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
			<title>Статистика <?=$data_stat[$char_id]["name"]?></title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>
			<div class="title"><?=$data_stat[$char_id]["name"]?></div>
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
