			<img src="{$template_relative}/images/navigation-hr-top.png" width="223" height="3" alt="" title="" style="margin-left: 2px;" />	
			<ul>
				{foreach from=$site_primary_navigation key=key item=menu_item name=navigation}
					{if $menu_item.link_parent > 0}
						<li class="sub-pages{if $menu_item.link_selected} selected{/if}"><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}>{$menu_item.link_title}</a></li>
					{else}
						<li{if $menu_item.link_selected} class="selected"{/if}><a href="{$site_community_relative}{$menu_item.link_url}"{if $menu_item.link_new_window} target="_blank"{/if}>{$menu_item.link_title}</a></li>
					{/if}
				{/foreach}
			</ul>
			<img src="{$template_relative}/images/navigation-hr-bottom.png" width="223" height="3" alt="" title="" style="margin-left: 2px;" />