<?php
namespace clearswitch\http\parse;
use clearswitch\http\parser\ResponseParserInterface;
/**
 * URL编码解析器
 * @author clearSwitch。
 */
class UrlencodedParser implements ResponseParserInterface
{
	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function can($response){
		$a = strpos($response, '=');
		$b = strpos($response, '&');
		if($a > 0){
			if($b !== false){
				return $b > $a;
			}
			return true;
		}
	}

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function parse($response){
		$data = [];
		parse_str($response, $data);
		return $data;
	}
}