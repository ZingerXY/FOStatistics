<?php

	if(!isset($mainphp)) {
		 header("HTTP/1.1 301 Moved Permanently"); 
		 header("Location: main.php"); 
		 exit(); 
	}

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (mysqli_num_rows($chrtbl) > 0) {

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
			}
		}
		
		if(!$faction_stats)
			$faction_stats = [];
		
		usort($faction_stats, 'myCmp');

		$content = "";
		$num = 1;
		foreach ($faction_stats	as $sfaction)
		{

			if ($sfaction["kills"] == 0 && $sfaction["deaths"] == 0)
				continue;
			//if (!isset($sfaction["name"])) continue;
			$resreit = round($sfaction['raiting'], 2);
			$content .= "
			<tr>
				<td class='td3'>$num</td>
				<td class='td'><a href='frac_info.php?s={$sess}&frac_id={$sfaction['id']}'>$sfaction[name]</td>
				<td class='td1'><img class ='image'src='images/kill.png'></td>
				<td class='td1'>$sfaction[kills]</td>
				<td class='td2'><img class ='image'src='images/death.png'></td>
				<td class='td1'>$sfaction[deaths]</td>
				<td class='td2'><img class ='image'src='images/rating.png'></td>
				<td class='td1'>$resreit</span></td>
			</tr>";
			$num++;
		}
?>
	<div class="title">
		Stats of <?=$sess?> session
	</div>
	<div class="block">
		<table align='center' class='table'>
			<?=$content?>
		</table>
	</div>
<?
	}