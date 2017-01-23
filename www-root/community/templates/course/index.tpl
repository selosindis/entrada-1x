<!doctype html>
    <!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
    <!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
    <!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
    <!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
    <!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
    <head>
        <meta charset="{$site_default_charset}">

        <title>{$page_title}</title>
        <meta name="description" content="{$page_description}" />
        <meta name="keywords" content="{$page_keywords}" />

        <meta name="robots" content="index, follow" />

        <link rel="stylesheet" href="{$sys_website_relative}/css/font-awesome/css/font-awesome.min.css">

        <link rel="stylesheet" href="{$sys_website_relative}/css/jquery/jquery-ui.css"  />
        <script src="{$sys_website_relative}/javascript/jquery/jquery.min.js"></script>
        <script src="{$sys_website_relative}/javascript/jquery/jquery-ui.min.js"></script>
        <script>jQuery.noConflict();</script>

        <script src="{$sys_website_relative}/javascript/scriptaculous/prototype.js"></script>
        <script src="{$sys_website_relative}/javascript/scriptaculous/scriptaculous.js"></script>
        <script src="{$template_relative}/javascript/libs/bootstrap.min.js"></script>
        <script src="{$template_relative}/javascript/libs/modernizr-2.5.3.min.js"></script>

        <link href="{$template_relative}/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />
        <link href="{$template_relative}/css/common.css" rel="stylesheet" type="text/css" />
        <link href="{$template_relative}/css/style.css" rel="stylesheet" type="text/css" />

        <link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />

        <link href="{$template_relative}/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />

        <script src="{$sys_website_relative}/javascript/livepipe/livepipe.js"></script>
        <script src="{$sys_website_relative}/javascript/livepipe/window.js"></script>
        <script src="{$sys_website_relative}/javascript/livepipe/selectmultiplemod.js"></script>

        {$page_head}

        %JQUERY%
    </head>
    <body>
        <header id="main-header">
            <div class="banner">
                <div class="container">
                    <div class="row-fluid">
                        <div class="span4">
                            <a class="brand" href="{$sys_website_url}"><img src="{$template_relative}/images/logo.png" width="211" height="33" alt="{$application_name}" title="{$application_name}"/></a>
                        </div>

                        {if $isAuthorized}
                            <div class="span8 pull-right">
                                <div class="welcome-area">
                                    <div class="welcome-block">
                                        <a href="{$sys_website_relative}/profile">
                                            <div class="userAvatar">
                                                <img src="{$sys_profile_photo}" width="36" height="36" style="width: 36px; height: 36px;" alt="{$member_name}" class="img-polaroid" />
                                                <span class="fa fa-user header-icon"></span>
                                            </div>
                                            {$member_name}
                                        </a>
                                        <a href="{$sys_website_relative}/assessments">
                                            <span class="fa fa-list-ul header-icon"></span>
                                            {translate}Assessment &amp; Evaluation{/translate}
                                        </a>

                                        <a href="{$sys_website_relative}/?action=logout" class="log-out"><span class="fa fa-power-off"></span> Logout</a>
                                    </div>
                                </div>
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
                    {$page_sidebar}
                    {if $twitter}
                        <div class="panel-head">
                            <h3>{translate}Twitter{/translate}</h3>
                        </div>
                        <div class="clearfix panel-body">
                            {$twitter}
                        </div>
                    {/if}
                </div>
                <div class="span9" id="content">
                    <div class="clearfix inner-content">
                        {$site_breadcrumb_trail}
                        <h1 class="community-title">{$site_community_title}</h1>
                        {$child_nav}
                        <div class="content">
                            {$page_content}
                        </div>
                        {if $is_sequential_nav}
                            {include file="sequential_nav.tpl"}
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
            <script>
                var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
                document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
            </script>
            <script>
                var pageTracker = _gat._getTracker("{$google_analytics_code}");
                pageTracker._initData();
                pageTracker._trackPageview();
            </script>
        {/if}

        {if $isAuthorized}
            <script src = "{$sys_website_url}/javascript/jquery/jquery.session.timeout.js?release={$application_version}" ></script >
            <script type = "text/javascript" >
                jQuery(document) . ready(function ($) {
                    $.timeoutMonitor({
                        sessionTime: {$maxlifetime},
                        warnTime: 60000,    // 60 seconds before it expires
                        title: '{$session_expire_title}',
                        message: '{$session_expire_message}'
                    });
                });
            </script >
        {/if}
    </body>
</html>
