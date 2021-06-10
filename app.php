<?php
	/*ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);*/
	$version = 4;

	include_once "config.php";

	mysqli_query($link, "SET NAMES utf8");
	// Текущая ссесия
	$sess = '24';
	if (isset($_REQUEST['s'])) {
		$sess = filter_var(def($_REQUEST['s']), FILTER_VALIDATE_INT, $filter);
	}

	$filter = [
		'options' => [
			'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
			// другие параметры
			'min_range' => 0
		],
		'flags' => FILTER_FLAG_ALLOW_OCTAL,
	];

	$result = mysqli_query($link, "SELECT id, pidlist FROM serv{$sess}_perks");
	// Номер перка статист
	$statPerk = 161; // Статист
	$statChars = [];
	while ($result && $row = mysqli_fetch_assoc($result)) {
		$res = str_split($row["pidlist"]);
		if (count($res) > 168) {
			$countBrokenStr++;
			continue;
		}
		if ((int) $res[$statPerk]) {
			$statChars[] = $row["id"];
		}
	}

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

	/**
	 * Определяет считать ли стату персонажу с указанным id
	 * @param int $id идетификатор персонажа
	 * @return bool
	*/
	function isNotStatChar($id) {
		// return false; // Отключить статиста
		global $statChars;
		return !in_array($id, $statChars);
	}

	// защита БД от SQL иньекций
	function def($text, $linksql = false) {
		$result = strip_tags($text);
		$result = htmlspecialchars($result);
		if ($linksql)
			$result = mysqli_real_escape_string ($linksql, $result);
		return $result;
	}

	// Сравнение значений для сортировки
	function myCmp($a, $b) {
		return ($b["raiting"]*1000) - ($a["raiting"]*1000);
	}

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
