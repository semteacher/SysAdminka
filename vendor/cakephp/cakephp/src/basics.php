<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
use Cake\Core\Configure;
use Cake\Error\Debugger;
use Cake\I18n\I18n;

/**
 * Basic defines for timing functions.
 */
    define('SECOND', 1);
    define('MINUTE', 60);
    define('HOUR', 3600);
    define('DAY', 86400);
    define('WEEK', 604800);
    define('MONTH', 2592000);
    define('YEAR', 31536000);

if (!function_exists('debug')) {
    /**
     * Prints out debug information about given variable.
     *
     * Only runs if debug level is greater than zero.
     *
     * @param mixed $var Variable to show debug information for.
     * @param bool|null $showHtml If set to true, the method prints the debug data in a browser-friendly way.
     * @param bool $showFrom If set to true, the method prints from where the function was called.
     * @return void
     * @link http://book.cakephp.org/3.0/en/development/debugging.html#basic-debugging
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#debug
     */
    function debug($var, $showHtml = null, $showFrom = true)
    {
        if (!Configure::read('debug')) {
            return;
        }

        $file = '';
        $line = '';
        $lineInfo = '';
        if ($showFrom) {
            $trace = Debugger::trace(['start' => 1, 'depth' => 2, 'format' => 'array']);
            $search = [ROOT];
            if (defined('CAKE_CORE_INCLUDE_PATH')) {
                array_unshift($search, CAKE_CORE_INCLUDE_PATH);
            }
            $file = str_replace($search, '', $trace[0]['file']);
            $line = $trace[0]['line'];
        }
        $html = <<<HTML
<div class="cake-debug-output">
%s
<pre class="cake-debug">
%s
</pre>
</div>
HTML;
        $text = <<<TEXT
%s
########## DEBUG ##########
%s
###########################

TEXT;
        $template = $html;
        if (php_sapi_name() === 'cli' || $showHtml === false) {
            $template = $text;
            if ($showFrom) {
                $lineInfo = sprintf('%s (line %s)', $file, $line);
            }
        }
        if ($showHtml === null && $template !== $text) {
            $showHtml = true;
        }
        $var = Debugger::exportVar($var, 25);
        if ($showHtml) {
            $template = $html;
            $var = h($var);
            if ($showFrom) {
                $lineInfo = sprintf('<span><strong>%s</strong> (line <strong>%s</strong>)</span>', $file, $line);
            }
        }
        printf($template, $lineInfo, $var);
    }

}

if (!function_exists('stackTrace')) {
    /**
     * Outputs a stack trace based on the supplied options.
     *
     * ### Options
     *
     * - `depth` - The number of stack frames to return. Defaults to 999
     * - `args` - Should arguments for functions be shown? If true, the arguments for each method call
     *   will be displayed.
     * - `start` - The stack frame to start generating a trace from. Defaults to 1
     *
     * @param array $options Format for outputting stack trace
     * @return mixed Formatted stack trace
     * @see Debugger::trace()
     */
    function stackTrace(array $options = [])
    {
        if (!Configure::read('debug')) {
            return;
        }

        $options += ['start' => 0];
        $options['start']++;
        echo Debugger::trace($options);
    }

}

if (!function_exists('__')) {
    /**
     * Returns a translated string if one is found; Otherwise, the submitted message.
     *
     * @param string $singular Text to translate
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return mixed translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__
     */
    function __($singular, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 2 ? (array)$args : array_slice(func_get_args(), 1);
        return I18n::translator()->translate($singular, $arguments);
    }

}

if (!function_exists('__n')) {
    /**
     * Returns correct plural form of message identified by $singular and $plural for count $count.
     * Some languages have more than one form for plural messages dependent on the count.
     *
     * @param string $singular Singular text to translate
     * @param string $plural Plural text
     * @param int $count Count
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return mixed plural form of translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__n
     */
    function __n($singular, $plural, $count, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 4 ? (array)$args : array_slice(func_get_args(), 3);
        return I18n::translator()->translate(
            $plural,
            ['_count' => $count, '_singular' => $singular] + $arguments
        );
    }

}

