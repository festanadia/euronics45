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
$pdf->SetMargins(0, 0, 0);
$pdf->SetTitle($certificate->name);
//$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

$x = 0;
$y = 60;
$sealx = 150;
$sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
$brdrx = 0;
$brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
$codey = 250;
$txtareawdth = 247;
/*
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 30;
    $sigy = 230;
    $custx = 30;
    $custy = 230;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = 250;*/

$prise = false;
$course_item = grade_item::fetch_course_item($course->id);
$grade = new grade_grade(array('itemid' => $course_item->id, 'userid' => $USER->id));
$prisetxt ='';
$stringavotazione = "(punteggio ".grade_format_gradevalue($grade->finalgrade, $course_item, true, GRADE_DISPLAY_TYPE_REAL, $decimals = 0)."/100) il Corso di Formazione in materia";


// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, null, null, $brdrh);
certificate_draw_frame($pdf, $certificate);
$pdf->SetTextColor(1, 75, 152);
certificate_print_text($pdf, $x+5, $y+92, 'C', 'Helvetica', 'B', 14, $stringavotazione,$txtareawdth);
//Ricava il voto


// Add personal data
$pdf->SetTextColor(38, 69, 136);
certificate_print_text($pdf, $x+5, $y+45, 'C', 'Helvetica', 'B', 22, strtoupper(fullname($USER)),$txtareawdth);
//certificate_print_text($pdf, $x+5, $y+155, 'C', 'Helvetica', 'B', 18, date('d/m/Y', time()),$txtareawdth);

$pdf->SetTextColor(255, 255, 255);

$result = getEuronicsPersonalAttribute($USER,$certificate->scormname,'euronics');

if(isset($USER->azpiva)) {
    $infofooter = $USER->azdenominazione.' - P.IVA:'.$USER->azpiva.' Cap '.$USER->azcap.' - '.$USER->azcitta.' ('.$USER->azprovincia.')';
    certificate_print_text($pdf, $x, $y+220, 'C', 'Helvetica', '', 10, $infofooter,$txtareawdth);
    certificate_print_text($pdf, $x+12, $y-20, 'L', 'Helvetica', 'B', 8, $USER->azdenominazione,$txtareawdth);
}
