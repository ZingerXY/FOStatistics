<?php

	include_once "app.php";

	if(!isset($mainphp)) {
		 header("HTTP/1.1 301 Moved Permanently"); 
		 header("Location: main.php"); 
		 exit(); 
	}		

	// Проверка существования таблицы с префиксом
	$chrtbl = mysqli_query($link, "SHOW TABLES LIKE 'serv{$sess}_chars'") or die(mysqli_error($link));

	if (mysqli_num_rows($chrtbl) > 0) {

		$start = microtime(true);

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

		$time1 = microtime(true) - $start;
		$start = microtime(true);

		$allstats = $data_stat;
		foreach ($data_kills as $dkills)
		{
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];

			if (!isset($allstats[$id_killer],$allstats[$id_victim])) continue;
			if($faction_id_killer != 0 && $faction_id_killer == $faction_id_victim) continue;

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
		foreach ($allstats as $schar)
		{
			if ($schar["kills"] == 0 && $schar["deaths"] == 0)
				continue;
			$resreit = round($schar['raiting'] - 1000);
			$content .= "
			<tr>
				<td class='td3'>$num</td>
				<td class='td'><a href='char_info.php?s={$sess}&char_id={$schar['id']}'>$schar[name]</td>
				<td class='td1'><img class ='image'src='images/kill.png'></td>
				<td class='td1'>$schar[kills]</td>
				<td class='td2'><img class ='image'src='images/death.png'></td>
				<td class='td1'>$schar[deaths]</td>
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
		<table align='center' id='table' class='table'>
			<?=$content?>
		</table>
	</div>
<?
	}