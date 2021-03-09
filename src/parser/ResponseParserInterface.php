<?php
namespace clearswitch\http\parser;

/**
 * 响应解析器接口
 * @author clearSwitch。
 */
interface ResponseParserInterface
{
	/**
	 * 是否可以解析
	 * @param string $response 响应内容
	 * @return bool
	 * @author clearSwitch。
	 */
	public function can($response);

	/**
	 * 解析
	 * @param string $response 响应内容
	 * @return array|bool
	 * @author clearSwitch。
	 */
	public function parse($response);
}