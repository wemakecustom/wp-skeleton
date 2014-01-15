<?php

/**
 * Automatically compile LESS files
 * 
 * Features:
 *  - Uses the system temp directory to ensure it is writable
 *  - Gzip compression
 *  - Compile only if not modified
 *  - Respect If-Modified-Since header
 *  - @todo Add caching for gzipped version
 *  - @todo Add caching header
 * 
 * Installation:
 * 1. Download lessphp and extract to $DOCUMENT_ROOT/lessphp
 * @link http://leafo.net/lessphp/
 * 
 * 2. Add this script in the lessphp directory
 * 
 * 3.1. Add RewriteRule in htaccess
 * RewriteCond %{REQUEST_FILENAME} !-f
 * RewriteCond %{REQUEST_FILENAME} ^(.*)\.css
 * RewriteCond %1.less -f
 * RewriteRule ^(.*)\.css lessc.php?f=$1.less
 * 
 * 3.2. If htaccess is not possible, replace CSS links from
 * /css/style.less
 * to
 * lessc.php?f=css/style.less
 * 
 * 
 * @link https://gist.github.com/4127137
 */

if (empty($_GET['f']) || !preg_match('/\.less$/', $_GET['f'])) {
    header('HTTP/1.0 400 Bad Request');
    die();
}

$cache_dir   = sys_get_temp_dir() . '/lessphp/' . $_SERVER['SERVER_NAME']; // will store files in /tmp/lessphp/example.com/css/style.css
$doc_root    = dirname(__FILE__);
$less_file   = "$doc_root/{$_GET['f']}";
$css_file    = $cache_dir . '/' . preg_replace('/\.less/', '.css', $_GET['f']);
$enable_gzip = !empty($_SERVER['HTTP_ACCEPT_ENCODING']) && in_array('gzip', explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']));

if (!is_file($less_file)) {
    header('HTTP/1.0 404 Not Found');
    die();
}

if (!is_dir(dirname($cache_dir))) {
    @mkdir($cache_dir, 0700, true);
    @chmod(dirname($cache_dir), 0777);
}

if (!is_dir(dirname($css_file))) {
    @mkdir(dirname($css_file), 0777, true);
}

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

$less = new lessc;
$less->setFormatter("compressed");

if (!is_writable(dirname($css_file))) {
    // hack to continue working even if cache dir is not writable
    $temp_file = $css_file = tempnam(null, "lessphp_" . $_SERVER['SERVER_NAME']);
    touch($temp_file, 0); // always older
}

try {
    // Compiles only if $less_file mtime != $css_file mtime
    $less->checkedCompile($less_file, $css_file);
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    echo $e->getMessage();
    die();
}

$fp = fopen($css_file, 'r');
$stat = fstat($fp);

if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
    header('HTTP/1.0 304 Not Modified');
} else {
    header('Cache-Control: must-revalidate');
    header('Content-Type: text/css; charset=utf-8');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $stat['mtime']) . ' GMT');

    if ($enable_gzip) {
        header('Content-Encoding: gzip');
        ob_start("ob_gzhandler");
    }
    fpassthru($fp);
}

fclose($fp);

if (isset($temp_file)) {
    unlink($temp_file);
}
