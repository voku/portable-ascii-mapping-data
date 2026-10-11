<?php

require __DIR__ . '/../vendor/autoload.php';
$contracts = json_decode(file_get_contents(__DIR__ . '/fixtures/contract.json'), true);
$raw = json_decode(file_get_contents(__DIR__ . '/fixtures/files.json'), true);
$files = glob(__DIR__ . '/../data/*.php');
if (count($files) !== count($contracts)) {
    throw new RuntimeException('Data file inventory differs');
}
foreach ($contracts as $name => $contract) {
    $map = \Voku\PortableAsciiMappingData\BankMap::load(substr($name, 0, -4));
    if (count($map) !== $contract['entries'] || hash('sha256', serialize($map)) !== $contract['sha256']) {
        throw new RuntimeException('Key/value/order contract differs: ' . $name);
    }
    if (hash_file('sha256', __DIR__ . '/../data/' . $name) !== $raw[$name]) {
        throw new RuntimeException('Deterministic representation differs: ' . $name);
    }
    // Callers can mutate their returned copy without poisoning another load.
    $map['caller mutation'] = 'changed';
    if (hash('sha256', serialize(\Voku\PortableAsciiMappingData\BankMap::load(substr($name, 0, -4)))) !== $contract['sha256']) {
        throw new RuntimeException('Provider leaked a caller mutation');
    }
}
if (\Voku\PortableAsciiMappingData\BankMap::load('x10ff') !== []) {
    throw new RuntimeException('Unknown bank contract differs');
}
foreach (['../composer', '../../data/x000', 'X000', 'x00', 'x000.php'] as $invalid) {
    try {
        \Voku\PortableAsciiMappingData\BankMap::load($invalid);
        throw new LogicException('Invalid bank identifier accepted');
    } catch (InvalidArgumentException $expected) {
    }
}

// A damaged installed package must fail explicitly, not return partial/silent mappings.
$path = __DIR__ . '/../data/x000.php';
$backup = $path . '.contract-backup';
if (!rename($path, $backup)) {
    throw new RuntimeException('Cannot prepare corruption probe');
}
try {
    try {
        \Voku\PortableAsciiMappingData\BankMap::load('x000');
        throw new LogicException('Missing known data did not throw');
    } catch (RuntimeException $error) {
        if (strpos($error->getMessage(), 'voku/portable-ascii-mapping-data') === false) {
            throw $error;
        }
    }
} finally {
    rename($backup, $path);
}
echo count($contracts) . ' exact mapping contracts, mutation isolation and missing-data guards passed.' . PHP_EOL;
