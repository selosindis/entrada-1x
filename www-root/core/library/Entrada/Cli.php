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
 * This file is the base class for the Entrada CLI program.
 *
 * @author Organization: Queen's University
 * @author Unit: Health Sciences, Education Technology Unit
 * @author Developer: Matt Simpson <simpson@queensu.ca>
 * @copyright Copyright 2016 Queen's University. All Rights Reserved.
 *
 */
class Entrada_Cli {

    const CLI_VERSION = "1.0.1";

    protected $commands = array(
        "migrate" => "Allows you to manage database migrations.",
        "model" => "Create blank models based on database information.",
        "help" => "The Entrada CLI help menu.",
    );

    protected $quiet = false;

    public function gogogo() {
        /*
         * Determine which command is being run based on argv.
         */
        $command = (isset($_SERVER["argv"][1]) ? clean_input($_SERVER["argv"][1], "alpha") : "help");

        if (array_key_exists($command, $this->commands)) {
            /*
             * Add the name of the command being run, for usage instructions.
             */
            if (isset($_SERVER["argv"][0])) {
                $_SERVER["argv"][0] = ($_SERVER["argv"][0] . " " . $command);
            }

            $controller = "Entrada_Cli_" . ucwords($command);
            $entrada = new $controller($command);

            $action = $entrada->getCliAction();

            if ($action["value"]) {
                $entrada->$action["action"]($action["value"]);
            } else {
                $entrada->$action["action"]();
            }
        } else {
            $help = new Entrada_Cli_Help("help");
            $help->playHelp($this->commands);
        }
    }

    /**
     * Gets the current action being requested.
     *
     * @return array
     */
    public function getCliAction() {
        $action = "playHelp";
        $value = false;

        try {
            $opts = new Zend_Console_Getopt($this->actions);
            $opts->parse();

            $requested_actions = $opts->getOptions();

            if ($requested_actions && ($key = array_search("quiet", $requested_actions)) !== false) {
                $this->quiet = true;

                unset($requested_actions[$key]);

                $requested_actions = array_values($requested_actions);
            }

            if ($requested_actions && isset($requested_actions[0])) {
                $action = "play" . ucwords($requested_actions[0]);

                if (isset($opts->$requested_actions[0]) && is_string($opts->$requested_actions[0])) {
                    $value = $opts->$requested_actions[0];
                }
            }
        } catch (Zend_Console_Getopt_Exception $e) {
            // @todo This should be logged: $e->getMessage();
        }

        return array("action" => $action, "value" => $value);
    }

    /**
     * Used to render the help menu for the different commands.
     *
     * @param $command
     * @param $actions
     */
    public function renderActionHelp($command, $actions) {
        print "\n";

        print "The following actions are available in the " . $this->color($command, "yellow") . " command:\n\n";

        foreach ($actions as $action => $help) {
            $col_pad = str_pad(" ", (15 - strlen($action)));

            print $this->color("--" . $action, "purple") . $col_pad . $this->color($help, "grey") . "\n";
        }

        print "\n";
    }

    public function prompt($question = "", $acceptable_responses = false, $input_rules = array()) {
        $accepted = false;

        do {
            print $question . ": ";
            fscanf(STDIN, "%s\n", $response);

            $response = clean_input($response, $input_rules);

            /*
             * Accepted if:
             * $acceptable_responses is false
             * $acceptable_responses is true and a response has been recorded.
             * $acceptable_responses is an array, and the response is a value of $acceptable_responses.
             */
            if (!$acceptable_responses || ($acceptable_responses === true && $response) || (is_array($acceptable_responses) && in_array($response, $acceptable_responses))) {
                $accepted = true;
            }
        } while (!$accepted);

        return $response;
    }

    public function color($message = "", $foreground = false) {
        $foreground_colors = array(
            "red" => "\033[31m",
            "green" => "\033[32m",
            "blue" => "\033[34m",
            "yellow" => "\033[93m",
            "purple" => "\033[35m",
            "pink" => "\033[95m",
            "grey" => "\033[37m",
            "white" => "\033[97m",
            "black" => "\033[30m",
        );

        if ($foreground && isset($foreground_colors[$foreground])) {
            $message = $foreground_colors[$foreground] . $message . "\033[0m";
        }

        return $message;
    }
}