<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */
namespace clearswitch\http;
use clearswitch\http\transport\TransportInterface;
class Request
{
    /**
     * @var array 内建构造器
     * @author clearSwitch。
     */
    const BUILT_IN_BUILDERS = [
        'json' => 'clearswitch\http\builder\JsonBuilder',
        'urlencoded' => 'clearswitch\http\builder\UrlencodedBuilder',
        'xml' => 'clearswitch\http\builder\XmlBuilder'
    ];

    /**
     * @var array 内建传输通道
     * @author clearSwitch。
     */
    const BUILT_IN_TRANSPORTS = [
        'cUrl' => 'clearswitch\http\transport\CUrlTransport',
        'coroutine' => 'clearswitch\http\transport\CoroutineTransport',
        'stream' => 'clearswitch\http\transport\StreamTransport'
    ];

    public $transports=[
        'cUrl' => 'clearswitch\http\transport\CUrlTransport',
        'coroutine' => 'clearswitch\http\transport\CoroutineTransport',
        'stream' => 'clearswitch\http\transport\StreamTransport'
    ];

    /**
     * @var string 默认你的请求方式
     * @author clearSwitch。
     */
    public $transport='cUrl';

    /**
     * @var string 默认的构建器
     * @author clearSwitch。
     */
    public $builder="json";

    /**
     * @var string 请求地址
     * @author clearSwitch。
     */
    protected $_url = null;

    /**
     * @var string 请求方法
     * @author clearSwitch。。
     */
    protected $_method = 'GET';

    /**
     * @var array 头部参数
     * @author clearSwitch。
     */
    protected $_headers = [];

    /**
     * @var array 请求参数
     * @author clearSwitch。
     */
    protected $_query = [];

    /**
     * @var int 超时时间
     * @author clearSwitch。
     */
    protected $_timeout = 15;

    /**
     * @param $url
     * @return $this
     * @author clearSwitch
     */
    public function setUrl($url){
        $this->_url = $url;
        return $this;
    }

    /**
     * @param array $headers 设置请求头
     * @return $this
     * @author clearSwitch
     */
    public function setHeaders(array $headers){
        $this->_headers = $headers;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @author 添加请求头
     */
    public function addHeader($key, $value){
        $this->_headers[$key] = $value;
        return $this;
    }

    /**
     * @param $method 设置请求方式
     * @return $this
     * @author clearSwitch
     */
    public function setMethod($method){
        $this->_method = strtoupper($method);
        return $this;
    }

    /**
     * @param $timeout 设置请求的过期时间
     * @return $this
     * @author clearSwitch
     */
    public function setTimeout($timeout){
        $this->_timeout = $timeout;
        return $this;
    }

    /**
     * @return mixed 发送请求
     * @return TransportInterface
     * @author clearSwitch
     */
    public function send(){
        $this->getTransport();
        print_R($this);exit;
        list($statusCode, $headers, $content, $response) = $this->getTransport()->send($this);
        $result = ObjectHelper::create([
            'class' => static::responseClass(),
            'request' => $this,
            'tryParse' => $this->tryParse,
            'parsers' => $this->parsers
        ], $statusCode, $headers, $content, $response);
        $this->trigger(static::EVENT_AFTER_REQUEST, $this, $result);
        return $result;
    }

    /**
     * @return TransportInterface
     * @throws \Exception
     * @author daikai
     */
    public function getTransport(){
        if(!isset($this->transports[$this->transport])){
            throw new \Exception('Unkrown transport: ' . $this->transport);
        }
        return $this->_transport=new $this->transports[$this->transport]();
    }

}