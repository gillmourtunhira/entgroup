<?php

declare(strict_types=1);

namespace EntGroup;

use Timber\Timber;

final class Theme
{
    public static function boot(): void
    {
        Timber::init();
        Timber::$dirname = ['views'];

        add_action('after_setup_theme', [self::class, 'setup']);
        add_action('init', [Blocks::class, 'register']);
        Bookings::boot();
        add_action('init', [Blocks::class, 'registerPatternCategory']);
        add_filter('block_categories_all', [Blocks::class, 'categories']);
        add_action('wp_enqueue_scripts', [Assets::class, 'enqueue']);
        add_action('acf/init', [Fields::class, 'register']);
        add_action('customize_register', [Customizer::class, 'register']);
        add_filter('timber/context', [self::class, 'context']);
    }

    public static function setup(): void
    {
        load_theme_textdomain('entgroup', get_template_directory() . '/languages');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo', ['height' => 72, 'width' => 220, 'flex-height' => true, 'flex-width' => true]);
        add_theme_support('html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script']);
        add_theme_support('align-wide');
        add_theme_support('editor-styles');
        add_theme_support('responsive-embeds');
        Assets::addEditorStyle();

        register_nav_menus([
            'primary' => __('Primary navigation', 'entgroup'),
            'footer' => __('Footer — Quick Links', 'entgroup'),
            'footer_specialties' => __('Footer — Specialties', 'entgroup'),
        ]);
    }

    public static function context(array $context): array
    {
        $context['primary_menu'] = Timber::get_menu('primary');
        $context['footer_menu'] = Timber::get_menu('footer');
        $context['footer_specialties_menu'] = has_nav_menu('footer_specialties') ? Timber::get_menu('footer_specialties') : null;
        if (! has_nav_menu('footer')) {
            $context['footer_menu'] = null;
        }
        $context['site_options'] = function_exists('get_fields') ? (get_fields('option') ?: []) : [];
        $context['footer_details'] = Customizer::values($context['site_options']);
        foreach (['primary_phone', 'email', 'opening_hours'] as $name) {
            $context['site_options'][$name] = $context['footer_details'][$name];
        }
        return $context;
    }
}
