<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/Services/IEPPartThreeImportService.php';
// Synthetic OOXML fixtures only. No application/database/bootstrap is loaded.
$checks = 0; $files = [];
function expect(bool $condition, string $message): void {
    global $checks; $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function rejects(callable $action, string $message): void {
    try { $action(); } catch (RuntimeException $e) { expect(true, $message); return; }
    throw new RuntimeException('Expected rejection: ' . $message);
}
function archiveFixture(array $entries): string {
    global $files;
    $path = tempnam(sys_get_temp_dir(), 'signed-iep-import-');
    unlink($path); $path .= '.zip'; $files[] = $path;
    $archive = new PharData($path);
    foreach ($entries as $name => $text) $archive[$name] = $text;
    unset($archive);
    return $path;
}
function types(string $kind): string {
    return '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/'.($kind === 'docx' ? 'word/document.xml' : 'xl/workbook.xml').'" ContentType="application/vnd.openxmlformats-officedocument.'.($kind === 'docx' ? 'wordprocessingml.document' : 'spreadsheetml.sheet').'.main+xml"/></Types>';
}
function word(array $rows): string {
    $xml = '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:tbl>';
    foreach ($rows as $row) {
        $xml .= '<w:tr>';
        foreach ($row as $cell) $xml .= '<w:tc><w:p><w:r><w:t>'.htmlspecialchars($cell, ENT_XML1).'</w:t></w:r></w:p></w:tc>';
        $xml .= '</w:tr>';
    }
    return $xml . '</w:tbl></w:body></w:document>';
}
$service = new IEPPartThreeImportService();
try {
    $header = ['Duration of LP','Step Objectives','Instructional Evaluation','Developmental Domain','Materials'];
    $rows = [$header, ['2 weeks','Identify familiar signs','Teacher observation','Language Development','Flashcards'], ['', '', '', '', '']];
    $docx = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word($rows)]);
    $result = $service->read($docx, 'teacher.docx');
    expect(count($result['steps']) === 1, 'Blank rows ignored');
    expect($result['steps'][0]['step_objective'] === 'Identify familiar signs', 'Word objective matched by heading, not position');
    expect($result['steps'][0]['duration_lp'] === '2 weeks', 'Word duration matched by heading');
    expect($result['steps'][0]['instructional_evaluation'] === 'Teacher observation', 'Planned evaluation method text supported');
    expect(!isset($result['steps'][0]['materials']), 'Materials do not create resources or links');
    // PHP upload temporary names need not have a ZIP extension.
    $temporaryUpload = tempnam(sys_get_temp_dir(), 'signed-iep-upload-'); $files[] = $temporaryUpload;
    copy($docx, $temporaryUpload);
    expect(count($service->read($temporaryUpload, 'upload.docx')['steps']) === 1, 'Extensionless upload temp path accepted');
    $duplicate = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word([$header,$rows[1],$rows[1]])]);
    $result = $service->read($duplicate, 'duplicate.docx');
    expect(count($result['steps']) === 2 && str_contains(implode(' ', $result['warnings']), 'Duplicate'), 'Duplicate rows preserved for correction and flagged');
    $blank = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word([$header,['','','','','']])]);
    rejects(fn() => $service->read($blank, 'blank.docx'), 'Blank document rejected');
    $noHeadings = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word([['Something','Else']])]);
    rejects(fn() => $service->read($noHeadings, 'no-headings.docx'), 'Missing objective heading rejected');
    $broken = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>'<w:document>']);
    rejects(fn() => $service->read($broken, 'malformed.docx'), 'Malformed XML rejected');
    $entities = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>'<!DOCTYPE foo [<!ENTITY x SYSTEM "file:///never-read">]><foo>&x;</foo>']);
    rejects(fn() => $service->read($entities, 'entities.docx'), 'External XML entities rejected without resolving');
    rejects(fn() => $service->read($docx, 'wrong.xls'), 'Unsupported legacy extension rejected');
    rejects(fn() => $service->read($docx, 'wrong.xlsx'), 'Word renamed to Excel rejected');
    $plain = tempnam(sys_get_temp_dir(), 'signed-iep-plain-'); $files[] = $plain; file_put_contents($plain, 'not a document');
    rejects(fn() => $service->read($plain, 'plain.docx'), 'Fake document rejected');
    file_put_contents($plain, '');
    rejects(fn() => $service->read($plain, 'empty.xlsx'), 'Zero-byte file rejected');
    file_put_contents($plain, str_repeat('x', IEPPartThreeImportService::MAX_BYTES + 1));
    rejects(fn() => $service->read($plain, 'large.docx'), 'Oversized file rejected');
    $macro = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word($rows), 'word/vbaProject.bin'=>'macro']);
    rejects(fn() => $service->read($macro, 'macro.docx'), 'Macro payload rejected');

    $sheet = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'
        .'<row r="1"><c r="B1" t="inlineStr"><is><t>Duration</t></is></c><c r="D1" t="s"><v>0</v></c><c r="F1" t="inlineStr"><is><t>Domain</t></is></c></row>'
        .'<row r="2"><c r="B2" t="inlineStr"><is><t>3 weeks</t></is></c><c r="D2" t="s"><v>1</v></c><c r="F2" t="inlineStr"><is><t>Cognitive</t></is></c></row><row r="3"/></sheetData></worksheet>';
    $xlsxEntries = ['[Content_Types].xml'=>types('xlsx'), 'xl/workbook.xml'=>'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"/>',
        'xl/sharedStrings.xml'=>'<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><si><t>Step Objectives</t></si><si><r><t>Match </t></r><r><t>pictures</t></r></si></sst>',
        'xl/worksheets/sheet1.xml'=>$sheet];
    $xlsx = archiveFixture($xlsxEntries);
    $result = $service->read($xlsx, 'teacher.xlsx');
    expect($result['steps'][0]['step_objective'] === 'Match pictures', 'Excel shared rich strings read');
    expect($result['steps'][0]['duration_lp'] === '3 weeks', 'Excel sparse reordered columns and inline strings read');
    expect($result['steps'][0]['pdsp_indicator_text'] === 'Cognitive', 'Domain retained');
    $xlsxEntries['xl/worksheets/sheet1.xml'] = str_replace('<v>1</v>', '<f>HYPERLINK("https://never-call")</f><v>1</v>', $sheet);
    $formula = archiveFixture($xlsxEntries);
    rejects(fn() => $service->read($formula, 'formula.xlsx'), 'Formulas rejected, never evaluated');
    $many = array_merge([$header], array_fill(0, 11, $rows[1]));
    $tooMany = archiveFixture(['[Content_Types].xml'=>types('docx'), 'word/document.xml'=>word($many)]);
    rejects(fn() => $service->read($tooMany, 'many.docx'), 'Existing ten-step maximum enforced');

    $iep = ['id'=>9,'drafted_by'=>12,'status'=>'draft'];
    IEPPartThreeImportService::authorize('sped_teacher', 12, $iep, 'test-token', 'test-token');
    expect(true, 'Draft owner authorized');
    foreach (['parent','general_teacher','principal','user'] as $role) rejects(fn() => IEPPartThreeImportService::authorize($role, 12, $iep, 'x', 'x'), 'Unauthorized role rejected');
    rejects(fn() => IEPPartThreeImportService::authorize('sped_teacher', 99, $iep, 'x', 'x'), 'Another teacher rejected');
    rejects(fn() => IEPPartThreeImportService::authorize('sped_teacher', 12, null, 'x', 'x'), 'Missing or different IEP rejected');
    rejects(fn() => IEPPartThreeImportService::authorize('sped_teacher', 12, array_merge($iep,['status'=>'signed']), 'x', 'x'), 'Finalized IEP rejected');
    rejects(fn() => IEPPartThreeImportService::authorize('sped_teacher', 12, $iep, 'wrong', 'right'), 'Invalid CSRF rejected');
    rejects(fn() => IEPPartThreeImportService::authorize('sped_teacher', 12, $iep, '', ''), 'Missing CSRF rejected');
    // Exercise the real endpoint with constructors bypassed and a fake read-only model.
    // No application bootstrap, real PDO connection, or schema setup is run.
    require_once __DIR__ . '/../app/Controllers/IEPController.php';
    $controllerClass = new ReflectionClass(IEPController::class);
    $controller = $controllerClass->newInstanceWithoutConstructor();
    $model = new class {
        public int $lookups = 0;
        public function findById($id): ?array {
            $this->lookups++;
            return (int)$id === 9 ? ['id'=>9,'drafted_by'=>12,'status'=>'draft'] : null;
        }
    };
    $controllerClass->getProperty('iepModel')->setValue($controller, $model);
    $controllerClass->getProperty('userId')->setValue($controller, 12);
    $_SESSION['iep_part3_import_csrf'] = [9=>'token-for-9'];
    foreach ([
        ['parent',9,'token-for-9',403],
        ['sped_teacher',10,'token-for-9',403],
        ['sped_teacher',9,'incorrect-token',403],
        ['sped_teacher',9,'token-for-9',422],
    ] as [$role,$id,$token,$status]) {
        $controllerClass->getProperty('userRole')->setValue($controller, $role);
        $_POST = ['iep_id'=>$id,'import_csrf'=>$token]; $_FILES = [];
        http_response_code(200);
        ob_start(); $controller->importStepsPreview(); $response = json_decode(ob_get_clean(), true);
        expect(http_response_code() === $status && $response['success'] === false, 'Real preview endpoint rejects unauthorized, cross-IEP, invalid CSRF, or absent uploads');
    }
    expect($model->lookups === 3, 'Unauthorized roles rejected before learner lookup');
    // Render only the new partial. Its CSRF token lives in the session, not a database.
    $iep = ['id'=>9]; $basePath = '/local';
    ob_start(); require __DIR__ . '/../app/Views/iep/partials/iep_steps_import.php'; $html = ob_get_clean();
    expect(str_contains($html, 'data-iep-id="9"') && str_contains($html, 'data-csrf="token-for-9"'), 'View binds upload and CSRF to the opened learner IEP');
    echo "PASS: {$checks} Part III import checks (synthetic files; no database or network).\n";
} finally {
    foreach ($files as $file) if (is_file($file)) unlink($file);
}
