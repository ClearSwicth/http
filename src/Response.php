<?php
/**
 *
 * User: clearswitch
 * Date: 2021/3/8
 */

namespace clearswitch\http;

use clearswitch\http\parser\ResponseParserInterface;

class Response
{
    /**
     * @var array 内建解析器
     * @author clearswitch。
     */
    const BUILT_IN_PARSERS = [
        'application/json' => 'clearswitch\http\parser\JsonParser',
        'application/x-www-form-urlencoded' => 'clearswitch\http\parser\UrlencodedParser',
        'application/xml' => 'clearswitch\http\parser\XmlParser',
    ];

    /**
     * @var bool 是否尝试解析
     * @author clearswitch。
     */
    public $tryParse = true;

    /**
     * @var Request 请求对象
     * @author clearswitch。
     */
    public $request;

    /**
     * @var array 解析器
     * @author clearswitch。
     */
    public $parsers = [];

    /**
     * @var int 状态码
     * @author clearswitch。
     */
    protected $_statusCode = null;

    /**
     * @var string 状态消息
     * @author clearswitch。
     */
    protected $_statusMessage = null;

    /**
     * @var string HTTP版本
     * @author clearswitch。
     */
    protected $_httpVersion = null;

    /**
     * @var string 原始响应
     * @author clearswitch。
     */
    protected $_rawReponse = null;

    /**
     * @var string 原始头部
     * @author clearswitch。
     */
    protected $_rawHeaders = null;

    /**
     * @var string 原始消息体
     * @author clearswitch。
     */
    protected $_rawContent = null;

    /**
     * @var mixed 消息体
     * @author clearswitch。
     */
    protected $_body = false;

    /**
     * @var array 头部信息
     * @author clearswitch。
     */
    protected $_headers = false;

    /**
     * @var string 消息体类型
     * @author clearswitch。
     */
    protected $_contentType = false;

    /**
     * @var string 字符集
     * @author clearswitch。
     */
    protected $_charset = false;

    /**
     * @inheritdoc
     * @author clearswitch。
     */
    public function __construct($config = [], $status, $headers, $content, $response)
    {
        //parent::__construct($config);
        $this->parsers = array_merge(static::BUILT_IN_PARSERS, $this->parsers);
        $status = explode(' ', $status);
        if (count($status) > 2) {
            $this->_httpVersion = array_shift($status);
            $this->_statusCode = (int)array_shift($status);
            $this->_statusMessage = implode(' ', $status);
        }
        $this->_rawHeaders = $headers;
        $this->_rawContent = $content;
        $this->_rawReponse = $response;
    }

    /**
     * 获取解析器
     * @param string $name 名称
     * @param string $charset 字符集
     * @return ResponseParserInterface|bool
     * @author clearswitch。
     */
    protected function getParser($name, $charset = null)
    {
        $parser = strtolower($name);
        $parser = isset($this->parsers[$parser]) ? $this->parsers[$parser] : null;
        if ($parser) {
            $parser = ObjectHelper::create($parser);
            if (!$parser instanceof ResponseParserInterface) {
                throw new \Exception('parser must implements ' . ResponseParserInterface::class);
            }
            if (!empty($charset)) {
                $parser->charset = $charset;
            }
            return $parser;
        }
        return false;
    }

    /**
     * 获取响应
     * @return string
     * @author clearswitch。
     */
    public function getRawResponse()
    {
        return $this->_rawReponse;
    }

    /**
     * 获得请求的参数
     * @param Request $request
     * @return string
     * @author daikai
     */
    public function getRequestData()
    {
        return (new request())->getRequestData();
    }

    /**
     * 获取消息体
     * @return string
     * @author clearswitch。
     */
    public function getRawContent()
    {
        return $this->_rawContent;
    }

    /**
     * 获取原始头部
     * @return string
     * @author clearswitch。
     */
    public function getRawHeaders()
    {
        return $this->_rawHeaders;
    }

