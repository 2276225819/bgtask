<?php
namespace xlx
{
    if (!function_exists('\xlx\co')) {
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
                }
                if ($val instanceof \Generator) {
                    return co($val); 
                }
                $gen->send($val);
            }
            return \React\Promise\resolve($gen->getReturn());
        }
    }

    if (!function_exists('xlx\async')) {
        function async($fn)
        {
            return function (...$args) use ($fn) {
                return \xlx\co($fn, ...$args);
            };
        }
    }
}
namespace xlx\co

{
    if (!function_exists('xlx\warp')) {
        function warp($fn)
        {
            return function (...$args) use ($fn) {
                return \xlx\co($fn, ...$args);
            };
        }
    }
}
