<?php

abstract class eccSystemPlugin
{
	/** @var modX $modx */
	protected $modx;
	/** @var ecc $ecc */
	protected $ecc;
	/** @var array $scriptProperties */
	protected $scriptProperties;

	public function __construct(modX &$modx, &$scriptProperties)
	{
		$this->scriptProperties =& $scriptProperties;
		$this->modx = &$modx;

		if (!is_object($this->ecc)) {
			$corePath = $modx->getOption('ecc_core_path', null, $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/ecc/');
			$this->ecc = $modx->getService('ecc', 'ecc', $corePath . 'model/ecc/', array('core_path' => $corePath));
		}
	}

	abstract public function run();
}