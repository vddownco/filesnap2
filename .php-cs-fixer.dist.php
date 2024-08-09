<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var'])
    ->notPath(['tests/bootstrap.php']);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'single_quote' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'ordered_class_elements' => true,
        'date_time_immutable' => true,
    ])
    ->setFinder($finder);
