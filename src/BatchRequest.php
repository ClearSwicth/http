<?php
namespace clearswitch\http;
use clearswitch\http\transport\TransportInterface;
use clearswitch\http\builder\BuilderInterface;

/**
 * 批量请求
 * @author clearSwitch。
 */
class BatchRequest
{
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
	 * 批大小
	 * @author clearSwitch。
	 */
	public $batchSize = 100;

	/**
	 * @var string 传输通道
	 * @author clearSwitch。
	 */
	public $transport = 'cUrl';

	/**
	 * @var array 传输通道
	 * @author clearSwitch。
	 */
	public $transports = [
        'cUrl' => 'clearswitch\http\transport\CUrlTransport',
        'coroutine' => 'clearswitch\http\transport\CoroutineTransport',
        'stream' => 'clearswitch\http\transport\StreamTransport'
    ];

	/**
	 * @var array 请求集合
	 * @author clearSwitch。
	 */
	protected $requests = [];

	/**
	 * @var TransportInterface 传输通道
	 * @author clearSwitch。
	 */
	protected $_transport = null;

	/**
	 * 设置请求
	 * @param array $requests 请求集合
	 * @param int $batchSize 批大小
	 * @return BatchRequest
	 * @author clearSwitch。
	 */
	public function setRequests($requests, $batchSize = null){
		if(!$batchSize){
			$batchSize = $this->batchSize;
		}
		$this->requests = array_chunk($requests, $batchSize, true);
		return $this;
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
	 * 发送请求
	 * @return array
	 */
	public function send(){
		$responses = [];
		foreach($this->requests as $requests){
			foreach($requests as $request){
				$request->prepare();
			}
			foreach($this->getTransport()->batchSend($requests) as $key => $result){
				list($statusCode, $headers, $content, $response) = $result;
				$request = $requests[$key];
				$responses[$key] = ObjectHelper::create([
					'class' => $request::responseClass(),
					'request' => $request,
					'tryParse' => $request->tryParse,
					'parsers' => $request->parsers
				], $statusCode, $headers, $content, $response);
			}
		}
		return $responses;
	}
}