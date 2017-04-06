<?php

namespace Just;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command
{
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

        file_get_contents($cmd);
    }

    public function getYamlConfig()
    {
        $yamlContents = file_get_contents( __DIR__ . '/../config.yaml' );
        return Yaml::parse($yamlContents);
    }
}
