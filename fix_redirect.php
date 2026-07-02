<?php
$file = __DIR__ . '/wp-content/themes/aitrongcay/functions.php';
$content = file_get_contents($file);

// Fix the corrupted aitrongcay_accept_garden_invite_ajax
$bad_ajax = <<<EOD
    aitrongcay_remember_selected_garden_key(get_current_user_id(), (string) (\$row['garden_key'] ?? ''));
    \$referer = wp_get_referer();
    wp_send_json_success(['message' => 'Đã tham gia khu vườn.', 'garden_key' => \$row['garden_key'] ?? '', 'redirect' => \$referer ? \$referer : null]);
}
EOD;
$good_ajax = <<<EOD
    aitrongcay_remember_selected_garden_key(get_current_user_id(), (string) (\$row['garden_key'] ?? ''));
    wp_send_json_success(['message' => 'Đã tham gia khu vườn.', 'garden_key' => \$row['garden_key'] ?? '']);
}
EOD;
$content = str_replace(str_replace("\n", "\r\n", $bad_ajax), $good_ajax, $content);
$content = str_replace($bad_ajax, $good_ajax, $content);

// Fix the redirect
$old_redirect = <<<EOD
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

$new_redirect = <<<EOD
add_filter('comment_post_redirect', function (\$location, \$comment) {
    if (empty(\$comment->comment_post_ID)) return \$location;
    \$post = get_post(\$comment->comment_post_ID);
    if (\$post && \$post->post_type === 'aitr_market_post') {
        \$referer = wp_get_referer();
        if (\$referer) {
            \$referer = preg_replace('/#.*/', '', \$referer);
            return \$referer . '#comment-' . \$comment->comment_ID;
        }
        
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

$content = str_replace(str_replace("\n", "\r\n", $old_redirect), $new_redirect, $content);
$content = str_replace($old_redirect, $new_redirect, $content);

file_put_contents($file, $content);
echo "SUCCESS";
