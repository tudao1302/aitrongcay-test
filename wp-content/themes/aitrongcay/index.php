<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

get_header();
?>
<main>
    <section class="section">
        <div class="container">
            <div class="glass-card">
                <span class="eyebrow">Tin tức & cập nhật</span>
                <h1><?php bloginfo('name'); ?></h1>
                <?php if (have_posts()) : ?>
                    <div class="cards-3">
                        <?php while (have_posts()) : the_post(); ?>
                            <article <?php post_class('card'); ?>>
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                            </article>
                        <?php endwhile; ?>
                    </div>
                <?php else : ?>
                    <p>Theme WordPress foundation đã sẵn sàng. Bước tiếp theo là seed pages và thay dần nội dung từ HTML tĩnh vào WordPress.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>
<?php
get_footer();
