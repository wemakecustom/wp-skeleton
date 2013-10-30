<?php

namespace WMC\Wordpress\Composer;

use Composer\Script\Event;
use WMC\Composer\Utils\ConfigFile\IniConfigFile;

class ConfHandler
{
    public static function updateFiles(Event $event)
    {
        $configFile = new IniConfigFile($event->getIO());

        foreach (glob(getcwd() . '/confs/samples/*.ini') as $file) {
            $file = basename($file);
            $configFile->updateFile(getcwd() . "/confs/$file", getcwd() . "/confs/samples/$file");
        }
    }
}
