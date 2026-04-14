<?php

$EM_CONF['proxy'] = [
    'title' => 'HTML Proxy',
    'description' => 'Proxy extension for embedding external HTML sources',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3@wappler.systems',
    'category' => 'fe',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '14.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];

