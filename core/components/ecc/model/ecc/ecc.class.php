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
	 * @param array $scriptProperties
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

	public function handleRequest(array $request = array())
	{

	}

	/**
	 * return lexicon message if possibly
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