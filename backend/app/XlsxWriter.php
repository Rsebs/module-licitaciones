<?php

namespace App;

class XlsxWriter
{
  private $sheetName;
  private $headers;
  private $widths;
  private $sheetPath;
  private $handle;
  private $rowNumber = 0;

  public function __construct($sheetName, array $headers, array $widths = [])
  {
    $this->sheetName = mb_substr(preg_replace('/[\[\]:*?\/\\\\]/u', ' ', $sheetName), 0, 31);
    $this->headers = $headers;
    $this->widths = $widths;

    $this->sheetPath = tempnam(sys_get_temp_dir(), 'sheet');
    $this->handle = fopen($this->sheetPath, 'wb');
    fwrite($this->handle, $this->sheetStart());

    $this->writeRow($headers, 1);
  }

  public function addRow(array $cells)
  {
    $this->writeRow($cells, 2);
  }

  public function save()
  {
    $lastColumn = self::columnLetter(count($this->headers) - 1);
    fwrite($this->handle, '</sheetData><autoFilter ref="A1:' . $lastColumn . max(1, $this->rowNumber) . '"/></worksheet>');
    fclose($this->handle);

    $xlsxPath = tempnam(sys_get_temp_dir(), 'xlsx');
    $zip = new \ZipArchive();
    if ($zip->open($xlsxPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
      throw new \RuntimeException('No se pudo crear el archivo Excel.');
    }

    $main = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    $rel = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

    $zip->addFromString('[Content_Types].xml', $xml
      . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
      . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
      . '<Default Extension="xml" ContentType="application/xml"/>'
      . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
      . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
      . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
      . '</Types>');

    $zip->addFromString('_rels/.rels', $xml
      . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
      . '<Relationship Id="rId1" Type="' . $rel . '/officeDocument" Target="xl/workbook.xml"/>'
      . '</Relationships>');

    $zip->addFromString('xl/workbook.xml', $xml
      . '<workbook xmlns="' . $main . '" xmlns:r="' . $rel . '">'
      . '<sheets><sheet name="' . self::escape($this->sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
      . '</workbook>');

    $zip->addFromString('xl/_rels/workbook.xml.rels', $xml
      . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
      . '<Relationship Id="rId1" Type="' . $rel . '/worksheet" Target="worksheets/sheet1.xml"/>'
      . '<Relationship Id="rId2" Type="' . $rel . '/styles" Target="styles.xml"/>'
      . '</Relationships>');

    $zip->addFromString('xl/styles.xml', $xml
      . '<styleSheet xmlns="' . $main . '">'
      . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
      . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
      . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
      . '<fill><patternFill patternType="solid"><fgColor rgb="FF272F6A"/><bgColor indexed="64"/></patternFill></fill></fills>'
      . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
      . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
      . '<cellXfs count="3">'
      . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
      . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
      . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
      . '</cellXfs>'
      . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
      . '</styleSheet>');

    $zip->addFile($this->sheetPath, 'xl/worksheets/sheet1.xml');
    $zip->close();
    unlink($this->sheetPath);

    return $xlsxPath;
  }

  private function sheetStart()
  {
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
      . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
      . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';

    if ($this->widths) {
      $xml .= '<cols>';
      foreach ($this->widths as $i => $width) {
        $xml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $width . '" customWidth="1"/>';
      }
      $xml .= '</cols>';
    }

    return $xml . '<sheetData>';
  }

  private function writeRow(array $cells, $style)
  {
    $this->rowNumber++;
    $xml = '<row r="' . $this->rowNumber . '">';

    $i = 0;
    foreach ($cells as $value) {
      $text = mb_substr(self::cleanText($value === null ? '' : (string) $value), 0, 32767);
      $xml .= '<c r="' . self::columnLetter($i) . $this->rowNumber . '" t="inlineStr" s="' . $style . '">'
        . '<is><t xml:space="preserve">' . self::escape($text) . '</t></is></c>';
      $i++;
    }

    fwrite($this->handle, $xml . '</row>');
  }

  private static function columnLetter($index)
  {
    $letters = '';
    for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
      $letters = chr(65 + ($n - 1) % 26) . $letters;
    }
    return $letters;
  }

  private static function cleanText($text)
  {
    $clean = preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $text);
    return $clean === null ? '' : $clean;
  }

  private static function escape($text)
  {
    return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
  }
}
