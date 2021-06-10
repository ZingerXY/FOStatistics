<?php

	include_once "app.php";

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
		while ($row = mysqli_fetch_assoc($result)) {
			$data_stat[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["char_name"],
				"abuse" => []
			];
		}

		$query = "SELECT serv{$sess}_factions.id AS id, serv{$sess}_factions.name AS faction_name FROM serv{$sess}_factions";

		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_assoc($result))
		{
			$data_faction[$row["id"]] = [
				"id" => $row["id"],
				"name" => $row["faction_name"],
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 1000
			];
		}

		$faction_stats = $data_faction;
		$allstats = $data_stat;
		foreach ($data_kills as $dkills) {
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			if (isNotStatChar($id_killer) || isNotStatChar($id_victim)) {
				continue;
			}

			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];

			if (!isset($allstats[$id_killer], $allstats[$id_victim])) {
				continue;
			}

			if ($faction_id_killer == $faction_id_victim) {
				continue;
			}

			$date_kill = $dkills["date"];
			$unix_date_kill = strtotime($date_kill);

			//Берем текущие рейтинги килера и жертвы
			$Ra = $faction_stats[$faction_id_killer]["raiting"];
			$Rb = $faction_stats[$faction_id_victim]["raiting"];

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

			if ($faction_id_killer != 0 && 
				$faction_id_victim != 0 && 
				isset($faction_stats[$faction_id_killer]) && 
				isset($faction_stats[$faction_id_victim])) {

				$faction_stats[$faction_id_killer]["kills"]++;
				$faction_stats[$faction_id_killer]["raiting"] += $add_killer_raiting;

				$faction_stats[$faction_id_victim]["deaths"]++;
				$faction_stats[$faction_id_victim]["raiting"] += $add_victim_raiting;
			}
		}
		
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
			<? if ($num > 1): ?>
				<?=$content?>
			<? else: ?>
				<p>Недостаточно данных для вывода статистики</p>
			<? endif ?>
		</table>
	</div>
<?
	}