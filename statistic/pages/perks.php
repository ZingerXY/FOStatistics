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

	$classStat = [
		0 => ['class' => 'Без класса', 'count' => 0],
		1 => ['class' => 'Разведчик', 'count' => 0],
		2 => ['class' => 'Пулеметчик', 'count' => 0],
		3 => ['class' => 'Берсерк', 'count' => 0],
		4 => ['class' => 'Уворотчик', 'count' => 0],
		5 => ['class' => 'Танк', 'count' => 0],
		6 => ['class' => 'Медик', 'count' => 0],
		7 => ['class' => 'Стрелок', 'count' => 0],
		8 => ['class' => 'Пироман', 'count' => 0],
	];
	$isClasses = 0;
	$stat = [];
	$sum = 0;
	while ($result && $row = mysqli_fetch_assoc($result)) {
		$res = str_split($row["pidlist"]);
		if (count($res) > 168) {
			$countBrokenStr++;
			continue;
		}
		foreach ($res as $i => $e) {
			if ($i == 166) {
				$classStat[$e]['count']++;
				if ($e) {
					$isClasses++;
				}
			}
			if ($e > 0) {
				if (!array_key_exists($i, $stat)) {
					$stat[$i] = 1;
				} else {
					$stat[$i]++;
				}
			}
		}
		$sum++;
	}
	$perkContent = "";
	if ($isClasses) {
		$classContent = '<div class="turn title ptitle" data-ic="1" data-class="class">
							<span id="classfh" class="flesh fh">Классы</span>
							<span id="classsh" class="flesh sh">‹</span>
						</div>
						<div id="rollclass" class="roll"><table align="center" class="ptable">
						<tbody>';
		foreach ($classStat as $i => $e) {
			$img = 0;
			$name = $e['class'];
			$pr = round($e['count'] / $sum * 100, 2);
			$classContent .= "
				<tr class='class perks' data-pid='$i'>
					<td>
						<img align ='left' class ='image_perks' src='statistic/images/perks/$img.png'>
					</td>
					<td class='td'>$name</td>
					<td class='td'>$pr%</td>
				</tr>";
		}
		$classContent.= '</tbody></table></div>';
		$perkContent .= $classContent;
	}
	$perkContent .= '<div class="turn title ptitle" data-ic="1" data-class="trait">
					<span id="traitfh" class="flesh fh">Трейты</span>
					<span id="traitsh" class="flesh sh">‹</span>
				</div>
				<div id="rolltrait" class="roll"><table align="center" class="ptable">';
	$perkContent .= '<tbody>';
	$class = 'trait';
	$num = 1;
	foreach ($perk as $i => $e) {
		if (array_key_exists($i, $stat)) {
			$pr = round($stat[$i] / $sum * 100, 2);
		} else {
			$pr = 0;
		}
		$name = $e['name'];
		$id = $e['id'];
		$img = $id;
		if (!file_exists("statistic/images/perks/$id.png")) {
			$img = 0;
		}
		$perkContent .= "
			<tr class='$class perks' data-pid='$id' data-num='$i'>
				<td>
					<img align ='left' class ='image_perks' src='statistic/images/perks/$img.png'" .
					($id == 417 ? "onmouseover='this.src = \"statistic/images/perks/easter_egg.png\"' onmouseout='this.src = \"statistic/images/perks/$img.png\"'" : "") . ">
				</td>
				<td class='td'>$name</td>
				<td class='td'>$pr%</td>
			</tr>";
		if ($i == 15) {
			$perkContent .= '</tbody></table></div>
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
	$perkContent .= '</tbody>';

	$perkContent .= "<tr style='background-color:#444444;border-bottom:none;'><td></td><td class='td'>Всего данных</td><td class='td'>$sum</td></tr></table>";
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
	<script src="statistic/js/perktype_<?=($sess < 25 ? 25 : $sess)?>s.js?<?=$version?>"></script>
	<div align="center" class="block">
		<?
		if ($sum > 15) {
			echo $perkContent;
		} else {
			echo "<p id='nopes'>Недостаточно данных для вывода статистики</p>";
		}
		?>
	</div>
