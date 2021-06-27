<?php

$functions = [
    'block_notes_create_labels' => [
        'classname'   => '\\block_notes\\external',
        'methodname'  => 'create_label',
        'classpath'   => 'blocks/notes/classes/external.php',
        'description' => 'Creates a Label for notes',
        'type'        => 'write',
        'capabilities'=> '',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'block_notes_get_labels' => [
        'classname'   => '\\block_notes\\external',
        'methodname'  => 'get_labels',
        'classpath'   => 'blocks/notes/classes/external.php',
        'description' => 'Read the list of labels for a course',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => true,
        'loginrequired' => true,
    ],
    'block_notes_upload' => [
        'classname'   => '\\block_notes\\upload',
        'methodname'  => 'upload',
        'classpath'   => 'blocks/notes/classes/external.php',
        'description' => 'Uploads the screenshot and creates a note',
        'type'        => 'write',
        'capabilities'=> '',
        'ajax'        => true,
        'loginrequired' => true,
    ]
    /*,
    'block_notes_get_labels' => [
        'classname'   => '\\block_notes\\external',
        'methodname'  => 'get_labels',
        'classpath'   => 'blocks/notes/classes/external.php',
        'description' => 'Get labels for notes',
        'type'        => 'read',
        'capabilities'=> '',
        'ajax'        => true,
        'loginrequired' => true,
    ]*/
];
