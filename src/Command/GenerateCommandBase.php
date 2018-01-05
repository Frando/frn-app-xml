<?php
namespace FRNApp\Command;
use FRNApp\XmlCreatorBase;
use FRNApp\XmlCreatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GenerateCommandBase extends Command {
    protected function configure()
    {
        $this
            ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'Only include these broadcast IDs', NULL)
            ->addOption('save-path', NULL, InputOption::VALUE_REQUIRED, 'Save path (default: SAVE_PATH or data/data.xml)', $this->getEnvOrDefault('SAVE_PATH', 'data/data.xml'));
    }

    protected function getEnvOrDefault($name, $default) {
        $val = getenv($name);
        if ($val === FALSE)  {
            $val = $default;
        }
        return $val;
    }

    protected function createXml(InputInterface $input, OutputInterface $output, XmlCreatorInterface $xmlCreator) {
        $xmlCreator->setIdLimit($input->getOption('id'));
        $xmlCreator->createAndSaveXml($input->getOption('save-path'));
        $output->writeln("Wrote XML to " . realpath($input->getOption('save-path')));
    }

}