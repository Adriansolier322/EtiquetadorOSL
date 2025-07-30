<?php
// test_generate.php

// Simulate $_POST data
$_POST = [
    'board_type'          => 'ATX',
    'cpu_name'            => 'Intel i7',
    'cpu_other_name'      => '',
    'ram_capacity'        => '16GB',
    'ram_other_capacity'  => '',
    'ram_type'            => 'DDR4',
    'disc_capacity'       => '1TB',
    'disc_other_capacity' => '',
    'disc_type'           => 'SSD',
    'gpu_name'            => 'NVIDIA RTX 3060',
    'gpu_other_name'      => '',
    'gpu_type'            => 'Dedicated',
    'wifi'                => 'false',
    'bluetooth'           => 'true',
    'sn_prefix'           => 'HI2',
    'sn_prefix_other'     => '',
    'num_pag'             => '1', // Test with multiple pages
    'checkbox_save'       => 'True',
    'ticket_name'         => 'Test_Model_CLI_2',
    'observaciones'       => 'Generated from command line for testing.'
];

// Define $_SERVER variables that generate_pdf.php might expect (e.g., REQUEST_METHOD)
$_SERVER['REQUEST_METHOD'] = 'POST';

// Include the generate_pdf.php script
// Make sure the path is correct relative to test_generate.php
require_once 'generate_pdf.php';

echo "Script execution finished. Check your 'pdf' directory for generated files.\n";

?>
