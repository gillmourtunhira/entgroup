<?php

declare(strict_types=1);

namespace EntGroup;

final class Assets
{
    private const ENTRY = 'src/js/app.js';
    private const EDITOR_ENTRY = 'src/styles/editor.scss';

    public static function registerBlockStyle(string $slug): void
    {
        $handle = 'entgroup-block-' . sanitize_key($slug) . '-style';
        $source = 'blocks/' . sanitize_file_name($slug) . '/style.scss';
        $devServer = defined('ENTGROUP_VITE_DEV_SERVER') ? (string) ENTGROUP_VITE_DEV_SERVER : '';

        if ($devServer !== '') {
            wp_register_style($handle, rtrim($devServer, '/') . '/' . $source, [], null);
            return;
        }

        $manifest = self::manifest();
        $entry = $manifest[$source] ?? null;
        if (! is_array($entry) || empty($entry['file']) || ! str_ends_with($entry['file'], '.css')) {
            return;
        }

        wp_register_style($handle, get_theme_file_uri('dist/' . $entry['file']), [], null);
    }

    public static function enqueue(): void
    {
        $devServer = defined('ENTGROUP_VITE_DEV_SERVER') ? (string) ENTGROUP_VITE_DEV_SERVER : '';

        if ($devServer !== '') {
            wp_enqueue_script('entgroup-vite-client', rtrim($devServer, '/') . '/@vite/client', [], null, false);
            wp_enqueue_script('entgroup-app', rtrim($devServer, '/') . '/' . self::ENTRY, [], null, false);
            add_filter('script_loader_tag', [self::class, 'moduleTag'], 10, 2);
            return;
        }

        $manifest = self::manifest();
        $entry = $manifest[self::ENTRY] ?? null;
        if (! is_array($entry) || empty($entry['file'])) {
            return;
        }

        foreach (($entry['css'] ?? []) as $index => $css) {
            wp_enqueue_style('entgroup-app-' . $index, get_theme_file_uri('dist/' . $css), [], null);
        }

        wp_enqueue_script('entgroup-app', get_theme_file_uri('dist/' . $entry['file']), [], null, true);
        add_filter('script_loader_tag', [self::class, 'moduleTag'], 10, 2);
    }

    public static function moduleTag(string $tag, string $handle): string
    {
        if (! str_starts_with($handle, 'entgroup-')) {
            return $tag;
        }
        $tag = (string) preg_replace('/\\s+type=(["\']).*?\\1/', '', $tag, 1);
        return str_replace('<script ', '<script type="module" ', $tag);
    }

    public static function addEditorStyle(): void
    {
        $manifest = self::manifest();
        $entry = $manifest[self::EDITOR_ENTRY] ?? null;
        if (! is_array($entry)) {
            return;
        }

        if (isset($entry['file']) && str_ends_with($entry['file'], '.css')) {
            add_editor_style('dist/' . $entry['file']);
        }
    }

    private static function manifest(): array
    {
        static $manifest;
        if (is_array($manifest)) {
            return $manifest;
        }

        $path = get_theme_file_path('dist/.vite/manifest.json');
        if (! file_exists($path)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($path), true);
        $manifest = is_array($decoded) ? $decoded : [];
        return $manifest;
    }
}
