<?php


class HttpServer
{
	protected $defaultHost = '0.0.0.0';
	protected $defaultPort = '9501';
	protected $serverConfig = [];
	protected $appConfigFile = '';
	protected $httpStatus = 200;

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

	const HTTP_STATUS = [
		// Informational 1xx
		100 => 'Continue',
		101 => 'Switching Protocols',
		// Success 2xx
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// Redirection 3xx
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily ',  // 1.1
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		// 306 is deprecated but reserved
		307 => 'Temporary Redirect',
		// Client Error 4xx
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		// Server Error 5xx
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		509 => 'Bandwidth Limit Exceeded',
	];

	/**
	 * 获取httpServer实例
	 * @return HttpServer|null
	 */
	public static function getInstance(): ?HttpServer
	{
		if (empty(self::$instance) || !(self::$instance instanceof HttpServer)) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * HttpServer constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * 设置server配置
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
	 * 设置app配置
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

	/**
	 * 请求事件回调
	 * @param swoole_http_request $request
	 * @param swoole_http_response $response
	 */
	public function onRequest(swoole_http_request $request, swoole_http_response $response)
	{
		// 兼容chrome浏览器
		if ($request->server['path_info'] == '/favicon.ico' || $request->server['request_uri'] == '/favicon.ico') {
			$response->end();

			return;
		}

		// 注册全局信息
		$this->initRequestParam($request);

		// 执行
		ob_start();
		try {
			$requestObj = new Yaf_Request_Http($request->server['request_uri'], '');

			$configArr = Yaf_Application::app()->getConfig()->toArray();
			if (!empty($configArr['application']['baseUri'])) { // set base_uri
				$requestObj->setBaseUri($configArr['application']['baseUri']);
			}

			$this->application->getDispatcher()->dispatch($requestObj);

		} catch (Throwable $e) {
			$this->throwableHandle($e);
		}

		$result = ob_get_contents();
		ob_end_clean();

		// 响应状态码
		$response->status($this->httpStatus);
		// 响应头信息设置
		$response->header('Content-Type', 'application/json; charset=utf-8');

		$response->end($result);
	}

	/**
	 * 注册请求环境参数
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

	/**
	 * 异常处理
	 * @param Throwable $e
	 */
	private function throwableHandle(Throwable $e)
	{
		$exceptionCode = $e->getCode();
		switch ($exceptionCode) {
			case YAF_ERR_NOTFOUND_MODULE:
			case YAF_ERR_NOTFOUND_CONTROLLER:
			case YAF_ERR_NOTFOUND_ACTION:
			case YAF_ERR_NOTFOUND_VIEW:
				$this->httpStatus = 404;
				break;
			// TODO 其他异常。。。
			default:
				$this->httpStatus = 500;
				break;
		}

		$appConfig = $this->application->getConfig()->toArray();
		if (isset($appConfig['application']['environment']) && $appConfig['application']['environment'] == 'development') {
			$msg = $e->getMessage() . '; Trace: ' . $e->getTraceAsString();
		} else {
			$msg = self::HTTP_STATUS[$this->httpStatus];
		}

		echo json_encode([
			'code' => $this->httpStatus,
			'msg' => $msg,
			'status' => false,
			'data' => null,
		]);
	}
}