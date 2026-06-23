<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}
?><?php $company = aitrongcay_company_profile(); $meta = aitrongcay_meta_context(); ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo esc_attr($meta['description']); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($company['brand']); ?>">
    <meta property="og:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
    <meta property="og:description" content="<?php echo esc_attr($meta['description']); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo esc_url(home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''))); ?>">
    <meta property="og:image" content="<?php echo esc_url($meta['og_image']); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo esc_attr(wp_get_document_title()); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($meta['description']); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($meta['og_image']); ?>">
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url($company['favicon']); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part('template-parts/site/header', 'public'); ?>
