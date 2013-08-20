<?php

namespace WMC\Wordpress\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;

class ConfHandler
{
    public static function updateFiles(Event $event)
    {
        $io = $event->getIO();

        foreach (glob(getcwd() . '/confs/samples/*.ini') as $file) {
            $file = basename($file);
            self::updateFile($io, getcwd() . "/confs/$file", getcwd() . "/confs/samples/$file");
        }
    }

    private static function updateFile(IOInterface $io, $realFile, $distFile)
    {
        $keepOutdatedParams = false;

        if (!is_file($distFile)) {
            throw new \InvalidArgumentException(sprintf('%s is missing.', $distFile));
        }

        $exists = is_file($realFile);

        $action = $exists ? 'Updating' : 'Creating';
        $io->write(sprintf('<info>%s "%s"</info>', $action, $realFile));

        // Find the expected params
        $expectedValues = parse_ini_file($distFile);

        // find the actual params
        $actualValues = array();
        if ($exists) {
            $existingValues = parse_ini_file($realFile);
            if (!is_array($existingValues)) {
                throw new \InvalidArgumentException(sprintf('The existing "%s" file does not contain an array', $realFile));
            }
            $actualValues = array_merge($actualValues, $existingValues);
        }

        if (!$keepOutdatedParams) {
            // Remove the outdated params
            foreach ($actualValues as $key => $value) {
                if (!array_key_exists($key, $expectedValues)) {
                    unset($actualValues[$key]);
                }
            }
        }

        // Add the params coming from the environment values
        $actualValues = array_replace($actualValues, self::getEnvValues());
        $actualValues = self::getParams($io, $expectedValues, $actualValues);

        file_put_contents($realFile, "; This file is auto-generated during the composer install\n" . self::dump($actualValues));
    }

    private static function getEnvValues(array $envMap = array())
    {
        $params = array();
        foreach ($envMap as $param => $env) {
            $value = getenv($env);
            if ($value) {
                $params[$param] = $value;
            }
        }

        return $params;
    }

    private static function getParams(IOInterface $io, array $expectedParams, array $actualParams)
    {
        // Simply use the expectedParams value as default for the missing params.
        if (!$io->isInteractive()) {
            return array_replace($expectedParams, $actualParams);
        }

        $isStarted = false;

        foreach ($expectedParams as $key => $message) {
            if (array_key_exists($key, $actualParams)) {
                continue;
            }

            if (!$isStarted) {
                $isStarted = true;
                $io->write('<comment>Some app loader parameters are missing. Please provide them.</comment>');
            }

            $default = self::dumpSingle($message);
            $value = $io->ask(sprintf('<question>%s</question> (<comment>%s</comment>):', $key, $default), $default);

            $actualParams[$key] = self::parseSingle($value);
        }

        return $actualParams;
    }

    private static function dump(array $params)
    {
        $ini = "";

        foreach ($params as $key => $value) {
            $ini .= "$key=" . self::dumpSingle($value) . "\n";
        }

        return $ini;
    }

    private static function dumpSingle($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (empty($value)) {
            return 'false';
        } elseif ($value == '1') {
            return 'true';
        } else {
            return "\"$value\"";
        }
    }

    private static function parseSingle($value)
    {
        $ini = parse_ini_string("value=$value");

        return $ini['value'];
    }
}
