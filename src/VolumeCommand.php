<?php

namespace Just;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VolumeCommand extends BaseCommand
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('vol')
            ->addArgument('direction', InputArgument::REQUIRED)
            ->setDescription('Set the phone volume [up] or [down].');
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
        switch(strtolower($input->getArgument('direction')))
        {
            case 'up':
                $this->sendAction(['VUP']);
                break;
            case 'down':
                $this->sendAction(['VDOWN']);
                break;
        }
    }
}
