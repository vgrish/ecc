<?php

/**
 * The home manager controller for ecc.
 *
 */
class eccHomeManagerController extends eccMainController {
	/* @var ecc $ecc */
	public $ecc;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('ecc');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addCss($this->ecc->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->ecc->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->ecc->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "ecc-page-home"});
		});
		</script>');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->ecc->config['templatesPath'] . 'home.tpl';
	}
}