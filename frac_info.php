<?php
	
	include_once "app.php";
	
	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (isset($_REQUEST['frac_id']) && ctype_digit ($_REQUEST['frac_id']) && mysqli_num_rows($chrtbl) > 0) {

		$frac_id = filter_var(def($_REQUEST['frac_id'],$link), FILTER_VALIDATE_INT, $filter);

		$query = "	SELECT kills.id_killer,
					kills.faction_id_killer,
					kills.id_victim,
					kills.faction_id_victim,
					kills.date
					FROM serv{$sess}_kills kills";

		$result = mysqli_query($link, $query);
		for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

		$query = "SELECT serv{$sess}_chars.id AS id, serv{$sess}_chars.name AS char_name FROM serv{$sess}_chars";

		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_assoc($result))
		{		
			$data_stat[$row["id"]] =
			[
				"id" => $row["id"],
				"name" => $row["char_name"],
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 1000,
				"abuse" => []
			];
		}

		$query = "SELECT serv{$sess}_factions.id AS id, serv{$sess}_factions.name AS faction_name FROM serv{$sess}_factions";

		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_assoc($result))
		{
			$data_faction[$row["id"]] =
			[
				"id" => $row["id"],
				"name" => $row["faction_name"],
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 0
			];
		}

		$faction_stats = $data_faction;
		$allstats = $data_stat;
		foreach ($data_kills as $dkills)
		{
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];			

			if (!isset($allstats[$id_killer],$allstats[$id_victim])) continue;
			if($faction_id_killer == $faction_id_victim) continue;

			$allstats[$id_killer]["kills"]++;
			$allstats[$id_victim]["deaths"]++;

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];

			$date_kill = $dkills["date"];
			$unix_date_kill = strtotime($date_kill);

			//Берем текущие рейтинги килера и жертвы
			$Ra = $allstats[$id_killer]["raiting"];
			$Rb = $allstats[$id_victim]["raiting"];

			//Передаем их в функцию расчета рейтинга
			$raiting = EloRating($Ra, $Rb);

			//Изменяем рейтинги игроков
			$add_killer_raiting = $raiting["killer_raiting"];
			$add_victim_raiting = $raiting["victim_raiting"];

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

			if ($faction_id_killer != 0 && $faction_id_victim != 0 && isset($faction_stats[$faction_id_killer]) && isset($faction_stats[$faction_id_victim]))
			{
				$faction_stats[$faction_id_killer]["kills"]++;
				$faction_stats[$faction_id_killer]["raiting"] += $add_killer_raiting;

				$faction_stats[$faction_id_victim]["deaths"]++;
				$faction_stats[$faction_id_victim]["raiting"] += $add_victim_raiting;

				$faction_kills = $faction_stats[$faction_id_killer]["kills"];
				$faction_deaths = $faction_stats[$faction_id_victim]["deaths"];

				$list_of_faction_kills[$faction_id_killer][$faction_kills] =
				[
					"faction_id" => $faction_id_victim,
					"faction_name" => $faction_stats[$faction_id_victim]["name"],
					"raiting" => $add_killer_raiting,
					"char_name_killer" => $data_stat[$id_killer]["name"],
					"char_name_victim" => $data_stat[$id_victim]["name"]
				];

				$list_of_faction_deaths[$faction_id_victim][$faction_deaths] =
				[
					"faction_id" => $faction_id_killer,
					"faction_name" => $faction_stats[$faction_id_killer]["name"],
					"raiting" => $add_victim_raiting,
					"char_name_killer" => $data_stat[$id_killer]["name"],
					"char_name_victim" => $data_stat[$id_victim]["name"]
				];
			}

		}
		
		$faction_name = $data_faction[$frac_id]["name"];
		$faction_rait = $faction_stats[$frac_id]["raiting"];

		usort($faction_stats, 'myCmp');

		$content = '<tr><td class="th" colspan="6"><div class="title">Убийства</div></td></tr>';
		if (isset($list_of_faction_kills[$frac_id]))
		{
			krsort($list_of_faction_kills[$frac_id]);
			foreach ($list_of_faction_kills[$frac_id] as $sfaction)
			{
				$resreit = round($sfaction['raiting']);
				$content .= "
				<tr>
					<td class='td'>$sfaction[char_name_killer]</td>
					<td class='td2'>►</td>
					<td class='td'>$sfaction[char_name_victim]</td>
					<td class='td'><a href='frac_info.php?s={$sess}&frac_id={$sfaction['faction_id']}'>$sfaction[faction_name]</td>
					<td class='td1'><img class ='image'src='images/rating.png'></td>
					<td class='td1'>$resreit</span></td>
				</tr>";
			}
		}
		$content .= '<tr><td class="th" colspan="6"><a name="deaths"></a><div class="title">Смерти</div></td></tr>';
		if (isset($list_of_faction_deaths[$frac_id]))
		{
			krsort($list_of_faction_deaths[$frac_id]);
			foreach ($list_of_faction_deaths[$frac_id] as $sfaction)
			{
				$resreit = round($sfaction['raiting']);
				$content .= "
				<tr>
					<td class='td'>$sfaction[char_name_victim]</td>
					<td class='td2'>◄</td>
					<td class='td'>$sfaction[char_name_killer]</td>
					<td class='td'><a href='frac_info.php?s={$sess}&frac_id={$sfaction['faction_id']}'>$sfaction[faction_name]</td>
					<td class='td1'><img class ='image'src='images/rating.png'></td>
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
			<div class="title"><?=round($faction_rait,2)?></div>
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