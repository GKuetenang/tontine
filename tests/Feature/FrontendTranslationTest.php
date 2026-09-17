<?php

use Illuminate\Support\Facades\File;

it('declares every literal frontend translation key for each supported locale', function () {
    $sourceFiles = collect(File::allFiles(resource_path('js')))
        ->filter(fn (SplFileInfo $file): bool => in_array($file->getExtension(), ['ts', 'tsx'], true));

    $keys = $sourceFiles
        ->flatMap(function (SplFileInfo $file): array {
            preg_match_all(
                '/\bt\(\s*([\'\"])((?:\\.|(?!\1).)*)\1/s',
                $file->getContents(),
                $matches,
            );

            return array_map(
                fn (string $key): string => stripcslashes($key),
                $matches[2],
            );
        })
        ->unique()
        ->values();

    foreach (['en', 'fr'] as $locale) {
        $translations = json_decode(
            File::get(lang_path("{$locale}.json")),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $missingKeys = $keys->reject(
            fn (string $key): bool => array_key_exists($key, $translations),
        );

        expect($missingKeys->all())->toBe([]);
    }
});
