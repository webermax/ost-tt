<?php
/*********************************************************************
    class.pdf.report.php

    Ticket PDF Export

    Peter Rotich <peter@osticket.com>
    Maximilian Weber <post@wbrmx.de>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

define('THIS_DIR', str_replace('\\', '/', Misc::realpath(dirname(__FILE__))) . '/'); //Include path..

require_once(INCLUDE_DIR.'mpdf/mpdf.php');

class Ticket2Report extends mPDF
{

	var $includenotes = false;

	var $pageOffset = 0;

    var $ticket = null;
    
    //var $pagecount = null;
    
    protected $_tplIdx;

	function Ticket2Report($source, $ticket, $psize='A4', $notes=false) {
        global $thisstaff;

        $this->ticket = $ticket;
        $this->includenotes = $notes;
        
        parent::__construct('', $psize);
        
        $this->SetImportUse();
        
        //$this->percentSubset = 0;
        
        $this->SetSourceFile(INCLUDE_DIR.'fpdf/' . $source);
        

        //$this->SetMargins(10,10,10);
		//$this->AliasNbPages();
		//$this->AddPage();
		//$this->cMargin = 3;
		
        //$this->SetMargins(0,0,0);
        //$this->cMargin = 0;
        
        $this->AddPage();
        $this->cMargin = 0;

        $this->_print();
	}

    function getTicket() {
        return $this->ticket;
    }

    function getLogoFile() {
        global $ost;

        if (!function_exists('imagecreatefromstring')
                || (!($logo = $ost->getConfig()->getClientLogo()))) {
            return INCLUDE_DIR.'fpdf/print-logo.png';
        }

        $tmp = tempnam(sys_get_temp_dir(), 'pdf') . '.jpg';
        $img = imagecreatefromstring($logo->getData());
        // Handle transparent images with white background
        $img2 = imagecreatetruecolor(imagesx($img), imagesy($img));
        $white = imagecolorallocate($img2, 255, 255, 255);
        imagefill($img2, 0, 0, $white);
        imagecopy($img2, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagejpeg($img2, $tmp);
        return $tmp;
    }

	//report header...most stuff are hard coded for now...
	function Header() {
		
		if(!($ticket=$this->getTicket()))
			return;

		/*
        global $cfg;

		//Common header
        $logo = $this->getLogoFile();
		$this->Image($logo, $this->lMargin, $this->tMargin, 0, 20);
        if (strpos($logo, INCLUDE_DIR) === false)
            unlink($logo);
		$this->SetFont('Arial', 'B', 16);
		$this->SetY($this->tMargin + 20);
        $this->SetX($this->lMargin);
        $this->WriteCell(0, 0, '', "B", 2, 'L');
		$this->Ln(1);
        $this->SetFont('Arial', 'B',10);
        $this->WriteCell(0, 5, $cfg->getTitle(), 0, 0, 'L');
        $this->SetFont('Arial', 'I',10);
        $this->WriteCell(0, 5, Format::date($cfg->getDateTimeFormat(), Misc::gmtime(),
            $_SESSION['TZ_OFFSET'], $_SESSION['TZ_DST'])
            .' GMT '.$_SESSION['TZ_OFFSET'], 0, 1, 'R');
		$this->Ln(5);
		*/
		
		if (null === $this->_tplIdx) {
			$this->_tplIdx = $this->importPage(1);
		}
		
		$this->useTemplate($this->_tplIdx);
		
		$this->SetLeftMargin(17);
		$this->SetAutoPageBreak(true, 50);
		
		$this->SetFont('Arial', '', 10);
		
		/*
		// number
		$this->SetFont('Arial', 'B', 10);
		$this->SetY(52);
		$this->WriteCell(0, 5.5 ,sprintf(__('Ticket #%s'), $ticket->getNumber()), 0, 1, 'L');
		*/
		
		// owner
		$this->setY(17);
		$owner = $ticket->getOwner();
		
		$this->Cell(80, 7.15, $owner->getFullName(), 0, 1, 'L');
		//$this->Cell(80, 7.15, $owner->getVar('Street'), 0, 1, 'L');
		
		// date
		$info = $this->getTicketInfo();
		
		$this->SetFont('Arial', '', 8.5);
		$this->setY(40.75);
		
		$this->SetX(160);
		$this->Cell(0, 5.5, $info['day'], 0, 0, 'L');
		$this->SetX(170);
		$this->Cell(0, 5.5, $info['month'], 0, 0, 'L');
		$this->SetX(181);
		$this->Cell(0, 5.5, $info['year'], 0, 0, 'L');
		
		// continue
		//$this->setY(100.5);
		$this->SetY(52);
	}

	//Page footer baby
	function Footer() {
		/*
        global $thisstaff;

		$this->SetY(-15);
        $this->WriteCell(0, 2, '', "T", 2, 'L');
		$this->SetFont('Arial', 'I', 9);
        $this->WriteCell(0, 7, sprintf(__('Ticket #%1$s printed by %2$s on %3$s'),
            $this->getTicket()->getNumber(), $thisstaff->getUserName(), date('r')), 0, 0, 'L');
		//$this->WriteCell(0,10,'Page '.($this->PageNo()-$this->pageOffset).' of {nb} '.$this->pageOffset.' '.$this->PageNo(),0,0,'R');
		$this->WriteCell(0, 7, sprintf(__('Page %d'), ($this->PageNo() - $this->pageOffset)), 0, 0, 'R');
		*/
	}

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    function WriteText($w, $text, $border) {

        $this->SetFont('Arial','',11);
        $this->MultiCell($w, 7, $text, $border, 'L');

    }
    
    // TODO: german float values for currency units

    function _print() {

        if(!($ticket=$this->getTicket()))
            return;
        
        /*
        $w =(($this->w/2)-$this->lMargin);
        $l = 35;
        $c = $w-$l;
        */

        /*
        $this->SetFont('Arial', 'B', 11);
        $this->cMargin = 0;
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(10, 86, 142);
        */
        
        //$this->WriteCell($w, 7,sprintf(__('Ticket #%s'),$ticket->getNumber()), 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetY(52);
        $this->WriteCell(0, 5.5 ,sprintf(__('Ticket #%s'), $ticket->getNumber()), 0, 1, 'L');
        
        /*
        $source = ucfirst($ticket->getSource());
        if($ticket->getIP())
            $source.='  ('.$ticket->getIP().')';
        $this->WriteCell($c, 7, $source, 1, 0, 'L', true);
        $this->Ln(12);
        */

        /*
        $this->SetFont('Arial', 'B', 11);
        if($ticket->isOpen()) {
            $this->WriteCell($l, 7, __('Assigned To'), 1, 0, 'L', true);
            $this->SetFont('');
            $this->WriteCell($c, 7, $ticket->isAssigned()?$ticket->getAssigned():' -- ', 1, 0, 'L', true);
        } else {

            $closedby = __('unknown');
            if(($staff = $ticket->getStaff()))
                $closedby = (string) $staff->getName();

            $this->WriteCell($l, 7, __('Closed By'), 1, 0, 'L', true);
            $this->SetFont('');
            $this->WriteCell($c, 7, $closedby, 1, 0, 'L', true);
        }
        */

        /*
        $this->SetFont('Arial', 'B', 11);
        $this->WriteCell($l, 7, __('Help Topic'), 1, 0, 'L', true);
        $this->SetFont('');
        $this->WriteCell($c, 7, $ticket->getHelpTopic(), 1, 1, 'L', true);
        $this->SetFont('Arial', 'B', 11);
        $this->WriteCell($l, 7, __('SLA Plan'), 1, 0, 'L', true);
        $this->SetFont('');
        $sla = $ticket->getSLA();
        $this->WriteCell($c, 7, $sla?$sla->getName():' -- ', 1, 0, 'L', true);
        $this->SetFont('Arial', 'B', 11);
        $this->WriteCell($l, 7, __('Last Response'), 1, 0, 'L', true);
        $this->SetFont('');
        $this->WriteCell($c, 7, Format::db_datetime($ticket->getLastRespDate()), 1, 1, 'L', true);
        $this->SetFont('Arial', 'B', 11);
        if($ticket->isOpen()) {
            $this->WriteCell($l, 7, __('Due Date'), 1, 0, 'L', true);
            $this->SetFont('');
            $this->WriteCell($c, 7, Format::db_datetime($ticket->getEstDueDate()), 1, 0, 'L', true);
        } else {
            $this->WriteCell($l, 7, __('Close Date'), 1, 0, 'L', true);
            $this->SetFont('');
            $this->WriteCell($c, 7, Format::db_datetime($ticket->getCloseDate()), 1, 0, 'L', true);
        }

        $this->SetFont('Arial', 'B', 11);
        $this->WriteCell($l, 7, __('Last Message'), 1, 0, 'L', true);
        $this->SetFont('');
        $this->WriteCell($c, 7, Format::db_datetime($ticket->getLastMsgDate()), 1, 1, 'L', true);

        $this->SetFillColor(255, 255, 255);
        foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
            $idx = 0;
            foreach ($form->getAnswers() as $a) {
                if (in_array($a->getField()->get('name'),
                            array('email','name','subject','phone','priority')))
                    continue;
                $this->SetFont('Arial', 'B', 11);
                if ($idx++ === 0) {
                    $this->Ln(5);
                    $this->SetFillColor(244, 250, 255);
                    $this->WriteCell(($l+$c)*2, 7, $a->getForm()->get('title'),
                        1, 0, 'L', true);
                    $this->SetFillColor(255, 255, 255);
                }
                if ($val = $a->toString()) {
                    $this->Ln(7);
                    $this->WriteCell($l*2, 7, $a->getField()->get('label'), 1, 0, 'L', true);
                    $this->SetFont('');
                    $this->WriteCell($c*2, 7, $val, 1, 0, 'L', true);
                }
            }
        }
        $this->SetFillColor(244, 250, 255);
        $this->Ln(10);

        $this->SetFont('Arial', 'B', 11);
        $this->cMargin = 0;
        $this->SetTextColor(10, 86, 142);
        $this->WriteCell($w, 7,trim($ticket->getSubject()), 0, 0, 'L');
        $this->Ln(7);
        $this->SetTextColor(0);
        $this->cMargin = 3;

        //Table header colors (RGB)
        $colors = array('M'=>array(195, 217, 255),
                        'R'=>array(255, 224, 179),
                        'N'=>array(250, 250, 210));
        //Get ticket thread
        $types = array('M', 'R');
        if($this->includenotes)
            $types[] = 'N';
        */
        
        $this->SetFont('Arial', '', 10);

        if(($entries = $ticket->getThreadEntries($types))) {
        	
        	$comments = array_splice($entries, 1);
        	
        	$this->MultiCell(186, 5.55, strip_tags($this->specialChars($this->lineBreaks($entries[0]['body']))), 0, 'L', 0);
        	
        	//$this->SetY(100.5);
        	$this->WriteCell(0, 5.55, '', 0, 1, 'L');
        	$this->SetFont('Arial', 'B', 10);
        	
            foreach($comments as $idx => $entry) {
            	
            	//if($entry['poster'] == 'SYSTEM' || !$entry['time_spent']) {
            	if($entry['poster'] == 'SYSTEM') {
            		continue;
            	}

                //$color = $colors[$entry['thread_type']];

                //$this->WriteCell($w/2, 7, Format::db_datetime($entry['created']), 'LTB', 0, 'L', true);
                
            	//$entry['title']
            	
                //$this->MultiCell(186, 7.15, '[' . ($entry['name'] ?: $entry['poster']) . ', ' . $this->formatTime($entry['time_spent']) . ']: ' . Format::html2text(strip_tags($entry['body']->display('pdf'))), 0, 'L', 0);

            	$this->SetFont('Arial', 'B');
            	
            	if($entry['time_spent']) {
            		$intro = '[' . ($entry['name'] ?: $entry['poster']) . ', ' . $this->formatDatetime($entry['created']) . ', ' . $this->formatTime($entry['time_spent']) . ']: ';
            	} else {
            		$intro = '[' . ($entry['name'] ?: $entry['poster']) . ', ' . $this->formatDatetime($entry['created']) . ']: ';
            	}
            	
            	//$this->MultiCell(186, 7.15, $intro, 0, 'L', 0);
            	$this->MultiCell(186, 5.55, $intro, 0, 'L', 0);
            	
            	$this->SetFont('Arial', '');
            	
            	$body = strip_tags($this->specialChars($this->lineBreaks($entry['body'])));
            	
            	//$this->MultiCell(186, 7.15, $body, 0, 'L', 0);
            	$this->MultiCell(186, 5.55, $body, 0, 'L', 0);
            	
            	//$body = $intro . strip_tags($this->specialChars($entry['body']));

				//$this->writeHtml($body);

            	//$this->MultiCell(186, 7.15, $body, 0, 'L', 0);
            }
        }
        
        // owner
        //$owner = $ticket->getOwner();
        
        // hardware
        $sql = 'SELECT * FROM `ost_ticket_hardware` WHERE `ticket_id` = ' . $ticket->getId();
	$res = db_query($sql);
	
	if($res->num_rows) {
		//$this->AddPage();
		$this->WriteCell(0, 5.55, '', 0, 1, 'L');
		
		$this->SetFont('Arial', 'B');
		$this->MultiCell(186, 5.55, 'Hardware', 0, 'L', 0);
		
		$this->SetFont('Arial', '');
	
		//$i = 0;
		while($row = db_fetch_array($res, MYSQL_ASSOC)) {
			// $row['unit_cost']
			//$this->WriteCell(93, 7.15, $row['qty'] . ' x ' . $row['description'] . ': ' . $row['total_cost'] . ' €', 0, ($i % 2) ? 1 : 0, 'L');
			//$i++;
			$hw = '• ' . $row['qty'] . ' x ' . $row['description'];
			
			//if($owner->getVar('blanco') == 'Nein') {
				$hw .= ': ' . $row['total_cost'] . ' €';
			//}
			
			$this->MultiCell(186, 5.55, $hw, 0, 'L', 0);
		}
	}
        
        // stats
        $stats = $this->getTicketStats();
        
        $this->SetFont('Arial', 'B', 10);
        
        $this->WriteCell(0, 5.55, '', 0, 1, 'L');
        
        if($stats['sum']) {
        	//$this->WriteCell(93, 7.15, 'Arbeitszeit Gesamt: ' . $this->formatTime($stats['sum']), 0, 0, 'L');
        	
        	$overall = 'Gesamt: ' . $this->formatTime($ticket->getRealTimeSpent());
        	
        	if($ticket->getVar('maintenance') == 'Ja') {
        		$overall .= ' (Wartung)';
        	}
        	
        	$this->WriteCell(93, 5.55, $overall, 0, 0, 'L');
        	//$this->MultiCell(186, 7.15, $overall, 0, 'L', 0);
        }
        
        if($stats['numOnsite']) {
        
        	$visits = 'Anfahrten: ' . $stats['numOnsite'];
        
        	if($ticket->getVar('maintenance') == 'Ja') {
        		$visits .= ' (nächste Monatsrechnung)';
        	}
        
        	$this->WriteCell(93, 5.55, $visits, 0, 0, 'L');
        	//$this->MultiCell(186, 7.15, $visits, 0, 'L', 0);
        }
    }
    
    function formatTime($time) {
    	$hours = floor($time / 60);
    	$minutes = $time % 60;
    
    	$formatted = '';
    
    	if ($hours > 0) {
    		$formatted .= $hours . ' Stunde';
    	}
    	
    	if ($hours > 1) {
    		$formatted .= 'n';
    	}
    	
    	if ($minutes > 0) {
    		if ($formatted) {
    			$formatted .= ', ';
    		}
    		$formatted .= $minutes . ' Minute';
    	}
    	
    	if ($minutes > 1) {
    		$formatted .= 'n';
    	}
    	
    	return $formatted;
    }
    
    function formatDatetime($time) {
    	return date("d.m.y", strtotime($time));
    }
    
    function getTicketStats() {
    	if(!($ticket=$this->getTicket()))
    		return;
    	
    	$sql = 'SELECT
		    	ti.ticket_id,
		    	ti.number,
		    	ti.time_spent AS sumOverall,
		    	t.numOnsite AS numOnsite,
		    	t.sum AS sum,
		    	t.activities AS activities
		    FROM
		    	ost_ticket ti,
		    	(SELECT
			    	ticket_id,
			    	SUM(time_spent) AS sum,
			    	SUM(case when time_type = 5 then 1 else 0 end) AS numOnsite,
			    	COUNT(id) AS activities
		    	FROM
		    		ost_ticket_thread
		    	WHERE
			    	time_spent
		    	GROUP BY
		    		ticket_id) t
		    WHERE
		    	ti.ticket_id = ' . $ticket->getId() . '
		    	AND ti.ticket_id = t.ticket_id';

    	return db_fetch_array(db_query($sql), MYSQL_ASSOC);
    }
    
    function getTicketInfo() {
    	if(!($ticket=$this->getTicket()))
    		return;
    	 
    	$sql = 'SELECT
	    	DATE_FORMAT(ti.created,"%d") AS day,
	    	DATE_FORMAT(ti.created,"%m") AS month,
	    	DATE_FORMAT(ti.created,"%y") AS year
    	FROM
    		ost_ticket ti
    	WHERE
    		ti.ticket_id = ' . $ticket->getId();
    
    	return db_fetch_array(db_query($sql), MYSQL_ASSOC);
    }
    
    function specialChars($string) {
    	return str_replace(
    			array('&auml;', '&ouml;', '&uuml;', '&Auml;', '&Ouml;', '&Uuml;', '&szlig;', '&nbsp;', '<br>', '<br />', '<br></br>', '<br/>'),
    			array('ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß', ' ', ' ', ' ', ' ', ' '),
    			$string
    	);
    }
    
    function lineBreaks($string) {
    	return str_replace(
    			array('<br></br>', '<br />', '<br>'),
    			array(' ', ' ', ' '),
    			$string
    	);
    }
}
