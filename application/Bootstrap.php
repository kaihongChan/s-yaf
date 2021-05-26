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

	}

	/**
	 * 路由
	 * @param Yaf_Dispatcher $dispatcher
	 */
	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
	}

	/**
	 *
	 */
	public function _initLocalNamespace()
	{
//		$namespace = array(
//			'Base',
//		);
//		Yaf_Loader::getInstance()->registerLocalNamespace($namespace);
	}

}