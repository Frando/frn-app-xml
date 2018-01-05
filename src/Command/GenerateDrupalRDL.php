<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapterRDL;
use FRNApp\XmlCreatorRDL;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDrupalRDL extends GenerateCommandBase
{
    protected function configure()
    {
        // Init base options.
        parent::configure();
        $this
            ->setName('generate:rdl')
            ->setDescription('Generate XML from Drupal (RDL)')
            ->setHelp('Todo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = getenv('DRUPAL_ROOT');
        $url = getenv('DRUPAL_URL');
        $adapter = new DrupalAdapterRDL($path, $url);
        $xmlCreator = new XmlCreatorRDL($adapter);

        $this->createXml($input, $output, $xmlCreator);

        return;
    }
}
