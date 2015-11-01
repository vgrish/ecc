<?php

class CurrencyRateController extends eccBaseController
{
	/** @inheritdoc} */
	public function initialize($ctx = 'web')
	{
		$this->modx->error->errors = array();
		$this->modx->error->message = '';

		$config = $this->modx->toJSON(array(
			'connectorUrl' => $this->config['actionUrl'],
			'namespace' => $this->config['namespace'],
			'controller' => $this->config['controller'],
			'path' => $this->config['path'],
		));
		$this->regTopScript("eccConfig.{$this->config['namespace']}={$config};");

		/* @var currencyrate $currencyrate */
		$corePath = $this->modx->getOption('currencyrate_core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/currencyrate/');
		if (!$currencyrate = $this->modx->getService('currencyrate', 'currencyrate', $corePath . 'model/currencyrate/', array('core_path' => $corePath))) {
			return false;
		}

		$config = $this->modx->toJSON(array(
			'connector_url' => $this->config['actionUrl'],
			'last_date' => $currencyrate->config['last_date'],
		));
		$this->regTopScript("currencyrateConfig={$config};");

		return true;
	}

	/** @inheritdoc} */
	public function DefaultAction()
	{
		$this->modx->regClientCSS(MODX_ASSETS_URL . 'components/currencyrate/css/mgr/main.css');
		$this->modx->regClientCSS(MODX_ASSETS_URL . 'components/currencyrate/css/mgr/bootstrap.buttons.css');

		$this->ecc->addClientExtJS();
		$this->ecc->addClientLexicon(array(
			'currencyrate:default',
		), 'lexicon/lexicon');

		$this->ecc->addClientJs(array(
			MODX_ASSETS_URL . 'components/currencyrate/js/mgr/currencyrate.js',
			MODX_ASSETS_URL . 'components/currencyrate/js/mgr/misc/utils.js',
			MODX_ASSETS_URL . 'components/currencyrate/js/mgr/widgets/home.panel.js',

			$this->ecc->config['assetsUrl'] . 'external/currencyrate/grid.js',
			$this->ecc->config['assetsUrl'] . 'external/currencyrate/window.js',
		), 'currencyrate/all');

		$this->onReady();

		return $this->getWrapper();
	}

	/** @inheritdoc} */
	public function onReady()
	{
		$this->regBottomScript("
			Ext.onReady(function () {
				currencyrate.config = currencyrateConfig || {};
				var panel = new currencyrate.panel.Home();
				panel.render(\"{$this->config['wrapperId']}\");
				var preloader = document.getElementById(\"{$this->config['wrapperId']}\").querySelectorAll(\".ecc-preloader\");
				if (preloader) {
					preloader[0].parentNode.removeChild(preloader[0]);
				}
		});");
	}

	/** @inheritdoc} */
	public function defaultProcessorAction($data = array())
	{
		$this->ecc->config['processorsPath'] = MODX_CORE_PATH . 'components/currencyrate/processors/';

		return $this->runProcessor($data['action'], $data);
	}

}
