<nav class="span-24 top-navigation">
	<ul class="span-24 navigation-list">
		{foreach from=$site_primary_navigation key=key item=menu_item name=navigation}
				{if $menu_item.link_title == "Home"}
					<li class="home"><a href="{$site_community_url}"><img src="{$template_relative}/images/home.png" alt="go-to-homepage"/></a></li>
				{else}
					{if $menu_item.link_parent == 0}
						<li><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}>{$menu_item.link_title}</a>
						{assign var="has_children" value="false"}
						{foreach from=$child_data key=key item=child}
							{if $menu_item.cpage_id == $child.parent_id}
								{assign var="has_children" value="true"}
							{/if}
						{/foreach}
						{if $has_children=="true"}
							<span class="arrow-down">&#9660;</span>
							<ul class="submenu">
								{foreach from=$child_data key=key item=child}
									{if $menu_item.cpage_id == $child.parent_id}
										<li><a href="{$site_community_relative}:{$child.page_url}">{$child.menu_title}</a></li>
									{/if}
								{/foreach}
							</ul></li>
						{else}
							</li>
						{/if}
					{/if}
				{/if}
		{/foreach}
	</ul>
</nav>