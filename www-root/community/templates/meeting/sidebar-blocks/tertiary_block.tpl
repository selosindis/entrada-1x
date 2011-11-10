<section>
	<div class="panel-content">
		<h1>Additional Pages</h1>
		<ul>
			{foreach from=$tertiary_pages key=key item=tertiary_page}
				<li><a href="">{$tertiary_page.menu_title}</a></li>
			{/foreach}
		</ul>
	</div>
</section>