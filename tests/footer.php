<?php

declare(strict_types=1);

require __DIR__ . '/about-blocks.php';

$mods = [];
function get_theme_mod(string $key, mixed $default = false): mixed {
    return $GLOBALS['mods'][$key] ?? $default;
}

$values = EntGroup\Customizer::values(['primary_phone' => '+263 123', 'email' => 'office@example.test']);
$check($values['primary_phone'] === '+263 123', 'Legacy contact fallback failed');
$mods['entgroup_footer_primary_phone'] = '';
$mods['entgroup_footer_description'] = '<script>test</script>';
$values = EntGroup\Customizer::values(['primary_phone' => '+263 123']);
$check($values['primary_phone'] === '', 'Explicit blank must hide the phone');
$html = $twig->render('partials/footer.twig', [
    'site' => ['name' => 'The ENT Group', 'url' => 'https://example.test'],
    'footer_details' => $values,
    'footer_menu' => ['items' => [['link' => '/about', 'title' => 'About', 'target' => '_blank', 'children' => []]]],
    'footer_specialties_menu' => ['items' => [['link' => '/ear', 'title' => 'Otology', 'children' => []]]],
]);
$check(str_contains($html, '&lt;script&gt;') && !str_contains($html, '<script>'), 'Footer text is not escaped');
$check(str_contains($html, 'Otology') && str_contains($html, 'About') && str_contains($html, 'rel="noopener noreferrer"'), 'Footer menus missing or unsafe');
$check(!str_contains($html, 'tel:'), 'Blank phone rendered a link');
echo "Footer checks passed: legacy defaults, explicit blanks, escaping and two menus.\n";
