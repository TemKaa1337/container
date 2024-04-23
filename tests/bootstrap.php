<?php

declare(strict_types=1);

$envVariables = [
    'APP_BOUND_VAR'           => 'bound_variable_value',
    'ENV_CASTABLE_STRING_VAR' => '10.1',
    'ENV_FLOAT_VAR'           => '10.1',
    'ENV_BOOL_VAL'            => 'false',
    'ENV_INT_VAL'             => '3',
    'ENV_STRING_VAL'          => 'string',
    'ENV_STRING_VAR'          => 'string',
    'ENV_VAR_1'               => 'test_one',
    'ENV_VAR_2'               => '10.1',
    'ENV_VAR_3'               => 'test-three',
    'ENV_VAR_4'               => 'true',
    'CIRCULAR_ENV_VARIABLE_1' => 'env(CIRCULAR_ENV_VARIABLE_2)',
    'CIRCULAR_ENV_VARIABLE_2' => 'env(CIRCULAR_ENV_VARIABLE_1)',
    'ENV_VARIABLE_REFERENCE' => 'env(ENV_STRING_VAR)_additional_string',
];

foreach ($envVariables as $name => $value) {
    putenv("$name=$value");
}
