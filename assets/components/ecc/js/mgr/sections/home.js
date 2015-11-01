ecc.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'ecc-panel-home', renderTo: 'ecc-panel-home-div'
		}]
	});
	ecc.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(ecc.page.Home, MODx.Component);
Ext.reg('ecc-page-home', ecc.page.Home);