<a name="method{$method.function_name}" id="{$method.function_name}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
	
	<div class="method-header">
		<img src="{$subdir}media/images/{if $method.ifunction_call.constructor}Constructor{elseif $method.ifunction_call.destructor}Destructor{else}{if $method.abstract}Abstract{/if}Method{/if}.png" />
		<span class="method-title">{$method.function_name}</span>
        <span class="line-number">
            {if count($method.ifunction_call.params)}
    			({section name=params loop=$method.ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}{$method.ifunction_call.params[params].name}{if $method.ifunction_call.params[params].default} = {$method.ifunction_call.params[params].default}{/if}{/section})
    		{else}
    		()
    		{/if}
        </span>
	</div> 
	
	{include file="docblock.tpl" sdesc=$method.sdesc desc=$method.desc tags=$method.tags params=$method.params function=false}
	
	{if $method.params}
		<ul class="parameters">
		{section name=params loop=$method.params}
			<li>
				<span class="var-type">{$method.params[params].datatype}</span>
				<span class="var-name">{$method.params[params].var}</span>{if $method.params[params].data}<span class="var-description">: {$method.params[params].data}</span>{/if}
			</li>
		{/section}
		</ul>
	{/if}
	
	{if $method.method_overrides}
		<hr class="separator" />
		<div class="notes">Redefinition of:</div>
		<dl>
			<dt>{$method.method_overrides.link}</dt>
			{if $method.method_overrides.sdesc}
			<dd>{$method.method_overrides.sdesc}</dd>
			{/if}
		</dl>
	{/if}
	
	{if $method.descmethod}
		<hr class="separator" />
		<div class="notes">Redefined in descendants as:</div>
		<ul class="redefinitions">
		{section name=dm loop=$method.descmethod}
			<li>
				{$method.descmethod[dm].link}
				{if $method.descmethod[dm].sdesc}
				: {$method.descmethod[dm].sdesc}
				{/if}
			</li>
		{/section}
		</ul>
	{/if}
</div>
