<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Validate extends Command
{
    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDescription('Validate against XSD')
            ->setHelp('Todo')
            ->addArgument('schema', InputArgument::REQUIRED, 'Schema to validate against')
            ->addArgument('xml', InputArgument::REQUIRED, 'XML file');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = $input->getArgument('schema');
        $xml = $input->getArgument('xml');

        try {
            libxml_use_internal_errors(true);

            $doc = new \DOMDocument();
            $doc->load($xml);
            $ret = $doc->schemaValidate($schema);
            if ($ret) {
                $output->writeln('<info>Validated successfully!</info>');
            }
            else {
                $output->writeln('<error>Errors.</error>');
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
    $return .= '</u>';
    $return .= "\n    ";
    if ($error->file) {
        $return .=    "in $error->file";
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
