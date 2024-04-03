<?php

use PhpCsFixer\Finder;

$finder = Finder::create()
    ->notPath('bootstrap')
    ->notPath('storage')
    ->notPath('packages')
    ->notPath('vendor')
    ->in(getcwd())
    ->name('*.php')
    ->notName('*.blade.php')
    ->notName('index.php')
    ->notName('_ide_helper.php')
    ->notName('server.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return \ShiftCS\styles($finder, [
    'no_unused_imports' => true,
]);
