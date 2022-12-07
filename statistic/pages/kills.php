<?php
include_once "app.php";

$query = <<<QUERY
SELECT 
(SELECT name from serv{$sess}_chars WHERE id = id_killer) as name_killer, 
(SELECT name from serv{$sess}_factions WHERE id = faction_id_killer) as faction_killer,
(SELECT name from serv{$sess}_chars WHERE id = id_victim) as name_victim,
(SELECT name from serv{$sess}_factions WHERE id = faction_id_victim) as faction_victim,
(date + INTERVAL 4 MINUTE) as date
FROM `serv{$sess}_kills` 
WHERE date >= DATE(NOW() - INTERVAL 1 DAY)
ORDER BY date DESC
QUERY;

$result = mysqli_query($link, $query);
$killsList = "<tr>
				<td class='td'>Name</td>
				<td class='td'>Frac</td>
				<td class='td2'></td>
				<td class='td'>Name</td>
				<td class='td'>Frac</td>
				<td class='td'>Date</td>
			</tr>";
while ($result && $row = mysqli_fetch_assoc($result)) {
	$killsList .= "<tr>
					<td class='td'>{$row['name_killer']}</td>
					<td class='td'>{$row['faction_killer']}</td>
					<td class='td2'>►</td>
					<td class='td'>{$row['name_victim']}</td>
					<td class='td'>{$row['faction_victim']}</td>
					<td class='td'>{$row['date']}</td>
				</tr>";
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<link href="https://fonts.googleapis.com/css?family=Orbitron:500" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
	<title>Килы за сутки</title>
</head>

<body>
	<div class="title">Килы за сутки</div>
	<div align="center" class="block">
		<table align='center' class='table'>
			<?= $killsList ?>
		</table>
	</div>
</body>

</html>