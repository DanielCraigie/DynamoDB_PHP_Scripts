<?php

use App\dynamodb\Table;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '.bootstrap.php']);

$table = new Table('Dog');

$table->delete();

if (!$table->exists()) {
    $table->create([
        'KennelName' => 'S',
    ], [
        'KennelName' => Table::PRIMARY_KEY_HASH,
    ]);
}

$tara = new \App\dynamodb\Item($table);
$tara->KennelName = 'Elveswood Goldberry';
$tara->PetName = 'Tara';

$tara->put();

$tara->get();
