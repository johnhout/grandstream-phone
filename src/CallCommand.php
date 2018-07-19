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
            ->addArgument('query', InputArgument::REQUIRED)
            ->addArgument('external', InputArgument::OPTIONAL)
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
        $query = strtolower(trim($input->getArgument('query')));
        $external = (trim($input->getArgument('external')) == 1);
        $phonenumber = str_replace([' ', '-'], '', $query);

        $config = $this->getYamlConfig();
        if (isset($config['map'][$phonenumber])) {
            $phonenumber = $config['map'][$phonenumber];

        } else if ($external) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "{$config['intern_api_url']}users/phone?name={$phonenumber}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "X-API-KEY: {$config['intern_api_key']}"
            ]);

            $response = json_decode(curl_exec($ch));
            curl_close($ch);
            if (isset($response->phone_number)) {
                $phonenumber = $response->phone_number;
            }
        } else {
            $xml = simplexml_load_string(file_get_contents($config['phonebook']));

            $users = (object)[
                'full' => [],
                'partial' => [],
                'firstname' => [],
                'lastname' => [],
            ];

            foreach ($xml as $item) {
                $firstname = strtolower(trim($item->FirstName));
                $lastname = strtolower(trim(str_replace('(E)', '', (string)$item->LastName)));
                $name = "{$firstname} {$lastname}";
                $number = (int)trim((string)$item->Phone->phonenumber);

                if ($query == $name) {
                    $users->full[] = $number;
                } else if (strpos($name, $query) !== false) {
                    $users->partial[] = $number;
                } else if (strpos($firstname, $query) !== false) {
                    $users->firstname[] = $number;
                } else if (strpos($lastname, $query) !== false) {
                    $users->lastname[] = $number;
                }
            }

            $numbers = array_merge($users->full, $users->partial, $users->firstname, $users->lastname);
            if (count($numbers)) {
                $phonenumber = array_shift($numbers);
            }
        }

        $phonenumber = str_replace('+', '0', $phonenumber);
        $phonenumber = preg_replace('/\s|\(|\)|-/', '', $phonenumber);

        if (!is_numeric($phonenumber)) {
            echo 'Not a valid phonenumber';
            die();
        }

        $this->selectAvailableLine();
        $keys = array_merge(str_split($phonenumber), ['SEND']);
        $this->sendAction($keys);
    }
}
