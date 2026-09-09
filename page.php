<?php

declare(strict_types=1);

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

Timber::render('pages/page.twig', $context);
