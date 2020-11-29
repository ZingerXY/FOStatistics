<?php
	
	include_once "app.php";
	
	function getTopReitingCharsIds($link, $sess, $ck) {
		$query = "	SELECT serv{$sess}_kills.id_killer,
					serv{$sess}_kills.faction_id_killer,
					serv{$sess}_kills.id_victim,
					serv{$sess}_kills.faction_id_victim
					FROM serv{$sess}_kills";

		$result = mysqli_query($link, $query);
		for ($data_kills=[]; $row = mysqli_fetch_assoc($result); $data_kills[] = $row);

		$query = "SELECT serv{$sess}_chars.id AS id, serv{$sess}_chars.name AS char_name FROM serv{$sess}_chars";

		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_assoc($result)) {
			$data_stat[$row["id"]] =
			[
				"id" => $row["id"],
				"name" => $row["char_name"],
				"kills" => 0,
				"deaths" => 0,
				"raiting" => 0
			];
		}

		$allstats = $data_stat;
		foreach ($data_kills as $dkills) {
			$id_killer = $dkills["id_killer"];
			$id_victim = $dkills["id_victim"];
			$faction_id_killer = $dkills["faction_id_killer"];
			$faction_id_victim = $dkills["faction_id_victim"];

			if (!isset($allstats[$id_killer],$allstats[$id_victim])) continue;
			if($faction_id_killer == $faction_id_victim) continue;

			$allstats[$id_killer]["kills"]++;
			$allstats[$id_victim]["deaths"]++;

			$killer_kills = $allstats[$id_killer]["kills"];
			$victim_deaths = $allstats[$id_victim]["deaths"];
			$victim_kills = $allstats[$id_victim]["kills"];
			$killer_deaths = $allstats[$id_killer]["deaths"];

			$allstats[$id_killer]["raiting"] += ($victim_kills / ($victim_kills + $victim_deaths));
			$allstats[$id_victim]["raiting"] -= ($killer_deaths / ( $killer_deaths + $killer_kills));
		}
		
		if(!$allstats) {
			$allstats = [];
		}
		
		usort($allstats, 'myCmp');
		
		$allstats = array_slice($allstats, 0, $ck);
		
		$result = [];
		foreach ($allstats as $stat) {
			$result[] = $stat['id'];
		}
		return implode($result,',');
	}
	
	$ck = 0;
	if (isset($_REQUEST ['ck'])) {
		$ck = filter_var(def($_REQUEST ['ck']), FILTER_VALIDATE_INT, $filter);
	}
	
	$type = false; // тип отображения статиситики топа по убийствам по рейтингу или 
	$typeStat = 'топу';
	if (isset($_REQUEST ['kills'])) {
		$type = true;
		$typeStat = 'убийствам';
	}
	
	$query = "SELECT id,name FROM `serv{$sess}_name_perks`";
		
	$result = mysqli_query($link, $query);
	$perk = [];
	
	while ($result && $row = mysqli_fetch_assoc($result)) {
		$perk[] = ['name'=>$row["name"],'id'=>$row["id"]];
	}
	
	if ($ck) {
		if ($type) { // Расчет топа статки по убийствам
			$query = "SELECT id, pidlist FROM serv{$sess}_perks WHERE LENGTH( pidlist ) >= 155 && id = (select distinct id_killer from serv{$sess}_kills where id_killer = serv{$sess}_perks.id AND (select count(id_killer) from serv{$sess}_kills where id_killer = serv{$sess}_perks.id) >= $ck)";
		} else { // Расчет топа статки по рейтингу	
			$query = "SELECT id,pidlist FROM serv{$sess}_perks WHERE LENGTH( pidlist ) >= 155 && id in (".getTopReitingCharsIds($link, $sess, $ck).")";
		}
	} else {
		$query = "SELECT id,pidlist FROM serv{$sess}_perks WHERE LENGTH( pidlist ) >= 155";
	}

	$result = mysqli_query($link, $query);
	
	$countBrokenStr = 0;
	
	$stat = [];
	$sum = 0;
	while ($result && $row = mysqli_fetch_assoc($result)) {
		$res = str_split($row["pidlist"]);
		if (count($res) < 143) { // Старое исправление битых данных
			array_splice($res, 15, 0, [0]);
			$countBrokenStr++;
		}
		if (count($res) > 157 && $res[102] == 1) { // Фикс Специалиста
			array_splice($res, 102, 2, [9]);
			$countBrokenStr++;
		}
		foreach ($res as $i => $e) {
			if ($e > 0) {
				if (!array_key_exists($i,$stat))
					$stat[$i] = 1;
				else
					$stat[$i]++;
			}
		}
		$sum++;
	}
	
	$content = '<div id="turn" class="title ptitle"><span id="traitfh">Трейты</span><span id="traitsh">‹</span></div>
				<div id="roll"><table align="center" class="ptable">';
	$content .= '<tbody>';
	$class = 'trait';
	$num = 1;
	foreach ($perk as $i => $e) {
		if (array_key_exists($i,$stat)) {
			$pr = round($stat[$i] / $sum * 100, 2);
		} else {
			$pr = 0;
		}
		$name = $e['name'];
		$id = $e['id'];
		$content .= "
			<tr class='$class perks' data-id='$i'>
				<td>
					<img align ='left' class ='image_perks' src='images/perks/$id.png'" . 
					($num == 119 ? "onmouseover='this.src = \"images/perks/easter_egg.png\"' onmouseout='this.src = \"images/perks/$id.png\"'" : "") . ">
				</td>
				<td class='td'>$name</td>
				<td class='td'>$pr%</td>
			</tr>";
		if ($i == 15) {
			$content .= '</tbody></table></div>
					<table align="center" class="ptable">
						<tr class="perks">
							<td class="th" colspan="3">
								<div class="title">Перки</div>
							</td>
						</tr><tbody>';
			$class = 'perk';
		}
		$num++;
	}
	$content .= '</tbody>';
	
	$content .= "<tr style='background-color:#444444;border-bottom:none;'><td></td><td class='td'>Всего данных</td><td class='td'>$sum</td></tr></table>";
?>
	<div align="center" class="block" style="margin: 4px 0px;">Фильтр по <?=$typeStat?> игроков</div>
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
	<script>console.log("<?=$countBrokenStr?>")</script>
	<div align="center" class="block">
		<?
		if ($sum > 15) {
			echo $content;
		} else {
			echo "<p id='nopes'>Недостаточно данных для вывода статистики</p>";
		}
		?>
	</div>

