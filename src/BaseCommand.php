<?php

namespace Just;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command
{
    public function selectAvailableLine()
    {
        $config = $this->getYamlConfig();

        $cmd = sprintf(
            'http://%s/cgi-bin/api-get_line_status?passcode=%s', 
            $config['ip'], 
            $config['passcode']
        );

        $line = -1;
        $busy = false;
        $status = file_get_contents($cmd);
        $status = json_decode($status);
        foreach ($status->body as $l) {
            if ($l->state != 'idle') {
                $busy = true;
            } else if ($line == -1) {
                $line = $l->line;
            }
        }

        if ($busy) {
            $this->sendAction(['LINE' . $line]);
        }
    }

    public function sendAction(array $keys)
    {
        $config = $this->getYamlConfig();

        $parts = implode(':', $keys);
        
        $cmd = sprintf(
            'http://%s/cgi-bin/api-send_key?keys=%s&passcode=%s', 
            $config['ip'], 
            $parts, 
            $config['passcode']
        );

        return file_get_contents($cmd);
    }

    public function getYamlConfig()
    {
        $yamlContents = file_get_contents( __DIR__ . '/../config.yaml' );
        return Yaml::parse($yamlContents);
    }
}
