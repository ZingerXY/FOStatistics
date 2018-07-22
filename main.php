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
			<meta charset="utf-8">
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика взятия перков</title>
			<link rel='stylesheet' href='style.css'>
			<style>
			.tab {
				border: solid 1px #e2e2e2;
				border-bottom: none;
				padding: 5px 12px;
				border-radius: 2px 2px 0px 0px;
				margin-right: 4px;
				margin-left: 0px;
				cursor: pointer;
				position: relative;
			}
			.selecttab {
				color: white;
				background-color: #444444;
				margin: 0px 4px -1px 0px;
				border-radius: 2px 2px 0px 0px;
			}			
			.tab:hover {
				background: #696969;
			}
			.selecttab:hover {
				background-color: #444444;
			}
			.box {
				display: flex;
				align-items: stretch;
			}
			.tabcont {
				transition: 1s;
				padding: 10px;
				border: solid 1px;
				min-width: 500px;
				height: 650px;
				overflow: hidden;
			}
			.stabcont {
				transition: 1s;
				padding: 10px;
				border: solid 1px;
				min-width: 500px;
				margin-left: -1px;
				height: 650px;
				overflow: hidden;
			}
			.hide {
				display: none;
			}		
			#parent {
				width: 99%;
				position: absolute;
				text-align: center;
			}
			#main {
				display: inline-block;
			}			
			</style>
		</head>
		<body>
			<div id="parent">
			<div id="main">
				<div class="box">
					<div id="pers" class="tab selecttab">Персонажи</div>
					<div id="frac" class="tab">Фракции</div>
					<div id="perk" class="perk tab">Перки и трейты</div>
				</div>
				<div align="center" class="box">
					<div class="pers tabcont"><?include "chars2.php";?></div>
					<div class="frac tabcont hide"><?include "factions2.php";?></div>
					<div class="perk tabcont hide"><?include "perks.php";?></div>
					<div id="ajaxpage" class="stabcont hide"></div>
				</div>
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
				tabsconts[i].onwheel = scroll;
			}
			ajaxpage.onwheel = scroll;
			var links = document.querySelectorAll("a");
			for (var i = 0; i < links.length; i++) {
				links[i].onclick = relink;
			}
			// Для ajax запросов
			function ajax(url,func) {
				var xhr = new XMLHttpRequest();
				xhr.open('POST', url, true);
				xhr.onreadystatechange = function() { // (3)
				  if (xhr.readyState == 4) func(xhr.response);
				}
				xhr.responseType = "document";
				xhr.send();
			}
			// Для ajax ссылок
			function relink() {
				ajax(this.href,function(res) {
					ajaxpage.scrollTop = 0;
					ajaxpage.innerHTML = res.body.innerHTML;
					ajaxpage.classList.remove("hide");
					var inlinks = ajaxpage.querySelectorAll("a");
					for (var i = 0; i < inlinks.length; i++) {
						inlinks[i].onclick = relink;
					}
				})
				return false;
			}
			// Для скрола элементов без скролбаров
			function scroll(e) { 
				var delta = e.deltaY || e.detail || e.wheelDelta; 
				if(delta>0) this.scrollTop = this.scrollTop + 18; 
				else this.scrollTop = this.scrollTop - 18; e.preventDefault(); 
			}
			
			</script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>