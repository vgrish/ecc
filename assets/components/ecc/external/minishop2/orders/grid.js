miniShop2.grid.Orders = function (config) {
    config = config || {};

    this.exp = new Ext.grid.RowExpander({
        expandOnDblClick: false
        , tpl: new Ext.Template('<p class="desc">{comment}</p>')
        , renderer: function (v, p, record) {
            return record.data.comment != '' && record.data.comment != null ? '<div class="x-grid3-row-expander">&#160;</div>' : '&#160;';
        }
    });
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config, {
        id: 'minishop2-grid-orders',
        url: miniShop2.config.connector_url,
        bodyCssClass: 'grid-with-buttons',
        cls: 'minishop2-grid',
        sm: this.sm,
        plugins: this.exp,

        baseParams: {
            action: 'mgr/orders/getlist',
            namespace: eccConfig.minishop2.namespace || '',
            path: eccConfig.minishop2.path || ''
        },
        pageSize: 10
    });
    miniShop2.grid.Orders.superclass.constructor.call(this, config);
    this.changed = false;
    this._makeTemplates();
    this.on('afterrender', function (grid) {
        var params = miniShop2.utils.Hash.get();
        var order = params['order'] || '';
        if (order) {
            this.updateOrder(grid, Ext.EventObject, {data: {id: order}});
        }
    });
};
Ext.extend(miniShop2.grid.Orders, ecc.grid.Default, {

    getFields: function (config) {
        return miniShop2.config.order_grid_fields;
    },

    getTopBar: function (config) {
        var tbar = [];

        return tbar;
    },

    getColumns: function (config) {
        var columns = [this.sm, this.exp];
        var all = {
            id: {
                width: 35
            },
            customer: {
                width: 100,
                sortable: true,
                renderer: miniShop2.utils.userLink
            },
            num: {
                width: 100,
                sortable: true,
                renderer: {
                    fn: this._renderCustomer,
                    scope: this
                },
                id: 'main'
            },
            receiver: {
                width: 100,
                sortable: true
            },
            createdon: {
                width: 75,
                sortable: true,
                renderer: miniShop2.utils.formatDate
            },
            updatedon: {
                width: 75,
                sortable: true,
                renderer: miniShop2.utils.formatDate
            },
            cost: {
                width: 75,
                sortable: true,
                renderer: this._renderCost
            },
            cart_cost: {
                width: 75,
                sortable: true
            },
            delivery_cost: {
                width: 75,
                sortable: true
            },
            weight: {
                width: 50,
                sortable: true
            },
            status: {
                width: 75,
                sortable: true
            },
            delivery: {
                width: 75,
                sortable: true
            },
            payment: {
                width: 75,
                sortable: true
            },
            context: {
                width: 50,
                sortable: true
            }
        };

        for (var i = 0; i < miniShop2.config.order_grid_fields.length; i++) {
            var field = miniShop2.config.order_grid_fields[i];
            if (all[field]) {
                Ext.applyIf(all[field], {
                    header: _('ms2_' + field),
                    dataIndex: field
                });
                columns.push(all[field]);
            }
        }

        return columns;
    },

    setAction: function (method, field, value) {
        var ids = this._getSelectedIds();
        if (!ids.length && (field !== 'false')) {
            return false;
        }
        MODx.Ajax.request({
            url: miniShop2.config.connector_url,
            params: {
                action: 'mgr/status/multiple',
                namespace: eccConfig.minishop2.namespace || '',
                path: eccConfig.minishop2.path || '',
                method: method,
                field_name: field,
                field_value: value,
                ids: Ext.util.JSON.encode(ids)
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    },
                    scope: this
                },
                failure: {
                    fn: function (response) {
                        MODx.msg.alert(_('error'), response.message);
                    },
                    scope: this
                }
            }
        })
    },

    remove: function () {
        MODx.msg.alert(_('info'), '---');
    },

    update: function (btn, e, row) {
        var record = typeof(row) != 'undefined' ? row.data : this.menu.record;
        MODx.Ajax.request({
            url: miniShop2.config.connector_url,
            params: {
                action: 'mgr/status/get',
                id: record.id,
                namespace: eccConfig.minishop2.namespace || '',
                path: eccConfig.minishop2.path || ''
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var record = r.object;
                        var w = MODx.load({
                            xtype: 'minishop2-orders-window-create',
                            title: _('minishop2_action_update'),
                            action: 'mgr/status/update',
                            record: record,
                            listeners: {
                                success: {
                                    fn: this.refresh,
                                    scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(record);
                        w.show(e.target);
                    },
                    scope: this
                }
            }
        });
    },


    getMenu: function () {
        var m = [];
        m.push({
            text: _('ms2_menu_update'),
            handler: this.updateOrder
        });
        m.push('-');
        m.push({
            text: _('ms2_menu_remove'),
            handler: this.removeOrder
        });
        this.addContextMenuItem(m);
    },

    FilterByQuery: function (tf, nv, ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },

    clearFilter: function (btn, e) {
        var s = this.getStore();
        s.baseParams.query = '';
        Ext.getCmp('minishop2-orders-search').setValue('');
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },

    filterByStatus: function (cb) {
        this.getStore().baseParams['status'] = cb.value;
        this.getBottomToolbar().changePage(1);
        this.refresh();
    },

    _makeTemplates: function () {
        var userPage = MODx.action ? MODx.action['security/user/update'] : 'security/user/update';
        this.tplCustomer = new Ext.XTemplate('' + '<tpl for="."><div class="order-title-column {cls}">' + '<h3 class="main-column"><span class="title">' + _('ms2_order') + ' #{num}</span></h3>' + '<tpl if="actions">' + '<ul class="actions">' + '<tpl for="actions">' + '<li><a href="#" class="controlBtn {className}">{text}</a></li>' + '</tpl>' + '</ul>' + '</tpl>' + '</div></tpl>', {
            compiled: true
        });
    },

    _renderCustomer: function (v, md, rec) {
        return this.tplCustomer.apply(rec.data);
    },

    _renderCost: function (v, md, rec) {
        return rec.data.type && rec.data.type == 1 ? '-' + v : v;
    },

    onClick: function(e){
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if(elm == 'controlBtn') {
            var action = t.className.split(' ')[1];
            this.menu.record = this.getSelectionModel().getSelected().data;
            switch (action) {
                case 'update':
                    this.updateOrder(this,e);
                    break;
                case 'delete':
                    this.removeOrder(this,e);
                    break;
            }
        }
        this.processEvent('click', e);
    },

    removeOrder: function (btn, e) {

    },

    updateOrder: function (btn, e) {

    },

});
Ext.reg('minishop2-grid-orders', miniShop2.grid.Orders);
