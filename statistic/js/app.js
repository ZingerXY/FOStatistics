
// Работа с localStorage >>
function lsSet(key, data) {
	if (typeof data == "string") {
		localStorage.setItem(key, data);
	} else {
		textdata = JSON.stringify(data);
		localStorage.setItem(key, textdata);
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
	lsSet("select_tab", "pers");
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
	document.querySelector("#" + tabid).classList.add("selecttab");
	lsSet("select_tab", tabid);
	document.cookie = "select_tab=" + tabid + "; path=/; expires=" + new Date(Date.now() + 31556926000).toUTCString();
}
// Для ajax запросов
function ajax(url, func) {
	var xhr = new XMLHttpRequest();
	xhr.open('POST', url, true);
	xhr.onreadystatechange = function () { // (3)
		if (xhr.readyState == 4) func(xhr.response);
	}
	xhr.responseType = "document";
	xhr.send();
}
// Для ajax ссылок
function relink() {
	if (!~this.href.indexOf("perks")) {
		ajax(this.href, function (res) {
			ajaxpage.scrollTop = 0;
			ajaxpage.innerHTML = res.body.innerHTML;
			ajaxpage.classList.remove("hide");
			var inlinks = ajaxpage.querySelectorAll("a");
			for (var i = 0; i < inlinks.length; i++) {
				if (!~inlinks[i].href.indexOf("#")) {
					inlinks[i].onclick = relink;
				}
			}
		});
	} else {
		ajax(this.href, function (res) {
			perks.scrollTop = 0;
			perks.innerHTML = res.body.innerHTML;
			perks.classList.remove("hide");
			startperk();
			var inlinks = perks.querySelectorAll("a");
			for (var i = 0; i < inlinks.length; i++) {
				if (!~inlinks[i].href.indexOf("#")) {
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
	rowsArray.sort(function (a, b) {
		var compA = a.cells[2].innerText.slice(0, -1);
		var compB = b.cells[2].innerText.slice(0, -1);
		return compB - compA;
	});
	// добавить результат в нужном порядке в TBODY
	// они автоматически будут убраны со старых мест и вставлены в правильном порядке
	for (var i = 0; i < rowsArray.length; i++) {
		tbody.appendChild(rowsArray[i]);
	}
}
function hideshow() {
	var trs = document.querySelectorAll("tr.perk." + this.id);
	for (var i in trs) {
		if (trs[i].style) {
			if (this.checked) {
				trs[i].style.display = '';
			} else {
				trs[i].style.display = 'none';
			}
		}
	}
}
function startperk() {
	var checks = document.querySelectorAll("input.check");
	for (var i = 0; i < checks.length; i++) {
		checks[i].onclick = hideshow;
	}
	uncheck.onclick = function () {
		var checks = document.querySelectorAll("input.check");
		for (var i = 0; i < checks.length; i++) {
			checks[i].click();
		}
	};
	if (typeof nopes == 'undefined') {
		var tr = Array.from(document.querySelectorAll("tr.perk,.trait"));
		for (var i in perktype) {
			let perkPid = perktype[i].pid;
			let perkType = perktype[i].type;
			if (perkType) {
				document.querySelector('tr[data-pid="' + perkPid + '"]')?.classList?.add(perkType);
			}
		}
		// for (var i in tr) {
		// 	if (perktype[i].type) {
		// 		tr[i].classList.add(perktype[i].type);
		// 	}
		// }
		sortGrid("trait");
		sortGrid("perk");
		if (document.querySelector('#rollclass')) {
			sortGrid("class");
		}
		var turns = document.querySelectorAll("div.turn");
		for (let i = 0; i < turns.length; i++) {
			let turn = turns[i];
			turn.onclick = function () {
				let clasess = this.dataset.class
				let ic = this.dataset.ic;
				let roll = document.querySelector("#roll" + clasess);
				let fh = document.querySelector("#" + clasess + "fh");
				let sh = document.querySelector("#" + clasess + "sh");
				if (ic > 0) {
					roll.style.height = "0px";
					this.style.borderBottomWidth = "0px";
					fh.style.right = "520px";
					sh.style.right = "64px";
					this.dataset.ic = 0;
				} else {
					roll.style.height = "";
					this.style.borderBottomWidth = "1px";
					fh.style.right = "0px";
					sh.style.right = "-450px";
					this.dataset.ic = 1;
				}
			};
		}
	}
}