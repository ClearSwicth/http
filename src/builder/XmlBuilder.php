<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */

namespace http\builder;


class XmlBuilder
{
    /**
     * @var string  XML 版本
     */
    public $version = '1.0';


    /**
     * @var string
     */
    public $rootTag = 'request';


    /**
     * @var string
     */
    public $itemTag = 'item';


    /**
     * @var string
     */
    public $contentType = 'application/xml';


    /**
     * @var bool
     */
    public $useTraversableAsArray = true;


}