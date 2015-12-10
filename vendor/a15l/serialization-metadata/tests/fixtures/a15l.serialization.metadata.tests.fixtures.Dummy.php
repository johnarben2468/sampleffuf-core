<?php

return array(
    'default-deserialize-event' => 'onDeserialize',
    'default-serialize-event' => 'onSerialize',
    'readonly' => array(
        'readonly1' => true,
        'readonly2' => true
    ),
    'ignore' => array(
        'ignored1' => true,
        'ignored2' => true
    ),
    'aliases' => array(
        'aliasName' => 'alias1',
        'aliasName2' => 'alias2'
    ),
    'types' => array(
        'foo' => array(
            'float' => ''
        ),
        'date' => array(
            'DateTime' => 'Y-m-d'
        )
    ),
    'events' => array(
        'foo' => array(
            'deserialize' => 'fooDeserialize',
        ),
        'bar' => array(
            'serialize' => 'barSerialize'
        )
    )
);