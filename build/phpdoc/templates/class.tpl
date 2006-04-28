{include file="header.tpl" top3=true}

<h2 class="class-name"><img src="{$subdir}media/images/{if $abstract}{if $access == 'private'}AbstractPrivate{else}Abstract{/if}{else}{if $access == 'private'}Private{/if}{/if}Class_logo.png"
														alt="{if $abstract}{if $access == 'private'}AbstractPrivate{else}Abstract{/if}{else}{if $access == 'private'}Private{/if}{/if} Class"
														title="{if $abstract}{if $access == 'private'}AbstractPrivate{else}Abstract{/if}{else}{if $access == 'private'}Private{/if}{/if} Class"
														style="vertical-align: middle">{if $is_interface}Interface{/if} {$class_name}</h2>

<div class="info-box">
	<div class="info-box-body">
        {if $implements}
        <p class="implements">
            Implements interfaces:
            <ul>
                {foreach item="int" from=$implements}<li>{$int}</li>{/foreach}
            </ul>
        </p>
        {/if}
		{include file="docblock.tpl" type="class" sdesc=$sdesc desc=$desc}
		<p class="notes">
			Located in <a class="field" href="{$page_link}">{$source_location}</a> (line <span class="field">{if $class_slink}{$class_slink}{else}{$line_number}{/if}</span>)
		</p>
		
		{if $tutorial}
			<hr class="separator" />
			<div class="notes">Tutorial: <span class="tutorial">{$tutorial}</span></div>
		{/if}
	
		{if $conflicts.conflict_type}
			<hr class="separator" />
			<div><span class="warning">Conflicts with classes:</span><br /> 
			{section name=me loop=$conflicts.conflicts}
				{$conflicts.conflicts[me]}<br />
			{/section}
			</div>
		{/if}
	</div>
</div>

{if $children}
	<div class="info-box">
		<div class="info-box-title">Direct descendents</div>
		<div class="info-box-body">
			<table cellpadding="2" cellspacing="0" class="class-table">
				<tr>
					<th class="class-table-header">Class</th>
					<th class="class-table-header">Description</th>
				</tr>
				{section name=kids loop=$children}
				<tr>
					<td style="padding-right: 2em; white-space: nowrap">
						<img src="{$subdir}media/images/{if $children[kids].abstract}Abstract{/if}{if $children[kids].access == 'private'}Private{/if}Class.png"
								 alt="{if $children[kids].abstract}Abstract{/if}{if $children[kids].access == 'private'}Private{/if} class"
								 title="{if $children[kids].abstract}Abstract{/if}{if $children[kids].access == 'private'}Private{/if} class"
								 style="vertical-align: center"/>
						{$children[kids].link}
					</td>
					<td>
					{if $children[kids].sdesc}
						{$children[kids].sdesc}
					{else}
						{$children[kids].desc}
					{/if}
					</td>
				</tr>
				{/section}
			</table>
		</div>
	</div>
{/if}

{if $consts}
	<div class="info-box">
		<div class="info-box-title">Constants</span></div>
		<div class="info-box-body">
			<div class="const-summary">
				{section name=consts loop=$consts}
				<div class="const-title">
					<img src="{$subdir}media/images/Constant.png" alt=" " />
					<a href="#{$consts[consts].const_name}" title="details" class="const-name">{$consts[consts].const_name}</a> = <span class="var-type">{$consts[consts].const_value}</span>
				</div>
				{/section}
			</div>
		</div>
	</div>
{/if}

{if $methods}
	<div class="info-box">
		<div class="info-box-title">Methods</span></div>
		<div class="info-box-body">			
			<div class="method-summary">
				{section name=methods loop=$methods}				
				<div class="method-definition">
					<img src="{$subdir}media/images/{if $methods[methods].ifunction_call.constructor}Constructor{elseif $methods[methods].ifunction_call.destructor}Destructor{elseif $methods[methods].access == 'private'}{if $methods[methods].abstract}Abstract{/if}PrivateMethod{else}{if $methods[methods].abstract}Abstract{/if}Method{/if}.png" alt=" "/>
					<a href="#{$methods[methods].function_name}" title="details" class="method-name">{if $methods[methods].ifunction_call.returnsref}&amp;{/if}{$methods[methods].function_name}</a>
				</div>
				{/section}
			</div>
		</div>
	</div>		
{/if}

