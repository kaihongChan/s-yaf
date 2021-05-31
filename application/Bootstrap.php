<?php


class Bootstrap extends Yaf_Bootstrap_Abstract
{
    /**
     * 配置信息
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initConfig(Yaf_Dispatcher $dispatcher)
    {
        $arrConfig = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $arrConfig);
        $dispatcher->disableView();
    }

    /**
     * 插件
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new SignPlugin());
    }

    /**
     * 路由
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {
        //在这里注册自己的路由协议,默认使用简单路由
	    $router = $dispatcher->getRouter();

	    $router->addRoute('customRoute', new CustomRoute());
    }

    /**
     *
     */
    public function _initLocalNamespace()
    {
        $namespace = [
            'cache',
            'db',
        ];
        Yaf_Loader::getInstance()->registerLocalNamespace($namespace);
    }

    public function _initCommonFunction()
    {
        // 导入自定义函数
        Yaf_Loader::import(APPLICATION_PATH . DS . "functions.php");
    }

    /**
     * Mysql
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initRedis(Yaf_Dispatcher $dispatcher)
    {
//        $redis = \cache\Redis::getInstance();
    }

}