<?php

$remote_name = 'skeleton';
$remote = 'https://github.com/wemakecustom/wp-skeleton.git';
$package_name = 'wemakecustom/wp-skeleton';
$composer_file = getcwd() . '/composer.json';

function load_composer($composer_file, $package_name)
{
    if (!file_exists($composer_file)) {
        throw new Exception('composer.json must exist (CWD must be repository root)');
    }

    $composer = json_decode(file_get_contents($composer_file), true);

    if (empty($composer['replace'][$package_name])) {
        throw new Exception("composer.json must contain a replace.{$package_name} version");
    }

    return $composer;
}

/**
 * git remote add/set_url + fetch
 */
function update_remote($remote_name, $remote)
{
    if (!exec("git remote -v | grep '^${remote_name}\W' | grep -F '${remote}'")) {
        if (exec("git remote | grep '^${remote_name}$'")) {
            exec("git remote set-url '${remote_name}' '${remote}'");
        } else {
            exec("git remote add '${remote_name}' '${remote}'");
        }

        echo "git remote ${remote_name} was updated.\n";
    }

    exec("git fetch ${remote_name}");
}

/**
 * Returns all tags of a remote as array($tag => $commit_id)
 */
function current_remote_tags($remote_name)
{
    exec("git ls-remote --tags '${remote_name}'", $output);

    $tags = array();

    foreach ($output as $line) {
        if (preg_match('/^([a-f0-9]+)\s+refs\/tags\/(.+)$/', $line, $matches)) {
            $tags[$matches[2]] = $matches[1];
        }
    }

    if (empty($tags)) {
        throw new Exception("Cannot fetch tags for $remote_name");
    }

    return $tags;
}

/**
 * Apply all commits between $old_commit and $new_commit
 * Puts them all as one in index, but does not commit it.
 */
function apply_patch($old_commit, $new_commit)
{
    $tmp_patch = tempnam(null, 'wpskeleton_update_');
    unlink($tmp_patch);
    mkdir($tmp_patch);

    exec("git format-patch -o '$tmp_patch' ${old_commit}..${new_commit}");

    exec("git apply --3way --apply '$tmp_patch'/*.patch", $output, $return_value);

    if ($return_value == 0) {
        array_map('unlink', glob("$tmp_patch/*.patch"));
        rmdir($tmp_patch);

        return true;
    } else {
        echo "\nFailed to update, see the patches here for the full diff:\n$tmp_patch\n";

        return false;
    }
}

/**
 * Replaces '"$package_name": "$old"' with '"$package_name": "$new"' in $composer_file
 */
function composer_replace_version($composer_file, $package_name, $old, $new)
{
    $composer = file_get_contents($composer_file);
    $composer = str_replace('"' . $package_name . '": "' . $old . '"', '"' . $package_name . '": "' . $new . '"', $composer);
    file_put_contents($composer_file, $composer);
}

$composer = load_composer($composer_file, $package_name);
$version_local = $composer['replace'][$package_name];

update_remote($remote_name, $remote);

$remote_tags = current_remote_tags($remote_name);
$version_remote = array_keys($remote_tags);
$version_remote = $version_remote[count($version_remote) - 1];

if ($version_remote != $version_local) {
    if (apply_patch($remote_tags[$version_local], $remote_tags[$version_remote])) {
        composer_replace_version($composer_file, $package_name, $version_local, $version_remote);
        $message = "Updated remote $remote_name from $version_local to $version_remote";
        exec("git add '${composer_file}'");
        exec("git commit -m '$message'");
        echo "\n$message\n";
    }
}
