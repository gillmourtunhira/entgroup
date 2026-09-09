<?php

declare(strict_types=1);

use EntGroup\Theme;

$autoload = __DIR__ . '/vendor/autoload.php';

if (! file_exists($autoload)) {
    add_action('admin_notices', static function (): void {
        echo '<div class="notice notice-error"><p>'
            . esc_html__('The ENT Group theme dependencies are missing. Run composer install in the theme directory.', 'entgroup')
            . '</p></div>';
    });
    return;
}

require_once $autoload;

Theme::boot();
