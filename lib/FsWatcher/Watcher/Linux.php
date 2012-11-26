<?php

namespace FsWatcher\Watcher;

use Evenement\EventEmitter,
        Sh\Sh;

class Linux implements Iface
{
    private $directory;
    private $emitter;

    private $extensions = array();
    private $callbacks = array();

    const INOTIFY_PARAMS = "-m -r -q -e %s %s";

    const ACTION_SAVE   = 'move';
    const ACTION_DELETE = 'delete';
    const ACTION_CREATE = 'create';

    private $inotyfyAndActionDictionary = array();


    public function __construct(Sh $sh, EventEmitter $emitter)
    {
        $this->sh      = $sh;
        $this->emitter = $emitter;

        $this->registerOnOutput();
        $this->registerOnError();
    }

    public function registerExtensionToWatch($extension)
    {
        $this->extensions[] = strtolower($extension);
    }

    public function onSave(\Closure $function)
    {
        $this->callbacks[self::ACTION_SAVE]['callback'] = $function;

        $this->inotyfyAndActionDictionary['CLOSE_WRITE'] = self::ACTION_SAVE;
        $this->inotyfyAndActionDictionary['MOVED_TO']    = self::ACTION_SAVE;
        $this->inotyfyAndActionDictionary['MOVE']        = self::ACTION_SAVE;
        $this->inotyfyAndActionDictionary['MODIFY']      = self::ACTION_SAVE;

        return $this;
    }

    public function onDelete(\Closure $function)
    {
        $this->callbacks[self::ACTION_DELETE]['callback'] = $function;

        $this->inotyfyAndActionDictionary['DELETE'] = self::ACTION_DELETE;

        return $this;
    }

    public function onCreate(\Closure $function)
    {
        $this->callbacks[self::ACTION_CREATE]['callback'] = $function;

        $this->inotyfyAndActionDictionary['CREATE'] = self::ACTION_CREATE;

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
            }
        );
    }

    private function soWatcher(\Closure $callback)
    {
        $this->sh->inotifywait(
            sprintf(self::INOTIFY_PARAMS, implode(',', $this->getEventsToInotify()), $this->directory),
            $callback
        );
    }

    private function getEventsToInotify()
    {
        return array_map('strtolower', array_keys($this->inotyfyAndActionDictionary));
    }

    private function registerOnOutput()
    {
        $this->emitter->on('output', function ($buffer) {
                foreach (explode("\n", trim($buffer)) as $line) {
                    list($path, $action, $file) = explode(' ', $line, 3);
                    if ($this->isValidFileToWatch($file)) {
                        $callback = $this->getCallbackFromAction($action);
                        if (is_callable($callback)) {
                            call_user_func_array($callback, array($path . $file));
                            break;
                        }
                    }
                }
            }
        );
    }

    private function getCallbackFromAction($action)
    {
        foreach (explode(",", str_replace('"', null, $action)) as $act) {
            if (array_key_exists($act, $this->inotyfyAndActionDictionary)) {
                $key = $this->inotyfyAndActionDictionary[$act];
                if (array_key_exists($key, $this->callbacks)) {
                    return $this->callbacks[$key]['callback'];
                }
            }
        }

        return null;
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
            }
        );
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }
}