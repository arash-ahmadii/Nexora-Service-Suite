<?php

if (!defined('ABSPATH')) {
    exit;
}
define('PDF_PAGE_ORIENTATION', 'P');
define('PDF_UNIT', 'mm');
define('PDF_PAGE_FORMAT', 'A4');
define('PDF_FONT_NAME_MAIN', 'helvetica');
define('PDF_FONT_SIZE_MAIN', 10);
define('PDF_FONT_NAME_DATA', 'helvetica');
define('PDF_FONT_SIZE_DATA', 8);
define('PDF_FONT_MONOSPACED', 'courier');
define('PDF_IMAGE_SCALE_RATIO', 1.25);

class TCPDF {
    private $pageWidth = 210;
    private $pageHeight = 297;
    private $currentX = 0;
    private $currentY = 0;
    private $fontSize = 10;
    private $fontStyle = '';
    private $textColor = array(0, 0, 0);
    private $output = '';
    private $documentInfo = array();
    
    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false) {
    }
    
    public function SetCreator($creator) {
        $this->documentInfo['creator'] = $creator;
    }
    
    public function SetAuthor($author) {
        $this->documentInfo['author'] = $author;
    }
    
    public function SetTitle($title) {
        $this->documentInfo['title'] = $title;
    }
    
    public function SetSubject($subject) {
        $this->documentInfo['subject'] = $subject;
    }
    
    public function SetHeaderData($ln = '', $lw = 0, $ht = '', $hs = '', $tc = array(0, 0, 0), $lc = array(0, 0, 0)) {
    }
    
    public function setHeaderFont($font) {
    }
    
    public function setFooterFont($font) {
    }
    
    public function SetDefaultMonospacedFont($font) {
    }
    
    public function SetMargins($left, $top, $right = -1) {
    }
    
    public function SetHeaderMargin($margin) {
    }
    
    public function SetFooterMargin($margin) {
    }
    
    public function SetAutoPageBreak($auto, $margin = 0) {
    }
    
    public function setImageScale($scale) {
    }
    
    public function SetFont($family, $style = '', $size = 0) {
        $this->fontSize = $size;
        $this->fontStyle = $style;
    }
    
    public function AddPage($orientation = '', $format = '', $keepmargins = false, $tocpage = false) {
    }
    
    public function Image($file, $x = '', $y = '', $w = 0, $h = 0, $type = '', $link = '', $align = '', $resize = false, $dpi = 300, $palign = '', $ismask = false, $imgmask = false, $border = 0, $fitbox = false, $hidden = false, $fitonpage = false) {
        if (file_exists($file)) {
            $this->output .= "\n";
        }
    }
    
    public function SetXY($x, $y) {
        $this->currentX = $x;
        $this->currentY = $y;
    }
    
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M') {
        $this->output .= "\n";
    }
    
    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false, $ln = 1, $x = '', $y = '', $reseth = true, $stretch = 0, $ishtml = false, $autopadding = true, $maxh = 0, $valign = 'T', $fitcell = false) {
        $this->output .= "\n";
    }
    
    public function SetTextColor($r, $g = null, $b = null) {
        if (is_array($r)) {
            $this->textColor = $r;
        } else {
            $this->textColor = array($r, $g, $b);
        }
    }
    
    public function Output($name = '', $dest = '', $utf8 = false) {
        $html = $this->create_html_version();
        
        if ($dest == 'D') {
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="' . $name . '.html"');
            echo $html;
        } else {
            echo $html;
        }
    }
    
    private function create_html_version() {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . ($this->documentInfo['title'] ?? 'Invoice') . '</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .invoice-page {
            width: 210mm;
            height: 297mm;
            position: relative;
            background-image: url("../assets/images/invoice.jpg");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            margin: 0 auto;
        }
        .invoice-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #000;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            max-width: 400px;
        }
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .invoice-date {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .customer-info {
            margin: 15px 0;
        }
        .service-info {
            margin: 15px 0;
        }
        .amount {
            font-size: 20px;
            font-weight: bold;
            margin-top: 15px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">🖨️ Drucken</button>
    <div class="invoice-page">
        <div class="invoice-content">
            <div class="invoice-title">RECHNUNG</div>
            <div class="invoice-number">Nr: INV-000001</div>
            <div class="invoice-date">Datum: ' . date('d.m.Y') . '</div>
            <div class="invoice-date">Fälligkeitsdatum: ' . date('d.m.Y', strtotime('+30 days')) . '</div>
            
            <div class="customer-info">
                <strong>Kunde:</strong><br>
                Test Customer<br>
                test@example.com
            </div>
            
            <div class="service-info">
                <strong>Service Details:</strong><br>
                Gerät: Test Device<br>
                Problem: Test Problem
            </div>
            
            <div class="amount">Betrag: €150,00</div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
} 