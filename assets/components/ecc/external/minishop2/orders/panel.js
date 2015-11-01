miniShop2.panel.Orders = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: false,
            hideMode: 'offsets',
            items: [{
                title: _('minishop2'),
                layout: 'anchor',
                items: [{
                    html: _('ms2_orders_intro'),
                    cls: 'panel-desc'
                }, {
                    xtype: 'minishop2-grid-orders',
                    cls: 'main-wrapper'
                }]
            }]
        }]
    });
	miniShop2.panel.Orders.superclass.constructor.call(this, config);
};
Ext.extend(miniShop2.panel.Orders, MODx.Panel);
Ext.reg('minishop2-panel-orders', miniShop2.panel.Orders);