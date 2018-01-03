<?php

namespace FRNApp\Command;

use FRNApp\DrupalAdapter;
use RRule\RfcParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDrupal extends Command
{
    const RFC_DATE_FORMAT = 'Ymd\THis\Z';

    /**
     * @var \DomDocument
     */
    public $doc;

    /**
     * @var \DOMElement
     */
    public $station;

    /**
     * @var \DOMElement
     */
    public $programme;

    protected function configure()
    {
        $this
            ->setName('generate:drupal')
            ->setDescription('Generate XML from Drupal')
            ->addOption('id', NULL, InputOption::VALUE_REQUIRED, 'ID limit', 0)
            ->addOption('limit')
            ->setHelp('Todo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $path = getenv('DRUPAL_ROOT');
        $url = getenv('DRUPAL_URL');
        $save_path = getenv('SAVE_PATH');
        if (empty($save_path)) {
            $save_path = 'data/rdl.xml';
        }
        $adapter = new DrupalAdapter($path, $url);
        $limit = $input->getOption('limit');
        $offset = 0;

        $shows = $adapter->getShows(0, 0, $input->getOption('id'));

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $this->doc = $doc;

        $now = new \DateTime();
        $this->station = $this->el('station', NULL, [
            'lastupdate' => $now->format('c'),
            'xmlns:gml' => "http://www.opengis.net/gml"]
        );
        $doc->appendChild($this->station);

        $this->info = $this->getInfo();
        $this->station->appendChild($this->info);

        $this->programme = $doc->createElement('programme');
        $this->station->appendChild($this->programme);

        $id = 0;
        foreach ($shows as $show) {
            $rrule = $this->parseRruleFromDrupal($show);
            $now = new \DateTime();
            // Skip past shows.
            if (!empty($rrule['UNTIL']) && $rrule['UNTIL'] < $now) {
                continue;
            }
            $id++;

            $broadcast = $this->el('broadcast', NULL, ['id' => $id]);
            $broadcast->appendChild($this->el('title', $show->title));
            $broadcast->appendChild($this->el('description', $show->body));
            $broadcast->appendChild($this->el('website', $show->url));


            $times = $this->generateTimes($rrule, $show);
            $broadcast->appendChild($times);

            $this->programme->appendChild($broadcast);
        }
        $doc->formatOutput = TRUE;
        $xml = $doc->saveXML();
        file_put_contents($save_path, $xml);
    }

    public function el($name, $val = NULL, $attrs = [])
    {
        $el = $this->doc->createElement($name, $val);
        if (!empty($attrs)) {
            foreach ($attrs as $key => $val) {
                $el->setAttribute($key, $val);
            }
        }
        return $el;
    }

    public function generateTimes($rule, $show)
    {

        $times = $this->el('transmit-times');
        $time = $this->generateTimeRow($rule, $show);
        $times->appendChild($time);
        if (!empty($show->rerun)) {
            $rule = $this->parseRruleFromDrupal($show->rerun);
            $time = $this->generateTimeRow($rule, $show->rerun);
            $time->setAttribute('rerun', 'true');
            $times->appendChild($time);
        }
        return $times;
    }

    public function generateTimeRow($rule, $show, $rerun = FALSE) {

        $time = $this->el('transmit-time', NULL, ['recurrence' => 'true']);

        $start = $show->start;
        $end = $show->end;

        $initProps = [
            'priority' => 0,
            'time-from' => $start->format('H:i:s'),
            'time-to' => $end->format('H:i:s'),
        ];


        if ($rule['FREQ'] == 'WEEKLY' || $rule['FREQ'] == 'DAILY') {
            $props = $initProps;

            $days = explode(',', $rule['BYDAY']);
            $days = $this->translateDays($days);

            if ($rule['INTERVAL'] !== "1") {
                $props['weekFrequency'] = $rule['INTERVAL'];
                $props['date-from'] = $start->format('c');
            }
            foreach ($days as $day) {
                $props['day'] = $day;
                $weekly = $this->el('weekly', NULL, $props);
                $time->appendChild($weekly);
            }
        }
        else if ($rule['FREQ'] == 'MONTHLY') {

            $daysRaw = explode(',', $rule['BYDAY']);
            $days = [];
            foreach ($daysRaw as $day) {
                $matches = [];
                $day = trim(strtoupper($day));
                $valid = preg_match('/^([+-]?[0-9]+)?([A-Z]{2})$/', $day, $matches);

                if (!$valid) {
                    throw new \InvalidArgumentException('Invalid BYDAY value: ' . $day . ' for show ' . $show->originalId);
                }
                $day = $this->translateDay($matches[2]);
                if (!empty($matches[1])) {
                    $days[$day][] = $matches[1];
                }
                else {
                    $days[$day][] = [1,2,3,4,5];
                }
            }

            foreach ($days as $day => $week_nums) {
                $props = $initProps;
                $props['day'] = $day;
                for ($i = 1; $i <= 5; $i++) {
                    $props['week' . $i] = "false";
                }
                foreach ($week_nums as $num) {
                    $num = (int) $num;
                    if ($num > 0) {
                        $props['week' . $num] = "true";
                    }
                }
                $weekOfMonth = $this->el('weekOfMonth', NULL, $props);
                $time->appendChild($weekOfMonth);

            }


        }

        return $time;



        return $times;
    }

    public function elToString($el)
    {
        $el = clone $el;
        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($el, TRUE));
        $doc->formatOutput = TRUE;
        return $doc->saveXML();
    }

    public function translateDay($day)
    {
        $map = [
            'MO' => 'MO',
            'TU' => 'DI',
            'WE' => 'MI',
            'TH' => 'TH',
            'FR' => 'FR',
            'SA' => 'SA',
            'SU' => 'SO',
        ];
        return $map[$day];
    }

    public function translateDays($days)
    {
        foreach ($days as $i => $day) {
            $days[$i] = $this->translateDay($day);
        }
        return $days;
    }

    public function parseRruleFromDrupal($show)
    {
        $dtstart = 'DTSTART:' . $show->start->format(self::RFC_DATE_FORMAT);

        $rrule = $show->rrule;
        $rrule = str_replace("\r\n", "\n", $rrule);

        foreach (explode("\n", $rrule) as $key => $part) {
            $els = explode(':', $part);
            if ($els[0] == 'RRULE') {
                $rule = $part;
            } else if ($els[0] == 'DTSTART') {
                $dtstart = $part;
            } else if ($els[0] == 'EXDATE') {
                $exdate = $els[1];
            } else if ($els[0] == 'RDATE') {
                $rdate = $els[1];
            } else if (empty(trim($part))) {

            } else {
                throw new \InvalidArgumentException("Unsupported line: " . $part . " in show " . $show->originalId);
            }

        }

        $parseableString = $dtstart . "\n" . $rule;
        $parts = RfcParser::parseRRule($parseableString);
        if (empty($parts['WKST'])) {
            $parts['WKST'] = 'MO';
        }
        if (!empty($exdate)) {
            $parts['EXCLUDE'] = $exdate;
        }
        if (!empty($rdate)) {
            $parts['RDATE'] = $exdate;
        }
        return $parts;
    }

    public function getInfo() {
        $el = $this->el('info');
        $el->appendChild($this->el('displayname', 'Radio Dreyeckland'));
        $el->appendChild($this->el('fullname', 'Radio Dreyeckland'));
        $el->appendChild($this->el('logo', NULL, ['src' => 'https://rdl.de/sites/all/themes/zenrdl/logo.png']));
        $el->appendChild($this->el('basecolor', '#FF0000'));
        $el->appendChild($this->el('city', 'Freiburg'));
        $studio = $el->appendChild($this->el('studio'));
        $studio->appendChild($this->el('street', 'Adlerstr.'));
        $studio->appendChild($this->el('number', '12'));
        $studio->appendChild($this->el('city', 'Freiburg'));
        $studio->appendChild($this->el('zip', '79098'));
        $loc = $studio->appendChild($this->el('studio-location'));
        $gml = $loc->appendChild($this->el('gml:Point'));
        $gml->appendChild($this->el('gml:pos', '47.993157 7.840236'));
        $studio->appendChild($this->el('phone', '+49 761 31028', ['type' => 'studio']));
        $studio->appendChild($this->el('phone', '+49 761 30407', ['type' => 'office']));
        $studio->appendChild($this->el('email', 'aktuell@rdl.de', ['type' => 'studio']));
        $studio->appendChild($this->el('email', 'verwaltung@rdl.de', ['type' => 'office']));
        return $el;
    }
}