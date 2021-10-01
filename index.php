<?php
	include_once "statistic/pages/app.php";
	$mainphp = true;
	$tabid = 'pers';
	if (isset($_REQUEST['select_tab'])) {
		$tabid = $_REQUEST['select_tab'];
	}

?>
	<!DOCTYPE html>
	<html>
		<head>
			<meta charset="utf-8">
			<title>Статистика</title>
			<link rel='stylesheet' href='statistic/style/main.css?<?=$version?>'>
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
						<div class="pers tabcont<?=($tabid != 'pers'?' hide':'')?>"><?include "statistic/pages/chars2.php";?></div>
						<div class="frac tabcont<?=($tabid != 'frac'?' hide':'')?>"><?include "statistic/pages/factions2.php";?></div>
						<div id="perks" class="perk tabcont<?=($tabid != 'perk'?' hide':'')?>"><?include "statistic/pages/perks.php";?></div>
						<div id="ajaxpage" class="stabcont hide"></div>
					</div>
				</div>
			</div>
			<script src="statistic/js/app.js"></script>
		</body>
	</html>
<?php
	mysqli_close($link);
?>