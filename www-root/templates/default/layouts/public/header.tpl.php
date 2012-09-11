<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Serves as the main Entrada "public" header layout file.
 *
 * @author Organisation: University of Calgary
 * @author Unit: Undergraduate Medical Education
 * @author Developer: Doug Hall <hall@ucalgary.ca>
 * @copyright Copyright 2010 Queen's University. All Rights Reserved.
 *
*/
?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="<?php echo DEFAULT_CHARSET; ?>" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<title>%TITLE%</title>

	<meta name="description" content="%DESCRIPTION%" />
	<meta name="keywords" content="%KEYWORDS%" />

	<meta name="robots" content="index, follow" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/css/bootstrap.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/css/bootstrap-responsive.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/css/print.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="print" />
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	<link href="<?php echo TEMPLATE_RELATIVE; ?>/css/style.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" type="text/css" media="all" />
	

	<link href="<?php echo TEMPLATE_RELATIVE; ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
	<link href="<?php echo ENTRADA_RELATIVE; ?>/w3c/p3p.xml" rel="P3Pv1" type="text/xml" />
	
	<link href="<?php echo ENTRADA_URL;?>/css/jquery/jquery-ui.css" rel="stylesheet" type="text/css" />	
	<!--<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery-ui.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>	
	<script type="text/javascript">jQuery.noConflict();</script>
	%JQUERY%-->
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery-ui.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script>window.jQuery || document.write('<script src="js/libs/jquery-1.7.2.min.js"><\/script>')</script>
	<script type="text/javascript">jQuery.noConflict();</script>
	%JQUERY%

	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/prototype.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/scriptaculous.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/livepipe.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/window.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/selectmultiplemod.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/common.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo ENTRADA_RELATIVE; ?>/javascript/selectmenu.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	
	<script type="text/javascript" src="<?php echo TEMPLATE_RELATIVE; ?>/js/libs/bootstrap.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	<script type="text/javascript" src="<?php echo TEMPLATE_RELATIVE; ?>/js/libs/modernizr-2.5.3.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
	%HEAD%
</head>
<body>
<?php echo load_system_navigator(); ?>
<!--<div class="no-printing" style="margin-bottom: 5px">
	<div class="tail-top">
		<div class="entrada-header">
			<a href="<?php echo ENTRADA_URL; ?>"><img src="<?php echo TEMPLATE_RELATIVE; ?>/images/logo.jpg" alt="<?php echo APPLICATION_NAME; ?>" title="<?php echo APPLICATION_NAME; ?>" width="359" height="100" border="0" /></a>
		</div>
	</div>
</div>-->
<header id="main-header">
		<div class="banner">
				<div class="container">
					<div class="row-fluid">
						<div class="span5">
							<h1><a href="<?php echo ENTRADA_URL; ?>"><img src="<?php echo TEMPLATE_RELATIVE; ?>/images/logo.png" alt="<?php echo APPLICATION_NAME; ?>" title="<?php echo APPLICATION_NAME; ?>"/></a></h1>
						</div> <!-- /span5 -->
						<div class="span5">
								<div class="welcome-area">
										<a href="#"><span class="userAvatar"><img src="http://lorempixel.com/35/35/"></span></a> Welcome <span class="userName">Matt Simpson</span>
								</div>
						</div>
						<div class="span2">
								<a href="<?php echo ENTRADA_RELATIVE; ?>?action=logout\" class="log-out">Logout <i class="icon icon-logout"></i></a>
						</div>
					</div> <!--/row-fluid-->
				</div> <!--/container-->
		</div>
	
	<?php
	if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) {
		?>
	<div class="navbar">
			<div class="navbar-inner">
				<div class="container no-printing">
					<?php echo navigator_tabs();?>
				</div> <!-- /container -->
			</div><!--/navbar-inner-->
		</div> <!--/navbar-->
				<?php
	}
	?>
</header> <!--/main-header -->

<div class="container" id="page">
	<div class="row-fluid">
		<?php
	if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) {
		?>
		<div class="span3 no-printing" id="sidebar">%SIDEBAR%</div> <!--/span3-->
		<div class="span9" id="content">
			<?php
	}else { ?>
		<div class="span12" id="content">
	<?php }
	?>
		<!--<div class="span9" id="content">-->
				<div class="clearfix inner-content">
				<div class="clearfix">%BREADCRUMB%</div>
<!--
<div class="row-fluid">
	<div class="container">
	<div id="system-sidebar" class="no-printing">
		<div>
			%SIDEBAR%
		</div>
	</div>
	<div id="system-content">
		<div>
			%BREADCRUMB% -->