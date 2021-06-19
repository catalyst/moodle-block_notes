<?php
/*
 * TODO: Remove after debugging is done
 * TOKEN: I2WBd1nLLahzYkOCXtiKgv1MKKc1HRDk
 */

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
    ]/*,
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
