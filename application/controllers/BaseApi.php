<?php


abstract class BaseApiController extends Yaf_Controller_Abstract
{
    /**
     * Method init
     */
    public function init()
    {
        $this->disableView();
    }

    /**
     * 关闭自动渲染
     */
    public function disableView()
    {
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function response($msg = '', $status = true, $data = [], $code = 200)
    {
        echo json_encode([
            'code'   => $code,
            'status' => $status,
            'msg'    => $msg,
            'data'   => $data,
        ]);
    }
}