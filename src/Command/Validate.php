<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapterBase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Validate extends Command
{
    protected function configure()
    {
        $this
            ->setName('xml:validate')
            ->setDescription('Validate XML against XSD')
            ->setHelp('Todo')
            ->addOption('schema', NULL, InputOption::VALUE_REQUIRED, 'Set XSD schema to validate against (default: use included schema)', NULL)
            ->addArgument('xml', InputArgument::REQUIRED, 'XML file');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getOption('schema');
        if (empty($schema)) {
            $schema = realpath(FRNAPP_DIR . '/xsd/frn-app-station.xsd');
        }
        $xml = $input->getArgument('xml');

        if (!file_exists($schema)) {
            $output->writeln('<error>Schema file ' . $schema . ' not found.</error>');
            return;
        }
        if (!file_exists($xml)) {
            $output->writeln('<error>XML file ' . $xml . ' not found.</error>');
            return;
        }

        try {
            libxml_use_internal_errors(true);

            $doc = new \DOMDocument();
            $doc->load($xml);
            $ret = $doc->schemaValidate($schema);
            if ($ret) {
                $output->writeln('<info>Validation successfull!</info>');
            }
            else {
                $output->writeln('<error>Validation failed.</error>');
                $errors = libxml_display_errors();
                $output->writeln($errors);
            }
        }
        catch (\Exception $e) {
            $output->writeln('Error!');
            dump($e);
        }
    }
}


function libxml_display_error($error)
{
    $return = '';
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
        case LIBXML_ERR_ERROR:
            $return .= "<fg=red>Error $error->code: </>";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "<bg=red>Fatal Error $error->code: </>";
            break;
    }
    $return .= '<fg=yellow>';
    $return .= trim($error->message);
    $return .= '</>';
    $return .= "\n    ";
    if ($error->file) {
        $file = str_replace(FRNAPP_DIR, '.', $error->file);
        $return .=    "in $file";
    }
    $return .= " on line $error->line";

    return $return;
}

function libxml_display_errors() {
    $errors = libxml_get_errors();
    foreach ($errors as &$error) {
        $error = libxml_display_error($error);
    }
    libxml_clear_errors();
    return $errors;
}
