<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Path to pdftotext executable
    |--------------------------------------------------------------------------
    |
    | This option allows you to specify the path to the pdftotext executable
    | on your system. If the executable is located in one of the directories
    | listed in your system's PATH environment variable, you can simply set
    | this option to null.
    |
    */

    'pdftotext_path' => env('PDFTOTEXT_PATH', '/usr/bin/pdftotext'),
];
