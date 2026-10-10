<?php
require __DIR__ . '/vendor/autoload.php';
\ = require_once __DIR__ . '/bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();

echo " CHARACTERS:\n\;
print_r(Illuminate\Support\Facades\Schema::getColumnListing('characters'));

echo
