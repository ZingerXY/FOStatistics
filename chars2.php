<?php

	if(!isset($mainphp)) {
		 header("HTTP/1.1 301 Moved Permanently"); 
		 header("Location: main.php"); 
		 exit(); 
	}		

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (mysqli_num_rows($chrtbl) > 0) {

		$start = microtime(true);

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

		$time1 = microtime(true) - $start;
		$start = microtime(true);

		$allstats = $data_stat;
		foreach ($data_kills as $dkills)
		{
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];

			if (!isset($allstats[$id_killer],$allstats[$id_victim])) continue;
			if($faction_id_killer != 0 && $faction_id_killer == $faction_id_victim) continue;

			$allstats[$id_killer]["kills"]++;
			$allstats[$id_victim]["deaths"]++;

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];

			$allstats[$id_killer]["raiting"] += ($victim_kills / ($victim_kills + $victim_deaths));
		}

		$time2 = microtime(true) - $start;
		$start = microtime(true);
		
		if(!$allstats)
			$allstats = [];

		usort($allstats, 'myCmp');

		$time3 = microtime(true) - $start;
		$start = microtime(true);

		$content = "";
		$num = 1;
?>
	<div class="title">
		Stats of <?=$sess?> session
	</div>
	<div class="block">
		<table align='center' id='table' class='table'>
		<?php foreach ($allstats as $schar): ?>
		<?php if 
			($schar['kills'] == 0 && $schar['deaths'] == 0) 
				continue; 
			$resreit = round($schar['raiting'], 2);
		?>
		<tr>
			<td class='td3'><?=$num?></td>
			<td class='td'><a href='char_info.php?s=<?=$sess?>&char_id=<?=$schar['id']?>'><?=$schar['name']?></td>
			<td class='td1'><img class ='image'src='images/kill.png'></td>
			<td class='td1'><?=$schar['kills']?></td>
			<td class='td2'><img class ='image'src='images/death.png'></td>
			<td class='td1'><?=$schar['deaths']?></td>
			<td class='td2'><img class ='image'src='images/rating.png'></td>
			<td class='td1'><?=$resreit?></span></td>
		</tr>
		<?php $num++; endforeach;?>
		</table>
	</div>
<?
	}