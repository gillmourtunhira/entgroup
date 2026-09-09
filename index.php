<?php

declare(strict_types=1);

use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();

Timber::render('pages/index.twig', $context);
