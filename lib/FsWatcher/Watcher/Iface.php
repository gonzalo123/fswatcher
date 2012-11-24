<?php

namespace FsWatcher\Watcher;

use Evenement\EventEmitter,
    Sh\Sh;

interface Iface
{
    public function __construct(Sh $sh, EventEmitter $emiter);

    public function registerExtensionToWatch($extension);

    public function onSave(\Closure $function);

    public function onDelete(\Closure $function);

    public function onCreate(\Closure $function);

    public function start();

    public function setDirectory($directory);
}