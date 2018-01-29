<?php

namespace FRNApp\Rdl;

use FRNApp\XmlCreatorBase;
use RRule\RfcParser;

class RdlXmlCreator extends XmlCreatorBase
{
    const RFC_DATE_FORMAT = 'Ymd\THis\Z';

    public function getInfo()
    {
        $el = $this->el('info');
        $el->appendChild($this->el('displayname', 'Radio Dreyeckland'));
        $el->appendChild($this->el('fullname', 'Radio Dreyeckland'));
        $el->appendChild($this->el('logo', NULL, ['src' => 'https://rdl.de/sites/all/themes/zenrdl/logo.png']));
        $el->appendChild($this->el('basecolor', '#FF0000'));
        $el->appendChild($this->el('description', 'todo'));
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

    public function getMediaChannels()
    {
        $el = $this->el('media-channels');
        $channel[0] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'ukw']));
        $channel[0]->appendChild($this->el('frequency', '102.3'));
        $channel[0]->appendChild($this->el('city', 'Freiburg'));
        $channel[0]->appendChild($this->el('operator', 'MediaBroadcast'));
        $channel[0]->appendChild($this->el('transmit-power', '500'));
        $channel[0]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));
        $channel[0]->appendChild($this->el('transmit-location'))
            ->appendChild($this->el('gml:Point'))
            ->appendChild($this->el('gml:pos', '48.08081 7.66913'));

        $channel[1] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[1]->appendChild($this->el('frequency', '93.6'));
        $channel[1]->appendChild($this->el('city', 'Freiburg'));
        $channel[1]->appendChild($this->el('operator', 'KabelBW'));
        $channel[1]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        $channel[2] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[2]->appendChild($this->el('frequency', '88.15'));
        $channel[2]->appendChild($this->el('city', 'Lahr'));
        $channel[2]->appendChild($this->el('operator', 'KabelBW'));
        $channel[2]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        $channel[3] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[3]->appendChild($this->el('frequency', '97.35'));
        $channel[3]->appendChild($this->el('city', 'Müllheim/Neuenburg'));
        $channel[3]->appendChild($this->el('operator', 'KabelBW'));
        $channel[3]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        $channel[3] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[3]->appendChild($this->el('frequency', '89.35'));
        $channel[3]->appendChild($this->el('city', 'Staufen/Bad Krozingen'));
        $channel[3]->appendChild($this->el('operator', 'KabelBW'));
        $channel[3]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        $channel[4] = $el->appendChild($this->el('webstream'));
        $channel[4]->appendChild($this->el('url', 'http://www.rdl.de:8000/rdl.m3u'));
        $channel[4]->appendChild($this->el('format', 'audio/mpeg'));
        $channel[4]->appendChild($this->el('bitrate', '128'));
        $channel[4]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        return $el;
    }

    public function getBroadcast($id, $show)
    {
        $rrule = $this->parseRruleFromDrupal($show);
        $now = new \DateTime();
        // Skip past shows.
        if (!empty($rrule['UNTIL']) && $rrule['UNTIL'] < $now) {
            return FALSE;
        }

        $broadcast = $this->el('broadcast', NULL, ['id' => $id]);
        $broadcast->appendChild($this->el('title', $show->title));
        $broadcast->appendChild($this->el('description', $show->body));
        $broadcast->appendChild($this->el('website', $show->url));

        $categories = $broadcast->appendChild($this->el('categories', $show->url));
        if (empty($show->genre)) {
            $categories->appendChild($this->el('category', NULL, ['name' => 'Alternative', 'id' => 1]));
        }

        $times = $this->generateTimes($rrule, $show);
        $broadcast->appendChild($times);
        return $broadcast;
    }

    protected function generateTimes($rule, $show)
    {
        $times = $this->el('transmit-times');
        $time = $this->generateTimeRow($rule, $show);
        $times->appendChild($time);
        if (!empty($show->rerun)) {
            $rerunRule = $this->parseRruleFromDrupal($show->rerun);
            $rerunTime = $this->generateTimeRow($rerunRule, $show->rerun);
            $rerunTime->setAttribute('rerun', 'true');
            $times->appendChild($rerunTime);
        }
        return $times;
    }

    protected function generateTimeRow($rule, $show)
    {
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

            if (!empty($rule['BYDAY'])) {
                $days = explode(',', $rule['BYDAY']);
                $days = $this->translateDays($days);
            } else {
                $day = $start->format('N') - 1;
                $map = array_values($this->dayMap());
                $days = [$map[$day]];
            }

            if ($rule['INTERVAL'] !== "1") {
                $props['weekFrequency'] = $rule['INTERVAL'];
                $props['date-from'] = $start->format('c');
            }
            foreach ($days as $day) {
                $props['day'] = $day;
                $weekly = $this->el('weekly', NULL, $props);
                $time->appendChild($weekly);
            }
        } else if ($rule['FREQ'] == 'MONTHLY') {

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
                } else {
                    $days[$day][] = [1, 2, 3, 4, 5];
                }
            }

            foreach ($days as $day => $week_nums) {
                $props = $initProps;
                $props['day'] = $day;
                for ($i = 1; $i <= 5; $i++) {
                    $props['week' . $i] = "false";
                }
                foreach ($week_nums as $num) {
                    $num = (int)$num;
                    if ($num > 0) {
                        $props['week' . $num] = "true";
                    }
                }
                $weekOfMonth = $this->el('weekOfMonth', NULL, $props);
                $time->appendChild($weekOfMonth);
            }
        }

        return $time;
    }

    protected function dayMap()
    {
        $map = [
            'MO' => 'MO',
            'TU' => 'DI',
            'WE' => 'MI',
            'TH' => 'DO',
            'FR' => 'FR',
            'SA' => 'SA',
            'SU' => 'SO',
        ];
        return $map;
    }

    protected function translateDay($day)
    {
        $map = $this->dayMap();
        return $map[$day];
    }

    protected function translateDays($days)
    {
        foreach ($days as $i => $day) {
            $days[$i] = $this->translateDay($day);
        }
        return $days;
    }

    protected function parseRruleFromDrupal($show)
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
            $parts['RDATE'] = $rdate;
        }
        return $parts;
    }
}
