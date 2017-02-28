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
            ->setDescription('Set the phone in a DND state.');
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
        $phonenumber = trim($input->getArgument('phonenumber'));

        $query = str_replace(' ', '', $phonenumber);
        $query = str_replace('-', '', $query);
        $query = str_replace('+31', '031', $query);

        if (!is_numeric($query)) {
            echo 'Geen geldig telefoonnummer';
            die();
        }

        $keys = array_merge(str_split($query), ['SEND']);
        $this->sendAction($keys);
        
    }
}
