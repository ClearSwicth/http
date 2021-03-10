<?php

namespace clearswitch\http;

/**
 * ObjectHelper
 * 对象助手
 * ------------
 * @author clearSwitch。
 */
class ObjectHelper
{
    /**
     * 变成对象便于链式的写法
     * create(String $type)
     * 创建对象
     * --------------------
     * @param String $type 类型
     * -----------------------
     * @return Object
     * @author clearSwitch。
     */
    public static function create($type, ...$args)
    {
        $class = null;
        if (is_string($type)) {
            return new $type([], ...$args);
        }
        if (is_array($type)) {
            if (isset($type['class'])) {
                $class = $type['class'];
                unset($type['class']);
                return new $class($type, ...$args);
            }
        }
        throw new \Exception('Object configuration must be an array containing a "class" element.');
    }
}