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
	<script type="text/javascript" src="{$sys_website_url}/javascript/jquery/jquery-ui.min.js"></script>
	<script type="text/javascript">jQuery.noConflict();</script>

	<script type="text/javascript" src="{$sys_website_url}/javascript/scriptaculous/prototype.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/scriptaculous/scriptaculous.js"></script>
	<script type="text/javascript" src="{$template_relative}/javascript/libs/bootstrap.min.js"></script>
	<script type="text/javascript" src="{$template_relative}/javascript/libs/modernizr-2.5.3.min.js"></script>
	
	<link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />
	<link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />
	<link href="{$template_relative}/css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="{$template_relative}/css/common.css" rel="stylesheet" type="text/css" />
	<link href="{$template_relative}/css/style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/livepipe.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/window.js"></script>
	<script type="text/javascript" src="{$sys_website_url}/javascript/livepipe/selectmultiplemod.js"></script>

	{$page_head}

	<style type="text/css">
	body {literal}{{/literal}
		behavior: url('{$sys_website_url}/css/fixes/csshoverfix.htc');
	{literal}}{/literal}
	#site-header {literal}{{/literal}
		background: transparent url('{$template_relative}/images/header-{$site_theme}.gif') no-repeat bottom;
	{literal}}{/literal}
	</style>
	%JQUERY%
</head>
<body>
{$sys_system_navigator}
<header id="main-header">
<div class="banner">
    <div class="container">
        <div class="row-fluid">
            <div class="span5">
                <h1><a href="{$sys_website_url}"><img src="{$template_relative}/images/logo.png" alt="{php} echo APPLICATION_NAME; {/php}" title="{php} echo APPLICATION_NAME; {/php}"/></a></h1>                
            </div>
            {php}
            if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) : {/php}
                <div class="span5">

                </div>
                <div class="span2">
                    <a href="{php} echo ENTRADA_RELATIVE; {/php}/?action=logout" class="log-out">Logout <i class="icon icon-logout"></i></a>
                </div>
       		{php}
       		endif;
       		{/php}            
        </div>
    </div>
</div>


{php} if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])): {/php}
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container no-printing">
                {php} 
                	echo navigator_tabs(); 
            	{/php}
            </div>
        </div>
    </div>
{php}endif;{/php}
</header>
<div id="site-container" class="container">
	<div id="site-body" class="row-fluid">
			<div class="span3">
				{include file="navigation_primary.tpl" site_primary_navigation=$site_primary_navigation}
				<br />
				{$page_sidebar}
			</div>
			<div class="span9">
				{$site_breadcrumb_trail}
				<span class="community-title">{$site_community_title}</span>
				{$child_nav}
				<div class="content" style="padding-left: 10px;">
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
						{else $next_page_url == "#" && $previous_page_url == "#"}
							<p> </p>
						{/if}
					</div>
				{/if}
			</div>
	</div>	
</div>
<footer id="main-footer">
	<div class="no-printing container">
		{php} echo COPYRIGHT_STRING;{/php}
	</div>
</footer>
{php}if(((!defined("DEVELOPMENT_MODE")) || (!(bool) DEVELOPMENT_MODE)) && (defined("GOOGLE_ANALYTICS_CODE")) && (GOOGLE_ANALYTICS_CODE != "")) :{/php}
<script type="text/javascript">
	var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
	document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
	var pageTracker = _gat._getTracker("{php} echo GOOGLE_ANALYTICS_CODE;{/php}");
	pageTracker._initData();
	pageTracker._trackPageview();
</script>
{php}endif;{/php}
</body>
</html>