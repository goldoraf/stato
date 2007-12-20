<h2>Pages</h2>
<p id="actions">
    <?= link_to('Nouvelle page', array('action' => 'create'), array('class' => 'action new')); ?>
</p>
<script type="text/javascript">  
    Ext.onReady(function(){
        
        var tree = statoCms.app.createTree('page-tree', "<?= url_for(array('action' => 'nodes')); ?>");
        
        tree.on('move', function(tree, node, oldParent, newParent, index){ 
            var params = '?id='+node.id+'&new_parent_id='+newParent.id+'&index='+index;
            Ext.Ajax.request({url:"<?= url_for(array('action' => 'sort_tree')); ?>"+params});
        });
        
        tree.on('click', function(node, e){
            var info = Ext.get('page-info');
            info.load({
                url: '<?= url_for(array('action' => 'info')); ?>',
    			params: 'id=' + node.id,
    			text: 'Chargement...'
            });
            info.show();
        });
    });
</script>
<div id="page-tree" style="width:40%;float:left;">
    
</div>
<div style="width:55%;float:left;">
    <h3>Aperçu de la page</h3>
    <div class="form" id="page-info" style="min-height:300px;">Cliquez sur une page de l'arborescence pour en afficher un aperçu.</div>
</div>
