<?php

namespace FsWatcher;

use Evenement\EventEmitter,
    Sh\Sh;

class Watcher
{
    const LINUX    = 'Linux';
    const MAC_OS_X = 'Darwin';

    private $watcher;

    /**
     * @param $directory
     * @return Watcher\Iface
     */
    public static function factory($directory)
    {
        switch (PHP_OS) {
            case self::LINUX:
                return new self(new Watcher\Linux($directory));
                break;
            case self::MAC_OS_X:
                return new self(new Watcher\Mac($directory));
                break;
        }
    }

    public function __construct(Watcher\Iface $watcher)
    {
        $this->watcher = $watcher;
    }

    public function registerExtensionToWatch($extension)
    {
        $this->watcher->registerExtensionToWatch($extension);
        return $this;
    }

    public function onSave(\Closure $function)
    {
        $this->watcher->onSave($function);
        return $this;
    }

    public function onDelete(\Closure $function)
    {
        $this->watcher->onDelete($function);
        return $this;
    }

    public function onCreate(\Closure $function)
    {
        $this->watcher->onCreate($function);
        return $this;
    }

    public function start()
    {
        $this->watcher->start();
    }
}