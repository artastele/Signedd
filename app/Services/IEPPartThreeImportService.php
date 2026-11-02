<?php
/** Local OOXML table reader. No database, file extraction, or external requests. */
class IEPPartThreeImportService {
    public const MAX_BYTES = 5242880;
    public const DOMAINS = ['Daily Living Skills', 'Socio-Emotional', 'Language Development', 'Psychomotor',
        'Cognitive', 'Aesthetic/Creative', 'Behavioral Development', 'Orientation and Mobility'];
    private const WORD = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
    private const SHEET = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    public static function authorize(string $role, int $userId, ?array $iep, $token, $expected): void {
        if (!in_array($role, ['sped_teacher', 'admin'], true) || !$iep
            || ($role !== 'admin' && (int)$iep['drafted_by'] !== $userId)
            || ($iep['status'] ?? '') !== 'draft') {
            throw new RuntimeException('You may import only into an editable IEP draft you are authorized to manage.', 403);
        }
        if (!is_string($token) || !is_string($expected) || $expected === '' || !hash_equals($expected, $token)) {
            throw new RuntimeException('Your import session expired. Reload this IEP and try again.', 403);
        }
    }

    public function read(string $path, string $name): array {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($extension, ['docx', 'xlsx'], true)) throw new RuntimeException('Choose a Word (.docx) or Excel (.xlsx) file.');
        $size = is_file($path) ? filesize($path) : 0;
        if (!$size || $size > self::MAX_BYTES) throw new RuntimeException('Choose a readable, non-empty file no larger than 5 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!in_array($mime, ['application/zip', 'application/octet-stream', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], true)) throw new RuntimeException('The file is not a readable Word or Excel document.');
        $handle = fopen($path, 'rb');
        $signature = $handle ? fread($handle, 4) : '';
        if ($handle) fclose($handle);
        if ($signature !== "PK\x03\x04") throw new RuntimeException('The document is damaged, encrypted, or unsupported.');
        $tempZipPath = null;
        $targetPath = $path;
        if (!preg_match('/\.(zip|docx|xlsx)$/i', $path)) {
            $tempZipPath = tempnam(sys_get_temp_dir(), 'signed_doc_');
            if ($tempZipPath) {
                @unlink($tempZipPath);
                $tempZipPath .= '.zip';
                if (copy($path, $tempZipPath)) {
                    $targetPath = $tempZipPath;
                } else {
                    $tempZipPath = null;
                }
            }
        }
        try {
            $zip = new PharData($targetPath);
            $count = 0; $expanded = 0;
            foreach (new RecursiveIteratorIterator($zip) as $entry) {
                if (++$count > 2000 || ($expanded += $entry->getSize()) > 20971520) {
                    throw new RuntimeException('The document is too large to process safely.');
                }
            }
            $types = $this->xml($this->entry($zip, '[Content_Types].xml'));
            $typeText = $types->saveXML();
            if (stripos($typeText, 'macroEnabled') !== false || isset($zip['word/vbaProject.bin']) || isset($zip['xl/vbaProject.bin'])) {
                throw new RuntimeException('Macro-enabled documents are not supported.');
            }
            $typePath = new DOMXPath($types);
            $typePath->registerNamespace('t', 'http://schemas.openxmlformats.org/package/2006/content-types');
            $part = $extension === 'docx' ? '/word/document.xml' : '/xl/workbook.xml';
            $expectedType = 'application/vnd.openxmlformats-officedocument.'
                . ($extension === 'docx' ? 'wordprocessingml.document' : 'spreadsheetml.sheet') . '.main+xml';
            $actualType = $typePath->evaluate('string(/t:Types/t:Override[@PartName="' . $part . '"]/@ContentType)');
            if (!$actualType) {
                // OOXML allows packaging via Default Extension="xml"
                $actualType = $typePath->evaluate('string(/t:Types/t:Default[@Extension="xml"]/@ContentType)');
            }
            if ($actualType !== $expectedType) {
                throw new RuntimeException('The document contents do not match its .docx or .xlsx file type.');
            }
            $tables = $extension === 'docx' ? $this->wordTables($zip) : $this->excelTables($zip);
            return $this->mapTables($tables);
        } catch (UnexpectedValueException $e) {
            throw new RuntimeException('The document archive is damaged or unreadable. Save it again as .docx or .xlsx and retry.');
        } catch (RuntimeException $e) { throw $e; }
        catch (Throwable $e) { throw new RuntimeException('The document is unreadable. Save it again as .docx or .xlsx and retry.'); }
        finally {
            if ($tempZipPath && file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }
        }
    }

