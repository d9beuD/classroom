<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return new Config()
    ->setRules([
        '@Symfony' => true,
    ])
    ->setFinder($finder)
;
