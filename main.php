<?php
	include_once "app.php";
?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
			<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
			<title>Статистика взятия перков</title>
			<link rel='stylesheet' href='style.css'>
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
					<div id="perks" class="perk tabcont hide"><?include "perks.php";?></div>
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
				//tabsconts[i].onwheel = scroll;
			}
			//ajaxpage.onwheel = scroll;
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
				if(!~this.href.indexOf("perks")) {
					ajax(this.href,function(res) {
						ajaxpage.scrollTop = 0;
						ajaxpage.innerHTML = res.body.innerHTML;
						ajaxpage.classList.remove("hide");
						var inlinks = ajaxpage.querySelectorAll("a");
						for (var i = 0; i < inlinks.length; i++) {
							if(!~inlinks[i].href.indexOf("#")) {
								inlinks[i].onclick = relink;
							}
						}
					});
				}else {
					ajax(this.href,function(res) {
						perks.scrollTop = 0;
						perks.innerHTML = res.body.innerHTML;
						perks.classList.remove("hide");
						var inlinks = perks.querySelectorAll("a");
						for (var i = 0; i < inlinks.length; i++) {
							if(!~inlinks[i].href.indexOf("#")) {
								inlinks[i].onclick = relink;
							}
						}
					});
				}
				return false;
			}
			// Для скрола элементов без скролбаров
			/*function scroll(e) { 
				var delta = e.deltaY || e.detail || e.wheelDelta; 
				if(delta>0) this.scrollTop = this.scrollTop + 18; 
				else this.scrollTop = this.scrollTop - 18; e.preventDefault(); 
			}*/
			
			</script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>