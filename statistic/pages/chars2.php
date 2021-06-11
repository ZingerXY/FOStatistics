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
					kills.weapon_killer,
					kills.id_victim,
					kills.faction_id_victim,
					kills.armor_victim,
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
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 1000,
				"abuse" => []
			];
		}


		calculateStats($allstats, $data_kills);

		if (!$allstats) {
			$allstats = [];
		}

		usort($allstats, 'myCmp');

		$content = "";
		$num = 1;
		foreach ($allstats as $schar) {
			if ($schar["kills"] == 0 && $schar["deaths"] == 0) {
				continue;
			}
			$resreit = round($schar['raiting'] - 1000, 2);
			$content .= "
			<tr>
				<td class='td3'>$num</td>
				<td class='td'><a href='statistic/pages/char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
				<td class='td1'><img class ='image'src='statistic/images/kill.png'></td>
				<td class='td1'>$schar[kills]</td>
				<td class='td2'><img class ='image'src='statistic/images/death.png'></td>
				<td class='td1'>$schar[deaths]</td>
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
		<table align='center' id='table' class='table'>
			<? if ($num > 1): ?>
				<?=$content?>
			<? else: ?>
				<p>Недостаточно данных для вывода статистики</p>
			<? endif ?>
		</table>
	</div>
<?
	}