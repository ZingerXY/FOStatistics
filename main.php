<?php
	include_once "app.php";
	$mainphp = true;
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
			startperk();
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
						startperk();
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
			function sortGrid(cls) {
				// Составить массив из TR
				var rowsArray = document.querySelectorAll("tr." + cls);
				var tbody = rowsArray[0].parentNode;
				rowsArray = Array.from(rowsArray);
				// сортировать
				rowsArray.sort(function(a,b) {
					var compA = a.cells[2].innerText.slice(0,-1);
					var compB = b.cells[2].innerText.slice(0,-1);
					return compB - compA;
				});
				// добавить результат в нужном порядке в TBODY
				// они автоматически будут убраны со старых мест и вставлены в правильном порядке
				for (var i = 0; i < rowsArray.length; i++)
					tbody.appendChild(rowsArray[i]);
			}
			function hideshow() {
				var trs = document.querySelectorAll("tr.perk."+this.id);	
				for(var i in trs) {
					if(trs[i].style) {
						if(this.checked)
							trs[i].style.display = '';
						else
							trs[i].style.display = 'none';
					}
				}
			}
			function startperk() {
				var checks = document.querySelectorAll("input.check");
				for (var i = 0; i < checks.length; i++) {
					checks[i].onclick = hideshow;
				}						
				uncheck.onclick = function() {
					var checks = document.querySelectorAll("input.check");
					for (var i = 0; i < checks.length; i++) {
						checks[i].click();
					}				
				};
				turn.onclick = function() {
					if(traitbox.style.display) {
						traitbox.style.display = "";
						this.innerHTML = "-";
					} else {
						traitbox.style.display = "none";
						this.innerHTML = "+";
					}
				}
				if (typeof nopes == 'undefined') {
					var perktype = ['','','','','','','','','','','','','','','','','lvl15','lvl33','lvl3','lvl6','lvl15','lvl3','lvl9','lvl3','lvl12','quest','lvl33','lvl6','lvl3','lvl6','lvl12','lvl33','lvl33','lvl3','lvl6','lvl12','lvl9','lvl3','lvl15','lvl15','lvl15','lvl15','lvl12','lvl12','lvl6','lvl33','lvl30','lvl30','lvl9','lvl33','lvl6','lvl6','lvl33','lvl6','lvl3','lvl12','lvl6','lvl6','lvl30','lvl15','lvl33','lvl12','lvl33','lvl3','lvl3','sys','lvl12','lvl15','lvl3','lvl12','lvl33','lvl12','lvl15','lvl33','lvl30','lvl12','lvl3','lvl3','lvl33','lvl3','lvl6','lvl6','lvl6','lvl6','lvl6','lvl6','lvl6','lvl33','lvl33','lvl3','lvl12','quest','lvl6','lvl12','lvl33','lvl33','lvl33','lvl9','lvl3','lvl33','lvl3','lvl30','lvl30','quest','sys','sys','lvl15','sys','lvl6','lvl9','lvl30','sys','quest','quest','lvl12','quest','sys','quest','quest','quest','quest','quest','lvl33','quest','lvl15','quest','imp','imp','imp','lvl15','sys','mperk','mperk','mperk','mperk','mperk','mperk','mperk','mperk','mperk','mperk','mperk','lvl12']
					var tr = Array.from(document.querySelectorAll("tr.perk,.trait"));
					for(var i in tr)
						if(perktype[i])
							tr[i].classList.add(perktype[i]);	
					sortGrid("trait");
					sortGrid("perk");
				}
			}
			</script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>