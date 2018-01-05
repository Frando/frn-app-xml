<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapterFreeFM;
use FRNApp\DrupalAdapterRDL;
use FRNApp\XmlCreatorFreeFM;
use FRNApp\XmlCreatorRDL;
use RRule\RfcParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDrupalFreeFM extends GenerateCommandBase
{
    protected function configure()
    {
        $this
            ->setName('generate:freefm')
            ->setDescription('Generate XML from Drupal (FreeFM)')
            ->setHelp('Todo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = getenv('FREEFM_ROOT');
        $url = getenv('FREEFM_URL');
        $adapter = new DrupalAdapterFreeFM($path, $url);
        $xmlCreator = new XmlCreatorFreeFM($adapter);

        $this->createXml($input, $output, $xmlCreator);

        return;
    }
}
