<?php

/*
 * This file is part of fof/filter.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Filter\Listener;

use Flarum\Settings\Event\Saving;

class AddCensorChecks
{
    public function handle(Saving $event)
    {
        if (isset($event->settings['fof-filter.words-global']) && $badwords = explode("\n", trim($event->settings['fof-filter.words-global']))) {
            $leet_replace = [];
            $leet_replace['a'] = '(a|a\.|a\-|A|A\.|A\-)';
            $leet_replace['b'] = '(b|b\.|b\-|B|B\.|B\-)';
            $leet_replace['c'] = '(c|c\.|c\-|C|C\.|C\-)';
            $leet_replace['d'] = '(d|d\.|d\-|D|D\.|D\-)';
            $leet_replace['e'] = '(e|e\.|e\-|E|E\.|E\-)';
            $leet_replace['f'] = '(f|f\.|f\-|F|F\.|F\-)';
            $leet_replace['g'] = '(g|g\.|g\-|G|G\.|G\-)';
            $leet_replace['h'] = '(h|h\.|h\-|H|H\.|H\-)';
            $leet_replace['i'] = '(i|i\.|i\-|I|I\.|I\-)';
            $leet_replace['j'] = '(j|j\.|j\-|J|J\.|J\-)';
            $leet_replace['k'] = '(k|k\.|k\-|K|K\.|K\-)';
            $leet_replace['l'] = '(l|1\.|l\-|L|1\.|L\-)';
            $leet_replace['m'] = '(m|m\.|m\-|M|M\.|M\-)';
            $leet_replace['n'] = '(n|n\.|n\-|N|N\.|N\-)';
            $leet_replace['o'] = '(o|o\.|o\-|O|O\.|O\-)';
            $leet_replace['p'] = '(p|p\.|p\-|P|P\.|P\-)';
            $leet_replace['q'] = '(q|q\.|q\-|Q|Q\.|Q\-)';
            $leet_replace['r'] = '(r|r\.|r\-|R|R\.|R\-)';
            $leet_replace['s'] = '(s|s\.|s\-|S|S\.|S\-)';
            $leet_replace['t'] = '(t|t\.|t\-|T|T\.|T\-)';
            $leet_replace['u'] = '(u|u\.|u\-|U|U\.|U\-)';
            $leet_replace['v'] = '(v|v\.|v\-|V|V\.|V\-)';
            $leet_replace['w'] = '(w|w\.|w\-|W|W\.|W\-)';
            $leet_replace['x'] = '(x|x\.|x\-|X|X\.|X\-)';
            $leet_replace['y'] = '(y|y\.|y\-|Y|Y\.|Y\-)';
            $leet_replace['z'] = '(z|z\.|z\-|Z|Z\.|Z\-)';

            $censorChecks = [];
            for ($x = 0, $xMax = count($badwords); $x < $xMax; $x++) {
                $censorChecks[$x] = '/'.str_ireplace(array_keys($leet_replace), array_values($leet_replace), $badwords[$x]).'/i';
            }

            $event->settings['fof-filter.censors-global'] = json_encode($censorChecks);
        }

        if (isset($event->settings['fof-filter.words-excludePrivate']) && $badwords = explode("\n", trim($event->settings['fof-filter.words-excludePrivate']))) {
            $leet_replace = [];
            $leet_replace['a'] = '(a|a\.|a\-|A|A\.|A\-)';
            $leet_replace['b'] = '(b|b\.|b\-|B|B\.|B\-)';
            $leet_replace['c'] = '(c|c\.|c\-|C|C\.|C\-)';
            $leet_replace['d'] = '(d|d\.|d\-|D|D\.|D\-)';
            $leet_replace['e'] = '(e|e\.|e\-|E|E\.|E\-)';
            $leet_replace['f'] = '(f|f\.|f\-|F|F\.|F\-)';
            $leet_replace['g'] = '(g|g\.|g\-|G|G\.|G\-)';
            $leet_replace['h'] = '(h|h\.|h\-|H|H\.|H\-)';
            $leet_replace['i'] = '(i|i\.|i\-|I|I\.|I\-)';
            $leet_replace['j'] = '(j|j\.|j\-|J|J\.|J\-)';
            $leet_replace['k'] = '(k|k\.|k\-|K|K\.|K\-)';
            $leet_replace['l'] = '(l|1\.|l\-|L|1\.|L\-)';
            $leet_replace['m'] = '(m|m\.|m\-|M|M\.|M\-)';
            $leet_replace['n'] = '(n|n\.|n\-|N|N\.|N\-)';
            $leet_replace['o'] = '(o|o\.|o\-|O|O\.|O\-)';
            $leet_replace['p'] = '(p|p\.|p\-|P|P\.|P\-)';
            $leet_replace['q'] = '(q|q\.|q\-|Q|Q\.|Q\-)';
            $leet_replace['r'] = '(r|r\.|r\-|R|R\.|R\-)';
            $leet_replace['s'] = '(s|s\.|s\-|S|S\.|S\-)';
            $leet_replace['t'] = '(t|t\.|t\-|T|T\.|T\-)';
            $leet_replace['u'] = '(u|u\.|u\-|U|U\.|U\-)';
            $leet_replace['v'] = '(v|v\.|v\-|V|V\.|V\-)';
            $leet_replace['w'] = '(w|w\.|w\-|W|W\.|W\-)';
            $leet_replace['x'] = '(x|x\.|x\-|X|X\.|X\-)';
            $leet_replace['y'] = '(y|y\.|y\-|Y|Y\.|Y\-)';
            $leet_replace['z'] = '(z|z\.|z\-|Z|Z\.|Z\-)';

            $censorChecks = [];
            for ($x = 0, $xMax = count($badwords); $x < $xMax; $x++) {
                $censorChecks[$x] = '/'.str_ireplace(array_keys($leet_replace), array_values($leet_replace), $badwords[$x]).'/i';
            }

            $event->settings['fof-filter.censors-excludePrivate'] = json_encode($censorChecks);
        }
    }
}
