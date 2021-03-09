<?php
namespace clearswitch\http\builder;

/**
 * 构建器接口
 * @author clearSwitch。
 */
interface BuilderInterface
{
	/**
	 * 获取元素
	 * @return array
	 * @author clearSwitch。
	 */
	public function getElements();

	/**
	 * 设置元素
	 * @param array $elements 元素
	 * @return BuilderInterface
	 * @author clearSwitch。
	 */
	public function setElements($elements);

	/**
	 * 添加元素
	 * @param string $name 名称
	 * @param string $value 内容
	 * @return BuilderInterface
	 * @author clearSwitch。
	 */
	public function addElement($name, $value);

	/**
	 * 移除元素
	 * @param string $name 名称
	 * @return BuilderInterface
	 * @author clearSwitch。
	 */
	public function removeElement($name);

	/**
	 * 转为字符串
	 * @return string
	 * @author clearSwitch。
	 */
	public function toString();

	/**
	 * 附加的头部
	 * @return array
	 * @author clearSwitch。
	 */
	public function headers();
}