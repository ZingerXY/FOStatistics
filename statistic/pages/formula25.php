<?php

	// Массив с коэффициетами от броней
	$armor_c = [
		//пижама и мантии
		0 => 0.5, //пижамка
		113 => 0.5,
		585 => 0.5,
		725 => 0.5,
		731 => 0.5,
		916 => 0.5,
		917 => 0.5,
		918 => 0.5,
		919 => 0.5,
		//тир 1 брони
		1 => 0.7, //Кожанка мк1
		2 => 0.7, //Металка мк1
		74 => 0.7, //Кожанная куртка
		379 => 0.7, //Кожанка мк2
		380 => 0.7, //Металка мк2
		7920 => 0.7, //Сборная броня (пустая)
		//тир 2 брони
		17 => 0.85, //ББмк1
		240 => 0.85, //Тесла
		265 => 0.85, //Боевая кожанка
		586 => 0.85, //Кустарка
		8010 => 0.85, // Химза
		8110 => 0.85, //Кожанная куртка мут.
		8115 => 0.85, //Тесла мут.
		13220 => 0.85, //Броня трапера
		//тир 3 брони
		381 => 1.0, //ББмк2
		524 => 1.0, //Мантия стража
		720 => 1.0, //ПББ
		911 => 1.0, //Костюм разведчика
		7921 => 1.0, //Тактичка
		7922 => 1.0, //FFL
		7923 => 1.0, //FFP
		7924 => 1.0, //Огнеупорка
		7925 => 1.0, //FFE
		7926 => 1.0, //EOD
		8111 => 1.0, //Кустарка мут.
		8112 => 1.0, //Кустарка мут. мк2
		//полутоп
		239 => 1.2, //ББС
		547 => 1.2, //ЧББ
		723 => 1.2, //ББА
		724 => 1.2, //РБК
		907 => 1.2, //Броня морпеха
		912 => 1.2, //СББ
		915 => 1.2, //Синька
		8116 => 1.2, //СББ мут.
		8117 => 1.2, //РБК мут.
		//ТОП
		3 => 1.4, //ПА
		232 => 1.4, //ЗПА
		348 => 1.4, //АПА
		349 => 1.4, //АМА мк2
		729 => 1.4, //РБК мк2
		8113 => 1.4, //Сиолвуха мут.
		8114 => 1.4, //Силовуха мк2 мут.
		8118 => 1.4, //РБК мк2 мут.
	];

	//функция расчета рейтинга игрока. Ra, Rb - рейтинги двух игроков, Ra - килера, Rb - жертвы
	function EloRating ($Ra, $Rb) {
		// Ea, Eb - вероятноти выигрыша игроков, вычисляются из входного рейтинга
		$Ea = 1/( 1 + pow(10,($Rb - $Ra)/400 ) );
		$Eb = 1/( 1 + pow(10,($Ra - $Rb)/400 ) );

		//Коэфициенты, зависящие от текущего входного рейтинга игроков
		if ($Ra <= 800) {
			$Ka = 30;
		} else if ($Ra <= 1100 ) {
			$Ka = 20;
		} else if ($Ra <= 1300) {
			$Ka = 10;
		} else if ($Ra > 1300) {
			$Ka = 5;
		}

		if ($Rb <= 800) {
			$Kb = 30;
		} else if ($Rb <= 1100 ) {
			$Kb = 20;
		} else if ($Rb <= 1300) {
			$Kb = 10;
		} else if ($Rb > 1300) {
			$Kb = 5;
		}
		//Возвращаем итоговую прибавку к рейтингу килера и снижение рейтинга у жертвы.
		return ['killer_raiting' => $Ka * (1 - $Ea), 'victim_raiting' => $Kb * (0 - $Eb)];
	}

	/**
	 * Считаем статистику персонажей
	 * @param array $allstats - массив информации о игроках
	 * @param array $data_kills - массив всех убийств на сервере
	 */
	function calculateStats(&$allstats, &$data_kills) {
		foreach ($data_kills as $dkills) {
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			if (isNotStatChar($id_killer) || isNotStatChar($id_victim)) {
				continue;
			}

			if (!isset($allstats[$id_killer], $allstats[$id_victim])) {
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


			if ($faction_id_killer != 0 && $faction_id_killer == $faction_id_victim) {
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
		}
	}

	/**
	 * Считаем статистику подробную персонажей
	 * @param array $allstats - массив информации о игроках
	 * @param array $data_kills - массив всех убийств на сервере
	 * @param array $list_of_kills - массив всех убийств игрока
	 * @param array $list_of_deaths - массив всех смертей игрока
	 */
	function calculateStatsDetails(&$allstats, &$data_kills, &$list_of_kills, &$list_of_deaths) {
		foreach ($data_kills as $dkills) {
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			if (isNotStatChar($id_killer) || isNotStatChar($id_victim)) {
				continue;
			}

			if (!isset($allstats[$id_killer], $allstats[$id_victim])) {
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
					"name" => $allstats[$id_victim]["name"],
					"raiting" => $add_killer_raiting,
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
					"date" => $date_kill
				];

			$list_of_deaths[$id_victim][$victim_deaths] = [
					"id" => $id_killer,
					"name" => $allstats[$id_killer]["name"],
					"raiting" => $add_victim_raiting,
					"weapon" => $weapon_killer,
					"armor" => $armor_victim,
					"date" => $date_kill
				];
		}
	}

	/**
	 * Считаем статистику персонажей
	 * @param array $allstats - массив информации о игроках
	 * @param array $data_kills - массив всех убийств на сервере
	 * @param array $faction_stats - массив информации о фракциях
	 */
	function calculateStatsFaction(&$allstats, &$data_kills, &$faction_stats) {
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
			if (!isset($faction_stats[$faction_id_killer], $faction_stats[$faction_id_victim])) {
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
	}

	/**
	 * Считаем статистику подробную персонажей
	 * @param array $allstats - массив информации о игроках
	 * @param array $data_kills - массив всех убийств на сервере
	 * @param array $faction_stats - массив информации о фракциях
	 * @param array $list_of_faction_kills - массив всех убийств фракции
	 * @param array $list_of_faction_deaths - массив всех смертей фракции
	 */
	function calculateStatsFactionDetails(&$allstats, &$data_kills, &$faction_stats, &$list_of_faction_kills, &$list_of_faction_deaths) {
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
			if (!isset($faction_stats[$faction_id_killer], $faction_stats[$faction_id_victim])) {
				continue;
			}
			if($faction_id_killer == $faction_id_victim) {
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

				$faction_kills = $faction_stats[$faction_id_killer]["kills"];
				$faction_deaths = $faction_stats[$faction_id_victim]["deaths"];

				$list_of_faction_kills[$faction_id_killer][$faction_kills] = [
					"faction_id" => $faction_id_victim,
					"faction_name" => $faction_stats[$faction_id_victim]["name"],
					"raiting" => $add_killer_raiting,
					"char_name_killer" => $allstats[$id_killer]["name"],
					"char_name_victim" => $allstats[$id_victim]["name"]
				];

				$list_of_faction_deaths[$faction_id_victim][$faction_deaths] = [
					"faction_id" => $faction_id_killer,
					"faction_name" => $faction_stats[$faction_id_killer]["name"],
					"raiting" => $add_victim_raiting,
					"char_name_killer" => $allstats[$id_killer]["name"],
					"char_name_victim" => $allstats[$id_victim]["name"]
				];
			}
		}
	}

?>