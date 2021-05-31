<?php


class CustomRoute implements Yaf_Route_Interface
{
	/**
	 * <p><b>Yaf_Route_Interface::route()</b> is the only method that a custom route should implement.</p><br/>
	 * <p>if this method return TRUE, then the route process will be end. otherwise, Yaf_Router will call next route in the route stack to route request.</p><br/>
	 * <p>This method would set the route result to the parameter request, by calling Yaf_Request_Abstract::setControllerName(), Yaf_Request_Abstract::setActionName() and Yaf_Request_Abstract::setModuleName().</p><br/>
	 * <p>This method should also call Yaf_Request_Abstract::setRouted() to make the request routed at last.</p>
	 *
	 * @link https://secure.php.net/manual/en/yaf-route-interface.route.php
	 *
	 * @param YafRequestHttp $request
	 * @return bool
	 */
	function route($request)
	{
		$requestUri = $request->getRequestUri();
		if ($requestUri == '/') {
			return false;
		}
		$path = str_replace($request->getBaseUri(), '', $requestUri);
		[, $request->module, $request->controller, $request->action] = array_pad(explode('/', $path), 4, null);

		if (!$request->module) {
			throw new Yaf_Exception('Bad Request', 400);
		}
		if (!in_array(ucwords($request->module), Yaf_Application::app()->getModules())) {
			throw new Yaf_Exception('Module Not Found', 404);
		}
		if (!$request->controller) {
			throw new Yaf_Exception('Bad Request', 400);
		}
		if (is_file(APPLICATION_PATH . '/controllers/' . $request->controller . '.php') ||
			is_file(APPLICATION_PATH . '/modules/' . $request->module . '/controllers/' . $request->controller . '.php')
		) {
			return true;
		} else {
			throw new Yaf_Exception('Controller Not Found', 404);
		}
	}

	/**
	 * <p><b>Yaf_Route_Interface::assemble()</b> - assemble a request<br/>
	 * <p>this method returns a url according to the argument info, and append query strings to the url according to the argument query.</p>
	 * <p>a route should implement this method according to its own route rules, and do a reverse progress.</p>
	 *
	 * @link https://secure.php.net/manual/en/yaf-route-interface.assemble.php
	 *
	 * @param array $info
	 * @param array $query
	 * @return bool
	 */
	function assemble(array $info, array $query = null)
	{
		return true;
	}
}