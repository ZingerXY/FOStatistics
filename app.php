<?php
	/*ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);*/
	$version = 3;

	include_once "config.php";

	mysqli_query($link, "SET NAMES utf8");

	// защита БД от SQL иньекций
	function def($text,$linksql = false) {
		$result = strip_tags($text);
		$result = htmlspecialchars($result);
		if ($linksql)
			$result = mysqli_real_escape_string ($linksql, $result);
		return $result;
	}

	$filter = [
		'options' => [
			'default' => 0, // значение, возвращаемое, если фильтрация завершилась неудачей
			// другие параметры
			'min_range' => 0
		],
		'flags' => FILTER_FLAG_ALLOW_OCTAL,
	];
	
	function myCmp($a, $b)
	{
		return ($b["raiting"]*1000) - ($a["raiting"]*1000);
	}
	// Текущая ссесия
	$sess = '22';
	if (isset($_REQUEST['s'])) {
		$sess = filter_var(def($_REQUEST['s']), FILTER_VALIDATE_INT, $filter);
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