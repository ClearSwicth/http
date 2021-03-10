<?php

namespace clearswitch\http\parser;

/**
 * JSON 解析器
 * @author clearSwitch。
 */
class JsonParser implements ResponseParserInterface
{
    /**
     * @var int 递归深度
     * @author clearSwitch。
     */
    public $depth = 512;

    /**
     * @var int 参数
     * @author clearSwitch。
     */
    public $options = 0;

    /**
     * @inheritdoc
     * @author clearSwitch。
     */
    public function can($response)
    {
        $response = trim($response);
        $start = mb_substr($response, 0, 1);
        $end = mb_substr($response, -1);
        return ($start === '{' && $end === '}') || ($start === '[' && $end === ']');
    }

    /**
     * @inheritdoc
     * @author clearSwitch。
     */
    public function parse($response)
    {
        try {
            return json_decode($response, true, $this->depth, $this->options);
        } catch (\Exception $e) {
            return false;
        }
    }
}