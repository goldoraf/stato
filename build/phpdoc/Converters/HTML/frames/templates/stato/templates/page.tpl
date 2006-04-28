{include file="header.tpl" top3=true}

<h2 class="file-name"><img src="{$subdir}media/images/Page_logo.png" alt="File" style="vertical-align: middle">{$source_location}</h2>

<div class="info-box">
	<div class="info-box-title">Description</div>
	<div class="info-box-body">	
		{include file="docblock.tpl" desc=$desc sdesc=$sdesc tags=$tags}
		
		{if $tutorial}
			<hr class="separator" />
			<div class="notes">Tutorial: <span class="tutorial">{$tutorial}</div>
		{/if}
	</div>
</div>
		
{if $classes}
	<div class="info-box">
		<div class="info-box-title">Classes</div>
		<div class="info-box-body">	
			<table cellpadding="2" cellspacing="0" class="class-table">
				<tr>
					<th class="class-table-header">Class</th>
					<th class="class-table-header">Description</th>
				</tr>
				{section name=classes loop=$classes}
				<tr>
					<td style="padding-right: 2em; vertical-align: top; white-space: nowrap">
						<img src="{$subdir}media/images/{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if}Class.png"
								 alt="{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if} class"
								 title="{if $classes[classes].abstract}Abstract{/if}{if $classes[classes].access == 'private'}Private{/if} class"/>
						{$classes[classes].link}
					</td>
					<td>
					{if $classes[classes].sdesc}
						{$classes[classes].sdesc}
					{else}
						{$classes[classes].desc}
					{/if}
					</td>
				</tr>
				{/section}
			</table>
		</div>
	</div>
{/if}
	
{if $functions}
	<div class="info-box">
		<div class="info-box-title">Functions</div>
		<div class="info-box-body">	
			{include file="function.tpl"}
		</div>
	</div>
{/if}
	
{include file="footer.tpl" top3=true}
