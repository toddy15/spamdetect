<?php

declare(strict_types=1);

dataset('tokens', [
    ['', []],
    [
        'This is a sentence',
        ['This', 'is', 'a', 'sentence']
    ],
    [
        'Some UTF-8 characters dünn été außen 💚️',
        ['Some', 'UTF-8', 'characters', 'dünn', 'été', 'außen', '💚️']
    ],
    [
        'Multiple     spaces',
        ['Multiple', 'spaces']
    ],
    [
        '   Spaces at the start and end     ',
        ['Spaces', 'at', 'the', 'start', 'and', 'end']
    ],
    [
        "A unix new \n line",
        ['A', 'unix', 'new', 'line']
    ],
    [
        "A windows new \r\n line",
        ['A', 'windows', 'new', 'line']
    ],
    [
        'Some <h1>HTML</h1> and <custom>XML</custom> tags',
        ['Some', '<h1>', 'HTML', '</h1>', 'and', '<custom>', 'XML', '</custom>', 'tags']
    ],
    [
        '<div><span>Nested</span><p>tags</p></div>',
        ['<div>', '<span>', 'Nested', '</span>', '<p>', 'tags', '</p>', '</div>']
    ],
    [
        'Here are :emojis: with two colons',
        ['Here', 'are', ':emojis:', 'with', 'two', 'colons']
    ],
    [
        'Even :more: :emojis: :without::space:',
        ['Even', ':more:', ':emojis:', ':without:', ':space:']
    ],
    [
        'This: is not an emoji:',
        ['This:', 'is', 'not', 'an', 'emoji:']
    ],
    [
        'These are::double colons',
        ['These', 'are::double', 'colons']
    ],
    [
        'Some other [emoji] markup',
        ['Some', 'other', '[emoji]', 'markup']
    ],
    [
        '[Multiple][emojis][here]',
        ['[Multiple]', '[emojis]', '[here]']
    ],
]);
