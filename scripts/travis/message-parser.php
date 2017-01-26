#!/usr/bin/env php
<?php
/**
 * @file
 * Parse a given commit string for variables given in the form of [VAR=VALUE].
 *
 * Calling like this:
 * string-parser.php "Commit with variable: [MY_VAR=some_value]" MY_VAR
 * will return "some_value".
 */

return find_variable_in_string($argv[1], $argv[2]);

/**
 * Find a given variable in a string.
 *
 * To find a variable in a string you have to provide it in the form of
 * [MY_VAR=some_value] within the string (including the brackets).
 *
 * @param string $string
 *   The string that contains the variable.
 * @param string $variable
 *   The variable name.
 */
function find_variable_in_string($string, $variable) {
  $expression = '/\[' . $variable . '=([^\]]*)\]/';

  if (preg_match($expression, $string, $matches)) {
    echo $matches[1];
  }
}
