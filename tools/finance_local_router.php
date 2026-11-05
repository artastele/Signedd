<?php
// Local PHP server router; NEVER run `php -S -t public` alone for premium media.
// Usage: php -S 127.0.0.1:8080 -t public tools/finance_local_router.php
$root=realpath(__DIR__.'/../public');
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI']??'/',PHP_URL_PATH)??'/');
if (strpos($path,'..')!==false || strpos($path,"\0")!==false || preg_match('#(?:^|/)\.#',$path)) { http_response_code(404); return true; }
$guarded=preg_match('#^/uploads/(materials|lesson_pages|lesson_editor|activities|fsl_custom_videos|lesson_plans)/#i',$path);
$file=realpath($root.$path);
if (!$guarded && $file && strpos($file,$root.'/')===0 && is_file($file) && strtolower(pathinfo($file,PATHINFO_EXTENSION))!=='php') return false;
$_SERVER['SCRIPT_NAME']='/index.php';
require $root.'/index.php';