if (!function_exists('__d')) {
    /**
     * Allows you to override the current domain for a single message lookup.
     *
     * @param string $domain Domain
     * @param string $msg String to translate
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return string translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__d
     */
    function __d($domain, $msg, $args = null)
    {
        if (!$msg) {
            return;
        }
        $arguments = func_num_args() === 3 ? (array)$args : array_slice(func_get_args(), 2);
        return I18n::translator($domain)->translate($msg, $arguments);
    }

}

if (!function_exists('__dn')) {
    /**
     * Allows you to override the current domain for a single plural message lookup.
     * Returns correct plural form of message identified by $singular and $plural for count $count
     * from domain $domain.
     *
     * @param string $domain Domain
     * @param string $singular Singular string to translate
     * @param string $plural Plural
     * @param int $count Count
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return string plural form of translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__dn
     */
    function __dn($domain, $singular, $plural, $count, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 5 ? (array)$args : array_slice(func_get_args(), 4);
        return I18n::translator($domain)->translate(
            $plural,
            ['_count' => $count, '_singular' => $singular] + $arguments
        );
    }

}

if (!function_exists('__x')) {
    /**
     * Returns a translated string if one is found; Otherwise, the submitted message.
     * The context is a unique identifier for the translations string that makes it unique
     * for in the same domain.
     *
     * @param string $context Context of the text
     * @param string $singular Text to translate
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return mixed translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__
     */
    function __x($context, $singular, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 3 ? (array)$args : array_slice(func_get_args(), 2);
        return I18n::translator()->translate($singular, ['_context' => $context] + $arguments);
    }

}

if (!function_exists('__xn')) {
    /**
     * Returns correct plural form of message identified by $singular and $plural for count $count.
     * Some languages have more than one form for plural messages dependent on the count.
     * The context is a unique identifier for the translations string that makes it unique
     * for in the same domain.
     *
     * @param string $context Context of the text
     * @param string $singular Singular text to translate
     * @param string $plural Plural text
     * @param int $count Count
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return mixed plural form of translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__xn
     */
    function __xn($context, $singular, $plural, $count, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 5 ? (array)$args : array_slice(func_get_args(), 2);
        return I18n::translator()->translate(
            $singular,
            ['_count' => $count, '_singular' => $singular, '_context' => $context] + $arguments
        );
    }

}

if (!function_exists('__dx')) {
    /**
     * Allows you to override the current domain for a single message lookup.
     * The context is a unique identifier for the translations string that makes it unique
     * for in the same domain.
     *
     * @param string $domain Domain
     * @param string $context Context of the text
     * @param string $msg String to translate
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return string translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__dx
     */
    function __dx($domain, $context, $msg, $args = null)
    {
        if (!$msg) {
            return;
        }

        $arguments = func_num_args() === 4 ? (array)$args : array_slice(func_get_args(), 2);
        return I18n::translator($domain)->translate(
            $msg,
            ['_context' => $context] + $arguments
        );
    }

}

if (!function_exists('__dxn')) {
    /**
     * Returns correct plural form of message identified by $singular and $plural for count $count.
     * Allows you to override the current domain for a single message lookup.
     * The context is a unique identifier for the translations string that makes it unique
     * for in the same domain.
     *
     * @param string $domain Domain
     * @param string $context Context of the text
     * @param string $singular Singular text to translate
     * @param string $plural Plural text
     * @param int $count Count
     * @param mixed $args Array with arguments or multiple arguments in function
     * @return mixed plural form of translated string
     * @link http://book.cakephp.org/3.0/en/core-libraries/global-constants-and-functions.html#__dxn
     */
    function __dxn($domain, $context, $singular, $plural, $count, $args = null)
    {
        if (!$singular) {
            return;
        }

        $arguments = func_num_args() === 6 ? (array)$args : array_slice(func_get_args(), 2);
        return I18n::translator($domain)->translate(
            $singular,
            ['_count' => $count, '_singular' => $singular, '_context' => $context] + $arguments
        );
    }

}
