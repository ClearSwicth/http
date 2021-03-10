<?php

namespace clearswitch\http\transport;

use clearswitch\http\Request;

/**
 * 传输通道接口
 * @author clearSwitch。
 */
interface TransportInterface
{
    /**
     * 发送
     * @param Request $request 请求对象
     * @return array [$statusCode, $headers, $content, $response]
     * @author clearSwitch。
     */
    public function send(Request $request);

    /**
     * 批量发送
     * @param array $requests 包含请求对象的数组
     * @return array
     * @author clearSwitch。
     */
    public function batchSend(array $requests);
}