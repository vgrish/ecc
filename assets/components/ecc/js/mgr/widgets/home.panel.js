ecc.panel.Home = function (config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		/*
		 stateful: true,
		 stateId: 'ecc-panel-home',
		 stateEvents: ['tabchange'],
		 getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
		 */
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('ecc') + '</h2>',
			cls: '',
			style: {margin: '15px 0'}
		}, {
			xtype: 'modx-tabs',
			defaults: {border: false, autoHeight: true},
			border: true,
			hideMode: 'offsets',
			items: [{
				title: _('ecc_items'),
				layout: 'anchor',
				items: [{
					html: _('ecc_intro_msg'),
					cls: 'panel-desc',
				}, {
					xtype: 'ecc-grid-items',
					cls: 'main-wrapper',
				}]
			}]
		}]
	});
	ecc.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(ecc.panel.Home, MODx.Panel);
Ext.reg('ecc-panel-home', ecc.panel.Home);
