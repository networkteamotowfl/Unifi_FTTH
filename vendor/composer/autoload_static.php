<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitfe58a616801603f6720403d512ea5235
{
    public static $prefixLengthsPsr4 = array (
        'U' => 
        array (
            'UniFi_API\\' => 10,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'UniFi_API\\' => 
        array (
            0 => __DIR__ . '/..' . '/art-of-wifi/unifi-api-client/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitfe58a616801603f6720403d512ea5235::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitfe58a616801603f6720403d512ea5235::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
