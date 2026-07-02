<?php
$file = __DIR__ . '/wp-content/themes/aitrongcay/functions.php';
$content = file_get_contents($file);

// Remove the duplicated block entirely
$bad_block = <<<EOD
    add_filter('comment_post_redirect', function (\$location, \$comment) {
    if (empty(\$comment->comment_post_ID)) return \$location;
    \$post = get_post(\$comment->comment_post_ID);
    if (\$post && \$post->post_type === 'aitr_market_post') {
        \$garden_key = get_post_meta(\$post->ID, '_aitrongcay_market_garden_key', true);
        \$url = home_url('/cho-que/');
        if (\$garden_key) {
            \$url = add_query_arg('garden', \$garden_key, \$url);
        }
        return \$url . '#comment-' . \$comment->comment_ID;
    }
    return \$location;
}, 10, 2);
EOD;
$content = str_replace(str_replace("\n", "\r\n", $bad_block), '', $content);
$content = str_replace($bad_block, '', $content);

// Remove the market_post arg in the good block
$bad_line = "\$url = add_query_arg('market_post', \$post->ID, \$url);";
$content = str_replace("\r\n        " . $bad_line, '', $content);
$content = str_replace("\n        " . $bad_line, '', $content);

file_put_contents($file, $content);
echo "Done";
