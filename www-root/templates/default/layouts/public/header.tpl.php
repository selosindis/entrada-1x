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

        <link href="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/css/bootstrap.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="all" />
        <link href="<?php echo ENTRADA_RELATIVE; ?>/css/font-awesome/css/font-awesome.min.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="all" />
        <link href="<?php echo ENTRADA_RELATIVE; ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="all" />
        <link href="<?php echo ENTRADA_RELATIVE; ?>/css/print.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="print" />
        <link href="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/css/common.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="all" />
        <link href="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/css/style.css?release=<?php echo html_encode(APPLICATION_VERSION); ?>" rel="stylesheet" media="all" />

        <link href="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <script>
            if (self !== top) {
                top.location = self.location;
            }
        </script>
        <link href="<?php echo ENTRADA_RELATIVE; ?>/css/jquery/jquery-ui.css" rel="stylesheet" />

        <script type="text/javascript">
            %JAVASCRIPT_TRANSLATIONS%
        </script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/jquery/jquery-ui.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script>jQuery.noConflict();</script>
        %JQUERY%

        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/prototype.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/scriptaculous/scriptaculous.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/livepipe.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/window.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/livepipe/selectmultiplemod.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/common.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo ENTRADA_RELATIVE; ?>/javascript/selectmenu.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>

        <script src="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/js/libs/bootstrap.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        <script src="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/js/libs/modernizr-2.5.3.min.js?release=<?php echo html_encode(APPLICATION_VERSION); ?>"></script>
        %HEAD%
    </head>
    <body>
        <header id="main-header">
            <div class="banner">
                <div class="container">
                    <div class="row-fluid">
                        <div class="span4">
                            <a class="brand" href="<?php echo ENTRADA_URL; ?>"><img src="<?php echo $ENTRADA_TEMPLATE->relative(); ?>/images/logo.png" width="211" height="33" alt="<?php echo APPLICATION_NAME; ?>" title="<?php echo APPLICATION_NAME; ?>"/></a>
                        </div>
                        <?php
                        if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) {
                            $assessment_tasks = 0;//Entrada_Utilities_Assessments_AssessmentTask::countAllIncompleteAssessmentTasks($ENTRADA_USER->getActiveID());
                            ?>
                            <div class="span8 pull-right">
                                <div class="welcome-area">
                                    <div class="welcome-block">
                                        <a href="<?php echo ENTRADA_RELATIVE; ?>/profile">
                                            <div class="userAvatar">
                                                <?php echo "<img src=\"".webservice_url("photo", array($ENTRADA_USER->getID(), (isset($uploaded_file_active) && $uploaded_file_active ? "upload" : (!file_exists(STORAGE_USER_PHOTOS."/".$ENTRADA_USER->getID()."-official") && file_exists(STORAGE_USER_PHOTOS."/".$ENTRADA_USER->getID()."-upload") ? "upload" : "official"))))."\" alt=\"".html_encode($_SESSION["details"]["firstname"]." ".$_SESSION["details"]["lastname"])."\" class=\"img-polaroid\" />"; ?>
                                                <span class="fa fa-user header-icon"></span>
                                            </div>
                                            <?php echo $ENTRADA_USER->getFirstname() . " " . $ENTRADA_USER->getLastname(); ?>
                                        </a>
                                        <a href="<?php echo ENTRADA_RELATIVE; ?>/assessments">
                                            <span class="fa fa-list-ul header-icon"></span>
                                            <?php
                                            echo $translate->_("Assessment &amp; Evaluation");
                                            if ($assessment_tasks > 0) {
                                                echo "<span class=\"space-left badge badge-success\">" . $assessment_tasks . "</span>";
                                            } ?>
                                        </a>
                                        <a href="<?php echo ENTRADA_RELATIVE; ?>/?action=logout" class="log-out"><span class="fa fa-power-off"></span> Logout</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"])) {
                ?>
                <div class="navbar">
                    <div class="navbar-inner">
                        <div class="container no-printing">
                            <?php echo navigator_tabs(); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </header>
        <div class="container" id="page">
            <div class="row-fluid">
                <?php
                // If the hash is set (GET or POST) and the assessment module is being accessed, an external assessor needs access to the sidebar to view assessment targets.
                $hash = false;
                if ((isset($_GET["external_hash"]) && $tmp_input = clean_input($_GET["external_hash"], array("trim", "striptags"))) || (isset($_POST["external_hash"]) && $tmp_input = clean_input($_POST["external_hash"], array("trim", "striptags")))) {
                    $hash = true;
                }
                if ((isset($_SESSION["isAuthorized"])) && ($_SESSION["isAuthorized"]) || ($MODULE == "assessment" && $hash)) {
                    ?>
                    <div class="span3 no-printing" id="sidebar">%SIDEBAR%</div>
                    <div class="span9" id="content">
                    <?php
                } else {
                    ?>
                    <div class="span12" id="content">
                    <?php
                }
                ?>
                <div class="clearfix inner-content">
                    <div class="clearfix">%BREADCRUMB%</div>
