<?php


class HttpServer
{
	protected $defaultHost = '0.0.0.0';
	protected $defaultPort = '9501';
	protected $serverConfig = [];
    protected $appConfigFile = '';

    /**
     * @var swoole_http_server
     */
	public $httpServer = null;

    /**
     * @var Yaf_Application
     */
	protected $application = null;

    /**
     * @var HttpServer
     */
	protected static $instance = null;

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
	 * 服务启动
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
	 * @param int $workerId
	 * @return bool
	 * @throws Yaf_Exception_StartupError
	 * @throws Yaf_Exception_TypeError
	 */
	public function onWorkerStart(swoole_http_server $serverObj, int $workerId): bool
	{
		// rename
		$processName = sprintf($this->serverConfig['server']['event_worker_process_name'], $workerId);
		swoole_set_process_name($processName);

		// 实例化yaf
		$this->application = new Yaf_Application($this->appConfigFile);
		$this->application->bootstrap();

		return true;
	}

	public function onWorkerStop(swoole_http_server $serverObj, $workerId): bool
	{
		return true;
	}

	public function onRequest(swoole_http_request $request, swoole_http_response $response)
	{
	    // 兼容chrome浏览器
        if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
            $response->end();
            return;
        }

		// 注册全局信息
		$this->initRequestParam($request);
        $response->header('Content-Type', 'application/json');
		// 执行
		ob_start();
		try {
			$requestObj = new Yaf_Request_Http($request->server['request_uri'], '/');

			$configArr = Yaf_Application::app()->getConfig()->toArray();
			if (!empty($configArr['application']['baseUri'])) { //set base_uri
				$requestObj->setBaseUri($configArr['application']['baseUri']);
			}

			$this->application->getDispatcher()->dispatch($requestObj);
		} catch (Yaf_Exception $e) {
			var_dump($e->getMessage());
		}

        $result = ob_get_contents();
		ob_end_clean();

		$response->end($result);
	}

	/**
	 * @param swoole_http_request $request
	 * @return void
     */
	private function initRequestParam(swoole_http_request $request): void
    {
		// 将请求的一些环境参数放入全局变量桶中
		$server = $request->server ?? [];
		$header = $request->header ?? [];
		$get = $request->get ?? [];
		$post = $request->post ?? [];
		$cookie = $request->cookie ?? [];
		$files = $request->files ?? [];

		Yaf_Registry::set('REQUEST_SERVER', $server);
		Yaf_Registry::set('REQUEST_HEADER', $header);
		Yaf_Registry::set('REQUEST_GET', $get);
		Yaf_Registry::set('REQUEST_POST', $post);
		Yaf_Registry::set('REQUEST_COOKIE', $cookie);
		Yaf_Registry::set('REQUEST_FILES', $files);
		Yaf_Registry::set('REQUEST_RAW_CONTENT', $request->rawContent());

    }
}