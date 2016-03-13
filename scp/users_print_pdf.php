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

$ticket = null;

//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id']))) {
         $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('ticket'));
    }
}

function pdfExport($ticket, $psize='A4', $notes=false) {
	require_once(INCLUDE_DIR.'class.pdf.report.php');

	$source = 'Dienstleistungsbericht.pdf';
	$name='Ticket-'.$ticket->getNumber().'-Report.pdf';
	
	$pdf = new Ticket2Report($source, $ticket, $psize, $notes);
	
	$pdf->Output($name, 'I');
	exit;
}

//Navigation & Page Info
$nav->setTabActive('users');
$ost->setPageTitle(sprintf(__('Ticketbericht #%s'), $ticket->getNumber()));

if(!$errors) {
	// Retrieve User Information
	//$Username = $user->getName();
	//$UserID = $user->getID();

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

if(!$errors) {
	
	pdfExport($ticket);

} else {
	
	require_once(STAFFINC_DIR.'header.inc.php');
?>
	<h1>Ticketbericht  - #<?php echo $ticket->number ?></h1>
	<p>Fehler.</p>
<?php
}
require_once(STAFFINC_DIR.'footer.inc.php');
?>
