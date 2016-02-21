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
					SUM(t.time_spent) AS sum,
					SUM(case when t.time_type = 1 then 1 else 0 end) AS numTelephone,
					SUM(case when t.time_type = 2 then 1 else 0 end) AS numEmail,
					SUM(case when t.time_type = 3 then 1 else 0 end) AS numRemote,
					SUM(case when t.time_type = 4 then 1 else 0 end) AS numWorkshop,
					SUM(case when t.time_type = 5 then 1 else 0 end) AS numOnsite
				FROM
					ost_ticket_thread t,
					ost_ticket ti
				WHERE
					t.ticket_id = ti.ticket_id
					AND ti.user_id = ' . $UserID . '
					AND (t.thread_type="R" OR t.thread_type="N")
					AND time_bill = 1
				GROUP BY
					DATE_FORMAT(t.created,"%Y - %m")
				ORDER BY
					t.created DESC';
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				if ($row['poster']<>"SYSTEM") {
					$date = new DateTime($row['created']);
					echo '<tr>';
						echo "<td>" . $date->format('Y - m') . "</td>";
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
?>
