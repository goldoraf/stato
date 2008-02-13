<h2>Liste des fichiers</h2>
<script type="text/javascript">  
    Ext.onReady(function(){
        // shorthand
        var Tree = Ext.tree;
        
        var tree = new Tree.TreePanel('directory-tree', {
            animate:true,
            lines:false,
            loader: new Tree.TreeLoader({
                dataUrl:'<?= url_for(array('action' => 'nodes')); ?>'
            }),
            enableDrag:true,
            containerScroll: true
        });
    
        // set the root node
        var root = new Tree.AsyncTreeNode({
            text: 'Racine du site',
            draggable:false,
            id:'source'
        });
        tree.setRootNode(root);
        
        // render the tree
        tree.render();
        root.expand();
        
        var el = Ext.get('trash');
        var trash = new Ext.dd.DropTarget(el, {ddGroup:'TreeDD'});
        trash.notifyDrop = function(source, e, data) {
            if (confirm("Etes-vous sûr(e) de vouloir supprimer ce fichier ? Cela peut rompre des liens présents sur certaines pages !")) {
                var parentNode=data.node.parentNode;
                parentNode.removeChild(data.node);
            }
        };
        
    });
</script>
<div id="directory-tree" />
<div id="trash">Trash</div>
<div id="sidebar">
    <h3 class="add-folder">Créer un nouveau dossier</h3>
    <?= form_tag(array('action' => 'create_dir')); ?>
        <p>
            <label>Nom</label>
            <?= text_field_tag('name'); ?>
        </p>
        <p>
            <label>Dossier parent</label>
            <?= select_tag('parent', directory_tree_for_select($this->root_dir)); ?>
        </p>
        <p class="submit">
            <?= submit_tag('Créer'); ?>
        </p>
    </form>
    <h3 class="upload-files">Transférer des fichiers</h3>
    <?= form_tag(array('action' => 'add_files'), array('multipart' => true)); ?>
        <p id="files">
            <label>Fichier</label>
            <?= file_field_tag('file[]'); ?>
        </p>
        <?= javascript_tag("function addFileField() { $('#files').append('".file_field_tag('file[]')."'); }"); ?>
        <?= link_to('Ajouter un autre fichier', '#', array('onclick' => 'addFileField(); return false;')); ?>
        <p>
            <label>Dossier parent</label>
            <?= select_tag('parent', directory_tree_for_select($this->root_dir)); ?>
        </p>
        <p class="submit">
            <?= submit_tag('Transférer'); ?>
        </p>
    </form>
</div>
