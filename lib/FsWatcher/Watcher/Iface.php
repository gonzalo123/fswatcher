<?php

namespace FsWatcher\Watcher;

interface Iface
{
    public function __construct($directory);

    public function registerExtensionToWatch($extension);

    public function onSave(\Closure $function);

    public function onDelete(\Closure $function);

    public function onCreate(\Closure $function);

    public function start();
}