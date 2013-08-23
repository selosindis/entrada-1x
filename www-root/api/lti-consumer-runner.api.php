<?php
/**
 * Entrada [ http://www.entrada-project.org ]
 *
 * Entrada is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Entrada is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Entrada.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Loads the Course link wizard when a course director wants to add / edit
 * a linked resource on the Manage Courses > Content page.
 */

@set_include_path(implode(PATH_SEPARATOR, array(
    dirname(__FILE__) . "/../core",
    dirname(__FILE__) . "/../core/includes",
    dirname(__FILE__) . "/../core/library",
    get_include_path(),
)));

/**
 * Include the Entrada init code.
 */
require_once("init.inc.php");

require_once LTI_DIR . '/oauth/oauth-utils.class.php';
require_once LTI_DIR . '/oauth/oauth-exception.class.php';
require_once LTI_DIR . '/oauth/oauth-request.class.php';
require_once LTI_DIR . '/oauth/oauth-token.class.php';
require_once LTI_DIR . '/oauth/oauth-consumer.class.php';
require_once LTI_DIR . '/oauth/oauth-signature-method.interface.php';
require_once LTI_DIR . '/oauth/method/oauth-signature-method-hmac-sha1.class.php';
require_once LTI_DIR . '/LTIConsumer.class.php';

ob_start("on_checkout");

if((!isset($_SESSION["isAuthorized"])) || (!$_SESSION["isAuthorized"])) {
    echo "<div id=\"scripts-on-open\" style=\"display: none;\">\n";
    echo "alert('It appears as though your session has expired; you will now be taken back to the login page.');\n";
    echo "if(window.opener) {\n";
    echo "	window.opener.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
    echo "	top.window.close();\n";
    echo "} else {\n";
    echo "	window.location = '".ENTRADA_URL.((isset($_SERVER["REQUEST_URI"])) ? "?url=".rawurlencode(clean_input($_SERVER["REQUEST_URI"], array("nows", "url"))) : "")."';\n";
    echo "}\n";
    echo "</div>\n";
    exit;
} else {
    $constValues = array('USER_ID'    => $ENTRADA_USER->getID(),
                         'USER_EMAIL' => $ENTRADA_USER->getEmail());

    $LTI_ID = 0;
    $WIDTH  = 400;
    $HEIGHT = 400;
    $IS_EVENT = false;

    if((isset($_GET["ltiid"])) && ((int) trim($_GET["ltiid"]))) {
        $LTI_ID = (int) trim($_GET["ltiid"]);
    }

    if((isset($_GET["width"])) && ((int) trim($_GET["width"]))) {
        $WIDTH = (int) trim($_GET["width"]);
    }

    if((isset($_GET["height"])) && ((int) trim($_GET["height"]))) {
        $HEIGHT = (int) trim($_GET["height"]);
    }

    if((isset($_GET["event"])) && ((int) trim($_GET["event"]))) {
        $IS_EVENT = true;
    }

    if($WIDTH <= 0)  { $WIDTH = 400; }
    if($HEIGHT <= 0) { $HEIGHT = 400; }

    $WIDTH  = $WIDTH - 1;
    $HEIGHT = $HEIGHT - 70;

    if($LTI_ID) {
        $query	= $IS_EVENT ? 'SELECT * FROM `event_lti_consumers` WHERE `id` = ' . $db->qstr($LTI_ID)
                            : 'SELECT * FROM `course_lti_consumers` WHERE `id` = ' . $db->qstr($LTI_ID);
        $result	= $db->GetRow($query);
        if($result) {
            add_statistic('LTI Module', 'Run lti consumer "' . $result['lti_title'] . '"');
            $parameters = array();
            if($result['lti_params']) {
                $paramsList = explode(';', $result['lti_params']);
                if($paramsList != null && count($paramsList) > 0) {
                    foreach($paramsList as $param) {
                        $parts = explode('=', $param);
                        $key   = trim($parts[0]);
                        $value = parseParameterValue(trim($parts[1]), $constValues);

                        if($key && $value) {
                            $parameters[$key] = $value;
                        }
                    }
                }
            }

            $ltiConsumer  = new LTIConsumer();
            $signedParams = $ltiConsumer->sign($parameters, $result['launch_url'], 'POST', $result['lti_key'], $result['lti_secret']);
            ?>
            <div id="ltiContainer">
            <form id="ltiSubmitForm" name="ltiSubmitForm" method="POST" action="<?php echo $result['launch_url']; ?>" target="ltiTestFrame" enctype="application/x-www-form-urlencoded">
                <?php
                if($signedParams && count($signedParams) > 0) {
                    foreach($signedParams as $key => $value) {
                        $key   = htmlspecialchars($key);
                        $value = htmlspecialchars($value);

                        echo '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
                    }
                }
                ?>
                <input id="ltiSubmitBtn" type="submit" style="display: none;"/>
            </form>
            <h3 class="border-below" style="margin-top: -30px;">LTI Provider - <?php echo $result['lti_title']; ?></h3>
            <iframe name="ltiTestFrame" id="ltiTestFrame" src="" width="<?php echo $WIDTH; ?>" height="<?php echo $HEIGHT; ?>" scrolling="auto" style="border: 1px solid rgba(0, 0, 0, 0.075);" transparency=""></iframe>
            <div>
                <input type="button" class="btn" value="Close" onclick="closeLTIDialog()" />
            </div>
            <div id="scripts-on-open" style="display: none;">
                submitLTIForm();
            </div>
            </div>
            <?php
        } else {
            $ERROR++;
            $ERRORSTR[]	= "Can't get LTI Provider from database" . $query;

            echo display_error();
        }

    } else {
        $ERROR++;
        $ERRORSTR[]	= "You must set LTI Provider identifier";

        echo display_error();
    }
}

function parseParameterValue($value, $constValues) {
    $result = $value;

    switch($value) {
        case '%USER_ID%':
            if(isset($_SESSION["isAuthorized"]) && $_SESSION["isAuthorized"] && isset($constValues['USER_ID'])) {
                $result = $constValues['USER_ID'];
            }
            break;
        case '%USER_EMAIL%':
            if(isset($_SESSION["isAuthorized"]) && $_SESSION["isAuthorized"] && isset($constValues['USER_EMAIL'])) {
                $result = $constValues['USER_EMAIL'];
            }
            break;
    }

    return $result;
}