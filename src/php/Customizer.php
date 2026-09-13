<?php

declare(strict_types=1);

namespace EntGroup;

final class Customizer
{
    public static function values(array $legacy = []): array
    {
        $defaults = [
            'description' => 'Specialist ear, nose and throat care in Zimbabwe.',
            'address' => '',
            'primary_phone' => $legacy['primary_phone'] ?? '',
            'email' => $legacy['email'] ?? '',
            'opening_hours' => $legacy['opening_hours'] ?? '',
        ];
        foreach ($defaults as $name => $default) {
            $defaults[$name] = (string) get_theme_mod('entgroup_footer_' . $name, $default);
        }
        return $defaults;
    }

    public static function register(\WP_Customize_Manager $manager): void
    {
        $legacy = function_exists('get_fields') ? (get_fields('option') ?: []) : [];
        $defaults = self::values($legacy);
        $manager->add_section('entgroup_footer', [
            'title' => __('Footer details', 'entgroup'),
            'description' => __('Footer copy and contact details. Phone, email and opening hours also supply site contact details. Leave a field blank to hide it. Assign footer menus under Menus.', 'entgroup'),
            'priority' => 140,
        ]);
        $fields = [
            'description' => [__('Description below site name', 'entgroup'), 'textarea', 'sanitize_textarea_field'],
            'address' => [__('Address', 'entgroup'), 'textarea', 'sanitize_textarea_field'],
            'primary_phone' => [__('Phone', 'entgroup'), 'text', 'sanitize_text_field'],
            'email' => [__('Email', 'entgroup'), 'email', 'sanitize_email'],
            'opening_hours' => [__('Opening hours', 'entgroup'), 'textarea', 'sanitize_textarea_field'],
        ];
        foreach ($fields as $name => [$label, $type, $sanitize]) {
            $key = 'entgroup_footer_' . $name;
            $manager->add_setting($key, [
                'type' => 'theme_mod', 'capability' => 'edit_theme_options',
                'default' => $defaults[$name], 'sanitize_callback' => $sanitize,
                'transport' => 'refresh',
            ]);
            $manager->add_control($key, [
                'label' => $label, 'section' => 'entgroup_footer', 'type' => $type,
            ]);
        }
    }
}
