<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$twig = new Twig\Environment(new Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/views'));
$twig->addFunction(new Twig\TwigFunction('function', static function (string $name, mixed ...$args): mixed {
    return match ($name) {
        'esc_url' => htmlspecialchars((string) $args[0], ENT_QUOTES),
        'wp_kses_post' => $args[0],
        'home_url' => 'https://example.test' . $args[0],
        default => throw new RuntimeException('Unexpected function: ' . $name),
    };
}));
$twig->addFunction(new Twig\TwigFunction('get_image', static fn () => new class {
    public int $width = 800;
    public int $height = 600;
    public string $alt = 'A "portrait"';
    public function src(string $size): string { return '/portrait.jpg'; }
}));
$render = static fn (string $name, array $fields, bool $preview = false): string => $twig->render('blocks/' . $name . '.twig', [
    'fields' => $fields, 'block_id' => 'test-block', 'block_classes' => 'ent-block',
    'site_options' => [], 'is_preview' => $preview,
]);
$check = static function (bool $ok, string $message): void {
    if (!$ok) { throw new RuntimeException($message); }
};

foreach (['media-content', 'doctors', 'cta'] as $name) {
    json_decode(file_get_contents(dirname(__DIR__) . '/blocks/' . $name . '/block.json'), true, 512, JSON_THROW_ON_ERROR);
    $check($render($name, []) !== '', $name . ' default render failed');
}
$legacy = $render('media-content', ['image' => 1, 'image_position' => 'left']);
$check(str_contains($legacy, 'media-content--reverse') && str_contains($legacy, 'portrait.jpg'), 'Legacy media fields changed');
$purpose = $render('media-content', ['companion' => 'cards', 'purpose_cards' => [
    ['title' => 'Our mission <test>', 'description' => "Care\nwith respect", 'icon' => 'invalid'],
]]);
$check(str_contains($purpose, 'Our mission &lt;test&gt;') && str_contains($purpose, 'fa-bullseye'), 'Purpose escaping or icon fallback failed');
$check(!str_contains($purpose, 'media-content__image'), 'Purpose mode should replace the image');
$check(str_contains($render('media-content', ['companion' => 'cards'], true), 'Add purpose cards'), 'Editor empty state missing');
$doctor = $render('doctors', ['intro' => 'Meet our team', 'doctors' => [
    ['name' => 'Dr Example', 'qualifications' => 'Approved qualification', 'image' => 1, 'link' => ['url' => '/profile', 'title' => 'View profile']],
]]);
$check(str_contains($doctor, 'Approved qualification') && str_contains($doctor, 'doctor__link'), 'New doctor fields missing');
$cta = $render('cta', ['action' => ['url' => '/book', 'title' => 'Book'], 'secondary_action' => ['url' => '/contact', 'title' => 'Contact']]);
$check(str_contains($cta, 'href="/book"') && str_contains($cta, 'href="/contact"'), 'CTA actions missing');
$check(str_contains($render('cta', []), 'https://example.test/#booking'), 'Default booking link changed');
$purposeLayout = $render('media-content', ['layout' => 'purpose', 'heading' => 'Our purpose', 'content' => '<p>Purpose body</p>', 'image' => 1, 'purpose_cards' => [
    ['title' => 'Feature', 'description' => 'Description', 'icon' => 'award'],
]]);
$check(str_contains($purposeLayout, 'purpose-layout__body') && str_contains($purposeLayout, 'fa-award'), 'Purpose layout missing');
$check(!str_contains($purposeLayout, 'media-content__image') && substr_count($purposeLayout, 'Purpose body') === 1, 'Purpose body/image regression');
$intro = $render('media-content', ['layout' => 'intro', 'heading' => 'About', 'companion' => 'cards', 'image' => 1, 'badge_value' => 'Approved value', 'badge_label' => 'Approved label', 'secondary_link' => ['url' => '/book', 'title' => 'Book']]);
$check(substr_count($intro, '<h1>') === 1 && str_contains($intro, 'fetchpriority="high"') && str_contains($intro, 'media-content__badge') && str_contains($intro, 'media-content__secondary'), 'Intro layout fields missing');
$check(!str_contains($legacy, '<h1>') && !str_contains($legacy, 'media-content__badge'), 'Legacy media gained intro elements');
$split = $render('doctors', ['heading_layout' => 'split', 'doctors' => [['name' => 'Example', 'specialty' => 'Otology']]]);
$check(str_contains($split, 'doctors__header--split') && str_contains($split, 'doctor__specialty'), 'Split doctors layout missing');
$strip = $render('cta', ['layout' => 'strip', 'action_icon' => 'location-dot', 'action' => ['url' => '/#locations', 'title' => 'Locations']]);
$check(str_contains($strip, 'ent-cta--strip') && str_contains($strip, 'fa-location-dot'), 'CTA strip missing');
$check(!str_contains($render('cta', []), 'ent-cta--strip'), 'Existing CTA default changed');
echo "About block checks passed: metadata, legacy layouts, purpose cards, editor empty state, doctor fields and CTA links.\n";
