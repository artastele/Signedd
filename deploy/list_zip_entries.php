<?php
$zip = new PharData('C:/Users/Administrator/Downloads/SignED_IEP_Part_III_Import_Test.xlsx');
echo isset($zip['xl/workbook.xml']) ? "HAS workbook.xml\n" : "NO workbook.xml\n";
foreach (new RecursiveIteratorIterator($zip) as $f) {
    echo $f->getPathname() . "\n";
}
