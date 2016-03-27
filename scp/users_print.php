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
}

require_once(STAFFINC_DIR.'header.inc.php');

if(!$errors) {
	
?>

	<h1>Ticketbericht: <?php echo $Username ?></h1>
	
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
				<th width="12%">Aktive Tickets</th>
				<th width="12%">Aktivitäten</th>
				<th width="12%">Antwortzeit</th>
				<th width="12%">Gesamtzeit</th>
				<th width="12%">Anfahrten</th>
			</tr>
		</thead>
		<?php
			$sql = 'SELECT
					SUM(t.activities) AS activities,
					COUNT(ti.ticket_id) AS numTickets,
					SUM(ti.time_spent) AS sumOverall,
					SUM(t.numOnsite) AS numOnsite,
					t.created AS created,
					SUM(t.sum) AS sum
				FROM
					ost_ticket ti,
					(SELECT
						ticket_id,
						created,
						SUM(time_spent) AS sum,
						SUM(case when time_type = 5 then 1 else 0 end) AS numOnsite,
						COUNT(id) AS activities
					FROM
						ost_ticket_thread
					WHERE
						time_spent
					GROUP BY
						ticket_id, DATE_FORMAT(created,"%Y - %m")) t
				WHERE
					ti.user_id = ' . $UserID . '
					AND ti.ticket_id = t.ticket_id
				GROUP BY
					DATE_FORMAT(t.created,"%Y - %m")
				ORDER BY
					created DESC';
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				$date = new DateTime($row['created']);
				echo '<tr>';
					echo "<td><a href='users_print_details.php?id=" . $UserID . "&date=" . $date->format('Y - m') . "'>" . $date->format('Y - m') . "</a></td>";
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
	<h1>Ticketbericht: <?php echo $Username ?></h1>
	<p>Fehler.</p>
<?php
}
require_once(STAFFINC_DIR.'footer.inc.php');
?>
