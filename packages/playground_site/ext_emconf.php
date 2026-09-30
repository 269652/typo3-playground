<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Playground Site',
    'description' => 'Site package: site set, file-based backend layouts, page TSconfig and PAGEVIEW templates.',
    'category' => 'templates',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => ['typo3' => '14.3.0-14.3.99', 'fluid_styled_content' => '14.3.0-14.3.99'],
        'conflicts' => [],
        'suggests' => [],
    ],
];
