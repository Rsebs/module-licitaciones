<?php

namespace App;

class UnspscImporter
{
  public function read($xlsxPath)
  {
    $zip = new \ZipArchive();
    if ($zip->open($xlsxPath) !== true) {
      throw new \RuntimeException('No se pudo abrir el archivo. ¿Es un .xlsx válido?');
    }

    $strings = $this->readSharedStrings($xlsxPath);

    $sheets = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
      $name = $zip->getNameIndex($i);
      if (preg_match('#^xl/worksheets/sheet(\d+)\.xml$#', $name, $matches)) {
        $sheets[(int) $matches[1]] = $name;
      }
    }
    $zip->close();
    ksort($sheets);

    foreach ($sheets as $sheet) {
      foreach ($this->readSheet($xlsxPath, $sheet, $strings) as $row) {
        yield $row;
      }
    }
  }

  private function readSheet($xlsxPath, $sheet, array $strings)
  {
    $reader = new \XMLReader();
    $reader->open('zip://' . $xlsxPath . '#' . $sheet, null, LIBXML_NONET | LIBXML_COMPACT);
    $document = new \DOMDocument();

    while ($reader->read()) {
      if ($reader->nodeType !== \XMLReader::ELEMENT || $reader->name !== 'row') {
        continue;
      }

      $expanded = $reader->expand($document);
      if (!($expanded instanceof \DOMElement)) {
        continue;
      }
      $cells = $this->readCells($expanded, $strings);

      $values = [];
      for ($column = 0; $column < 8; $column++) {
        $values[$column] = isset($cells[$column]) ? $cells[$column] : '';
      }

      $codes = [$values[0], $values[2], $values[4], $values[6]];
      $names = [$values[1], $values[3], $values[5], $values[7]];
      if (!$this->allDigits($codes) || in_array('', $names, true)) {
        continue;
      }

      yield [
        'codigo_segmento' => (int) $values[0],
        'segmento' => $values[1],
        'codigo_familia' => (int) $values[2],
        'familia' => $values[3],
        'codigo_clase' => (int) $values[4],
        'clase' => $values[5],
        'codigo_producto' => (int) $values[6],
        'producto' => $values[7],
      ];
    }

    $reader->close();
  }

  private function readCells(\DOMElement $row, array $strings)
  {
    $cells = [];

    foreach ($row->childNodes as $cell) {
      if ($cell->nodeType !== XML_ELEMENT_NODE || $cell->localName !== 'c') {
        continue;
      }

      /** @var \DOMElement $cell */
      $column = $this->columnIndex($cell->getAttribute('r'));
      $type = $cell->getAttribute('t');
      $value = '';

      foreach ($cell->childNodes as $child) {
        if ($child->localName === 'v' || $child->localName === 'is') {
          $value = $child->textContent;
        }
      }

      if ($type === 's') {
        $value = isset($strings[(int) $value]) ? $strings[(int) $value] : '';
      } elseif ($type !== 'str' && $type !== 'inlineStr' && is_numeric($value)) {
        $value = (string) (int) round((float) $value);
      }

      $cells[$column] = trim($value);
    }

    return $cells;
  }

  private function readSharedStrings($xlsxPath)
  {
    $strings = [];
    $reader = new \XMLReader();
    $reader->open('zip://' . $xlsxPath . '#xl/sharedStrings.xml', null, LIBXML_NONET | LIBXML_COMPACT);
    $document = new \DOMDocument();

    while ($reader->read()) {
      if ($reader->nodeType === \XMLReader::ELEMENT && $reader->name === 'si') {
        $strings[] = $reader->expand($document)->textContent;
      }
    }

    $reader->close();
    return $strings;
  }

  private function allDigits(array $values)
  {
    foreach ($values as $value) {
      if ($value === '' || !ctype_digit($value)) {
        return false;
      }
    }
    return true;
  }

  private function columnIndex($reference)
  {
    preg_match('/^[A-Z]+/', strtoupper($reference), $matches);
    $index = 0;
    foreach (str_split(isset($matches[0]) ? $matches[0] : 'A') as $letter) {
      $index = $index * 26 + (ord($letter) - 64);
    }
    return $index - 1;
  }
}
