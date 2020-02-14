<?php
	
	include_once "app.php";
	
	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (isset($_REQUEST['frac_id']) && ctype_digit ($_REQUEST['frac_id']) && mysqli_num_rows($chrtbl) > 0) {

		$frac_id = filter_var(def($_REQUEST['frac_id'],$link), FILTER_VALIDATE_INT, $filter);

		$query = "	SELECT serv{$sess}_kills.id_killer,
					serv{$sess}_kills.faction_id_killer,
					serv{$sess}_kills.id_victim,
					serv{$sess}_kills.faction_id_victim
					FROM serv{$sess}_kills";

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
				"raiting" => 0
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

			$allstats[$id_killer]["raiting"] += ($victim_kills / ($victim_kills + $victim_deaths));
			$allstats[$id_victim]["raiting"] -= ($killer_deaths / ( $killer_deaths + $killer_kills));

			if ($faction_id_killer != 0 && $faction_id_victim != 0 && isset($faction_stats[$faction_id_killer]) && isset($faction_stats[$faction_id_victim]))
			{
				$faction_stats[$faction_id_killer]["kills"]++;
				$faction_stats[$faction_id_killer]["raiting"] += ($victim_kills / ($victim_kills + $victim_deaths));

				$faction_stats[$faction_id_victim]["deaths"]++;
				$faction_stats[$faction_id_victim]["raiting"] -= ($killer_deaths / ( $killer_deaths + $killer_kills));

				$faction_kills = $faction_stats[$faction_id_killer]["kills"];
				$faction_deaths = $faction_stats[$faction_id_victim]["deaths"];

				$list_of_faction_kills[$faction_id_killer][$faction_kills] =
				[
					"faction_id" => $faction_id_victim,
					"faction_name" => $faction_stats[$faction_id_victim]["name"],
					"raiting" => ($victim_kills / ($victim_kills + $victim_deaths)),
					"char_name_killer" => $data_stat[$id_killer]["name"],
					"char_name_victim" => $data_stat[$id_victim]["name"]
				];

				$list_of_faction_deaths[$faction_id_victim][$faction_deaths] =
				[
					"faction_id" => $faction_id_killer,
					"faction_name" => $faction_stats[$faction_id_killer]["name"],
					"raiting" => ($killer_deaths / ( $killer_deaths + $killer_kills)),
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
				$resreit = round($sfaction['raiting'], 2);
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
				$resreit = round($sfaction['raiting'], 2);
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