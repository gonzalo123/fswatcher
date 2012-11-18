<?php

namespace FsWatcher\Watcher;

use Evenement\EventEmitter,
    Sh\Sh;

class Linux implements Iface
{
    private $directory;
    private $emitter;

    /** @var null|Callable */
    private $onSaveCallback = NULL;
    /** @var null|Callable */
    private $onCreateCallback = NULL;
    /** @var null|Callable */
    private $onDeleteCallback = NULL;

    private $extensions = array();
    private $callbacks = array();

    const INOTIFY_PARAMS = "-c -m -r -q -e %s %s";

    const ACTION_SAVE   = 'move';
    const ACTION_DELETE = 'delete';
    const ACTION_CREATE = 'create';

    private $inotyfyAndActionDictionary = array(
        'MOVED_TO' => self::ACTION_SAVE,
        'DELETE'   => self::ACTION_DELETE,
        'CREATE'   => self::ACTION_CREATE,
    );


    public function __construct($directory)
    {
        $this->sh        = new Sh();
        $this->emitter   = new EventEmitter();
        $this->directory = $directory;

        $this->registerOnOutput();
        $this->registerOnError();
    }

    public function registerExtensionToWatch($extension)
    {
        $this->extensions[] = strtolower($extension);
    }

    public function onSave(\Closure $function)
    {
        $this->callbacks[self::ACTION_SAVE] = $function;
        return $this;
    }

    public function onDelete(\Closure $function)
    {
        $this->callbacks[self::ACTION_DELETE] = $function;
        return $this;
    }

    public function onCreate(\Closure $function)
    {
        $this->callbacks[self::ACTION_CREATE] = $function;
        return $this;
    }

    public function start()
    {
        $this->soWatcher(function ($buffer, $type) {
            if ('err' === $type) {
                $this->emitter->emit('error', array($buffer));
            } else {
                $this->emitter->emit('output', array($buffer));
            }
        });
    }

    private function soWatcher(\Closure $callback)
    {
        $this->sh->inotifywait(sprintf(self::INOTIFY_PARAMS, implode(',', array_keys($this->callbacks)), $this->directory), $callback);
    }

    private function registerOnOutput()
    {
        $this->emitter->on('output', function ($buffer) {
            foreach (explode("\n", trim($buffer)) as $line) {
                list($path, $action, $file) = $data = explode(',', $line);
                if ($this->isValidFileToWatch($file)) {

                    $callback = $this->getCallbackFromAction($action);
                    if (is_callable($callback)) {
                        call_user_func_array($callback, array($path . $file));
                    }
                }
            }
        });
    }

    private function getCallbackFromAction($action)
    {
        if (!isset($this->inotyfyAndActionDictionary[$action])) return NULL;

        $callbackKey = $this->inotyfyAndActionDictionary[$action];

        return $this->callbacks[$callbackKey];
    }

    private function isValidFileToWatch($file)
    {
        return in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), $this->extensions);
    }

    private function registerOnError()
    {
        $this->emitter->on('error', function ($buffer) {
            foreach (explode("\n", trim($buffer)) as $line) {
                echo "err: " . $line . "\n";
            }
        });
    }
}