<section>
	<h1>Additional Pages</h1>
	<ul>
		{foreach from=$tertiary_pages key=key item=tertiary_page}
			<li><a href="{$site_community_relative}:{$tertiary_page.page_url}">{$tertiary_page.menu_title}</a></li>
		{/foreach}
	</ul>
</section>