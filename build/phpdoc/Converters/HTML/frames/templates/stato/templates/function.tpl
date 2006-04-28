{section name=func loop=$functions}
<a name="{$functions[func].function_dest}" id="{$functions[func].function_dest}"><!-- --></a>
<div class="{cycle values="evenrow,oddrow"}">
	
	<div>
		<img src="{$subdir}media/images/Function.png" />
		<span class="method-title">{$functions[func].function_name}</span>
		{if count($functions[func].ifunction_call.params)}
			({section name=params loop=$functions[func].ifunction_call.params}{if $smarty.section.params.iteration != 1}, {/if}{$functions[func].ifunction_call.params[params].name}{if $functions[func].ifunction_call.params[params].hasdefault} = {$functions[func].ifunction_call.params[params].default|escape:"html"}{/if}{/section})
		{else}
		()
		{/if}
	</div> 

	{include file="docblock.tpl" sdesc=$functions[func].sdesc desc=$functions[func].desc tags=$functions[func].tags params=$functions[func].params function=false}

</div>
{/section}
