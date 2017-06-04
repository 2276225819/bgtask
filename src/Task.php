<?php
use Workerman\Events\EventInterface;

class Task implements React\Promise\PromiseInterface
{
    public static $addr="tcp://127.0.0.1:33445";
    public static $timeout=-1;

    /**
     * @var  React\Promise\Deferred
     */
    public $defer;
    /**
     * Primary Method
     * @return EventInterface
     */
    public static function getLoop()
    {
        static $loop=null;
        if (empty($loop)) {
            $loop=\Workerman\Worker::getEventLoop();
            if (empty($loop)) {
                $loop = new WaitEvent();
            }
        }
        return $loop;
    }
    
    /**
     * New and Start Task
     * @param Closure $fn
     * @return Task
     */
    public static function run($fn)
    {
        $task = new self();
        $task->fnstr=serialize(new Opis\Closure\SerializableClosure($fn));
        $task->start();
        return $task;
    }
    /**
     * Remote Access
     * @param Task[] $tasks
     * @return React\Promise\Promise
     */
    public static function all($tasks)
    {
        return React\Promise\all($tasks);
    }
    /**
     * Remote Access
     * @param Task[] $tasks
     * @return void
     */
    public static function any($tasks)
    {
        return React\Promise\all($tasks);
    }
    /**
     * Local Access
     * @param Task[] $tasks
     * @return Task
     */
    public static function whenAll($tasks)
    {
        $task = new self();
        $task->defer = new React\Promise\Deferred();
        React\Promise\all($tasks)->then(function ($data) use ($task) {
            $task->result = $data;
            $task->defer->resolve($data);
        }, function ($data) use ($task) {
            $task->defer->reject($data);
        });
        return $task;
    }
    public static function whenAny($tasks)
    {
        $task = new self();
        $task->defer = new React\Promise\Deferred();
        React\Promise\any($tasks)->then(function ($data) use ($task) {
            $task->result = $data;
            $task->defer->resolve($data);
        }, function ($data) use ($task) {
            $task->defer->reject($data);
        });
        return $task;
    }
    public static function waitAll($tasks)
    {
        $loop = self::getLoop();
        if (!$loop instanceof WaitEvent) {
            throw new Exception("Not Support Wait", 1);
        }
        while (1) {
            if ($read = $loop->event_select()) {
                foreach ($read as $fd) {
                    $loop->event_trigger((int)$fd);
                }
            }
            foreach ($tasks as $k => $task) {
                if (isset($task->result)) {
                    unset($tasks[$k]);
                }
            }
            if (empty($tasks)) {
                return true;
            }
        }
    }
    public static function waitAny($tasks)
    {
        $loop = self::getLoop();
        if (!$loop instanceof WaitEvent) {
            throw new Exception("Not Support Wait", 1);
        }
        while (1) {
            if ($read = $loop->event_select()) {
                foreach ($read as $fd) {
                    $loop->event_trigger((int)$fd);
                    return true;
                }
            }
        }
    }
    public static function sleep($time){
        return new React\Promise\Promise(function($next)use($time){
            \Workerman\Lib\Timer::add($time,$next,[],false);
        });
    }

    public function wait()
    {
        return self::waitAll([$this]);
    }

    public function encodeData()
    {
        return json_encode([self::class,$this->fnstr]);
    }
    public function decodeData($data)
    {
        $json = stream_get_contents($data);
        return json_decode($json);
    }
    public function start()
    {
        $this->defer = new React\Promise\Deferred();
        $this->connection = @stream_socket_client(self::$addr, $code, $msg, self::$timeout);
        if (!$this->connection) {
            $this->defer->reject(new \Exception($msg, $code));
            return false;
        }
        $loop = self::getLoop();
        $loop->add($this->connection, EventInterface::EV_READ, function ($data) use ($loop) {
            $result = $this->decodeData($data);
            $this->result = $result;
            $this->defer->resolve($result);
            $loop->del($this->connection, EventInterface::EV_READ);
        });

        $buffer = $this->encodeData();
        if (strlen($buffer) !== @fwrite($this->connection, $buffer)) {
            $this->defer->reject(new \Exception("send data error"));
            return false;
        }
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        return $this->defer->promise()->then($onFulfilled, $onRejected, $onProgress);
    }
    public function getResult()
    {
        if (!$this->defer) {
            $this->start();
        }
        $this->wait();
        return $this->result;
    }
}
