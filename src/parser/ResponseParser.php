<?php
namespace clearswitch\http\parser;

/**
 * 响应解析器
 * @author clearSwitch。
 */
abstract class ResponseParser implements ResponseParserInterface
{
	/**
	 * 字符集
	 * @author clearSwitch。
	 */
	public $charset = null;
}