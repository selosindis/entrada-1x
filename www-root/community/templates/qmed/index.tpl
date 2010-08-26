<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$site_default_charset}" />

	<title>{$page_title}</title>

	<meta name="description" content="{$page_description}" />
	<meta name="keywords" content="{$page_keywords}" />

	<meta name="robots" content="index, follow" />

	
		<meta name="keywords" content="" />
		<meta name="description" content="" />

		<meta name="robots" content="index,follow" />

		<meta http-equiv="pics-label" content='(pics-1.1 "http://www.icra.org/ratingsv02.html" comment "ICRAonline EN v2.0" l gen true for "http://www.qmed.ca" r (nz 1 vz 1 lz 1 oz 1 cz 1) "http://www.rsac.org/ratingsv01.html" l gen true for "http://www.qmed.ca" r (n 0 s 0 v 0 l 0))' />

		<link rel="shortcut icon" href="favicon.ico" />
		<link rel="icon" href="favicon.ico" type="image/x-icon" />

		<link rel="P3Pv1" href="w3c/p3p.xml">


		<link href="{$template_relative}/css/blueprint/screen.css" media="screen, projection" rel="stylesheet" type="text/css" />
		<link href="{$template_relative}/css/blueprint/print.css" media="print" rel="stylesheet" type="text/css" />
		<link href="{$template_relative}/css/blueprint/ie.css" media="screen, projection" rel="stylesheet" type="text/css" />

		<link href="{$template_relative}/css/jquery/jquery-ui.css" media="screen" rel="stylesheet" type="text/css" />

		<link href="{$template_relative}/css/stylesheet.css" rel="stylesheet" type="text/css" media="all" />
		<link href="{$template_relative}/css/print.css" rel="stylesheet" type="text/css" media="print" />
		<link href="{$template_relative}/css/qmed.css" media="screen" rel="stylesheet" type="text/css" />

		<link href="http://meds.queensu.ca/courses/community/feeds/qmed/rss" rel="alternate" type="application/rss+xml" title="QMed.ca News and Updates" />

		<script type="text/javascript" src="{$template_relative}/javascript/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="{$template_relative}/javascript/jquery/jquery-ui.min.js"></script>
		<script type="text/javascript" src="{$template_relative}/javascript/jquery/jquery.equalheights.js"></script>
		<script type="text/javascript" src="{$template_relative}/javascript/jquery/jquery.corner.js"></script>

		<script type="text/javascript">
		{literal}
		$(function() {
//			$('button, input:submit').button();
			$(".qmed-body").equalHeights(602);
			$(".news-item").corner('tr bl br');

		});
		{/literal}
		</script>
	</head>
	<body>
		<div id="qmed-website" class="container">
			<div id="qmed-search-bar" class="span-12 last">
				<div style="margin-right: 4px">
					<form action="http://www.qmed.ca/search" method="get" id="search-form">
						<input type="text" name="query" id="search-query" value="" />
						<input type="submit" value="Search" />
					</form>
				</div>
			</div>
			<div id="qmed-header" class="span-12 last">
				<img src="{$template_relative}/images/bg-qmed-header.jpg" width="972" height="248" alt="QMed.ca Heading Image" title="" />
				
				<img id="ribbon-left" src="{$template_relative}/images/ribbon-left.png" width="201" height="136" alt="" title="" />
				<img id="ribbon-right" src="{$template_relative}/images/ribbon-right.png" width="133" height="258" alt="" title="" />
			</div>

			<div id="qmed-navigation" class="span-3 qmed-body">
				{include file="navigation_primary.tpl" site_primary_navigation=$site_primary_navigation}
			</div>
			<div id="qmed-content" class="span-9 last qmed-body">
				<div class="content">
					{$page_content}
				</div>
			</div>
			
			<div id="qmed-footer" class="span-12 last">
				<div class="content right">
					<span class="copyright">{php}echo COPYRIGHT_STRING;{/php}</span>
				</div>

				<img id="footer-left" src="{$template_relative}/images/footer-left.png" width="9" height="46" alt="" title="" />
				<img id="footer-right" src="{$template_relative}/images/footer-right.png" width="9" height="46" alt="" title="" />
			</div>
		</div>
	</body>
</html>