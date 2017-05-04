<?php

namespace Just;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CallCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('call')
            ->addArgument('phonenumber', InputArgument::REQUIRED)
            ->setDescription('Call a phonenumber or a mapped number.');
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->selectAvailableLine();
        
        $phonenumber = trim($input->getArgument('phonenumber'));

        $config = $this->getYamlConfig();
        if (isset($config['map'][$phonenumber])) {
            $phonenumber = $config['map'][$phonenumber];
        }

        $phonenumber = str_replace('+', '0', $phonenumber);
        $phonenumber = preg_replace('/\s|\(|\)|-/', '', $phonenumber);

        if (!is_numeric($phonenumber)) {
            echo 'Not a valid phonenumber';
            die();
        }

        $keys = array_merge(str_split($phonenumber), ['SEND']);
        $this->sendAction($keys);
    }
}
