<?php

declare(strict_types=1);

namespace EntGroup;

/**
 * Private appointment requests. No appointment is confirmed automatically.
 */
final class Bookings
{
    public static function services(): array
    {
        return [
            'general-ent' => 'General ENT consultation',
            'otology' => 'Otology (ear)',
            'rhinology' => 'Rhinology (nose & sinus)',
            'laryngology' => 'Laryngology (throat & voice)',
            'paediatric-ent' => 'Paediatric ENT',
        ];
    }

    public static function clinics(): array
    {
        return [
            'harare-main' => 'Harare Main Centre',
            'borrowdale' => 'Borrowdale',
            'chitungwiza' => 'Chitungwiza',
            'mutare' => 'Mutare',
        ];
    }

    public static function boot(): void
    {
        add_action('init', [self::class, 'register']);
        add_action('admin_post_nopriv_entgroup_booking', [self::class, 'submit']);
        add_action('admin_post_entgroup_booking', [self::class, 'submit']);
        add_action('add_meta_boxes', [self::class, 'metaBoxes']);
        add_action('save_post_ent_booking', [self::class, 'saveStatus']);
        add_filter('manage_ent_booking_posts_columns', [self::class, 'columns']);
        add_action('manage_ent_booking_posts_custom_column', [self::class, 'column'], 10, 2);
        add_filter('the_title', [self::class, 'adminTitle'], 10, 2);
    }

    public static function statuses(): array
    {
        return ['new' => 'New', 'contacted' => 'Contacted', 'confirmed' => 'Confirmed by staff', 'closed' => 'Closed'];
    }

    public static function columns(array $columns): array
    {
        $result = [];
        foreach ($columns as $key => $label) {
            $result[$key] = $key === 'title' ? 'Full name' : $label;
            if ($key === 'title') {
                $result['ent_service'] = 'Service';
                $result['ent_review_status'] = 'Review Status';
                $result['ent_preferred_date'] = 'Preferred Date';
            }
        }
        return $result;
    }

    public static function column(string $column, int $postId): void
    {
        switch ($column) {
            case 'ent_service':
                $service = (string) get_post_meta($postId, '_ent_service', true);
                echo esc_html(self::services()[$service] ?? 'Not provided');
                break;
            case 'ent_review_status':
                $status = (string) get_post_meta($postId, '_ent_status', true);
                echo esc_html(self::statuses()[$status ?: 'new'] ?? 'Unknown');
                break;
            case 'ent_preferred_date':
                $date = (string) get_post_meta($postId, '_ent_date', true);
                $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                echo esc_html($parsed && $parsed->format('Y-m-d') === $date ? $parsed->format('j F Y') : 'Not provided');
                break;
        }
    }

    public static function adminTitle(string $title, int $postId): string
    {
        if (! is_admin() || get_post_type($postId) !== 'ent_booking') {
            return $title;
        }
        // Display older requests consistently without rewriting stored records.
        $name = (string) get_post_meta($postId, '_ent_name', true);
        return $name !== '' ? $name : $title;
    }