    private function entry(PharData $zip, string $name): string {
        if (!isset($zip[$name]) || $zip[$name]->getSize() > 4194304) throw new RuntimeException('A required document table is missing or too large.');
        return $zip[$name]->getContent();
    }

    private function xml(string $text): DOMDocument {
        if (stripos($text, '<!DOCTYPE') !== false || stripos($text, '<!ENTITY') !== false) throw new RuntimeException('Documents containing XML entities are not supported.');
        $previous = libxml_use_internal_errors(true);
        try {
            $doc = new DOMDocument();
            if (!$doc->loadXML($text, LIBXML_NONET | LIBXML_COMPACT)) throw new RuntimeException('The document contains malformed XML.');
            return $doc;
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }

    private function wordTables(PharData $zip): array {
        $doc = $this->xml($this->entry($zip, 'word/document.xml'));
        $xp = new DOMXPath($doc); $xp->registerNamespace('w', self::WORD);
        $tables = [];
        foreach ($xp->query('//w:tbl[not(ancestor::w:tbl)]') as $table) {
            $rows = [];
            foreach ($xp->query('./w:tr', $table) as $tr) {
                $row = [];
                foreach ($xp->query('./w:tc', $tr) as $cell) {
                    $paragraphs = [];
                    foreach ($xp->query('./w:p', $cell) as $p) {
                        $value = '';
                        foreach ($xp->query('.//w:t | .//w:br | .//w:tab', $p) as $node) $value .= $node->localName === 't' ? $node->textContent : ' ';
                        $paragraphs[] = $value;
                    }
                    $row[] = trim(implode("\n", $paragraphs));
                    $span = $xp->query('./w:tcPr/w:gridSpan/@w:val', $cell)->item(0);
                    for ($i = 1; $i < min(50, (int)($span ? $span->nodeValue : 1)); $i++) $row[] = '';
                }
                $rows[] = $row;
                if (count($rows) > 1000) throw new RuntimeException('The document has too many table rows.');
            }
            $tables[] = $rows;
        }
        return $tables;
    }

    private function excelTables(PharData $zip): array {
        $this->xml($this->entry($zip, 'xl/workbook.xml'));
        $strings = [];
        if (isset($zip['xl/sharedStrings.xml'])) {
            $xp = new DOMXPath($this->xml($this->entry($zip, 'xl/sharedStrings.xml'))); $xp->registerNamespace('s', self::SHEET);
            foreach ($xp->query('//s:si') as $si) {
                $text = ''; foreach ($xp->query('.//s:t', $si) as $t) $text .= $t->textContent;
                $strings[] = $text;
            }
        }
        $tables = [];
        if (!isset($zip['xl/worksheets'])) throw new RuntimeException('No Excel worksheet was found.');
        foreach (new RecursiveIteratorIterator($zip) as $file) {
            if (!preg_match('~/xl/worksheets/[^/]+\.xml$~', str_replace('\\', '/', $file->getPathname()))) continue;
            if ($file->getSize() > 4194304) throw new RuntimeException('The worksheet is too large.');
            $xp = new DOMXPath($this->xml($file->getContent())); $xp->registerNamespace('s', self::SHEET);
            $rows = [];
            foreach ($xp->query('//s:sheetData/s:row') as $tr) {
                $row = [];
                foreach ($xp->query('./s:c', $tr) as $cell) {
                    if (!preg_match('/^([A-Z]{1,3})[1-9][0-9]*$/', $cell->getAttribute('r'), $m)) throw new RuntimeException('The worksheet has unreadable cell references.');
                    $column = 0;
                    foreach (str_split($m[1]) as $letter) $column = $column * 26 + ord($letter) - 64;
                    if ($column > 100) throw new RuntimeException('Use a worksheet with no more than 100 columns.');
                    // Formula results may be stale or externally sourced. Never evaluate or import them.
                    if ($xp->query('./s:f', $cell)->length) throw new RuntimeException('Replace worksheet formulas with their text values before importing.');
                    $value = $xp->evaluate('string(s:v)', $cell);
                    if ($cell->getAttribute('t') === 's') $value = $strings[(int)$value] ?? '';
                    if ($cell->getAttribute('t') === 'inlineStr') {
                        $value = ''; foreach ($xp->query('./s:is//s:t', $cell) as $t) $value .= $t->textContent;
                    }
                    $row[$column - 1] = $value;
                }
                $rows[] = $row;
                if (count($rows) > 1000) throw new RuntimeException('The worksheet has too many rows.');
            }
            $tables[] = $rows;
        }
        return $tables;
    }

    private function heading(string $text): ?string {
        $key = trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($text)));
        $aliases = [
            'step_objective' => ['step objectives','step objective','enabling objectives','enabling objective','objectives','objective'],
            'duration_lp' => ['duration of lp','duration of lesson plan','duration','time frame','timeframe','timeline'],
            'instructional_evaluation' => ['instructional evaluation','evaluation method','evaluation methods','assessment method'],
            'pdsp_indicator_text' => ['targeted developmental domain','developmental domain','targeted domain','domain'],
        ];
        foreach ($aliases as $field => $headings) if (in_array($key, $headings, true)) return $field;
        return null;
    }

    private function mapTables(array $tables): array {
        $steps = []; $warnings = []; $found = false;
        foreach ($tables as $table) {
            $columns = [];
            foreach ($table as $row) {
                $header = [];
                foreach ($row as $i => $value) {
                    $field = $this->heading($value);
                    if ($field !== null) {
                        if (in_array($field, $header, true)) throw new RuntimeException('The table has duplicate column headings. Give each supported column one heading.');
                        $header[$i] = $field;
                    }
                }
                if (in_array('step_objective', $header, true)) { $columns = $header; $found = true; continue; }
                if (!$columns || !array_filter($row, fn($v) => trim($v) !== '')) continue;
                $step = array_fill_keys(['step_objective','duration_lp','instructional_evaluation','pdsp_indicator_text'], '');
                foreach ($columns as $i => $field) $step[$field] = trim($row[$i] ?? '');
                if ($step['step_objective'] === '') { $warnings[] = 'Rows without a Step Objective were skipped.'; continue; }
                $cleanObj = trim(preg_replace('/[^a-z0-9]+/', ' ', mb_strtolower($step['step_objective'])));
                if (in_array($cleanObj, ['date of re evaluation', 'date of reevaluation', 'prepared and implemented by', 'prepared by', 'implemented by', 'signatories', 'signatures'], true)) {
                    continue;
                }
                if ($step['pdsp_indicator_text'] !== '' && !in_array($step['pdsp_indicator_text'], self::DOMAINS, true)) {
                    $warnings[] = 'An unrecognized developmental domain was left blank. Select the correct domain in the preview.';
                    $step['pdsp_indicator_text'] = '';
                }
                foreach ($step as $value) if (mb_strlen($value) > 5000) throw new RuntimeException('A cell exceeds the 5,000 character limit. Shorten it before importing.');
                $steps[] = $step;
                if (count($steps) > 10) throw new RuntimeException('Import at most 10 Step Objectives at a time.');
            }
        }
        if (!$found) throw new RuntimeException('No Step Objectives heading was found. Add a heading row to the table and retry.');
        if (!$steps) throw new RuntimeException('No completed Step Objectives were found. Blank rows are ignored.');
        $seen = [];
        foreach ($steps as $step) {
            $key = mb_strtolower(preg_replace('/\s+/u', ' ', $step['step_objective']));
            if (isset($seen[$key])) $warnings[] = 'Duplicate objectives were found. Edit or remove duplicate rows before confirming.';
            $seen[$key] = true;
        }
        return ['steps'=>$steps, 'warnings'=>array_values(array_unique(array_merge($warnings, [
            'Only objectives, developmental domain, duration, and planned instructional evaluation text are imported. Observations, lesson plans, materials, evaluations, and ratings are not created.',
        ])))];
    }
}
