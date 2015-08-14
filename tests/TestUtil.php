<?php

/*
 * This file is part of the puli/repository package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\Repository\Tests;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class TestUtil
{
    /**
     * Creates a temporary directory.
     *
     * @param string $namespace The directory path in the system's temporary
     *                          directory.
     * @param string $className The name of the test class.
     *
     * @return string The path to the created directory.
     */
    public static function makeTempDir($namespace, $className)
    {
        if (false !== ($pos = strrpos($className, '\\'))) {
            $shortClass = substr($className, $pos + 1);
        } else {
            $shortClass = $className;
        }

        // Usage of realpath() is important if the temporary directory is a
        // symlink to another directory (e.g. /var => /private/var on some Macs)
        // We want to know the real path to avoid comparison failures with
        // code that uses real paths only
        $basePath = realpath(sys_get_temp_dir()).'/'.$namespace.'/'.$shortClass;

        while (false === @mkdir($tempDir = $basePath.rand(10000, 99999), 0777, true)) {
            // Run until we are able to create a directory
        }

        return $tempDir;
    }

    private function __construct()
    {
    }
}
