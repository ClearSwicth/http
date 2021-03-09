<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */
namespace clearswitch\http;
use clearswitch\http\transport\TransportInterface;
use clearswitch\http\builder\BuilderInterface;
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

    /**
     * 用那种请求方式
     * @var string[] 
     */
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
    public $builders=[
        'json' => 'clearswitch\http\builder\JsonBuilder',
        'urlencoded' => 'clearswitch\http\builder\UrlencodedBuilder',
        'xml' => 'clearswitch\http\builder\XmlBuilder'
    ];

    /**
     * @var string 请求地址
     * @author clearSwitch。
     */
    protected $_url = null;

    /**
     * 请求的参数
     * @var string
     */

    protected $requestData='';
    /**
     * @var string|callback 消息体序列化器
     * @author clearSwitch。
     */
    public $bodySerializer = 'json';

    /**
     * @var string 请求方法默认的是get方法
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
     * @return string|null
     * @author clearSwitch
     */
    public function getUrl(){
        return $this->_url;
    }

    /**
     * @var string 消息体
     * @author clearSwitch。
     */
    protected $_content = null;

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
     * 获得头部信息
     * @return array
     * @author daikai
     */
    public function getHeaders(){
        return $this->_headers;
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
     * 获得请求方式
     * @return string
     * @author clearswitch
     */
    public function getMethod(){
        return $this->_method;
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
     * 获得连接超时时间
     * @return int
     * @author daikai
     */
    public function getTimeout(){
        return $this->_timeout;
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
     * 请求
     * @param string $method 请求方式
     * @return Response|string
     * @author clearSwitch。
     */
    public function request($method){
        $this->setMethod($method);
        return $this->send();
    }
    /**
     * @return mixed 发送请求
     * @return TransportInterface
     * @author clearSwitch
     */
    public function send(){
        $this->prepare();
        list($statusCode, $headers, $content, $response) = $this->getTransport()->send($this);
        $result = ObjectHelper::create([
            'class' => static::responseClass(),
            'request' => $this,
            'tryParse' => $this->tryParse,
            'parsers' => $this->parsers
        ], $statusCode, $headers, $content, $response);
        return $result;
    }
    
    /**
     * 实例化响应类
     * @author clearSwitch。
     */
    public static function responseClass(){
        return Response::class;
    }

    public function getRequestData(){
       return $this->requestData;
    }
    /**
     * 获得是那种请求方式
     * @return TransportInterface
     * @throws \Exception
     * @author clearswitch
     */
    public function getTransport(){
        if(!isset($this->transports[$this->transport])){
            throw new \Exception('Unkrown transport: ' . $this->transport);
        }
        return $this->_transport=new $this->transports[$this->transport]();
    }

    /**
     * 获得消息体
     * @return mixed
     * @author daikai
     */
    public function getContent(){
        return $this->_content;
    }
    /**
     * @param $data
     * @param null $serializer
     * @return $this
     * @author switchswitch
     */
    public function setContent($data, $serializer = null){
        $this->_content = $this->normalizeContent($data, $serializer);
        return $this;
    }

    /**
     * 发送之前的准备工作
     * @return $this
     * @author clearSwitch
     */
    public function prepare(){
        $this->_url = $this->normalizeUrl($this->_url);
        if(in_array($this->_method, ['POST', 'PUT', 'DELETE', 'PATCH'])){
            if(empty($this->_content) && !empty($this->_body)){
                $this->_content = $this->normalizeContent($this->_body, $this->bodySerializer);
            }
            $this->addHeader('Content-Length', strlen($this->_content));
        }
        return $this;
    }

    /**
     * @param $data
     * @param null $serializer
     * @return string
     * @author daikai
     */
    public function normalizeContent($data, $serializer = null){
        //print_r(is_callable($serializer));exit;
        /*if(is_callable($serializer)){
            $data = call_user_func($serializer, $data);
        }else */if(is_string($serializer) && !empty($serializer) && is_array($data)){
            $builder = $this->getBuilder($serializer);
            $builder->setElements($data);
            $data = $builder;
        }
        if($data instanceof BuilderInterface){
            foreach($data->headers() as $name => $value){
                $this->addHeader($name, $value);
            }
            $this->requestData=$data->toString();
            $data = $data->toString();
        }
        if(!is_string($data)){
            throw new \Exception('content must be a string');
        }
        return $data;
    }


    /**
     * 获取构建器
     * @param string $builder 构建器
     * @return BuilderInterface
     * @author Verdient。
     */
    public function getBuilder($name){
        $builder = strtolower($name);
        $builder = isset($this->builders[$builder]) ? $this->builders[$builder] : null;
        if($builder){
            $builder = ObjectHelper::create($builder);
            if(!$builder instanceof BuilderInterface){
                throw new \Exception('builder must instance of ' . BuilderInterface::class);
            }
            return $builder;
        }
        throw new \Exception('Unkrown builder: ' . $name);
    }

    /**
     * 格式化的url
     * @param $url
     * @return string
     * @throws \Exception
     * @author clearSwitch
     */
    public function normalizeUrl($url){
        $url = parse_url($url);
        foreach(['scheme', 'host'] as $name){
            if(!isset($url[$name])){
                throw new \Exception('Url is not a valid url');
            }
        }
        $query = [];
        if(isset($url['query'])){
            parse_str($url['query'], $query);
        }
        if(!empty($this->_query)){
            $query = array_merge($query, $this->_query);
        }
        $url = $url['scheme'] . '://' .
            (isset($url['user']) ? $url['user'] : '') .
            (isset($url['pass']) ? ((isset($url['user']) ? ':' : '') . $url['pass']) : '') .
            ((isset($url['pass']) || isset($url['pass'])) ? '@' : '') .
            $url['host'] .
            (isset($url['port']) ? ':' . $url['port'] : '') .
            (isset($url['path']) ? $url['path'] : '') .
            (!empty($query) ? ('?' . http_build_query($query)) : '') .
            (isset($url['fragment']) ? ('#' . $url['fragment']) : '');
        return $url;
    }
}