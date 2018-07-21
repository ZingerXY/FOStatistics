<?php
	
	/*ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);*/

	include_once "config.php";
	
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
	
	$sess = 18;
	if (isset($_REQUEST['s'])) {
		$sess = filter_var(def($_REQUEST['s']), FILTER_VALIDATE_INT, $filter);
	}
?>
	<!DOCTYPE html>
	<html>
		<head>
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика взятия перков</title>
			<link rel='stylesheet' href='style.css'>
			<style>
			.tab {
				border: solid 1px #e2e2e2;
				border-bottom: none;
				padding: 5px 12px;
				border-radius: 2px 15px 0px 0px;
				margin-right: 2px;
				cursor: pointer;
			}
			.selecttab {
				color: white;
				box-shadow: inset 0px 0px 9px #e2e2e2;
			}
			.tab:hover {
				background: #696969;
			}
			.box {
			  display: flex;
			  align-items: stretch;
			  border-bottom: solid 1px;
			}
			.tabcont {
				transition: 1s;
				padding: 10px;
				border: solid 1px;
				border-top: none;
			}
			.hide {
				display: none;
			}
			</style>
		</head>
		<body>
			<div class="container">
				<div class="box">
					<div id="pers" class="tab selecttab">Персонажи</div>
					<div id="frac" class="tab">Фракции</div>
					<div id="perk" class="perk tab">Перки и трейты</div>
				</div>
				<div class="container">
					<div class="pers tabcont"><?include "chars2.php";?></div>
					<div class="frac tabcont hide"><?include "factions2.php";?></div>
					<div class="perk tabcont hide"><?include "perks.php";?></div>
				</div>
			</div>
			<script>
			var tabsconts = document.querySelectorAll(".tabcont");
			var tabs = document.querySelectorAll(".tab");
			for (var i = 0; i < tabs.length; i++) {
			  tabs[i].onclick = function(e){
					for (var i = 0; i < tabs.length; i++) {
						tabs[i].classList.remove("selecttab");
						tabsconts[i].classList.add("hide");
					}
					var tabcount = document.querySelector(".tabcont." + this.id);
					tabcount.classList.remove("hide");
					this.classList.add("selecttab");
			  };
			}
			function ajax(url,func) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', url, true);
				xhr.onreadystatechange = function() { // (3)
				  if (xhr.readyState == 4) func(xhr.response);
				}
				xhr.responseType = "document";
				xhr.send();
			}
			var links = document.querySelectorAll("a");
			for (var i = 0; i < links.length; i++) {
				links.onclick = function () {
					ajax(this.href,function(res) {
						console.log(res);
					})
					return false;
				}
			}
			</script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>