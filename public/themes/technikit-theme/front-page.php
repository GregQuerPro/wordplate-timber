<?php

use Timber\Timber;

$context = \Timber\Timber::context();

$context['home'] = new \Timber\Post();
$context['posts'] = new \Timber\PostQuery([
    'posts_per_page' => 3,
    'orderby'        => 'date'
]);


\Timber\Timber::render(['front-page.twig'], $context);
