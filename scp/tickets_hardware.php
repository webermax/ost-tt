<?php
require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');

$ticket = $user = null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id'])))
         $errors['err']=sprintf(__('%s: Unknown or invalid ID.'), __('ticket'));
    elseif(!$ticket->checkStaffAccess($thisstaff)) {
        $errors['err']=__('Access denied. Contact admin if you believe this is in error');
        $ticket=null; //Clear ticket obj.
    }
}

//Navigation & Page Info
$nav->setTabActive('tickets');
$ost->setPageTitle(sprintf(__('Ticket #%s Hardware Management'),$ticket->getNumber()));


if(!$errors) {
	// Retrieve Ticket Information
	$TicketID = $_GET['id'];
	$Subject = $ticket->getSubject();
	$TicketNo = $ticket->getNumber();
	
	if($_POST['ticket_id']) {
		// Collect information from submit
		$ticket_id = $_POST['ticket_id'];
		$description = $_POST['description'];
		$qty = $_POST['qty'];
		$unit_cost = $_POST['unit_cost'];
		$total_cost = $_POST['total_cost'];
		
		// Create and Run SQL Query
		$sql  = "INSERT INTO ost_ticket_hardware (ticket_id,description,qty,unit_cost,total_cost) values(". $ticket_id .",'". $description ."',". $qty .",". $unit_cost .",". $total_cost .")";
		$res = db_query($sql);
	}
}

require_once(STAFFINC_DIR.'header.inc.php');

if(!$errors) {
?>

	<h1>Hardware Management</h1>
	
	<h2>Ticket Information</h2>
	<p><b>Ticket:</b> #<?php echo $TicketNo; ?> <br />
		<b>Subject:</b> <?php echo $Subject; ?> <br />
	</p>
	<p>&nbsp;</p>
	
	<h2>Hardware Details</h2>
	<table border="2">
		<tr>
			<th>Description</th>
			<th>Qty</th>
			<th>Unit Cost (Ex VAT / Taxes)</th>
			<th>Total Cost (Ex VAT / Taxes)</th>
		</tr>
		<?php
			$sql = 'SELECT * FROM `ost_ticket_hardware` WHERE `ticket_id` = ' . $TicketID;
			$res = db_query($sql);
			while($row = db_fetch_array($res, MYSQL_ASSOC)) {
				echo '<tr>';
					echo "<td>" . $row['description'] . "</td>";
					echo "<td>" . $row['qty'] . "</td>";
					echo "<td>" . $row['unit_cost'] . "</td>";
					echo "<td>" . $row['total_cost'] . "</td>";
				echo '</tr>';
			}
		?>
	</table>
	
	<p>&nbsp;</p>
	<h2>Add Hardware</h2>
	<form action="tickets_hardware.php?id=<?php echo $TicketID; ?>" name="reply" method="post">
		<?php csrf_token(); ?>
		<input type="hidden" name="ticket_id" value="<?php echo $TicketID; ?>">
		<table>
			<tr>
				<td>Hardware Description:</td>
				<td>&nbsp;</td>
				<td>
					<textarea name="description" id="description" cols="50" rows="5"></textarea>
				</td>
			</tr>
			<tr>
				<td>Quantity:</td>
				<td>&nbsp;</td>
				<td>
					<input type="number" name="qty" id="qty" maxlength="3" size="4" />
				</td>
			</tr>
			<tr>
				<td>Unit Cost (Ex VAT / Taxes):</td>
				<td>&nbsp;</td>
				<td>
					<input type="text" name="unit_cost" id="unit_cost" maxlength="18" size="19" />
				</td>
			</tr>
			<tr>
				<td>Total Cost (Ex VAT / Taxes):</td>
				<td>&nbsp;</td>
				<td>
					<input type="text" name="total_cost" id="total_cost" maxlength="18" size="19" />
				</td>
			</tr>
		</table>
		<input class="btn_sm" type="submit" value="<?php echo __('Add Hardware');?>">
		<input class="btn_sm" type="reset" value="<?php echo __('Reset');?>">
	</form>
	<p>&nbsp;</p>
	
<?php
} else {
?>
	<h1>Hardware Management</h1>
	<p>You do not have access to this module.</p>
<?php
}
require_once(STAFFINC_DIR.'footer.inc.php');
?>