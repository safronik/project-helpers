<?php

namespace Safronik\Helpers;

class VersionHelper
{
    
    /**
     * Checks if the string is correct semantic version
     *
     * @param string $version
     *
     * @return bool
     */
    public static function isCorrectSemanticVersion( string $version ): bool
    {
        return (bool) preg_match(
            '@^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$@',
            $version,
        );
    }
    
    /**
     * Gets major.minor.patch version from the version string
     *
     * @param $version
     *
     * @return string
     */
    public static function standardizeVersion( $version ): string
    {
        preg_match(
            '@^(?P<major>0|[1-9]\d*)\.(?P<minor>0|[1-9]\d*)\.(?P<patch>0|[1-9]\d*)(?:-(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+(?P<buildmetadata>[0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$@',
            $version,
            $version_groups
        );
        
        return implode(
            '.',
            [
                $version_groups['major'],
                $version_groups['minor'],
                $version_groups['patch'],
            ]
        );
    }
    
    /**
     * Compare two versions
     *
     * @param string $version1
     * @param string $version2
     *
     * @return int -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
     */
    public static function compare( string $version1, string $version2 ): int
    {
        return version_compare( $version1, $version2 );
    }
}