    /**
     * 获取消息体
     * @return array|string|null
     * @author clearswitch。
     */
    public function getBody()
    {
        if ($this->_body === false) {
            $this->_body = null;
            if ($this->_rawContent) {
                $this->_body = $this->_rawContent;
                $content = $this->_rawContent;
                if (ord(substr($content, 0, 1)) === 239 && ord(substr($content, 1, 1)) === 187 && ord(substr($content, 2, 1)) === 191) {
                    $content = substr($content, 3);
                }
                $parsed = false;
                $parser = $this->getParser($this->getContentType(), $this->getCharset());
                if ($parser) {
                    $body = $parser->parse($content);
                    if ($body) {
                        $parsed = true;
                        $this->_body = $parser->parse($content);
                    }
                }
                if (!$parsed && $this->tryParse === true) {
                    foreach (array_keys($this->parsers) as $name) {
                        $parser = $this->getParser($name);
                        if ($parser->can($content)) {
                            try {
                                $body = $parser->parse($content);
                                if ($body) {
                                    $this->_body = $parser->parse($content);
                                    break;
                                }
                            } catch (\Exception $e) {
                            } catch (\Error $e) {
                            }
                        }
                    }
                }
            }
        }
        return $this->_body;
    }

    /**
     * 获取头部
     * @return array|null
     * @author clearswitch。
     */
    public function getHeaders()
    {
        if ($this->_headers === false) {
            $this->_headers = null;
            if ($this->_rawHeaders) {
                $this->_headers = [];
                $headers = explode("\r\n", $this->_rawHeaders);
                foreach ($headers as $header) {
                    if ($header) {
                        $header = explode(': ', $header);
                        if (isset($header[1])) {
                            if (isset($this->_headers[$header[0]])) {
                                if (!is_array($this->_headers[$header[0]])) {
                                    $this->_headers[$header[0]] = [$this->_headers[$header[0]]];
                                }
                                $this->_headers[$header[0]][] = $header[1];
                            } else {
                                $this->_headers[$header[0]] = $header[1];
                            }
                        }
                    }
                }
            }
        }
        return $this->_headers;
    }

    /**
     * 获取Cookies
     * @return array
     * @author clearswitch。
     */
    public function getCookies()
    {
        $result = [];
        $headers = $this->getHeaders();
        if (isset($headers['Set-Cookie'])) {
            if ($cookies = $headers['Set-Cookie']) {
                if (!is_array($cookies)) {
                    $cookies = [$cookies];
                }
                foreach ($cookies as $cookie) {
                    $cookie = $this->parseCookie($cookie);
                    $result[$cookie['key']] = $cookie;
                }
            }
        }
        return $result;
    }

    /**
     * 解析Cookie
     * @param string $cookie cookie
     * @return array
     * @author clearswitch。
     */
    public function parseCookie($cookie)
    {
        $cookie = explode('; ', $cookie);
        $keyValue = explode('=', $cookie[0]);
        unset($cookie[0]);
        $result['key'] = $keyValue[0];
        $result['value'] = urldecode($keyValue[1]);
        foreach ($cookie as $element) {
            $elements = explode('=', $element);
            $name = strtolower($elements[0]);
            if (count($elements) === 2) {
                $result[$name] = $elements[1];
            } else {
                $result[$name] = true;
            }
        }
        return $result;
    }

    /**
     * 获取消息体类型
     * @return string
     * @author clearswitch。
     */
    public function getContentType()
    {
        if ($this->_contentType === false) {
            $this->_contentType = null;
            $header = $this->getHeaders();
            if (isset($header['Content-Type'])) {
                $this->_contentType = explode(';', $header['Content-Type'])[0];
            }
        }
        return $this->_contentType;
    }

    /**
     * 获取字符集
     * @return string
     * @author clearswitch。
     */
    public function getCharset()
    {
        if ($this->_charset === false) {
            $this->_charset = null;
            $header = $this->getHeaders();
            if (isset($header['Content-Type'])) {
                if (preg_match('/charset=(.*)/i', $header['Content-Type'], $matches)) {
                    $this->_charset = $matches[1];
                }
            }
        }
        return $this->_charset;
    }

    /**
     * 获取状态码
     * @return int
     * @author clearswitch。
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * 获取状态消息
     * @return int
     * @author clearswitch。
     */
    public function getStatusMessage()
    {
        return $this->_statusMessage;
    }

    /**
     * 获取HTTP版本
     * @return int
     * @author clearswitch。
     */
    public function getHttpVersion()
    {
        return $this->_httpVersion;
    }
}