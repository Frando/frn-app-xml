<?php
namespace FRNApp\Command;
use FRNApp\XmlCreatorBase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GenerateCommandBase extends Command {
    protected function configure()
    {
        $this
            ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'Only include these show IDs', 0)
            ->addOption('save-path', NULL, InputOption::VALUE_REQUIRED, 'Save path (default: SAVE_PATH or data/data.xml', 0);
    }

    protected function createXml(InputInterface $input, OutputInterface $output, XmlCreatorBase $xmlCreator) {
        $save_path = $input->getOption('save-path');
        if (empty($save_path)) {
            $save_path = getenv('SAVE_PATH');
        }
        if (empty($save_path)) {
            $save_path = 'data/data.xml';
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