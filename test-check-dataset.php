<?php
require_once __DIR__ . '/wp-load.php';
$garden_key = 'garden:e7ae08ddd1d0e1b4fa18977b08abb22b';
$db_pots = aitrongcay_get_db_pots($garden_key);
echo "DB Pots:\n";
print_r($db_pots);

$dataset = aitrongcay_portal_dataset_for_garden($garden_key);
echo "Dataset Pots:\n";
print_r($dataset['pots']);
