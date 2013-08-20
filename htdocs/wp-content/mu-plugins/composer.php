<?php

$path = dirname(dirname(dirname(dirname(__FILE__))));
$autoloader = "$path/vendor/autoload.php";

if (!is_file($autoloader)) {
    if (WP_DEBUG) {
        header('Content-Type: text/plain');
        passthru("composer.phar install -d '$path' -n --prefer-dist --optimize-autoloader 2>&1", $return_code);
        if ($return_code == 0) {
            die("\n\nComposer has been ran. Please reload.");
        } else {
            wp_die('Composer was attempted to be installed, but an error occured');
        }
    } else {
        wp_die('Composer must be ran for this website to function properly.');
    }
}

require_once $autoloader;
