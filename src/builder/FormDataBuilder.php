<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */
namespace clearswitch\http\builder;


class FormDataBuilder
{
    /**
     * @var string
     * @author clearswitch
     */
    public $contentType = 'application/x-www-form-urlencoded';


    /**
     * @var int 编码方式
     * @author clearswitch
     */
    public $encodingType = PHP_QUERY_RFC1738;

}