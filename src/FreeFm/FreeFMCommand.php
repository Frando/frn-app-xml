<?php

namespace FRNApp\FreeFm;

use FRNApp\Command\GenerateCommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FreeFMCommand extends GenerateCommandBase
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('generate:freefm')
            ->setDescription('Generate XML from Drupal (FreeFM)')
            ->addOption('drupal-root', 'r', InputOption::VALUE_REQUIRED, 'Path to Drupal root', $this->getEnvOrDefault('FREEFM_DRUPAL_ROOT', '.'))
            ->addOption('drupal-url', 'l', InputOption::VALUE_REQUIRED, 'Drupal base URL', $this->getEnvOrDefault('FREEFM_DRUPAL_URL', 'localhost'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = new FreeFmDrupalAdapter([
            'path' => $input->getOption('drupal-root'),
            'url' => $input->getOption('drupal-url')
        ]);
        $xmlCreator = new FreeFmXmlCreator($adapter);
        $this->createXml($input, $output, $xmlCreator);
        return 0;
    }
}
