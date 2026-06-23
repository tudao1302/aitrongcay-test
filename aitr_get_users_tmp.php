<?php
require_once('wp-load.php');
$users = get_users();
foreach ($users as $user) {
    echo "User Login: " . $user->user_login . " | Email: " . $user->user_email . " | Roles: " . implode(', ', $user->roles) . "\n";
}
?>
