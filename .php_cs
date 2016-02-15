<?php
$path = getcwd();

$args = array_slice($_SERVER['argv'], array_search('fix', $_SERVER['argv'], true) + 1);
$args = array_filter($args, function($arg) { return strpos($arg, '-') !== 0;});
$args = array_filter($args, 'file_exists');
if (count($args)) {
    $path = getcwd() . DIRECTORY_SEPARATOR . $args[0];
}

$finder = Symfony\CS\Finder\DefaultFinder::create();
if (is_dir($path)) {
    $finder->name('*.php')->in($path);
} else {
    $finder->path($path);
}

if (file_exists(__DIR__ . '/.gitignore')) {
    foreach (file(__DIR__ . '/.gitignore') as $ignore) {
        $ignore = trim($ignore);
        if (is_dir(__DIR__ . '/' . trim($ignore, '/'))) {
            $finder->exclude(trim($ignore, '/'));
        } else {
            $finder->notName(trim($ignore, '/'));
        }
    }
}

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    ->finder($finder)
    ->level(\Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        'phpdoc_order',
        'align_double_arrow',
        'align_equals',
        'concat_with_spaces',
        'ereg_to_preg',
        'multiline_spaces_before_semicolon',
        'newline_after_open_tag',
        'no_blank_lines_before_namespace',
        'ordered_use',
        'header_comment',
        'short_array_syntax',
//        'php4_constructor',
//        'phpdoc_var_to_type',
    ]);
