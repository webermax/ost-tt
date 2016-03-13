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

	function Ticket2Report($source, $ticket, $psize='A4', $notes=false) {
        global $thisstaff;

        $this->ticket = $ticket;
        $this->includenotes = $notes;
        
        parent::__construct('', $psize);
        
        $this->SetImportUse();
        
        //$this->percentSubset = 0;

        $pagecount = $this->SetSourceFile(INCLUDE_DIR.'fpdf/' . $source);
        $tplId = $this->ImportPage($pagecount);
        
        $this->UseTemplate($tplId);
        
        $this->SetLeftMargin(17);

        //$this->SetMargins(10,10,10);
		//$this->AliasNbPages();
		//$this->AddPage();
		//$this->cMargin = 3;
		
        //$this->SetMargins(0,0,0);
        //$this->cMargin = 0;

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
        $this->SetY(87.5);
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
        	
        	$this->WriteCell(0, 5.5, Format::truncate(strip_tags($entries[0]['body']->display('pdf')), 50), 0, 1, 'L');
        	
        	$this->SetY(125);
        	
            foreach($comments as $idx => $entry) {
            	
            	if($entry['poster'] == 'SYSTEM' || !$entry['time_spent']) {
            		continue;
            	}

                //$color = $colors[$entry['thread_type']];

                //$this->WriteCell($w/2, 7, Format::db_datetime($entry['created']), 'LTB', 0, 'L', true);
                
            	//$entry['title']
                
                $this->WriteCell(120, 7, Format::truncate(strip_tags($entry['body']->display('pdf')), 50), 0, 0, 'L');
                $this->WriteCell(120, 7, ($entry['name'] ?: $entry['poster']) . ' / ' . $this->formatTime($entry['time_spent']), 0, 1, 'L');

            }
        }

    }
    
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
}
?>
