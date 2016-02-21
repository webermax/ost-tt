<?php

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');

function formatTime($time) {
	$hours = floor($time / 60);
	$minutes = $time % 60;

	$formatted = '';

	if ($hours > 0) {
		$formatted .= $hours . ' h';
	}
	if ($minutes > 0) {
		if ($formatted)
			$formatted .= ', ';
		$formatted .= $minutes . ' min';
	}
	return $formatted;
}

//Navigation & Page Info
$nav->setTabActive('users');
$ost->setPageTitle(__('Ticketberichte'));

require_once(STAFFINC_DIR.'header.inc.php');

/*
 * $date = new DateTime($row['created']);
 */

if(!$errors) {
	
	// get all months with activities
	$sql = "SELECT DISTINCT DATE_FORMAT(created, '%Y - %m') AS date FROM ost_ticket_thread WHERE time_bill ORDER BY date DESC";
	$res = db_query($sql);
	$monthOptions = array();
	while($row = db_fetch_array($res, MYSQL_ASSOC)) {
		$monthOptions[] = $row['date'];
	}
	
?>

	<h1>Ticketberichte</h1>
	
	<p>
		<select onchange="location.href='?date=' + this.value">
			<option value="">Gesamt</option>
			<?php
			foreach($monthOptions as $option) echo "<option" . (($_REQUEST['date'] == $option) ? ' selected' : '') . ">" . $option . "</option>";
			?>
		</select>
	</p>
	
	<table class="list" border="0" cellpadding="2" cellspacing="1" width="940">
		<thead>
			<tr>
				<th width="40%">Kunde</th>
				<th width="10%">Tickets aktiv</th>
				<th width="10%">Zeit</th>
				<th width="10%">Anfahrten</th>
				<th width="30%">Kategorien</th>
				
			</tr>
		</thead>
		<?php
			$sql = 'SELECT
					t.created,
					COUNT(t.id) AS count,
					COUNT(DISTINCT ti.ticket_id) AS numTickets,
					SUM(case when t.time_type = 1 then 1 else 0 end) AS numTelephone,
					SUM(case when t.time_type = 2 then 1 else 0 end) AS numEmail,
					SUM(case when t.time_type = 3 then 1 else 0 end) AS numRemote,
					SUM(case when t.time_type = 4 then 1 else 0 end) AS numWorkshop,
					SUM(case when t.time_type = 5 then 1 else 0 end) AS numOnsite,
					SUM(t.time_spent) AS sum,
					u.name AS name,
					u.id AS userId
				FROM
					ost_ticket_thread t, ost_user u, ost_ticket ti
				WHERE
					t.ticket_id = ti.ticket_id
					AND ti.user_id = u.id
					AND (t.thread_type="R" OR t.thread_type="N")
					AND t.time_bill = 1
					' . ($_REQUEST['date'] ? 'AND DATE_FORMAT(t.created,"%Y - %m") = "' . $_REQUEST["date"] . '"' : '') . '
				GROUP BY
					ti.user_id
				ORDER BY
					sum DESC';
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				if ($row['poster']<>"SYSTEM") {
					echo '<tr>';
						echo "<td><a href='users_print.php?id=" . $row['userId'] . "'>" . $row['name'] . "</a></td>";
						echo "<td>" . $row['numTickets'] . "</td>";
						echo "<td>" . formatTime($row['sum']) . "</td>";
						echo "<td>" . $row['numOnsite'] . "</td>";
						echo "<td>";
						echo "<div style='display:inline-block;height:10px;background:black;width:" . $row['numTelephone'] / $row['count'] * 100 . "%'></div>";
						echo "<div style='display:inline-block;height:10px;background:gray;width:" . $row['numEmail'] / $row['count'] * 100 . "%'></div>";
						echo "<div style='display:inline-block;height:10px;background:orange;width:" . $row['numRemote'] / $row['count'] * 100 . "%'></div>";
						echo "<div style='display:inline-block;height:10px;background:chocolate;width:" . $row['numWorkshop'] / $row['count'] * 100 . "%'></div>";
						echo "<div style='display:inline-block;height:10px;background:firebrick;width:" . $row['numOnsite'] / $row['count'] * 100 . "%'></div>";
						echo "</td>";
					echo '</tr>';
				}
			}
		?>
	</table>
	
<?php

echo "<p />";

echo "<div style='display:inline-block;height:10px;background:black;width:10px'></div> Telefon ";
echo "<div style='display:inline-block;height:10px;background:gray;width:10px'></div> E-Mail ";
echo "<div style='display:inline-block;height:10px;background:orange;width:10px'></div> Remote ";
echo "<div style='display:inline-block;height:10px;background:chocolate;width:10px'></div> Werkstatt ";
echo "<div style='display:inline-block;height:10px;background:firebrick;width:10px'></div> Anfahrt";

} else {
?>
	<h1>Ticketbericht <?php echo $Username ?></h1>
	<p>Fehler.</p>
<?php
}
require_once(STAFFINC_DIR.'footer.inc.php');
