<?php

/**
 * The base class for ecc.
 */
class ecc
{

	/* @var modX $modx */
	public $modx;
	/** @var string $namespace */
	public $namespace = 'ecc';
	/* @var array The array of config */
	public $config = array();
	/** @var array $opts */
	public $opts = array(
		'namespace' => null,
		'controller' => null,
		'location' => null,
		'path' => null,
		'action' => null,
	);
	/** @var array $initialized */
	public $initialized = array();
	/** @var bool $baseController */
	public $isBaseController = false;

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array())
	{
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('ecc_core_path', $config, $this->modx->getOption('core_path') . 'components/ecc/');
		$assetsUrl = $this->modx->getOption('ecc_assets_url', $config, $this->modx->getOption('assets_url') . 'components/ecc/');
		$connectorUrl = $assetsUrl . 'connector.php';
		$assetsPath = MODX_ASSETS_PATH;

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,
			'actionUrl' => $assetsUrl . 'action.php',

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/',

			'controllersPath' => $corePath . 'controllers/',
			'assetsCachePath' => $assetsPath . 'components/easycontroller/cache/',
			'assetsCacheUrl' => $assetsUrl . 'cache/',

			'prepareResponse' => true,
			'jsonResponse' => true,

		), $config);

		$this->modx->addPackage('ecc', $this->config['modelPath']);
		$this->modx->lexicon->load('ecc:default');
		$this->namespace = $this->getOption('namespace', $config, 'ecc');
	}

	/**
	 * @param $n
	 * @param array $p
	 */
	public function __call($n, array$p)
	{
		echo __METHOD__ . ' says: ' . $n;
	}

	/**
	 * @param $key
	 * @param array $config
	 * @param null $default
	 * @return mixed|null
	 */
	public function getOption($key, $config = array(), $default = null)
	{
		$option = $default;
		if (!empty($key) AND is_string($key)) {
			if ($config != null AND array_key_exists($key, $config)) {
				$option = $config[$key];
			} elseif (array_key_exists($key, $this->config)) {
				$option = $this->config[$key];
			} elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
				$option = $this->modx->getOption("{$this->namespace}_{$key}");
			}
		}
		return $option;
	}

	/**
	 * Initializes component into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties
	 * @return boolean
	 */
	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;

		if (!empty($this->initialized[$ctx])) {
			return true;
		}

		switch ($ctx) {
			case 'mgr':
				break;
			default:
				if (!defined('MODX_API_MODE') OR !MODX_API_MODE) {
					$config = $this->modx->toJSON(array(
						'assetsUrl' => $this->config['assetsUrl'],
						'actionUrl' => $this->config['actionUrl'],
					));
					$this->modx->regClientStartupScript(preg_replace('#(\n|\t)#', '', '
							<script type="text/javascript">
							eccConfig=' . $config . '
							</script>
						'), true);
					$this->modx->regClientScript(preg_replace('#(\n|\t)#', '', '
							<script type="text/javascript">
							if (typeof jQuery == "undefined") {
								document.write("<script src=\"' . $this->config['assetsUrl'] . 'vendor/jquery/jquery.min.js\" type=\"text/javascript\"><\/script>");
							}
							</script>
						'), true);
					$this->initialized[$ctx] = true;
				}
				break;
		}

		return true;
	}

	/**
	 * Loads an instance of baseController
	 * @return boolean
	 */
	public function loadBaseController()
	{
		$baseControllerClass = $this->getOption('defaultClassBaseController', null, 'eccBaseController');
		if (!$this->isBaseController OR !class_exists($baseControllerClass)) {
			$this->isBaseController = $this->modx->loadClass($baseControllerClass, $this->config['controllersPath'], true, true);
		}
		return !empty($this->isBaseController) AND class_exists($baseControllerClass);
	}

	public function loadController()
	{
		if (!$this->isBaseController) {
			$this->loadBaseController();
		}

		$baseControllerClass = $this->getOption('defaultClassBaseController', null, 'eccBaseController');
		if (!$namespace = $this->modx->getObject('modNamespace', array('name' => $this->opts['namespace']))) {
			$this->modx->log(modX::LOG_LEVEL_ERROR, "[ecc] Not found modNamespace: {$this->opts['namespace']}");
			return false;
		}

		$locationControllerClass = $this->getOption('location', null, 0);
		$path = empty($locationControllerClass) ? $this->getOption('corePath', null, '') : $namespace->getCorePath();
		if ($class = $this->modx->loadClass($this->opts['path'], $path, true, true)) {
			$controller = new $class($this->modx, $this->config);
			if ($controller instanceof $baseControllerClass AND $controller->initialize()) {
				return $controller;
			} else {
				$this->modx->log(modX::LOG_LEVEL_ERROR, "[ecc] Controller in {$class} must be an instance of {$baseControllerClass}");
			}
		}

		return false;
	}

	public function handleRequest(array $request = array())
	{
		foreach (array_keys($this->opts) as $k) {
			$this->opts[$k] = strtolower(trim(trim(str_replace('/', '.', $request[$k]), '.')));
		}
		/** @var eccBaseController $controller */
		if ($controller = $this->loadController()) {

			switch (true) {
				case (strtolower($this->opts['controller']) == 'default'):
					$this->opts['controller'] = $controller->getDefaultAction();
					break;
				case (empty($this->opts['controller']) AND empty($this->opts['action'])):
					$this->opts['controller'] = $controller->getDefaultAction();
					break;
				case (empty($this->controller) AND !empty($this->opts['action'])):
					$this->opts['controller'] = $controller->getDefaultProcessorAction();
					break;
			}

			$controller->setConfig(array_merge($this->config, $request, array('opts' => $this->opts)));
			if (method_exists($controller, $this->opts['controller'])) {
				return $controller->{$this->opts['controller']}($request);
			} else {
				return $this->failure("Could not find method {$this->opts['controller']}", $this->opts);
			}
		}

		return $this->failure("Could not load controller {$this->opts['controller']}", $this->opts);
	}

	/**
	 * Shorthand for the call of processor
	 *
	 * @param string $action Path to processor
	 * @param array $data Data to be transmitted to the processor
	 * @return mixed The result of the processor
	 */
	public function runProcessor($action = '', $data = array(), $json = true)
	{
		if (empty($action)) {
			return false;
		}
		$this->modx->error->reset();
		/* @var modProcessorResponse $response */
		$response = $this->modx->runProcessor($action, $data, array('processors_path' => $this->config['processorsPath']));

		if (!$json) {
			$this->setJsonResponse(false);
		}
		$result = $this->config['prepareResponse'] ? $this->prepareResponse($response) : $response;
		$this->setJsonResponse();
		return $result;
	}

	/**
	 * This method returns prepared response
	 *
	 * @param mixed $response
	 * @return array|string $response
	 */
	public function prepareResponse($response)
	{
		if ($response instanceof modProcessorResponse) {
			$output = $response->getResponse();
		} else {
			$message = $response;
			if (empty($message)) {
				$message = $this->lexicon('err_unknown');
			}
			$output = $this->failure($message);
		}
		if ($this->config['jsonResponse'] AND is_array($output)) {
			$output = $this->modx->toJSON($output);
		} elseif (!$this->config['jsonResponse'] AND !is_array($output)) {
			$output = $this->modx->fromJSON($output);
		}
		return $output;
	}

	/**
	 * from https://github.com/bezumkin/pdoTools/blob/f947b2abd9511919de56cbb85682e5d0ef52ebf4/core/components/pdotools/model/pdotools/pdotools.class.php#L282
	 *
	 * Transform array to placeholders
	 *
	 * @param array $array
	 * @param string $plPrefix
	 * @param string $prefix
	 * @param string $suffix
	 * @param bool $uncacheable
	 * @return array
	 */
	public function makePlaceholders(array $array = array(), $plPrefix = '', $prefix = '[[+', $suffix = ']]', $uncacheable = true)
	{
		$result = array('pl' => array(), 'vl' => array());
		$uncached_prefix = str_replace('[[', '[[!', $prefix);
		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$result = array_merge_recursive($result, $this->makePlaceholders($v, $plPrefix . $k . '.', $prefix, $suffix, $uncacheable));
			} else {
				$pl = $plPrefix . $k;
				$result['pl'][$pl] = $prefix . $pl . $suffix;
				$result['vl'][$pl] = $v;
				if ($uncacheable) {
					$result['pl']['!' . $pl] = $uncached_prefix . $pl . $suffix;
					$result['vl']['!' . $pl] = $v;
				}
			}
		}
		return $result;
	}

	/**
	 * return lexicon message if possibly
	 *
	 * @param $message
	 * @param array $placeholders
	 * @return string
	 */
	public function lexicon($message, $placeholders = array())
	{
		$key = '';
		if ($this->modx->lexicon->exists($message)) {
			$key = $message;
		} elseif ($this->modx->lexicon->exists($this->namespace . '_' . $message)) {
			$key = $this->namespace . '_' . $message;
		}
		if ($key !== '') {
			$message = $this->modx->lexicon->process($key, $placeholders);
		}
		return $message;
	}

	/**
	 * @param string $message
	 * @param array $data
	 * @param array $placeholders
	 * @return array|string
	 */
	public function failure($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			'message' => $this->lexicon($message, $placeholders),
			'data' => $data,
		);
		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

	/**
	 * @param string $message
	 * @param array $data
	 * @param array $placeholders
	 * @return array|string
	 */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->lexicon($message, $placeholders),
			'data' => $data,
		);
		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

	/**
	 * @param bool $json
	 * @return bool
	 */
	public function setJsonResponse($json = true)
	{
		return ($this->config['jsonResponse'] = $json);
	}

}