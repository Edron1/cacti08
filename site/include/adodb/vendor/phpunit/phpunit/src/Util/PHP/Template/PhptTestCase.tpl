<?php
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;

$composerAutoload = [composerAutoload];
$phar             = [phar];

ob_start();

$GLOBALS['__PHPUNIT_ISOLATION_EXCLUDE_LIST'][] = '[job]';

if ($composerAutoload) {
    require_once $composerAutoload;

    define('PHPUNIT_COMPOSER_INSTALL', $composerAutoload);
} else if ($phar) {
    require $phar;
}

[globals]
$coverage = null;

if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
    require_once $GLOBALS['__PHPUNIT_BOOTSTRAP'];
}

if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage')) {
    $filter = new Filter;

    $coverage = new CodeCoverage(
        (new Selector)->[driverMethod]($filter),
        $filter
    );

    if ([codeCoverageCacheDirectory]) {
        $coverage->cacheStaticAnalysis([codeCoverageCacheDirectory]);
    }

    $coverage->start(__FILE__);
}

register_shutdown_function(
    function() use ($coverage) {
        $output = null;

        if ($coverage) {
            $output = $coverage->stop();
        }

        file_put_contents('[coverageFile]', serialize($output));
    }
);

ob_end_clean();

require '[job]';
