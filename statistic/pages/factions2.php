<?php

	include_once "app.php";
	include_once "formula25.php";

	if (!isset($mainphp)) {
		 header("HTTP/1.1 301 Moved Permanently");
		 header("Location: main.php");
		 exit();
	}

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (mysqli_num_rows($chrtbl) > 0) {

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
		// $allstats = $data_stat;
		calculateStatsFaction($allstats, $data_kills, $faction_stats);

		if (!$faction_stats) {
			$faction_stats = [];
		}

		usort($faction_stats, 'myCmp');

		$content = "";
		$num = 1;
		foreach ($faction_stats	as $sfaction) {
			if ($sfaction["kills"] == 0 && $sfaction["deaths"] == 0) {
				continue;
			}
			//if (!isset($sfaction["name"])) continue;
			$resreit = round($sfaction['raiting'] - 1000, 2);
			$content .= "
			<tr>
				<td class='td3'>$num</td>
				<td class='td'><a href='statistic/pages/frac_info.php?s={$sess}&frac_id={$sfaction['id']}'>$sfaction[name]</td>
				<td class='td1'><img class ='image'src='statistic/images/kill.png'></td>
				<td class='td1'>$sfaction[kills]</td>
				<td class='td2'><img class ='image'src='statistic/images/death.png'></td>
				<td class='td1'>$sfaction[deaths]</td>
				<td class='td2'><img class ='image'src='statistic/images/rating.png'></td>
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
			<? if ($num > 1): ?>
				<?=$content?>
			<? else: ?>
				<p>Недостаточно данных для вывода статистики</p>
			<? endif ?>
		</table>
	</div>
<?
	}