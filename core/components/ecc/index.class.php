<?php

/**
 * Class eccMainController
 */
abstract class eccMainController extends modExtraManagerController
{
	/** @var ecc $ecc */
	public $ecc;


	/**
	 * @return void
	 */
	public function initialize()
	{
		$corePath = $this->modx->getOption('ecc_core_path', null, $this->modx->getOption('core_path') . 'components/ecc/');
		require_once $corePath . 'model/ecc/ecc.class.php';

		$this->ecc = new ecc($this->modx);
		$this->addCss($this->ecc->config['cssUrl'] . 'mgr/main.css');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/ecc.js');
		$this->addHtml('
		<script type="text/javascript">
			ecc.config = ' . $this->modx->toJSON($this->ecc->config) . ';
			ecc.config.connector_url = "' . $this->ecc->config['connectorUrl'] . '";
		</script>
		');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics()
	{
		return array('ecc:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions()
	{
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends eccMainController
{

	/**
	 * @return string
	 */
	public static function getDefaultController()
	{
		return 'home';
	}
}