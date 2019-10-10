<?php

namespace app\controller\v1;

use sap\Package;

class Performance
{
    /**
     * 获取后端性能
     * @return Package
     */
    public function backend() : Package
    {
        $now = explode(' ', microtime());
        $begin = explode(' ', $GLOBALS['backend']['begin']);
        $routeBegin = explode(' ', $GLOBALS['backend']['route_begin']);
        $routeEnd = explode(' ', $GLOBALS['backend']['route_end']);

        $t = function ($begin, $end) {
            $ms = $end[0] - $begin[0];
            $s = $end[1] - $begin[1];
            if ($ms < 0) {
                $ms += 1.0;
                $s -= 1;
            }
            return $s + substr($ms,0, 8);
        };

        $p = [
            'backend_init' => $t($begin, $routeBegin),
            'route_init' => $t($routeBegin, $routeEnd),
            'api_init' => $t($routeEnd, $now),
            'response' => $t($begin, $now)
        ];

        return Package::ok('后端性能', $p);
    }
}