<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapterRDL;
use FRNApp\XmlCreatorRDL;
use RRule\RfcParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDrupalRDL extends Command
{
    protected function configure()
    {
        $this
            ->setName('generate:rdl')
            ->setDescription('Generate XML from Drupal')
            ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'ID limit', 0)
            ->addOption('limit')
            ->setHelp('Todo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = getenv('DRUPAL_ROOT');
        $url = getenv('DRUPAL_URL');
        $adapter = new DrupalAdapterRDL($path, $url);
        $xmlCreator = new XmlCreatorRDL($adapter);

        $save_path = getenv('SAVE_PATH');
        if (empty($save_path)) {
            $save_path = 'data/rdl.xml';
        }
        $xmlCreator->savePath = $save_path;

        $id = $input->getOption('id');
        if ($id) {
            $xmlCreator->setIdLimit($id);
        }

        $xmlCreator->createXml();

        return;
    }
}
