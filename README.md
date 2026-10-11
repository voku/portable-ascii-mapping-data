# portable-ascii-mapping-data

Lazy mapping provider for `voku/portable-ascii`.
PHP >= 7.1; no extensions, runtime downloads, plugins or other dependencies are required.
This initial development package has no release tags.

```php
$map = \Voku\PortableAsciiMappingData\BankMap::load('x020');
```

Returned arrays preserve original keys, values, insertion order and sparse keys. A caller's
mutations do not affect later loads. Unknown valid banks return an empty array; malformed identifiers are rejected.
Missing known data raises a clear exception. The library controls its own memoization and
transliteration/emoji policy; this provider only supplies data. Bootstrap does not load maps.

## Development installation

Until a stable version is approved, use an explicit Composer VCS repository and a committed
application lockfile. The `dev-main` branch alias is `1.0.x-dev`; it is not a published `1.0.0`.
When exercising the core integration branch's `^1.0` requirement, applications can pin the
reviewed provider commit as `dev-main#<full-sha> as 1.0.0`. Composer reads branch metadata;
the lockfile and reviewed manifest are required for reproducible development installations.

Do not use root `replace` as a normal substitute for installation. An application opting out
of this data through the core library's explicitly documented slim mode deliberately gives
up affected functionality. It must audit indirect callers and test its workload. The core
will throw an actionable missing-provider exception when a required path reaches this data.
Extensions alone do not prove that maps are unnecessary.

## Provenance and reproducible updates

`PROVENANCE.json` pins upstream `voku/portable-ascii` at `e745ca2778f90e109275fedb6f23c9ddede400bf`.
All 189 mapping files were copied byte-for-byte from the compact in-place candidate.
Original ordered-array contract hashes and raw file hashes are retained in `tests/fixtures`.
LICENSE.txt preserves the upstream MIT license and NOTICE retains attribution.

```sh
composer install
composer test
composer check-data
php build/import.php --source=/path/to/upstream --write
composer test
```

Import checks the source Git revision and expected bytes before writing; it never updates
contract fixtures implicitly. Representation generation remains owned by the pinned upstream
compact generator for this initial extraction. Future mapping changes require a reviewed
provenance/fixture update and provider compatibility/versioning decision in this repository;
the old upstream data paths must not become a second independently maintained source.

CI tests PHP 7.1, 7.4, 8.4 and 8.5, exact arrays, mutation isolation, missing-file guards and
an exported production archive. Build/test/CI files are excluded from Dist archives. No
release/tag/publishing automation is installed.
