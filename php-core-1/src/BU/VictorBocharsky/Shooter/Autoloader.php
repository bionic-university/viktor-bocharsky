<?php

namespace BU\VictorBocharsky\Shooter;

/**
 * Class Autoloader
 * @package BU\VictorBocharsky\Shooter
 */
class Autoloader
{
    /**
     * Registers Autoloader as an SPL autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not.
     */
    public static function register($prepend = false)
    {
        if (version_compare(phpversion(), '5.3.0', '>=')) {
            spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);
        } else {
            spl_autoload_register(array(__CLASS__, 'autoload'));
        }
    }

    /**
     * Handles autoloading of classes.
     *
     * @param string $class A class name.
     */
    public static function autoload($class)
    {
        if (is_file($file = dirname(__FILE__) . str_replace('\\', DIRECTORY_SEPARATOR, '/../../../../src/' . $class) .'.php')) {
            require_once $file;
        }
    }
}