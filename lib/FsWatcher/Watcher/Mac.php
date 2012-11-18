<?php

namespace FsWatcher\Watcher;

class Mac implements Iface
{
    public function __construct($directory)
    {
        throw new \Exception('Not implemented. Wanna help with MAC_OS_X version? contact with me');
    }

    public function registerExtensionToWatch($extension)
    {
    }

    public function onSave(\Closure $function)
    {
    }

    public function onDelete(\Closure $function)
    {
    }

    public function onCreate(\Closure $function)
    {
    }

    public function start()
    {
    }
}