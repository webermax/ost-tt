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

function convTimeType($typeid) {
	$sql = 'SELECT `value` FROM `ost_list_items` WHERE `id` = '. $typeid;
	$res = db_query($sql);
	
	$typearray = db_fetch_array($res);

	$typetext = $typearray['value'];
	return $typetext;
}

function countTime($ticketid, $typeid) {
	$sql = 'SELECT SUM(`time_spent`) AS `totaltime` FROM `ost_ticket_thread` WHERE `ticket_id` = '. $ticketid .' AND `time_type` = '. $typeid .' AND time_bill = 1';
	$res = db_query($sql);
	
	$timearray = db_fetch_array($res);

	$totaltime = $timearray['totaltime'];
	return $totaltime;
}


$user = null;

//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($user=User::lookup($_REQUEST['id']))) {
         $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('user'));
    }
}

//Navigation & Page Info
$nav->setTabActive('users');
$ost->setPageTitle(sprintf(__('Ticketbericht %s'), $user->getName()));

if(!$errors) {
	// Retrieve User Information
	$Username = $user->getName();
	$UserID = $user->getID();

	// Determine ID value for time-type
	$sql = 'SELECT * FROM `ost_list` WHERE `type` = "time-type"';
	$res = db_query($sql);
	$timelist = db_fetch_array($res);
	$timelistid = $timelist['id'];

	// Generate Array of times for summary
	$sql = 'SELECT * FROM `ost_list_items` where `list_id` = ' . $timelistid;
	$res = db_query($sql);
	$loop = 0;
	while($row = db_fetch_array($res, MYSQL_ASSOC)) {
		$loop++;
		$time[$loop][0] = countTime($TicketID, $row['id']);
		$time[$loop][1] = $row['id'];
	}
}

require_once(STAFFINC_DIR.'header.inc.php');

if(!$errors) {
?>

	<h1>Ticketbericht <?php echo $Username ?></h1>
	
	<p>
		<?php
		for ($x = 1; $x <= count($time); $x++) {
			if ($time[$x][0] <> "" && $time[$x][0] > 0) {
				echo formatTime($time[$x][0]) . " " . convTimeType($time[$x][1]) . "<br />";
			}
		} 
		?>
	</p>
	
	<table class="list" border="0" cellpadding="2" cellspacing="1" width="940">
		<thead>
			<tr>
				<th width="40%">Monat</th>
				<th width="10%">Erledigt</th>
				<th width="10%">Gesamtzeit</th>
				<th width="10%">Anfahrten</th>
			</tr>
		</thead>
		<?php
			$sql = 'SELECT
					COUNT(ti.ticket_id) AS numTickets,
					SUM(ti.time_spent) AS sum,
					SUM(t.numOnsite) AS numOnsite,
					ti.closed
				FROM
					ost_ticket ti,
					(SELECT ticket_id, SUM(case when time_type = 5 then 1 else 0 end) AS numOnsite FROM ost_ticket_thread WHERE 1 GROUP BY ticket_id) t
				WHERE
					ti.user_id = ' . $UserID . '
					AND ti.ticket_id = t.ticket_id
					AND ti.status_id = 3
					AND ti.closed
					AND ti.time_spent
				GROUP BY
					DATE_FORMAT(ti.closed,"%Y - %m")
				ORDER BY
					ti.closed DESC';
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				if ($row['poster']<>"SYSTEM") {
					$date = new DateTime($row['closed']);
					echo '<tr>';
						echo "<td>" . $date->format('Y - m') . "</td>";
						echo "<td><a href='users.php?id=" . $UserID . "'>" . $row['numTickets'] . "</a></td>";
						echo "<td>" . formatTime($row['sum']) . "</td>";
						echo "<td>" . $row['numOnsite'] . "</td>";
					echo '</tr>';
				}
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
?>
