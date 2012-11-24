<?php

namespace FsWatcher;

use Evenement\EventEmitter,
    Sh\Sh;

class Watcher
{
    const LINUX    = 'Linux';
    const MAC_OS_X = 'Darwin';

    /**
     * @param $directory
     * @return Watcher\Linux|Watcher\Mac
     * @throws \Exception
     */
    public static function factory($directory)
    {
        switch (PHP_OS) {
            case self::LINUX:
                $watcher =  new Watcher\Linux(new Sh(),new EventEmitter());
                break;
            case self::MAC_OS_X:
                return new Watcher\Mac(new Sh(),new EventEmitter());
                break;
            default:
                throw new \Exception('Not implemented.');
        }

        $watcher->setDirectory($directory);
        return $watcher;
    }
}