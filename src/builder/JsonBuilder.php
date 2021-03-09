<?php
/**
 *
 * User: daikai
 * Date: 2021/3/4
 */
namespace clearswitch\http\builder;


class JsonBuilder extends Builder
{
    /**
     * @var string
     * @author clearswitch
     */
    public  $contentType="application/json";

    /**
     * @inheritdoc
     * @author clearSwitch。
     */
    public function toString(){
        return json_encode($this->getElements());
    }

}