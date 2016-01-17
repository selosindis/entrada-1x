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
                        <div class="span5">
                            <a href="{$sys_website_url}"><img src="{$template_relative}/images/logo.png" width="294" height="46" alt="{$application_name}" title="{$application_name}"/></a>
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
                                        <a href="{$sys_website_relative}/profile">My Profile</a> |
                                        <a href="{$sys_website_relative}/evaluations">My Evaluations</a>
                                        {$sys_profile_evaluations}
                                    </div>
                                </div>
                            </div>
                            <div class="span2">
                                <a href="{$sys_website_relative}/?action=logout" class="log-out">Logout <i class="icon icon-logout"></i></a>
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
    </body>
</html>
