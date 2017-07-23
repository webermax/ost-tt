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

$ticket = null;

//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id']))) {
         $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('ticket'));
    }
}

function pdfExport($ticket, $psize='A4', $notes=false) {
	$pdf = pdfGenerate($ticket, $psize, $notes);
	$pdf->Output($name, 'I');
	exit;
}

function pdfGenerate($ticket, $psize='A4', $notes=false) {
	require_once(INCLUDE_DIR.'class.pdf.report.php');

	$source = 'Dienstleistungsbericht.pdf';
	//$name='Ticket-'.$ticket->getNumber().'-Report.pdf';
	
	$pdf = new Ticket2Report($source, $ticket, $psize, $notes);

	return $pdf;
}

//Navigation & Page Info
$nav->setTabActive('users');
$ost->setPageTitle(sprintf(__('Ticketbericht #%s'), $ticket->getNumber()));

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
