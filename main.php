<?php
	include_once "app.php";
	$mainphp = true;
	$tabid = 'pers';
	if(isset($_REQUEST['select_tab'])) {
		$tabid = $_REQUEST['select_tab'];
	}
	
?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<title>Статистика</title>
			<link rel='stylesheet' href='style.css'>
		</head>
		<body>
			<div id="parent">
			<div id="main">
				<div class="box">
					<div id="pers" class="tab<?=($tabid == 'pers'?' selecttab':'')?>">Персонажи</div>
					<div id="frac" class="tab<?=($tabid == 'frac'?' selecttab':'')?>">Фракции</div>
					<div id="perk" class="perk tab<?=($tabid == 'perk'?' selecttab':'')?>">Перки и трейты</div>
				</div>
				<div align="center" class="box">
					<div class="pers tabcont<?=($tabid != 'pers'?' hide':'')?>"><?include "chars2.php";?></div>
					<div class="frac tabcont<?=($tabid != 'frac'?' hide':'')?>"><?include "factions2.php";?></div>
					<div id="perks" class="perk tabcont<?=($tabid != 'perk'?' hide':'')?>"><?include "perks.php";?></div>
					<div id="ajaxpage" class="stabcont hide"></div>
				</div>
			</div>
			</div>
			<script>
			// Работа с localStorage >>
			function lsSet(key,data) {
				if (typeof data == "string")
					localStorage.setItem(key,data);
				else {
					textdata = JSON.stringify(data);
					localStorage.setItem(key,textdata);
				}
			}
			
			function lsGet(key) {
				var data = localStorage.getItem(key);
				try {
					return JSON.parse(data);
				} catch (e) {
					return data;
				}		
			}
			
			function lsDel(key) {
				localStorage.removeItem(key)
			}
			
			function lsClear() {
				localStorage.clear();
			}
			// << Работа с localStorage
			if (!lsGet("select_tab")) {
				lsSet("select_tab","pers");
			}
			
			var tabsconts = document.querySelectorAll(".tabcont");
			var tabs = document.querySelectorAll(".tab");
			for (var i = 0; i < tabs.length; i++) {
				tabs[i].onclick = changeTab;
				//tabsconts[i].onwheel = scroll;
			}
			//ajaxpage.onwheel = scroll;
			var links = document.querySelectorAll("a");
			for (var i = 0; i < links.length; i++) {
				links[i].onclick = relink;
			}
			startperk();
			
			var select_tab = lsGet("select_tab");
			changeTab(select_tab);
			// Смена вкладки
			function changeTab(id) {
				var tabid = this.id || id;
				for (var i = 0; i < tabs.length; i++) {
					tabs[i].classList.remove("selecttab");
					tabsconts[i].classList.add("hide");
				}
				var tabcount = document.querySelector(".tabcont." + tabid);
				tabcount.classList.remove("hide");
				document.querySelector("#"+tabid).classList.add("selecttab");
				lsSet("select_tab",tabid);
				document.cookie = "select_tab=" + tabid + "; path=/; expires=" + new Date(Date.now() + 31556926000).toUTCString();
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
				if (typeof nopes == 'undefined') {
					var perktype = [{name:'Быстрый метаболизм',pid:'550',type:''},{name:'Крушила',pid:'551',type:''},{name:'Xилое тело',pid:'552',type:''},{name:'Однорукий',pid:'553',type:''},{name:'Точность',pid:'554',type:''},{name:'Камикадзе',pid:'555',type:''},{name:'Вор (трейт)',pid:'556',type:''},{name:'Быстрый стрелок',pid:'557',type:''},{name:'Маньяк',pid:'558',type:''},{name:'Дурной глаз (трейт)',pid:'559',type:''},{name:'Добродушие',pid:'560',type:''},{name:'Химик',pid:'561',type:''},{name:'Стабильный',pid:'562',type:''},{name:'Жидкое тело',pid:'563',type:''},{name:'Умелец',pid:'564',type:''},{name:'Импульсивный',pid:'565',type:''},{name:'Доп. рукопашн. атаки',pid:'302',type:'lvl15'},{name:'Доп. рукопашн. повр.',pid:'303',type:'lvl33'},{name:'Бонус движения',pid:'304',type:'lvl3'},{name:'Бонус точности',pid:'305',type:'lvl6'},{name:'Бонус скорости',pid:'306',type:'lvl15'},{name:'Быстрая реакция',pid:'307',type:'lvl3'},{name:'Быстрое лечение',pid:'308',type:'lvl6'},{name:'Больше крит. атак',pid:'309',type:'lvl3'},{name:'Иммунитет',pid:'310',type:'lvl12'},{name:'Охранник',pid:'311',type:'quest'},{name:'Уст. к радиации',pid:'312',type:'lvl33'},{name:'Крутизна',pid:'313',type:'lvl3'},{name:'Переноска',pid:'314',type:'lvl3'},{name:'Меткость',pid:'315',type:'lvl6'},{name:'Бесшумный бег',pid:'316',type:'lvl12'},{name:'Исследователь',pid:'317',type:'lvl33'},{name:'Торговля',pid:'318',type:'lvl33'},{name:'Образование',pid:'319',type:'lvl3'},{name:'Лечение',pid:'320',type:'lvl9'},{name:'Воодушевление',pid:'321',type:'lvl12'},{name:'Лучшие крит. атаки',pid:'322',type:'lvl9'},{name:'Двуличный',pid:'323',type:'lvl3'},{name:'Дробила',pid:'324',type:'lvl15'},{name:'Снайпер',pid:'325',type:'lvl15'},{name:'Хитрость',pid:'326',type:'lvl15'},{name:'Человек действия',pid:'327',type:'lvl15'},{name:'Стойкость',pid:'328',type:'lvl12'},{name:'Сила жизни',pid:'329',type:'lvl12'},{name:'Увертливость',pid:'330',type:'lvl6'},{name:'Змееглот',pid:'331',type:'lvl33'},{name:'Самоделкин',pid:'322',type:'lvl30'},{name:'Медик',pid:'323',type:'lvl30'},{name:'Вор-профессионал',pid:'334',type:'lvl9'},{name:'Болтливость',pid:'335',type:'lvl33'},{name:'Счастливчик',pid:'336',type:'lvl6'},{name:'Фанат дробовиков',pid:'337',type:'lvl6'},{name:'Карманник',pid:'338',type:'lvl33'},{name:'Привидение',pid:'339',type:'lvl6'},{name:'Отличник',pid:'340',type:'lvl3'},{name:'Полевой медик',pid:'341',type:'lvl12'},{name:'Непоседа',pid:'342',type:'lvl6'},{name:'Полевой санитар',pid:'343',type:'lvl6'},{name:'Следопыт',pid:'344',type:'lvl30'},{name:'Удачный промах',pid:'345',type:'lvl15'},{name:'Скаут',pid:'346',type:'lvl33'},{name:'Ветеран',pid:'347',type:'lvl12'},{name:'Рейнджер',pid:'348',type:'lvl33'},{name:'Оптимизация',pid:'349',type:'lvl3'},{name:'Самоучка',pid:'351',type:'lvl3'},{name:'Математик',pid:'352',type:'lvl12'},{name:'Мутация',pid:'353',type:'lvl12'},{name:'Регенерация',pid:'354',type:'lvl15'},{name:'Осведомленность',pid:'355',type:'lvl3'},{name:'Крепкий Орешек',pid:'356',type:'lvl12'},{name:'Эгоист',pid:'357',type:'Ур_2'},{name:'Ящерица',pid:'358',type:'Ур_9'},{name:'Крутой парень',pid:'359',type:'Ур_6'},{name:'Толстокожий',pid:'360',type:'Ур_9'},{name:'Мастер ближнего боя',pid:'361',type:'Ур_9'},{name:'Ковбой',pid:'362',type:'Ур_6'},{name:'Критическое мышление',pid:'363',type:'Ур_9'},{name:'Рикошет',pid:'364',type:'Ур_6'},{name:'Фанатик',pid:'365',type:'Ур_9'},{name:'Стоик',pid:'366',type:'Ур_9'},{name:'Концентрация',pid:'367',type:'Ур_15'},{name:'Пристрелка',pid:'368',type:'Ур_12'},{name:'Авторитет',pid:'369',type:'lvl15'},{name:'Зарядка',pid:'370',type:'lvl33'},{name:'Рефлексы',pid:'371',type:'lvl12'},{name:'Неудержимый',pid:'372',type:'lvl15'},{name:'Закалка',pid:'373',type:'lvl33'},{name:'Репликант',pid:'374',type:'lvl30'},{name:'Сапер',pid:'375',type:'lvl9'},{name:'Кровопийца',pid:'380',type:'lvl12'},{name:'Бдительность',pid:'381',type:'lvl3'},{name:'Наблюдательность',pid:'382',type:'lvl3'},{name:'Эксперт подрывник',pid:'383',type:'lvl33'},{name:'Атлет',pid:'384',type:'lvl6'},{name:'Получить силу',pid:'385',type:'lvl6'},{name:'Получить восприятие',pid:'386',type:'lvl6'},{name:'Получить выносливость',pid:'387',type:'lvl6'},{name:'Получить обаяние',pid:'388',type:'lvl6'},{name:'Получить интеллект',pid:'389',type:'lvl6'},{name:'Получить ловкость',pid:'390',type:'lvl6'},{name:'Получить удачу',pid:'391',type:'lvl6'},{name:'Безвредность',pid:'392',type:'lvl33'},{name:'Специалист',pid:'393',type:'lvl33'},{name:'Верткость',pid:'394',type:'lvl3'},{name:'Спортсмен',pid:'395',type:'lvl12'},{name:'Исполнительность',pid:'396',type:'quest'},{name:'Легкие шаги',pid:'397',type:'lvl6'},{name:'Анатомия жизни',pid:'398',type:'lvl12'},{name:'Привлекательность',pid:'399',type:'lvl33'},{name:'Негоциант',pid:'400',type:'lvl33'},{name:'Запаковка',pid:'401',type:'lvl33'},{name:'Пироманьяк',pid:'402',type:'lvl9'},{name:'Прыгучесть',pid:'403',type:'lvl3'},{name:'Продажа',pid:'404',type:'lvl33'},{name:'Человек-глыба',pid:'405',type:'lvl3'},{name:'Вор',pid:'406',type:'lvl30'},{name:'Обращение с оружием',pid:'407',type:'lvl30'},{name:'Стажировка в Городе-Убежище',pid:'408',type:'quest'},{name:'Спец по уборке экскрементов',pid:'417',type:'sys'},{name:'Дурной глаз(перк)',pid:'419',type:'sys'},{name:'Терминатор',pid:'420',type:'lvl15'},{name:'Взрывотехник',pid:'421',type:'lvl12'},{name:'Токсиколог',pid:'422',type:'lvl9'},{name:'Дополнительные атаки',pid:'423',type:'lvl12'},{name:'Бывалый',pid:'424',type:'lvl30'},{name:'Эксперт-метатель',pid:'425',type:'sys'},{name:'Скорняк',pid:'430',type:'quest'},{name:'Прививки из Города-Убежище',pid:'431',type:'quest'},{name:'Живчик',pid:'432',type:'lvl12'},{name:'Улучшенная подкожная броня',pid:'433',type:'quest'},{name:'Реаниматор',pid:'434',type:'lvl12'},{name:'Улучшенная подкожная защита',pid:'434',type:'quest'},{name:'Операция доктора Клауса:Крит',pid:'436',type:'quest'},{name:'Операция доктора Клауса:Антикрит',pid:'437',type:'quest'},{name:'Секреты мастерства:Бартер',pid:'438',type:'quest'},{name:'Секреты мастерства:Ремонт',pid:'439',type:'quest'},{name:'Золотые руки',pid:'440',type:'lvl33'},{name:'Водитель',pid:'442',type:'quest'},{name:'Офицер',pid:'443',type:'lvl15'},{name:'Смотрящий',pid:'444',type:'quest'},{name:'Боевой имплантант',pid:'446',type:'imp'},{name:'Медицинский имплантант',pid:'447',type:'imp'},{name:'Вспомогательный имплант',pid:'448',type:'imp'},{name:'Боевой Инженер',pid:'449',type:'lvl15'},{name:'Оператор чата',pid:'450',type:'sys'},{name:'Мастер урона',pid:'451',type:'mperk'},{name:'Мастер лазера',pid:'452',type:'mperk'},{name:'Мастер огня',pid:'453',type:'mperk'},{name:'Мастер плазмы',pid:'454',type:'mperk'},{name:'Мастер электричества',pid:'455',type:'mperk'},{name:'Мастер импульса',pid:'456',type:'mperk'},{name:'Мастер взрыва',pid:'457',type:'mperk'},{name:'Житель Пустоши',pid:'460',type:'mperk'},{name:'Опытный ремонтник',pid:'461',type:'mperk'},{name:'Опытный инженер',pid:'462',type:'mperk'},{name:'Опытный врач',pid:'463',type:'mperk'},{name:'Огневая поддержка',pid:'466',type:'lvl12'}];
					var tr = Array.from(document.querySelectorAll("tr.perk,.trait"));
					for(var i in tr) 
						if(perktype[i].type)
							tr[i].classList.add(perktype[i].type);	
					sortGrid("trait");
					sortGrid("perk");
					var ic = true;
				turn.onclick = function() {
					if(ic)
					{
						roll.style.height = "0px";
						this.style.borderBottomWidth = "0px";
						traitfh.style.right = "520px";
						traitsh.style.right = "64px";
						ic = false;
					}
					else
					{
						roll.style.height = "";
						this.style.borderBottomWidth = "1px";
						traitfh.style.right = "0px";
						traitsh.style.right = "-450px";
						ic = true;
					}
				};
				}
			}
			</script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>