<?php

class Welcome extends eccBaseController
{
	/** @inheritdoc} */
	public function getDefaultAction()
	{
		return 'WelcomeMsg';
	}

	/** @inheritdoc} */
	public function WelcomeMsg()
	{
		$this->ecc->addClientExtJS();
		$this->ecc->addClientLexicon(array(
			'ecc:default',
		), 'lexicon/lexicon');

		$this->regBottomScript("
			Ext.onReady(function () {
				Ext.MessageBox.alert('Title',_('ecc_welcome'));
				var preloader = document.getElementById(\"{$this->config['wrapperId']}\").querySelectorAll(\".ecc-preloader\");
				if (preloader) {
					preloader[0].parentNode.removeChild(preloader[0]);
				}
		});");

		return $this->getWrapper();
	}

}
