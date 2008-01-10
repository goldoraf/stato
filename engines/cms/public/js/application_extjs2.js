// reference local blank image
//Ext.BLANK_IMAGE_URL = '../extjs/resources/images/default/s.gif';
 
// create namespace
Ext.namespace('StatoCms');
 
// create application
StatoCms.app = function() {
    // do NOT access DOM from here; elements don't exist yet
 
    // private variables
 
    // private functions
 
    // public space
    return {
        // public properties, e.g. strings to translate
        //btn1Text: 'Button 1',
 
        // public methods
        initPageView: function() {
            
            var viewport = new Ext.Viewport({
                id:'viewport',
                layout:'border',
                items:[
                    new Ext.BoxComponent({ region:'north', el: 'header' }),
                    new Ext.BoxComponent({ region:'south', el: 'footer' }),
                    {
            			id:'page-container',
            			xtype:'panel',
            			region:'center',
            			layout:'fit',
            			margins:'5 5 5 0',
            			items: new Ext.Panel({ id: 'page-tip', html:'<p>Cliquez sur une page</p>' })
                	},
                    new PageTreePanel()
                 ]
            });
            /*Ext.get("hideit").on('click', function() {
               var w = Ext.getCmp('west-panel');
               w.collapsed ? w.expand() : w.collapse(); 
            });*/
        }
    };
}();

PageFormPanel = function() {
    PageFormPanel.superclass.constructor.call(this, {
        frame: true,
        title:"Edition d'une page",
        labelAlign: 'right',
        labelWidth: 85,
        width:340,
        waitMsgTarget: true,
        defaultType: 'textfield',
    
        items: [{
                    fieldLabel: 'Titre',
                    name: 'title',
                    width:300
                },

                new Ext.form.DateField({
                    fieldLabel: 'Date de publication',
                    name: 'updated_on',
                    width:190,
                    allowBlank:false
                }),
                
                new Ext.ux.CustomHtmlEditor({
                    fieldLabel: 'Texte',
                    id:'content',
                    width:750,
                    height:400
                })
        ]
    });
    
    this.getForm().load({url:StatoCms.BASE_URI+'/pages/details/1'/*, waitMsg:'Loading'*/});
};

Ext.extend(PageFormPanel, Ext.FormPanel, {

});

PageViewPanel = function() {
    PageViewPanel.superclass.constructor.call(this, {
        id:'page-view',
        region:'center',
        deferredRender:false,
        activeTab:0,
        items:[
            new PageFormPanel(),
            {
                //contentEl:'center2',
                title: 'Center Panel',
                autoScroll:true
            }
        ]
    });
};

Ext.extend(PageViewPanel, Ext.TabPanel, {

});

PageTreePanel = function() {
    PageTreePanel.superclass.constructor.call(this, {
        id:'page-tree',
        region:'west',
        title:'Pages',
        split:true,
        width: 225,
        minSize: 175,
        maxSize: 400,
        collapsible: true,
        margins:'5 0 5 5',
        cmargins:'5 5 5 5',
        rootVisible:true,
        lines:false,
        autoScroll:true,
        loader: new Ext.tree.TreeLoader({dataUrl:StatoCms.BASE_URI+'/pages/nodes'}),
        root: new Ext.tree.AsyncTreeNode({
                text: 'Racine du site',
                draggable:false,
                id:'source'
            }),
        tbar: [new Ext.Action({
                text: 'Nouvelle page',
                handler: function(){
                    Ext.example.msg('Click','You clicked on "Action 1".');
                },
                iconCls: 'new'
            })]
    });
    
    this.getSelectionModel().on({
        'selectionchange' : function(sm, node){
            if(node){
                var container = Ext.getCmp("page-container");
                container.remove(0, true); 
                container.add(new PageViewPanel());
                container.doLayout();
            }
        },
        scope:this
    });

    /*this.getSelectionModel().on({
        'beforeselect' : function(sm, node){
             return node.isLeaf();
        },
        'selectionchange' : function(sm, node){
            if(node){
                this.fireEvent('feedselect', node.attributes);
            }
            this.getTopToolbar().items.get('delete').setDisabled(!node);
        },
        scope:this
    });

    this.addEvents({feedselect:true});

    this.on('contextmenu', this.onContextMenu, this);*/
};

Ext.extend(PageTreePanel, Ext.tree.TreePanel, {

    onMove : function(tree, node, oldParent, newParent, index){ 
        var params = '?id='+node.id+'&new_parent_id='+newParent.id+'&index='+index;
        Ext.Ajax.request({url:StatoCms.BASE_URI+'/pages/sort_tree'+params});
    }/*,
    onContextMenu : function(node, e){
        if(!this.menu){ // create context menu on first right click
            this.menu = new Ext.menu.Menu({
                id:'feeds-ctx',
                items: [{
                    id:'load',
                    iconCls:'load-icon',
                    text:'Load Feed',
                    scope: this,
                    handler:function(){
                        this.ctxNode.select();
                    }
                },{
                    text:'Remove',
                    iconCls:'delete-icon',
                    scope: this,
                    handler:function(){
                        this.ctxNode.ui.removeClass('x-node-ctx');
                        this.removeFeed(this.ctxNode.attributes.url);
                        this.ctxNode = null;
                    }
                },'-',{
                    iconCls:'add-feed',
                    text:'Add Feed',
                    handler: this.showWindow,
                    scope: this
                }]
            });
            this.menu.on('hide', this.onContextHide, this);
        }
        if(this.ctxNode){
            this.ctxNode.ui.removeClass('x-node-ctx');
            this.ctxNode = null;
        }
        if(node.isLeaf()){
            this.ctxNode = node;
            this.ctxNode.ui.addClass('x-node-ctx');
            this.menu.items.get('load').setDisabled(node.isSelected());
            this.menu.showAt(e.getXY());
        }
    },

    onContextHide : function(){
        if(this.ctxNode){
            this.ctxNode.ui.removeClass('x-node-ctx');
            this.ctxNode = null;
        }
    },

    showWindow : function(btn){
        if(!this.win){
            this.win = new FeedWindow();
            this.win.on('validfeed', this.addFeed, this);
        }
        this.win.show(btn);
    },

    selectFeed: function(url){
        this.getNodeById(url).select();
    },

    removeFeed: function(url){
        var node = this.getNodeById(url);
        if(node){
            node.unselect();
            Ext.fly(node.ui.elNode).ghost('l', {
                callback: node.remove, scope: node, duration: .4
            });
        }
    },

    addFeed : function(attrs, inactive, preventAnim){
        var exists = this.getNodeById(attrs.url);
        if(exists){
            if(!inactive){
                exists.select();
                exists.ui.highlight();
            }
            return;
        }
        Ext.apply(attrs, {
            iconCls: 'feed-icon',
            leaf:true,
            cls:'feed',
            id: attrs.url
        });
        var node = new Ext.tree.TreeNode(attrs);
        this.feeds.appendChild(node);
        if(!inactive){
            if(!preventAnim){
                Ext.fly(node.ui.elNode).slideIn('l', {
                    callback: node.select, scope: node, duration: .4
                });
            }else{
                node.select();
            }
        }
        return node;
    },

    // prevent the default context menu when you miss the node
    afterRender : function(){
        FeedPanel.superclass.afterRender.call(this);
        this.el.on('contextmenu', function(e){
            e.preventDefault();
        });
    }*/
});

Ext.onReady(function(){

    Ext.QuickTips.init();
    
    StatoCms.app.initPageView();
    
    setTimeout(function(){
        Ext.get('loading').remove();
        Ext.get('loading-mask').fadeOut({remove:true});
    }, 250);
});