    public static function register(): void
    {
        register_post_type('ent_booking', [
            'labels' => ['name' => 'Bookings', 'singular_name' => 'Booking request'],
            'public' => false,
            'publicly_queryable' => false,
            'exclude_from_search' => true,
            'show_ui' => true,
            'show_in_rest' => false,
            'rewrite' => false,
            'query_var' => false,
            'menu_icon' => 'dashicons-calendar-alt',
            'supports' => ['title'],
            'capabilities' => [
                'edit_post' => 'manage_options', 'read_post' => 'manage_options',
                'delete_post' => 'manage_options', 'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options', 'publish_posts' => 'manage_options',
                'read_private_posts' => 'manage_options', 'delete_posts' => 'manage_options',
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap' => false,
        ]);
    }

    private static function input(string $key): string
    {
        return isset($_POST[$key]) && is_string($_POST[$key]) ? trim(wp_unslash($_POST[$key])) : '';
    }

    public static function submit(): void
    {
        $return = wp_validate_redirect(esc_url_raw(self::input('return_url')), home_url('/'));
        $nonce = self::input('booking_nonce');
        if (! wp_verify_nonce($nonce, 'entgroup_booking')) {
            self::redirect($return, 'expired');
        }
        if (self::input('website') !== '') {
            self::redirect($return, 'invalid');
        }
        $name = sanitize_text_field(self::input('patient_name'));
        $email = sanitize_email(self::input('email'));
        $phone = sanitize_text_field(self::input('phone'));
        $date = self::input('preferred_date');
        $service = self::input('service');
        $clinic = self::input('clinic');
        $message = sanitize_textarea_field(self::input('message'));
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($name === '' || strlen($name) > 120 || ! is_email($email) || strlen($email) > 254
            || ! preg_match('/^[+0-9() .-]{7,30}$/', $phone)
            || ! $parsed || $parsed->format('Y-m-d') !== $date
            || $date < current_time('Y-m-d') || self::input('consent') !== 'yes'
            || ! array_key_exists($service, self::services())
            || ! array_key_exists($clinic, self::clinics())
            || mb_strlen(self::input('message')) > 2000
            || (isset($_POST['message']) && ! is_string($_POST['message']))) {
            self::redirect($return, 'invalid');
        }
        $key = 'ent_booking_' . hash_hmac('sha256', strtolower($email), wp_salt());
        if (get_transient($key)) {
            self::redirect($return, 'recent');
        }
        $id = wp_insert_post([
            'post_type' => 'ent_booking',
            'post_status' => 'private',
            'post_title' => $name,
            'meta_input' => [
                '_ent_name' => $name, '_ent_email' => $email, '_ent_phone' => $phone,
                '_ent_date' => $date, '_ent_status' => 'new',
                '_ent_service' => $service, '_ent_clinic' => $clinic,
                '_ent_message' => $message,
                '_ent_consent_at' => current_time('mysql'),
            ],
        ], true);
        if (is_wp_error($id) || ! $id) {
            self::redirect($return, 'failed');
        }
        set_transient($key, true, 5 * MINUTE_IN_SECONDS);
        self::redirect($return, 'received');
    }

    private static function redirect(string $url, string $result): never
    {
        wp_safe_redirect(add_query_arg('booking_result', $result, $url) . '#booking-feedback', 303);
        exit;
    }

    public static function metaBoxes(): void
    {
        add_meta_box('ent-request-details', 'Request details', [self::class, 'details'], 'ent_booking');
    }

    public static function details(\WP_Post $post): void
    {
        foreach (['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'date' => 'Preferred date', 'consent_at' => 'Contact consent recorded'] as $key => $label) {
            echo '<p><strong>' . esc_html($label) . ':</strong> ' . esc_html((string) get_post_meta($post->ID, '_ent_' . $key, true)) . '</p>';
        }
        $service = (string) get_post_meta($post->ID, '_ent_service', true);
        $clinic = (string) get_post_meta($post->ID, '_ent_clinic', true);
        echo '<p><strong>Service:</strong> ' . esc_html(self::services()[$service] ?? 'Not provided') . '</p>';
        echo '<p><strong>Preferred clinic:</strong> ' . esc_html(self::clinics()[$clinic] ?? 'Not provided') . '</p>';
        $message = (string) get_post_meta($post->ID, '_ent_message', true);
        echo '<p><strong>Message:</strong></p><p style="white-space:pre-wrap">' . esc_html($message !== '' ? $message : 'Not provided') . '</p>';
        wp_nonce_field('ent_booking_status', 'ent_status_nonce');
        echo '<label for="ent-status">Review status</label> <select id="ent-status" name="ent_status">';
        foreach (self::statuses() as $key => $label) {
            echo '<option value="' . esc_attr($key) . '" ' . selected(get_post_meta($post->ID, '_ent_status', true), $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }

    public static function saveStatus(int $postId): void
    {
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ! current_user_can('manage_options')
            || ! wp_verify_nonce(self::input('ent_status_nonce'), 'ent_booking_status')) {
            return;
        }
        $status = self::input('ent_status');
        if (array_key_exists($status, self::statuses())) {
            update_post_meta($postId, '_ent_status', $status);
        }
    }
}
