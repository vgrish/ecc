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
		'namespace'  => null,
		'controller' => null,
		'location'   => null,
		'path'       => null,
		'action'     => null,
	);
	/** @var array $initialized */
	public $initialized = array();
	/** @var bool $baseController */
	public $isBaseController = false;

	/**
	 * @param modX  $modx
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
			'assetsUrl'       => $assetsUrl,
			'cssUrl'          => $assetsUrl . 'css/',
			'jsUrl'           => $assetsUrl . 'js/',
			'imagesUrl'       => $assetsUrl . 'images/',
			'connectorUrl'    => $connectorUrl,
			'actionUrl'       => $assetsUrl . 'action.php',

			'corePath'        => $corePath,
			'modelPath'       => $corePath . 'model/',
			'chunksPath'      => $corePath . 'elements/chunks/',
			'templatesPath'   => $corePath . 'elements/templates/',
			'snippetsPath'    => $corePath . 'elements/snippets/',
			'processorsPath'  => $corePath . 'processors/',

			'controllersPath' => $corePath . 'controllers/',
			'assetsCachePath' => $assetsPath . 'components/ecc/cache/',
			'assetsCacheUrl'  => $assetsUrl . 'cache/',

			'prepareResponse' => true,
			'jsonResponse'    => true,

		), $config);

		$this->modx->addPackage('ecc', $this->config['modelPath']);
		$this->modx->lexicon->load('ecc:default');
		$this->namespace = $this->getOption('namespace', $config, 'ecc');
	}

	/**
	 * @param       $n
	 * @param array $p
	 */
	public function __call($n, array$p)
	{
		echo __METHOD__ . ' says: ' . $n;
	}

	/**
	 * @param       $key
	 * @param array $config
	 * @param null  $default
	 *
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
	 * @param array  $scriptProperties
	 *
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
	 *
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

	/**
	 * @return bool
	 */
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

		$locationControllerClass = $this->getOption('location', $this->opts, 0);
		$path = empty($locationControllerClass) ? $this->getOption('corePath', $this->opts, '') : $namespace->getCorePath();
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

	/**
	 * @param array $request
	 *
	 * @return array|string
	 */
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
				case (empty($this->opts['controller']) AND !empty($this->opts['action'])):
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
	 * @access public
	 *
	 * @param string $action Path to processor
	 * @param array  $data Data to be transmitted to the processor
	 *
	 * @return mixed The result of the processor
	 */
	public function runProcessor($action = '', $data = array(), $json = true, $path = '')
	{
		if (empty($action)) {
			return false;
		}

		$this->modx->error->reset();
		/* @var modProcessorResponse $response */
		$response = $this->modx->runProcessor($action, $data, array(
			'processors_path' => !empty($path) ? $path : $this->config['processorsPath']
		));

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
	 *
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
	 * from
	 * https://github.com/bezumkin/pdoTools/blob/f947b2abd9511919de56cbb85682e5d0ef52ebf4/core/components/pdotools/model/pdotools/pdotools.class.php#L282
	 *
	 * Transform array to placeholders
	 *
	 * @param array  $array
	 * @param string $plPrefix
	 * @param string $prefix
	 * @param string $suffix
	 * @param bool   $uncacheable
	 *
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
	 * from
	 * https://github.com/bezumkin/Office/blob/0115a0f6998ec8c4176eb9f58a2111570f7a6a0a/core/components/office/model/office/office.class.php#L279
	 *
	 * Merges and minimizes given scripts or css and returns raw content
	 *
	 * @param array $files
	 *
	 * @return mixed|bool
	 */
	public function Minify($files = array())
	{
		if (empty($files)) {
			return false;
		}
		$_GET['f'] = implode(',', $files);
		$min_libPath = MODX_MANAGER_PATH . 'min/lib';
		@set_include_path($min_libPath . PATH_SEPARATOR . get_include_path());
		if (!class_exists('Minify')) {
			/** @noinspection PhpIncludeInspection */
			require_once MODX_MANAGER_PATH . 'min/lib/Minify.php';
		}
		if (!class_exists('Minify_Controller_MinApp')) {
			/** @noinspection PhpIncludeInspection */
			require_once MODX_MANAGER_PATH . 'min/lib/Minify/Controller/MinApp.php';
		}
		$min_serveController = new Minify_Controller_MinApp();
		// attempt to prevent suhosin issues
		@ini_set('suhosin.get.max_value_length', 4096);
		$min_serveOptions = array(
			'quiet'        => true,
			'encodeMethod' => '',
		);
		if (!file_exists(MODX_CORE_PATH . 'cache/minify')) {
			mkdir(MODX_CORE_PATH . 'cache/minify');
		}
		Minify::setCache(MODX_CORE_PATH . 'cache/minify');
		$content = array();
		foreach ($files as $file) {
			if (strpos($file, MODX_BASE_PATH) === false) {
				$file = MODX_BASE_PATH . ltrim($file, '/');
				$content[] = file_get_contents($file);
			}
		}
		$content = implode("\n", $content);

		return $content;
	}

	/**
	 * Merges, minimizes and registers javascript for use in controllers
	 *
	 * @param array  $files
	 * @param string $file
	 *
	 * @return bool
	 */
	public function addClientJs($files = array(), $file = 'main/all')
	{
		if (empty($files)) {
			return false;
		}
		if (!preg_match('#.*?\.js#i', $file)) {
			$file .= '.js';
		}
		if (
			!file_exists(MODX_BASE_PATH . ltrim($this->getCacheUrl() . $file, '/')) AND
			$js = $this->Minify($files)
		) {
			$this->saveFile($file, $js);
		}
		$this->modx->regClientScript($this->getCacheUrl() . $file);

		return true;
	}

	/**
	 * Merges, minimizes and registers css for use in controllers
	 *
	 * @param array  $files
	 * @param string $file
	 *
	 * @return bool
	 */
	public function addClientCss($files = array(), $file = 'main/all')
	{
		if (empty($files)) {
			return false;
		}
		if (!preg_match('#.*?\.css#i', $file)) {
			$file .= '.css';
		}
		if (
			!file_exists(MODX_BASE_PATH . ltrim($this->getCacheUrl() . $file, '/')) AND
			$css = $this->Minify($files)
		) {
			$this->saveFile($file, $css);
		}
		$this->modx->regClientCSS($this->getCacheUrl() . $file);

		return true;
	}

	/**
	 * from
	 * https://github.com/bezumkin/Office/blob/0115a0f6998ec8c4176eb9f58a2111570f7a6a0a/core/components/office/model/office/office.class.php#L389
	 *
	 * Registers lexicon entries for use in controllers
	 *
	 * @param array  $topics
	 * @param string $file
	 *
	 * @return bool
	 */
	public function addClientLexicon($topics = array(), $file = 'main/lexicon')
	{
		if (empty($topics)) {
			return false;
		}
		if (!preg_match('#.*?\.js#i', $file)) {
			$file .= '.js';
		}
		if (!file_exists(MODX_BASE_PATH . ltrim($this->getCacheUrl() . $file, '/'))) {
			$topics = array_merge(array('core:default'), $topics);
			foreach ($topics as $topic) {
				$this->modx->lexicon->load($topic);
			}
			$entries = $this->modx->lexicon->fetch();
			$lang = '
			eccLexicon = {';
			$s = '';
			while (list($k, $v) = each($entries)) {
				$s .= "'$k': " . '"' . strtr($v, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/')) . '",';
			}
			$s = trim($s, ',');
			$lang .= $s . '
			};
			var _ = function(s,v) {
				return eccLexicon[s]
				if (v != null && typeof(v) == "object") {
					var t = ""+eccLexicon[s];
					for (var k in v) {
						t = t.replace("[[+"+k+"]]",v[k]);
					}
					return t;
				} else return eccLexicon[s];
			}';
			$lang = str_replace('			', '', $lang);
			$this->saveFile($file, $lang);
		}
		$this->modx->regClientScript($this->getCacheUrl() . $file);

		return true;
	}


	/**
	 * from
	 * https://github.com/bezumkin/Office/blob/0115a0f6998ec8c4176eb9f58a2111570f7a6a0a/core/components/office/model/office/office.class.php#L429
	 *
	 * @param string $objectName
	 *
	 * @return bool|int
	 */
	public function addClientExtJS($objectName = 'ecc')
	{
		if (isset($this->modx->loadedjscripts[$objectName])) {
			return false;
		}

		$this->modx->regClientCSS($this->config['assetsUrl'] . 'vendor/extjs/css/ext-all-notheme.css');
		$config = $this->makePlaceholders($this->config);
		if ($css = $this->getOption('mainCss', null, '', true)) {
			$this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
		} else {
			$this->modx->regClientCSS($this->config['assetsUrl'] . 'vendor/fontawesome/css/font-awesome.min.css');
			$this->modx->regClientCSS($this->config['assetsUrl'] . 'vendor/extjs/css/xtheme-modx.new.css');
			$this->modx->regClientCSS($this->config['assetsUrl'] . 'vendor/extjs/css/ecc-add.css');
		}
		$this->addClientJs(array(
			MODX_MANAGER_URL . 'assets/ext3/adapter/jquery/ext-jquery-adapter.js',
			MODX_MANAGER_URL . 'assets/ext3/ext-all.js',
		), 'main/ext');
		$this->addClientJs(array(
			MODX_MANAGER_URL . 'assets/modext/core/modx.js',
		), 'main/modx');
		$this->addClientJs(array(
			MODX_MANAGER_URL . 'assets/modext/core/modx.localization.js',
			MODX_MANAGER_URL . 'assets/modext/util/utilities.js',
			MODX_MANAGER_URL . 'assets/modext/util/datetime.js',
			MODX_MANAGER_URL . 'assets/modext/core/modx.component.js',
			MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.panel.js',
			MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.tabs.js',
			MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.window.js',
			(file_exists(MODX_MANAGER_PATH . 'assets/modext/widgets/modx.treedrop.js')
				? MODX_MANAGER_URL . 'assets/modext/widgets/modx.treedrop.js'
				: MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.tree.js'
			),
			MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.combo.js',
			MODX_MANAGER_URL . 'assets/modext/widgets/core/modx.grid.js',
		), 'main/widgets');
		if ($js = $this->getOption('mainJs', null, '', true)) {
			$this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));
		} else {
			$this->addClientJs(array(
				$this->config['assetsUrl'] . 'vendor/extjs/js/default.js',
				$this->config['assetsUrl'] . 'vendor/extjs/js/default.utils.js',
				$this->config['assetsUrl'] . 'vendor/extjs/js/default.combo.js',
				$this->config['assetsUrl'] . 'vendor/extjs/js/default.grid.js',
				$this->config['assetsUrl'] . 'vendor/extjs/js/default.window.js',
			), 'lib/ecc');
		}

		return $this->modx->loadedjscripts[$objectName] = 1;
	}

	/**
	 *
	 * @param        $file
	 * @param        $data
	 * @param string $path
	 *
	 * @return bool|int
	 */
	protected function saveFile($file, $data)
	{
		$file = trim(trim($file), '/');
		$path = $this->getCachePath(true);
		$addPath = implode('/', array($this->getAddPath(), $file));
		if (strpos($addPath, '/') !== false) {
			$tmp = explode('/', $addPath);
			$file = array_pop($tmp);
			foreach ($tmp as $v) {
				$path = $path . '/' . $v;
				@mkdir($path);
			}
		}

		return file_put_contents($path . '/' . $file, $data);
	}

	/**
	 * @param bool $base
	 *
	 * @return string
	 */
	protected function getCachePath($base = false)
	{
		$path[] = rtrim($this->config['assetsCachePath'], '/');
		if (!$base) {
			$path[] = $this->getAddPath();
		}

		return implode('/', $path);
	}

	/**
	 * @return string
	 */
	protected function getCacheUrl()
	{
		$url[] = rtrim($this->config['assetsCacheUrl'], '/');
		$url[] = $this->getAddPath();
		$url[] = null;

		return implode('/', $url);
	}

	/**
	 * @return string
	 */
	protected function getAddPath()
	{
		$path = array();
		foreach (array('namespace', 'controller') as $k) {
			if (!empty($this->opts[$k])) {
				$path[] = strtolower($this->opts[$k]);
			}
		}

		return implode('/', $path);
	}

	/**
	 * return lexicon message if possibly
	 *
	 * @param       $message
	 * @param array $placeholders
	 *
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
	 * @param array  $data
	 * @param array  $placeholders
	 *
	 * @return array|string
	 */
	public function failure($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			'message' => $this->lexicon($message, $placeholders),
			'data'    => $data,
		);

		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

	/**
	 * @param string $message
	 * @param array  $data
	 * @param array  $placeholders
	 *
	 * @return array|string
	 */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->lexicon($message, $placeholders),
			'data'    => $data,
		);

		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

	/**
	 * @param bool $json
	 *
	 * @return bool
	 */
	public function setJsonResponse($json = true)
	{
		return ($this->config['jsonResponse'] = $json);
	}

}