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
	$sql = "SELECT DISTINCT DATE_FORMAT(created, '%Y - %m') AS date FROM ost_ticket_thread WHERE created AND time_spent ORDER BY date DESC";
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
				<th width="12%">Aktive Tickets</th>
				<th width="12%">Aktivitäten</th>
				<th width="12%">Antwortzeit</th>
				<th width="12%">Gesamtzeit</th>
				<th width="12%">Anfahrten</th>
			</tr>
		</thead>
		<?php
			// SUM(case when ti.status_id = 3 then 1 else 0 end) AS numTicketsClosed,
			$sql = 'SELECT
					COUNT(ti.ticket_id) AS numTickets,
					SUM(ti.time_spent) AS sumOverall,
					u.name AS name,
					u.id AS userId,
					SUM(t.numOnsite) AS numOnsite,
					SUM(t.sum) AS sum,
					SUM(t.activities) AS activities
				FROM
					ost_user u,
					ost_ticket ti,
					(SELECT
						ticket_id,
						SUM(case when time_type = 5 then 1 else 0 end) AS numOnsite,
						SUM(time_spent) AS sum,
						COUNT(id) AS activities
					FROM
						ost_ticket_thread
					WHERE
						time_spent
						' . ($_REQUEST['date'] ? 'AND DATE_FORMAT(created,"%Y - %m") = "' . $_REQUEST["date"] . '"' : '') . '
					GROUP BY
						ticket_id) t
				WHERE
					u.id = ti.user_id
					AND ti.ticket_id = t.ticket_id
				GROUP BY
					ti.user_id
				ORDER BY
					sum DESC';
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				$count = $row['count'];
				echo '<tr>';
					echo "<td><a href='users_print.php?id=" . $row['userId'] . "'>" . $row['name'] . "</a></td>";
					echo "<td>" . $row['numTickets'] . "</td>";
					echo "<td>" . $row['activities'] . "</td>";
					echo "<td>" . formatTime($row['sum']) . "</td>";
					echo "<td>" . formatTime($row['sumOverall']) . "</td>";
					echo "<td>" . $row['numOnsite'] . "</td>";
				echo '</tr>';
			}
		?>
	</table>
	
<?php

} else {
?>
	<h1>Ticketbericht <?php echo $Username ?></h1>
	<p>Fehler.</p>
<?php
}
require_once(STAFFINC_DIR.'footer.inc.php');
