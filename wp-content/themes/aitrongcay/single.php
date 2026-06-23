<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
$single_market_view_garden_key = '';
if (get_post_type() === 'aitr_market_post') {
    $single_market_view_garden_key = aitrongcay_migrate_market_post_garden_key(get_the_ID());
}
$single_market_list_url = $single_market_view_garden_key !== '' ? add_query_arg('garden', $single_market_view_garden_key, home_url('/cho-que/')) : home_url('/cho-que/');
?>
<main>
    <section class="section">
        <div class="container">
            <?php if (have_posts()) : ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php if (get_post_type() === 'aitr_market_post') : ?>
                        <?php $gallery = array_map('absint', (array) get_post_meta(get_the_ID(), '_aitrongcay_market_gallery', true)); ?>
                        <article <?php post_class('glass-card market-detail-card'); ?>>
                            <span class="eyebrow">Chợ quê</span>
                            <h1><?php the_title(); ?></h1>
                            <div class="meta-row" style="margin:10px 0 18px">
                                <span class="kicker" style="margin-bottom:0"><?php echo esc_html(get_the_author_meta('display_name', (int) get_post_field('post_author', get_the_ID()))); ?></span>
                                <span class="small subtle"><?php echo esc_html(get_the_date('d/m/Y H:i')); ?></span>
                            </div>
                            <?php
                            $single_thumb_url = has_post_thumbnail() ? (string) get_the_post_thumbnail_url(get_the_ID(), 'full') : '';
                            $single_gallery_url = ($gallery && ! empty($gallery[0])) ? (string) wp_get_attachment_image_url($gallery[0], 'large') : '';
                            $single_image_url = $single_thumb_url ?: $single_gallery_url;
                            $market_fallback_url = get_template_directory_uri() . '/assets/images/market-harvest.svg';
                            ?>
                            <div class="card-media media-frame media-frame-16x9" style="margin-top:12px">
                                <img class="media-thumb media-fit-cover" src="<?php echo esc_url($single_image_url ? wp_make_link_relative($single_image_url) : $market_fallback_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" onerror="this.onerror=null;this.src='<?php echo esc_url($market_fallback_url); ?>';this.alt='<?php echo esc_attr(get_the_title()); ?>';">
                            </div>
                            <?php if ($gallery) : ?>
                                <div class="cards-4 market-gallery" style="margin-top:18px">
                                    <?php foreach ($gallery as $gallery_id) : ?>
                                        <?php $gallery_item_url = (string) wp_get_attachment_image_url($gallery_id, 'large'); ?>
                                        <img class="media-thumb media-fit-cover" src="<?php echo esc_url($gallery_item_url ? wp_make_link_relative($gallery_item_url) : $market_fallback_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy" onerror="this.onerror=null;this.src='<?php echo esc_url($market_fallback_url); ?>';this.alt='<?php echo esc_attr(get_the_title()); ?>';">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <div class="entry-content" style="margin-top:20px">
                                <?php the_content(); ?>
                            </div>
                            <div class="inline-list" style="margin-top:24px">
                                <a class="btn btn-secondary" href="<?php echo esc_url($single_market_list_url); ?>">← Về Chợ quê</a>
                            </div>
                        </article>
                    <?php else : ?>
                        <article <?php post_class('glass-card'); ?>>
                            <h1><?php the_title(); ?></h1>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="card-media media-frame media-frame-16x9" style="margin-top:22px">
                                    <?php the_post_thumbnail('full', ['class' => 'media-thumb media-fit-cover']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="entry-content">
                                <?php the_content(); ?>
                            </div>
                        </article>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php
get_footer();
