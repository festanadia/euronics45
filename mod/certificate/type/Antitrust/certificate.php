<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A4_non_embedded certificate type
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG;

defined('MOODLE_INTERNAL') || die();

$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
//$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$x = 0;
$y = 0;
$sealx = 230;
$sealy = 150;
$sigx = 47;
$sigy = 155;
$custx = 47;
$custy = 155;
$wmarkx = 40;
$wmarky = 31;
$wmarkw = 212;
$wmarkh = 148;
$brdrx = 0;
$brdry = 0;
$brdrw = 297;
$brdrh = 210;
$codey = 175;
$txtareawdth = 247;

$prise = false;
$course_item = grade_item::fetch_course_item($course->id);
$grade = new grade_grade(array('itemid' => $course_item->id, 'userid' => $USER->id));
$prisetxt ='';
if(grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 2)== "86,00") {
    $prisetxt ='con Lode';
}



// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, null, null, $brdrh);
certificate_draw_frame($pdf, $certificate);

// Add personal data
$pdf->SetTextColor(0, 0, 120);
$pdf->SetTextColor(0, 0, 0);
$result = getEuronicsPersonalAttribute($USER,$certificate->scormname);
$stringnato = ' nato il ';
if($USER->gender=='F') $stringnato=' nata il ';

    $personaldata1 = strtoupper($USER->nome." ".$USER->cognome);
//    $personaldata = $stringnato.$USER->birthday.'/'.$USER->birthmonth.'/'.$USER->birthyear.' a '.$USER->residenza.' provincia di '.$USER->provincia;
  certificate_print_text($pdf, 20, 80, 'C', 'Helvetica', 'B', 18, $personaldata1,$txtareawdth);

    $codicefiscale = $USER->codicefiscale;
    certificate_print_text($pdf, 135, 91, 'L', 'Helvetica', 'B', 16, $codicefiscale,$txtareawdth);
    $periododicompletamento = $USER->courseend;
    certificate_print_text($pdf, 230, 154, 'L', 'Helvetica', '', 12, $periododicompletamento,$txtareawdth);
    //$datacompletamentocorso = $USER->courseend;
//	$datacompletamentocorso = date('d/m/Y');
//    certificate_print_text($pdf, $x+10, $y+125, 'L', 'Helvetica', '', 12, $datacompletamentocorso,$txtareawdth);
 

