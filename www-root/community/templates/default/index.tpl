<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$site_default_charset}" />

	<title>{$page_title}</title>

	<meta name="description" content="{$page_description}" />
	<meta name="keywords" content="{$page_keywords}" />

	<meta name="robots" content="index, follow" />

	<link href="{$sys_website_url}/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="{$sys_website_url}/javascript/jquery/jquery.min.js"></script>
    <script type="text/javascript">var COMMUNITY_ID = "{$community_id}";</script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/jquery/jquery-ui.min.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>
    <script src="{$template_relative}/js/collapse-menu.js"></script>

	<link href="{$template_relative}/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="all" />
	<link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />

	<link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />

	<link href="{$protocol}://fonts.googleapis.com/css?family=Roboto:400,400italic,700,700italic,300,300italic" rel="stylesheet" type="text/css" />

	{$page_head}
</head>
<body>
{$sys_system_navigator}
<div class="container">
	<div class="row">
		<div class="span2-5">
			{include file="navigation_primary.tpl" site_primary_navigation=$site_primary_navigation}
		</div>
		<div class="span9-5 content-container">
			<div class="row">
				<div class="span9-5">
					<div class="header table">
						<div class="table-cell">
							<div class="header-icon"></div>
						</div>
						<div class="table-cell table-cell-full-width">
							<div class="table">
								<div class="table-cell middle community-title">{$site_community_title}</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
                <div id="community-nav-collapse">
                    <a id="community-nav-collapse-toggle" href="#" class=""><span class="menu-icon" id="community-nav-menu-icon" title="Administrative Navigation"></span></a>
                </div>
				<div class="span6-5 content-area">
                    
					{$site_breadcrumb_trail}
					{$child_nav}
					<div class="content">
						{$page_content}
					</div>
					{if $is_sequential_nav}
						<div style="text-align:right;">
							{if $next_page_url != "#" && $previous_page_url != "#"}
								<p><a href="{$previous_page_url}"><< Previous</a> | <a href="{$next_page_url}">Next >></a></p>
							{elseif $next_page_url != "#" && $previous_page_url == "#"}
								<p> <a href="{$next_page_url}"> Next >></a></p>
							{elseif $next_page_url == "#" && $previous_page_url != "#"}
								<p> <a href="{$previous_page_url}"><< Previous</a> </p>
							{elseif $next_page_url == "#" && $previous_page_url == "#"}
								<p> </p>
							{/if}
						</div>
					{/if}
				</div>
				<div id="right-community-nav" class="span3 right-community-nav-expanded">
                    <div class="inner-sidebar no-printing">
                        {if $is_logged_in && $user_is_admin}
                            {include file="sidebar-blocks/admin_block.tpl"}
                        {/if}
                        {include file="sidebar-blocks/entrada_block.tpl"}
                        {if $is_logged_in && $user_is_member}
                            {include file="sidebar-blocks/community_block.tpl"}
                        {/if}
                    </div>
				</div>
			</div>
			<div class="footer span9">
				<div class="content-copyright">
                    {$copyright_string}
				</div>
			</div>
		</div>
	</div>
</div>
{if !$development_mode && $google_analytics_code}
    <script type="text/javascript">
        var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
        document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script type="text/javascript">
        var pageTracker = _gat._getTracker("{$google_analytics_code}");
        pageTracker._initData();
        pageTracker._trackPageview();
    </script>
{/if}
</body>
</html>