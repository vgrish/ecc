<?php

abstract class eccBaseController
{

	/** @var modX $modx */
	public $modx;
	/** @var ecc $ecc */
	public $ecc;
	/** @var array $config */
	public $config = array();
	/** @var array $opts */
	public $opts = array();

	/**
	 * @param $n
	 * @param array $p
	 */
	public function __call($n, array$p)
	{
		echo __METHOD__ . ' says: ' . $n;
	}

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, $config = array())
	{
		$this->modx = &$modx;
		$this->config = &$config;
		$this->ecc = $this->modx->ecc;

		if (!is_object($this->ecc)) {
			$corePath = $modx->getOption('ecc_core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ecc/');
			$this->ecc = $modx->getService('ecc', 'ecc', $corePath . 'model/ecc/', array('core_path' => $corePath));
		}

		$this->opts = &$this->ecc->opts;

		$this->config = array_merge(array(
			'wrapperId' => $this->getWrapperId()
		), $config);
	}

	/** @inheritdoc} */
	public function getOption($key, $config = array(), $default = null)
	{
		return $this->ecc->getOption($key, $config, $default);
	}

	/** @inheritdoc} */
	public function initialize($ctx = 'web')
	{
		return true;
	}

	/** @inheritdoc} */
	public function getLanguageTopics()
	{
		return array('ecc:default');
	}

	/** @inheritdoc} */
	public function getDefaultAction()
	{
		return 'defaultAction';
	}

	/** @inheritdoc} */
	public function getDefaultProcessorAction()
	{
		return 'defaultProcessorAction';
	}

	/** @inheritdoc} */
	public function defaultAction()
	{
		return 'Default action of default controller';
	}

	/** @inheritdoc} */
	public function defaultProcessorAction()
	{
		return 'Default action of processor controller';
	}

	/** @inheritdoc} */
	public function makePlaceholders(array $array = array(), $plPrefix = '', $prefix = '[[+', $suffix = ']]', $uncacheable = true)
	{
		return $this->ecc->makePlaceholders($array, $plPrefix, $prefix, $suffix, $uncacheable);
	}

	/** @inheritdoc} */
	public function setConfig($config = array())
	{
		$pls = $this->makePlaceholders($config);
		foreach ($config as $k => $v) {
			if (is_string($v)) {
				$this->config[$k] = str_replace($pls['pl'], $pls['vl'], $v);
			} else {
				$this->config[$k] = $v;
			}
		}
	}

	/** @inheritdoc} */
	public function runProcessor($action = '', $data = array(), $json = true)
	{
		return $this->ecc->runProcessor($action, $data, $json);
	}

	/** @inheritdoc} */
	public function regTopScript($script = '')
	{
		$this->modx->regClientStartupScript(preg_replace('#(\n|\t)#', '', "
				<script type=\"text/javascript\">
				{$script}
				</script>
		"), true);
	}

	/** @inheritdoc} */
	public function regBottomScript($script = '')
	{
		$this->modx->regClientScript(preg_replace('#(\n|\t)#', '', "
				<script type=\"text/javascript\">
				{$script}
				</script>
		"), true);
	}

	/** @inheritdoc} */
	public function getWrapperId()
	{
		$wrapperId = array();
		foreach (array('namespace', 'controller', 'path', 'location') as $o) {
			$wrapperId[] = $this->opts[$o];
		}

		return implode('-', $wrapperId);
	}

	/** @inheritdoc} */
	public function getWrapper()
	{
		return $this->modx->getChunk($this->config['tplOuter'], $this->config);
	}

	/** @inheritdoc} */
	public function failure($message = '', $data = array(), $placeholders = array())
	{
		return $this->ecc->failure($message, $data, $placeholders);
	}

	/** @inheritdoc} */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		return $this->ecc->success($message, $data, $placeholders);
	}

}