<?php
namespace xlx { 

    function co($gen, ...$args)
    {
        if (is_callable($gen)) {
            $gen = $gen(...$args);
        }
        while ($gen->valid()) {
            $val = $gen->current();
            if ($val instanceof \React\Promise\Promise) {
                return $val->then(function ($data) use ($gen) {
                    $gen->send($data);
                    return co($gen);
                });
            } else {
                $gen->send($val);
            }
        }
        return \React\Promise\resolve($gen->getReturn());
    }

    function async($fn)
    {
        return function (...$args)use($fn) {
            return \xlx\co($fn, ...$args);
        };
    }
}
namespace xlx\co { 
    function warp($fn)
    {
        return function (...$args)use($fn) {
            return \xlx\co($fn, ...$args);
        };
    }
}
