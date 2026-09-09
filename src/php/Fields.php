<?php

declare(strict_types=1);

namespace EntGroup;

final class Fields
{
    public static function register(): void
    {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => __('Site details', 'entgroup'),
                'menu_title' => __('Site details', 'entgroup'),
                'menu_slug' => 'entgroup-site-details',
                'capability' => 'manage_options',
                'redirect' => false,
            ]);
        }

        acf_add_local_field_group([
            'key' => 'group_entgroup_options',
            'title' => __('Contact details', 'entgroup'),
            'fields' => [
                ['key' => 'field_entgroup_phone', 'label' => __('Primary phone', 'entgroup'), 'name' => 'primary_phone', 'type' => 'text'],
                ['key' => 'field_entgroup_email', 'label' => __('Email', 'entgroup'), 'name' => 'email', 'type' => 'email'],
                ['key' => 'field_entgroup_booking', 'label' => __('Booking URL', 'entgroup'), 'name' => 'booking_url', 'type' => 'url'],
                ['key' => 'field_entgroup_hours', 'label' => __('Opening hours', 'entgroup'), 'name' => 'opening_hours', 'type' => 'text'],
            ],
            'location' => [[['param' => 'options_page', 'operator' => '==', 'value' => 'entgroup-site-details']]],
        ]);
    }
}
