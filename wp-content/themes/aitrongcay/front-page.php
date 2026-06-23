<?php
declare(strict_types=1);
if (! defined('ABSPATH')) {
    exit;
}
wp_safe_redirect(is_user_logged_in() ? home_url('/portal/dashboard-2/') : home_url('/cach-hoat-dong/'));
exit;