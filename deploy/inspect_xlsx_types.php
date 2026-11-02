<?php
$zip = new PharData('C:/Users/Administrator/Downloads/SignED_IEP_Part_III_Import_Test.xlsx');
echo "[Content_Types].xml content:\n";
echo $zip['[Content_Types].xml']->getContent();
