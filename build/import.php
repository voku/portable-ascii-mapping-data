<?php

// Reproducible byte-preserving import from a reviewed immutable upstream checkout.
$root = dirname(__DIR__);
$expected = json_decode(file_get_contents($root . '/tests/fixtures/files.json'), true);
$provenance = json_decode(file_get_contents($root . '/PROVENANCE.json'), true);
$source = null;
foreach ($argv as $argument) {
    if (strpos($argument, '--source=') === 0) {
        $source = substr($argument, 9);
    }
}
$write = in_array('--write', $argv, true);
if ($write && $source === null) {
    throw new InvalidArgumentException('--write requires --source=/path/to/immutable/upstream/checkout');
}
if ($source !== null) {
    $revision = trim(shell_exec('git -C ' . escapeshellarg($source) . ' rev-parse HEAD'));
    if ($revision !== $provenance['upstream_commit']) {
        throw new RuntimeException('Source revision differs from PROVENANCE.json');
    }
}
foreach ($expected as $name => $hash) {
    $path = $root . '/data/' . $name;
    if ($source !== null) {
        $bytes = file_get_contents($source . '/' . $provenance['upstream_path'] . $name);
        if (hash('sha256', $bytes) !== $hash) {
            throw new RuntimeException('Source bytes differ: ' . $name);
        }
        if ($write) {
            file_put_contents($path, $bytes);
        }
    }
    if (hash_file('sha256', $path) !== $hash) {
        throw new RuntimeException('Data bytes differ: ' . $name);
    }
}
if (count(glob($root . '/data/*.php')) !== count($expected)) {
    throw new RuntimeException('Data inventory differs');
}
echo count($expected) . ' deterministic imports verified.' . PHP_EOL;
