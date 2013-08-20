<?php

namespace WMC\Wordpress\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Composer\Composer;

class ScriptHandler
{
    public static function wordpressSymlinks(Event $event)
    {
        $composer = $event->getComposer();
        $io       = $event->getIO();
        $extras   = $composer->getPackage()->getExtra();
        $web_dir  = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);
        $wp_dir   = self::getPackagePath($event->getComposer(), 'wordpress/wordpress');

        $io->write(sprintf('<info>Symlinking %s/* into %s/</info>', str_replace(getcwd().'/', '', $wp_dir), str_replace(getcwd().'/', '', $web_dir)));

        $symlink = function ($link_dir, $target_dir, $file, $target_file = null) use ($io) {
            if (null === $target_file) {
                $target_file = $file;
            }

            $link   = "$link_dir/$file";
            $target = ScriptHandler::getRelativePath($link_dir, $target_dir) . ($target_file ? '/'.$target_file : '');

            if (file_exists($link)) {
                if (@readlink($link) == $target) {
                    return;
                } else {
                    $io->write(sprintf('<error>Error while creating a symlink to %s: file exists</error>', str_replace(getcwd().'/', '', $link)));
                }
            } else {
                $io->write(sprintf('<info>Creating symlink %s -> %s.</info>', str_replace(getcwd().'/', '', $link), $target));
                symlink($target, $link);
            }
        };

        foreach (scandir($wp_dir) as $file) {
            if ($file[0] == '.') continue;

            switch ($file) {
                case 'license.txt':
                case 'readme.html':
                case 'wp-config-sample.php':
                case 'wp-config.php':
                case 'wp-content':
                    break;
                default:
                    $symlink($web_dir, $wp_dir, $file);
                    break;
            }
        }

        // Link core themes
        $symlink("$web_dir/wp-content/themes", "$wp_dir/wp-content/themes", "default-themes", "");

        // Link our wp-config back into wordpress files
        $symlink($wp_dir, $web_dir, "wp-config.php");
    }

    public static function wordpressTweaks(Event $event)
    {
        $composer = $event->getComposer();
        $io       = $event->getIO();
        $extras   = $composer->getPackage()->getExtra();
        $web_dir  = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);
        $wp_dir   = self::getPackagePath($event->getComposer(), 'wordpress/wordpress');

        $wp_load = "$wp_dir/wp-load.php";
        $abspath = $web_dir . '/';

        $io->write(sprintf('<info>Setting ABSPATH to %s</info>', $abspath));

        $define = "define( 'ABSPATH', '$abspath' );";
        file_put_contents($wp_load, preg_replace("/^define\(\s*'ABSPATH'.+$/m", $define, file_get_contents($wp_load)));
    }

    public static function generateRandomKeys(Event $event)
    {
        $composer = $event->getComposer();
        $io       = $event->getIO();
        $extras   = $composer->getPackage()->getExtra();
        $web_dir  = getcwd() . '/' . (empty($extras['web-dir']) ? 'htdocs' : $extras['web-dir']);

        $file = "$web_dir/random-keys.php";

        if (!is_file($file)) {
            if (is_writable(dirname($file))) {
                $api = 'https://api.wordpress.org/secret-key/1.1/salt/';
                $io->write(sprintf('<info>Generating secret keys using %s</info>', $api));

                $rnd = "<?php\n\n" . file_get_contents($api);
                file_put_contents($file, $rnd);
            } else {
                $io->write('<error>Error while generating secret keys: random-keys.php is not writable</error>');
            }
        }
    }

    /**
     * @link https://gist.github.com/lavoiesl/5525558
     */
    public static function getRelativePath($from, $to)
    {
        $from = realpath($from);
        $to   = realpath(dirname($to)) . '/' . basename($to);
        if (!$from || !$to) {
            return false;
        }

        // Get dir if source is a file
        if (!is_dir($from)) {
            $from = dirname($from);
        }

        $from = explode(DIRECTORY_SEPARATOR, $from);
        $to   = explode(DIRECTORY_SEPARATOR, $to);

        for ($i=0; $i < count($from) && $i < count($to); $i++) { 
            if ($from[$i] != $to[$i]) {
                break;
            }
        }

        $from = array_splice($from, $i);
        $to   = array_splice($to, $i);

        $up   = str_repeat('..'.DIRECTORY_SEPARATOR, count($from));
        $down = implode(DIRECTORY_SEPARATOR, $to);

        return $up . $down;
    }

    protected static function getPackagePath(Composer $composer, $packageName)
    {
        $repo        = $composer->getRepositoryManager()->getLocalRepository();
        $install_mgr = $composer->getInstallationManager();
        $packages    = $repo->findPackages($packageName, null);

        foreach ($packages as $package) {
            if ($install_mgr->getInstaller($package->getType())->isInstalled($repo, $package)) {
                return $install_mgr->getInstallPath($package);
            }
        }
    }
}
