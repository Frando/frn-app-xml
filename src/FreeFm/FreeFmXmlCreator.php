<?php

namespace FRNApp\FreeFm;

use FRNApp\XmlCreatorBase;

class FreeFmXmlCreator extends XmlCreatorBase {
    public function getInfo() {
        $el = $this->el('info');
        $el->appendChild($this->el('displayname', 'Radio free FM'));
        $el->appendChild($this->el('fullname', 'Radio free FM gGmbH'));
        $el->appendChild($this->el('city', 'Ulm'));
        $studio = $el->appendChild($this->el('studio'));
        $studio->appendChild($this->el('street', 'Platzgasse'));
        $studio->appendChild($this->el('number', '18'));
        $studio->appendChild($this->el('city', 'Ulm'));
        $studio->appendChild($this->el('zip', '89073'));
        $loc = $studio->appendChild($this->el('studio-location'));
        $gml = $loc->appendChild($this->el('gml:Point'));
        $gml->appendChild($this->el('gml:pos', '48.4005863 9.9910884'));
        $studio->appendChild($this->el('phone', '+49 731 93862 84', ['type' => 'studio']));
        $studio->appendChild($this->el('phone', '+49 731 93862 99', ['type' => 'office']));
        $studio->appendChild($this->el('email', 'info@freefm.de', ['type' => 'office']));
        return $el;
    }
    public function getMediaChannels() {
        $el = $this->el('media-channels');
        $channel[0]= $el->appendChild($this->el('transmitter', NULL, ['type' => 'ukw']));
        $channel[0]->appendChild($this->el('frequency', '102.6'));
        $channel[0]->appendChild($this->el('city', 'Ulm-Ermingen'));
        $channel[0]->appendChild($this->el('transmit-power', '1000'));
        $channel[0]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));
        $channel[0]->appendChild($this->el('transmit-location'))
            ->appendChild($this->el('gml:Point'))
            ->appendChild($this->el('gml:pos', '48.3903404 9.8956938'));

        $channel[1] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[1]->appendChild($this->el('frequency', '97.7'));
        $channel[1]->appendChild($this->el('city', 'Ulm, Neu-Ulm'));
        $channel[1]->appendChild($this->el('operator', 'KabelBW'));
        $channel[1]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        $channel[2] = $el->appendChild($this->el('transmitter', NULL, ['type' => 'cable']));
        $channel[2]->appendChild($this->el('frequency', '93.45'));
        $channel[2]->appendChild($this->el('city', 'Neu-Ulm, Altenstadt a. d. Iller, Bellenberg, Holzheim bei Neu-Ulm, Illertissen, Kellmünz a. d. Iller, Pfaffenhofen a. d. Roth, Senden, Vöhringen, Weißenhorn'));
        $channel[2]->appendChild($this->el('operator', 'KabelBW'));
        $channel[2]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));


        $channel[4] = $el->appendChild($this->el('webstream'));
        $channel[4]->appendChild($this->el('url', 'http://stream.freefm.de:8100/listen.pls'));
        $channel[4]->appendChild($this->el('format', 'audio/mpeg'));
        $channel[4]->appendChild($this->el('bitrate', '160'));
        $channel[4]->appendChild($this->el('transmit-times'))
            ->appendChild($this->el('transmit-time', NULL, ['recurrence' => 'true']))
            ->appendChild($this->el('daily', NULL, ['time-from' => '00:00:00', 'time-to' => '23:59:59']));

        return $el;
    }

    public function getBroadcasts($id, $show)
    {
        $broadcast = parent::getBroadcasts($id, $show);
        $broadcast->appendChild($this->el('title', $show->title));
        $broadcast->appendChild($this->el('description', $show->body));
        $broadcast->appendChild($this->el('website', $show->url));
        $times = $this->generateTimes($show);
        $broadcast->appendChild($times);
        return $broadcast;
    }

    protected function generateTimes($show) {
        $times = $this->el('transmit-times');
        if (!empty($show->airtime)) {
            foreach ($show->airtime as $airobj) {
                $time = $this->generateTimeRow($airobj);
                $times->appendChild($time);
            }
        }
        return $times;
    }

    protected function generateTimeRow($airobj) {
        $time = $this->el('transmit-time', NULL, ['recurrence' => 'true']);

        $props = [
            'priority' => 0,
            'time-from' => substr($airobj->start, 0, 2) . ':' . substr($airobj->start, 2, 2) . ':00',
            'time-to' => substr($airobj->end, 0, 2) . ':' . substr($airobj->end, 2, 2) . ':00',
            'day' => $airobj->day,
        ];


        if ($airobj->type === 'week') {
            $name = 'weekly';
        }
        else if ($airobj->type === 'secondweek') {
            $name = 'weekly';
            $props['weekFrequency'] = "2";
            $props['oddEvenWeekNbr'] = $airobj->odd ? "odd" : "even";
        }
        else if ($airobj->type === 'last') {
            $name = 'weekOfMonth';
            $props['lastWeek'] = "true";
        }
        else if ($airobj->type === 'nlast') {
            $name = 'weekOfMonth';
            for ($i = 1; $i <= 5; $i++) {
                $props['week' . $i] = "true";
            }
            $props['lastWeek'] = "false";
        }
        else if ($airobj->type === 'weeknums') {
            $name = 'weekOfMonth';
            for ($i = 1; $i <= 5; $i++) {
                $props['week' . $i] = in_array($i, $airobj->weeknums) ? "true" : "false";
            }
        }

        if (isset($name)) {
            $el = $this->el($name, NULL, $props);
            $time->appendChild($el);
        }

        return $time;

    }
}
