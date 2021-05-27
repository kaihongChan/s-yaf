<?php

class AuthPlugin extends Yaf_Plugin_Abstract
{
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* 在路由之前执行,这个钩子里，你可以做url重写等功能 */

    }
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* 路由完成后，在这个钩子里，你可以做登陆检测等功能*/

    }
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }
    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {

    }
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {
        /* final hook in this hook user can do loging or implement layout */

    }
}
