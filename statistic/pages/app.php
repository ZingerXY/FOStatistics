<?php
	/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	*/
	// $version = time(); /* Для разработки */
	$version = "26.1";

	// include_once "config.php";
	include_once "../../config.php";

	mysqli_query($link, "SET NAMES utf8");
	
	$filter = [
		'options' => [
			'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
			// другие параметры
			'min_range' => 0
		],
		'flags' => FILTER_FLAG_ALLOW_OCTAL,
	];
	// Текущая ссесия
	$sess = '26';
	if (isset($_REQUEST['s'])) {
		$sess = filter_var(def($_REQUEST['s']), FILTER_VALIDATE_INT, $filter);
	}

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
		if (count($res) >= $statPerk) {
			if ((int) $res[$statPerk]) {
				$statChars[] = $row["id"];
			}
		}
	}


	/**
	 * Определяет считать ли стату персонажу с указанным id
	 * @param int $id идетификатор персонажа
	 * @return bool
	*/
	function isNotStatChar($id) {
		// return false; // Отключить статиста
		global $statChars;
		// return !in_array($id, $statChars);
		return false;
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