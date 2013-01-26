<?php

class Game_Track extends Game_Entity
{
    const Throne = 'throne';
    const Blade = 'blade';
    const Raven = 'raven';

    public $tracksIds = array(
        self::Throne,
        self::Blade,
        self::Raven,
    );

    public $tracks = array();
    public $stars = array();

    protected static $exportProps = array('tracks', 'stars');

    public function __construct($players, $stars)
    {
        $this->stars = $stars;

        $pos = array_map(function($p) {
            return array($p->house, $p->track);
        }, $players);

        foreach ($this->tracksIds as $track) {
            usort($pos, function($a, $b) use ($track) {
                $t1 = $a[1][$track];
                $t2 = $b[1][$track];
                if ($t1 == $t2) return 0;
                return $t1 < $t2 ? -1 : 1;
            });
            $this->tracks[$track] = array_map(function($p) { return $p[0]; }, $pos);
        }
    }

    public function trackSort($trackId, array $hids) {
        return array_filter($this->tracks[$trackId], function ($hid) use ($hids) {
            return in_array($hid, $hids);
        });
    }

    public function stars($hid)
    {
        $pos = array_search($hid, $this->tracks[self::Raven]);
        return $this->stars[$pos];
    }

    public function trackOrder($trackId, &$cur, $callback)
    {
        $track = $this->tracks[$trackId];
        $i = -1;
        if ($cur != null) {
            $i = array_search($cur, $track);
        }
        $c = $i;
        while (true) {
            $i++;
            if ($i >= count($track)) {
                if ($c == -1) {
                    return false;
                }
                $i = 0;
            }
            if ($callback($track[$i], $cur)) {
                return true;
            }
            if ($c == $i) {
                return false;
            }
        }
    }
}

