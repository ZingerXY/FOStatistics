<?php
	
	include_once "app.php";
	
	$ck = 0;
	if (isset($_REQUEST ['ck'])) {
		$ck = filter_var(def($_REQUEST ['ck']), FILTER_VALIDATE_INT, $filter);
	}
	
	$query = "SELECT name FROM `name_perks`";
		
	$result = mysqli_query($link, $query);
	$perk = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$perk[] = $row["name"];
	}
	
	if ($ck)
		$query = "SELECT id,pidlist FROM serv18_perks WHERE id = (select distinct id_killer from serv18_kills where id_killer = serv18_perks.id AND (select count(id_killer) from serv18_kills where id_killer = serv18_perks.id) >= $ck)";
	else
		$query = "SELECT id,pidlist FROM serv18_perks";
	
	$result = mysqli_query($link, $query);
	
	$stat = [];
	$sum = 0;
	while ($row = mysqli_fetch_assoc($result)) {
		$res = str_split($row["pidlist"]);
		if(count($res) < 143)
			array_splice($res, 15, 0, [0]);
		foreach($res as $i => $e) {
			if($e > 0) {
				if(!array_key_exists($i,$stat))
					$stat[$i] = 1;
				else
					$stat[$i]++;
			}
		}
		$sum++;
	}
	
	$content = '<tr id="turn" class="perks"><td class="th" colspan="3"></a><div style="cursor:pointer;" class="title">Трейты</div></td></tr>';
	$content .= '<tbody id="traitbox">';
	$class = 'trait';
	$num = 1;
	foreach($perk as $i => $e) {
		if (array_key_exists($i,$stat))
			$pr = round($stat[$i] / $sum * 100, 2);
		else
			$pr = 0;
		$content .= "
			<tr class='$class perks' data-id='$i'>
				<td>
					<img align ='middle' class ='image_perks' src='images/perks/$num.png'" . 
					($num == 105 ? "onmouseover='this.src = \"images/perks/easter_egg.png\"' onmouseout='this.src = \"images/perks/$num.png\"'" : "") . ">
				</td>
				<td class='td'>$e</td>
				<td class='td'>$pr%</td>
			</tr>";
		if ($i == 15) {
			$content .= '</tbody><tr class="perks"><td class="th" colspan="3"></a><div class="title">Перки</div></td></tr><tbody>';
			$class = 'perk';
		}
		$num++;
	}
	$content .= '</tbody>';
	
	$content .= "<tr style='background-color:#444444;border-bottom:none;'><td></td><td class='td'>Всего данных</td><td class='td'>$sum</td></tr>";
?>
	<div align="center" class="block" style="margin: 4px 0px;">Фильтр по убийствам</div>
	<div align="center">
		<a href="perks.php" class="button">Всё</a>
		<a href="perks.php?ck=25" class="button">25</a>
		<a href="perks.php?ck=50" class="button">50</a>
		<a href="perks.php?ck=100" class="button">100</a>
		<a href="perks.php?ck=150" class="button">150</a>
		<a href="perks.php?ck=200" class="button">200</a>
	</div>
	<div align="center" class="block" style="margin: 4px 0px;">Фильтр по типу перков</div>
	<div align="center" style="margin: 0px auto;width: 300px;">
		<input class="check" id="lvl3" type="checkbox" checked><label for="lvl3">3</label>
		<input class="check" id="lvl6" type="checkbox" checked><label for="lvl6">6</label>
		<input class="check" id="lvl9" type="checkbox" checked><label for="lvl9">9</label>
		<input class="check" id="lvl12" type="checkbox" checked><label for="lvl12">12</label>
		<input class="check" id="lvl15" type="checkbox" checked><label for="lvl15">15</label>
		<input class="check" id="lvl30" type="checkbox" checked><label for="lvl30">30</label>
		<input class="check" id="lvl33" type="checkbox" checked><label for="lvl33">33</label>
		<input class="check" id="quest" type="checkbox" checked><label for="quest">quests</label>
		<input class="check" id="imp" type="checkbox" checked><label for="imp">implant</label>
		<input class="check" id="mperk" type="checkbox" checked><label for="mperk">masters</label>
		<input class="check" id="sys" type="checkbox" checked><label for="sys">system</label>
		<input id="uncheck" type="checkbox" checked><label for="uncheck">uncheckall</label>
	</div>
	<div align="center" class="block">
		<?
		if ($sum > 15) 
			echo "<table align='center' class='table'>".$content."</table>";
		else
			echo "<p id='nopes'>Недостаточно данных для вывода статистики</p>";
		?>
	</div>
