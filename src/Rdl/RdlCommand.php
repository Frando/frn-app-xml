<?php

namespace FRNApp\Rdl;

use FRNApp\Command\GenerateCommandBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RdlCommand extends GenerateCommandBase
{
    protected function configure()
    {
        // Init base options.
        parent::configure();
        $this
            ->setName('generate:rdl')
            ->setDescription('Generate XML from Drupal (RDL)')
            ->addOption('drupal-root', 'r', InputOption::VALUE_REQUIRED, 'Path to Drupal root', $this->getEnvOrDefault('RDL_DRUPAL_ROOT', '.'))
            ->addOption('drupal-url', 'l', InputOption::VALUE_REQUIRED, 'Drupal base URL', $this->getEnvOrDefault('RDL_DRUPAL_URL', 'localhost'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $adapter = new RdlDrupalAdapter([
            'path' => $input->getOption('drupal-root'),
            'url' => $input->getOption('drupal-url')
        ]);
        $xmlCreator = new RdlXmlCreator($adapter);
        $this->createXml($input, $output, $xmlCreator);
        return 0;
    }
}
