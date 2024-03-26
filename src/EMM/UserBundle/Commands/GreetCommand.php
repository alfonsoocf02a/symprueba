<?php

namespace EMM\UserBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GreetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('demo:greet')
            ->setDescription('Saludar a alguien')
            ->addArgument(
                'nombre',
                InputArgument::OPTIONAL,
                'A quién quieres saludar?'
            )
            ->addOption(
                'gritar',
                null,
                InputOption::VALUE_NONE,
                'Si se establece, se mostrará en mayúsculas'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('nombre');
        if ($name) {
            $text = 'Hola ' . $name;
        } else {
            $text = 'Hola';
        }

        if ($input->getOption('gritar')) {
            $text = strtoupper($text);
        }

        $output->writeln($text);
    }
}