{if $vars}
	<div class="info-box">
		<div class="info-box-title">Public member variables</div>
		<div class="info-box-body">
			<table>
                {section name=vars loop=$vars}
                    {if $vars[vars].access == 'public'}
                        {include file="var.tpl" var=$vars[vars]}
                    {/if}
            	{/section}
            </table>	
		</div>
	</div>
	<div class="info-box">
		<div class="info-box-title">Protected member variables</div>
		<div class="info-box-body">
			<table>
                {section name=vars loop=$vars}
                    {if $vars[vars].access == 'protected'}
                        {include file="var.tpl" var=$vars[vars]}
                    {/if}
            	{/section}
            </table>			
		</div>
	</div>
{/if}
{if $ivars}
	<div class="info-box">
		<div class="info-box-title">Inherited variables</div>
		<div class="info-box-body">
			{section name=ivars loop=$ivars}
				<p>Inherited from <span class="classname">{$ivars[ivars].parent_class}</span></p>
				<blockquote>
					{section name=ivars2 loop=$ivars[ivars].ivars}
						<img src="{$subdir}media/images/{if $ivars[ivars].ivars[ivars2].access == 'private'}PrivateVariable{else}Variable{/if}.png" />
						<span class="var-title">
							<span class="var-name">{$ivars[ivars].ivars[ivars2].link}</span>{if $ivars[ivars].ivars[ivars2].ivar_sdesc}: {$ivars[ivars].ivars[ivars2].ivar_sdesc}{/if}<br>
						</span>
					{/section}
				</blockquote> 
			{/section}	
		</div>
	</div>
{/if}	
	
{if $methods}
	<div class="info-box">
		<div class="info-box-title">Public methods</div>
		<div class="info-box-body">
			{section name=methods loop=$methods}
        	    {if $methods[methods].access == 'public'}
                    {include file="method.tpl" method=$methods[methods]}
                {/if}
        	{/section}			
		</div>
	</div>
	<div class="info-box">
		<div class="info-box-title">Protected methods</div>
		<div class="info-box-body">
			{section name=methods loop=$methods}
        	    {if $methods[methods].access == 'protected'}
                    {include file="method.tpl" method=$methods[methods]}
                {/if}
        	{/section}		
		</div>
	</div>
{/if}
{if $imethods}
	<div class="info-box">
		<div class="info-box-title">Inherited methods</div>
		<div class="info-box-body">
			{section name=imethods loop=$imethods}
				<!-- =========== Summary =========== -->
				<p>Inherited From <span class="classname">{$imethods[imethods].parent_class}</span></p>
				<blockquote>
					{section name=im2 loop=$imethods[imethods].imethods}
						<img src="{$subdir}media/images/{if $imethods[imethods].imethods[im2].constructor}Constructor{elseif $imethods[imethods].imethods[im2].destructor}Destructor{elseif $imethods[imethods].imethods[im2].access == 'private'}{if $imethods[imethods].imethods[im2].abstract}Abstract{/if}PrivateMethod{else}{if $imethods[imethods].imethods[im2].abstract}Abstract{/if}Method{/if}.png" alt=" "/>
						<span class="method-name">{$imethods[imethods].imethods[im2].link}</span>{if $imethods[imethods].imethods[im2].ifunction_sdesc}: {$imethods[imethods].imethods[im2].ifunction_sdesc}{/if}<br>
					{/section}
				</blockquote>
			{/section}		
		</div>
	</div>
{/if}

{include file="footer.tpl" top3=true}
