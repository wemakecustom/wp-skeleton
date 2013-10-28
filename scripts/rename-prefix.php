#!/usr/bin/env php
<?php

if (empty($argv[1])) {
    echo "Rename a Wordpress prefix\n";
    echo "Change \$table_prefix in wp-config.php AFTER running this script\n";
    echo "Usage: {$argv[0]} prefix\n";
    die();
}

if ($argv[1] == 'update') {
  $file = file_get_contents('https://gist.github.com/lavoiesl/7198905/raw/wp-rename-prefix.php');
  $md5 = md5($file);
  $current = md5_file(__FILE__);
  if ($md5 == $current) {
    echo "Already up-to-date.\n";
  } else {
    file_put_contents(__FILE__, $file);
    echo "Updated. MD5: " . md5_file(__FILE__) . PHP_EOL;
  }

  chmod(__FILE__, 0755);
  exit(0);
}


$rename_to = $argv[1];

require __DIR__ . '/../htdocs/wp-config.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

$result = $mysqli->query('SHOW TABLES');

if ($result->num_rows == 0) {
    die("No table in database\n");
}

$row = $result->fetch_row();
if (preg_match('/^([a-z0-9]+)_/', $row[0], $matches)) {
    $rename_from = $matches[1];
} else {
    die("Cannot retrieve prefix\n");
}

if ($rename_from == $rename_to) {
    die("Prefix is already $rename_to\n");
}


$result->data_seek(0);

while ($row = $result->fetch_row()) {
    $sql[] = "RENAME TABLE ${row[0]} TO " . preg_replace("/^{$rename_from}_/", "{$rename_to}_", $row[0]) . ";";
}

$sql[] = '';

$result = $mysqli->query("SELECT option_name FROM ${rename_from}_options WHERE option_name LIKE '${rename_from}_%'");
while ($row = $result->fetch_row()) {
    $name = $row[0];
    $new_name = preg_replace("/^{$rename_from}_/", "{$rename_to}_", $name);

    $sql[] = "UPDATE ${rename_to}_options SET option_name = '$new_name' WHERE option_name LIKE '$name';";
}

$sql[] = '';

$result = $mysqli->query("SELECT DISTINCT meta_key FROM ${rename_from}_usermeta WHERE meta_key LIKE '${rename_from}_%'");
while ($row = $result->fetch_row()) {
    $name = $row[0];
    $new_name = preg_replace("/^{$rename_from}_/", "{$rename_to}_", $name);

    $sql[] = "UPDATE ${rename_to}_usermeta SET meta_key = '$new_name' WHERE meta_key LIKE '$name';";
}



echo "We are going to execute:\n\n" . implode("\n", $sql) . "\n\n";

if (readline('Continue [yN] ? ') == 'y') {
    foreach ($sql as $s) {
        $mysqli->query($s);
    }

    echo "\nGood, now change \$table_prefix to {$rename_to}_ in wp-config.php\n";
}
