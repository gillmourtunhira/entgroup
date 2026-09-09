<?php

declare(strict_types=1);

namespace EntGroup;

use Timber\Timber;

final class BlockRenderer
{
    public static function render(string $template, array $args): void
    {
        $block = is_array($args['block'] ?? null) ? $args['block'] : [];
        $anchor = sanitize_title((string) ($block['anchor'] ?? ''));
        $customClasses = array_filter(array_map('sanitize_html_class', preg_split('/\\s+/', (string) ($block['className'] ?? '')) ?: []));
        $alignClass = empty($block['align']) ? '' : 'align' . sanitize_html_class((string) $block['align']);

        $context = Timber::context();
        $context['fields'] = function_exists('get_fields') ? (get_fields() ?: []) : [];
        $context['block'] = $block;
        $context['is_preview'] = (bool) ($args['is_preview'] ?? false);
        $context['block_id'] = $anchor !== '' ? $anchor : sanitize_html_class((string) ($block['id'] ?? uniqid('block-', false)));
        $context['block_classes'] = trim(implode(' ', array_merge(['ent-block', 'ent-block--' . sanitize_html_class($template), $alignClass], $customClasses)));

        Timber::render('blocks/' . $template . '.twig', $context);
    }
}
