<?php
namespace clearswitch\http\builder;

/**
 * 构建器
 * @author clearSwitch。
 */
abstract class Builder  implements BuilderInterface
{
	/**
	 * @var string 字符集
	 * @author clearSwitch。
	 */
	public $charset = null;

	/**
	 * @var string 消息体类型
	 * @author clearSwitch。
	 */
	public $contentType = null;

	/**
	 * @var array 元素内容
	 * @author clearSwitch。
	 */
	protected $_elements = [];

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function getElements(){
		return $this->_elements;
	}

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function setElements($elements){
		$this->_elements = $elements;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function addElement($name, $value){
		$this->_elements[$name] = $value;
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function removeElement($name){
		unset($this->_elements[$name]);
		return $this;
	}

	/**
	 * @inheritdoc
	 * @author clearSwitch。
	 */
	public function headers(){
		if(!empty($this->contentType)){
			$contentType = $this->contentType;
			if(!empty($this->charset)){
				$contentType .= '; charset=' . $this->charset;
			}
			return [
				'Content-Type' => $contentType
			];
		}
	}
}