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

    <link href="{$template_relative}/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />

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
                <h1><a href="{$sys_website_url}"><img src="{$template_relative}/images/logo.png" alt="{$application_name}" title="{$application_name}"/></a></h1>
            </div>

            {if $isAuthorized}
                <div class="span5">
                    <div class="welcome-area">
                        <div class="userAvatar">
                            <a href="#"><img src="{$sys_profile_photo}" width="32" height="42" style="width: 32px; height: 42px;" alt="{$member_name}" class="img-polaroid" /></a>
                        </div>
                        <div class="welcome-block">
                            Welcome <span class="userName">{$member_name}</span>
                            <br />
                            <a href="{$sys_website_url}/profile">My Profile</a> |
                            <a href="{$sys_website_url}/evaluations">My Evaluations</a>
                            {$sys_profile_evaluations}
                        </div>
                    </div>
                </div>
                <div class="span2">
                    <a href="{$sys_website_url}/?action=logout" class="log-out">Logout <i class="icon icon-logout"></i></a>
                </div>
            {/if}
        </div>
    </div>
</div>

{if $isAuthorized}
    <div class="navbar">
        <div class="navbar-inner">
            <div class="container no-printing">
                {$navigator_tabs}
            </div>
        </div>
    </div>
{/if}
</header>
<div id="site-container" class="container">
	<div id="site-body" class="row-fluid">
			<div class="span3 no-printing" id="sidebar">
				{include file="navigation_primary.tpl" site_primary_navigation=$site_primary_navigation}
				<br />
				{$page_sidebar}
			</div>
			<div class="span9" id="content">
                <div class="clearfix inner-content">
                    {$site_breadcrumb_trail}
                    <h1 class="community-title">{$site_community_title}</h1>
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
</div>
<footer id="main-footer">
	<div class="no-printing container">
        {$copyright_string}
	</div>
</footer>
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