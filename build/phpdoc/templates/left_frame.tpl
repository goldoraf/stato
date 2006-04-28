{include file="header.tpl" top2=true}
<div class="package-title">{$package}</div>
<div class="package-details">
			
	<dl class="tree">
		
		<dt class="folder-title">Description</dt>
		<dd>
			<a href='{$classtreepage}.html' target='right'>Class trees</a><br />
			<a href='{$elementindex}.html' target='right'>Index of elements</a><br />
			{if $hastodos}
				<a href="{$todolink}" target="right">Todo List</a><br />
			{/if}
		</dd>
	
		{section name=p loop=$info}
					
			{if $info[p].subpackage != ""}
			
				{if $info[p].subpackage != "view"}
                    <dt class="sub-package"><img class="tree-icon" src="{$subdir}media/images/package.png" alt="Sub-package">{$info[p].subpackage}</dt>
    				<dd>
    					<dl class="tree">
    						{if $info[p].subpackagetutorial}
    							<div><img class="tree-icon" src="{$subdir}media/images/tutorial.png" alt="Tutorial"><a href="{$info.0.subpackagetutorialnoa}" target="right">{$info.0.subpackagetutorialtitle}</a></div>
    						{/if}
    						{if $info[p].classes}
    							<dt class="folder-title"><img class="tree-icon" src="{$subdir}media/images/class_folder.png" alt=" ">Classes</dt>
    							{section name=class loop=$info[p].classes}
    								<dd><img class="tree-icon" src="{$subdir}media/images/{if $info[p].classes[class].abstract}Abstract{/if}{if $info[p].classes[class].access == 'private'}Private{/if}Class.png" alt="Class"><a href='{$info[p].classes[class].link}' target='right'>{$info[p].classes[class].title}</a></dd>
    							{/section}
    						{/if}
    					</dl>
    				</dd>
                {else}
                    <dt class="sub-package"><img class="tree-icon" src="{$subdir}media/images/package.png" alt="Sub-package">{$info[p].subpackage}</dt>
    				<dd>
    					<dl class="tree">
    						{if $info[p].subpackagetutorial}
    							<div><img class="tree-icon" src="{$subdir}media/images/tutorial.png" alt="Tutorial"><a href="{$info.0.subpackagetutorialnoa}" target="right">{$info.0.subpackagetutorialtitle}</a></div>
    						{/if}
    						{if $info[p].files}
    							<dt class="folder-title"><img class="tree-icon" src="{$subdir}media/images/folder.png" alt=" ">Files</dt>
    							{section name=nonclass loop=$info[p].files}
    								<dd><img class="tree-icon" src="{$subdir}media/images/Page.png" alt="File"><a href='{$info[p].files[nonclass].link}' target='right'>{$info[p].files[nonclass].title}</a></dd>
    							{/section}
    						{/if}
    						{if $info[p].functions}
    							<dt class="folder-title"><img class="tree-icon" src="{$subdir}media/images/function_folder.png" alt=" ">Functions</dt>
    							{section name=f loop=$info[p].functions}
    								<dd><img class="tree-icon" src="{$subdir}media/images/Function.png" alt="Function"><a href='{$info[p].functions[f].link}' target='right'>{$info[p].functions[f].title}</a></dd>
    							{/section}
    						{/if}
    					</dl>
    				</dd>	
                {/if} 			
			{/if}
			
		{/section}
	</dl>
</div>
<p class="notes"><a href="{$phpdocwebsite}" target="_blank">phpDocumentor v <span class="field">{$phpdocversion}</span></a></p>
</BODY>
</HTML>
