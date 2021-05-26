<?php


class HttpServer
{
	protected $defaultHost = '0.0.0.0';
	protected $defaultPort = '9501';
	protected $serverConfig = [];
	protected $httpServer = null;
	protected $application = null;
	protected static $instance = null;
	protected $appConfigFile = [];

	public static function getInstance(): ?HttpServer
	{
		if (empty(self::$instance) || !(self::$instance instanceof HttpServer)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct()
	{

	}

	/**
	 * @param string $serverConfigIni
	 */
	public function setServerConfigIni(string $serverConfigIni)
	{
		if (!is_file($serverConfigIni)) {
			trigger_error('Server Config File Not Exist!', E_USER_ERROR);
		}
		$serverConfig = parse_ini_file($serverConfigIni, true);
		if (empty($serverConfig)) {
			trigger_error('Server Config Content Empty!', E_USER_ERROR);
		}
		$this->serverConfig = $serverConfig;
	}

	/**
	 * @param string $appConfigIni
	 */
	public function setAppConfigIni(string $appConfigIni)
	{
		if (!is_file($appConfigIni)) {
			trigger_error('Server Config File Not Exist!', E_USER_ERROR);
		}

		$this->appConfigFile = $appConfigIni;
	}

	/**
	 *
	 */
	public function start()
	{
		$ip = $this->serverConfig['server']['ip'] ?? $this->defaultHost;
		$port = $this->serverConfig['server']['port'] ?? $this->defaultPort;

		$this->httpServer = new swoole_http_server($ip, $port);
		$this->httpServer->set($this->serverConfig['swoole']);
		$this->httpServer->on('Start', [$this, 'onStart']);
		$this->httpServer->on('ManagerStart', [$this, 'onManagerStart']);
		$this->httpServer->on('WorkerStart', [$this, 'onWorkerStart']);
		$this->httpServer->on('WorkerStop', [$this, 'onWorkerStop']);
		$this->httpServer->on('request', [$this, 'onRequest']);
		$this->httpServer->start();
	}

	/**
	 * @param swoole_http_server $serverObj
	 * @return bool
	 */
	public function onStart(swoole_http_server $serverObj): bool
	{
		//rename
		swoole_set_process_name($this->serverConfig['server']['master_process_name']);

		return true;
	}

	/**
	 * @param swoole_http_server $serverObj
	 * @return bool
	 */
	public function onManagerStart(swoole_http_server $serverObj): bool
	{
		//rename
		swoole_set_process_name($this->serverConfig['server']['manager_process_name']);

		return true;
	}

	/**
	 * @param swoole_http_server $serverObj
	 * @param $workerId
	 * @return bool
	 * @throws Yaf_Exception_StartupError
	 * @throws Yaf_Exception_TypeError
	 */
	public function onWorkerStart(swoole_http_server $serverObj, $workerId): bool
	{
		//rename
		$processName = sprintf($this->serverConfig['server']['event_worker_process_name'], $workerId);
		swoole_set_process_name($processName);

		//实例化yaf
		$this->application = new Yaf_Application($this->appConfigFile);

		return true;
	}

	public function onWorkerStop(swoole_http_server $serverObj, $workerId): bool
	{
		return true;
	}

	public function onRequest(swoole_http_request $request, swoole_http_response $response)
	{
		//注册全局信息
		$this->initRequestParam($request);
		Yaf_Registry::set('SWOOLE_HTTP_REQUEST', $request);
		Yaf_Registry::set('SWOOLE_HTTP_RESPONSE', $response);

		//执行
		ob_start();
		try {
			$requestObj = new Yaf_Request_Http($request->server['request_uri'], '');

			$configArr = Yaf_Application::app()->getConfig()->toArray();
			if (!empty($configArr['application']['baseUri'])) { //set base_uri
				$requestObj->setBaseUri($configArr['application']['baseUri']);
			}

			$this->application->bootstrap()->getDispatcher()->dispatch($requestObj);
		} catch (Yaf_Exception $e) {
			var_dump($e);
		}

		$result = ob_get_contents();
		ob_end_clean();

		$response->end($result);
	}

	/**
	 * @param swoole_http_request $request
	 * @return bool
	 */
	private function initRequestParam(swoole_http_request $request): bool
	{
		// 将请求的一些环境参数放入全局变量桶中
		$server = isset($request->server) ? $request->server : [];
		$header = isset($request->header) ? $request->header : [];
		$get = isset($request->get) ? $request->get : [];
		$post = isset($request->post) ? $request->post : [];
		$cookie = isset($request->cookie) ? $request->cookie : [];
		$files = isset($request->files) ? $request->files : [];

		Yaf_Registry::set('REQUEST_SERVER', $server);
		Yaf_Registry::set('REQUEST_HEADER', $header);
		Yaf_Registry::set('REQUEST_GET', $get);
		Yaf_Registry::set('REQUEST_POST', $post);
		Yaf_Registry::set('REQUEST_COOKIE', $cookie);
		Yaf_Registry::set('REQUEST_FILES', $files);
		Yaf_Registry::set('REQUEST_RAW_CONTENT', $request->rawContent());

		return true;
	}
}