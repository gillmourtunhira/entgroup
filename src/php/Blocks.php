<?php

declare(strict_types=1);

namespace EntGroup;

final class Blocks
{
    public static function categories(array $categories): array
    {
        array_unshift($categories, [
            'slug' => 'entgroup',
            'title' => __('The ENT Group', 'entgroup'),
            'icon' => 'heart',
        ]);
        return $categories;
    }

    public static function register(): void
    {
        if (! function_exists('acf_register_block_type')) {
            return;
        }

        $files = glob(get_theme_file_path('blocks/*/block.json')) ?: [];
        sort($files);

        foreach ($files as $file) {
            Assets::registerBlockStyle(basename(dirname($file)));
            register_block_type(dirname($file));
        }
    }

    public static function registerPatternCategory(): void
    {
        register_block_pattern_category('entgroup', [
            'label' => __('The ENT Group pages', 'entgroup'),
        ]);
    }
}
