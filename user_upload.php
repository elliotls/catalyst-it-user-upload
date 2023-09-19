#!/usr/bin/env php
<?php
function readInputFile() {
    $cmdOptions = ["file:","dry_run:"];
    $cmdValues = getopt('file:u:p:h:', $cmdOptions);
    
    $csv = array_map("str_getcsv", file($cmdValues['file'], FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES));
    $header = array_shift($csv);
    if ($header[0] != "name" || $header[1] != "surname" || trim($header[2]) != "email") {
        echo "Invalid file format:\n";
        exit;
    }
    return [$cmdValues, $csv];
}

function validateDatabaseCreds($cmdValues) {
    $credentials = ['u' => 'username', 'p' => 'password', 'h' => 'host'];
    // $credentials = ['u' => 'username', 'h' => 'host'];
    foreach ($credentials as $credential => $description) {
        if (!isset($cmdValues[$credential])) {
            echo "Database credential {$description} not found. Please try again\n";
            exit;
        }
    }
}

function initialiseDatabase($user, $password, $host) {
    echo "Initialising DB with {$user}, {$password}, {$host}\n";
    // Creating a connection
    $databaseConnection = new mysqli($host, $user, $password);
    echo "Connected\n";

    // Check connection
    if ($databaseConnection->connect_error) {
        echo "DB connection failed\n";
        die("Connection failed: " . $databaseConnection->connect_error);
    }

    $sql = "DROP DATABASE IF EXISTS user_upload;
            CREATE DATABASE user_upload;
            CREATE TABLE user_upload.users;";

    if ($databaseConnection->multi_query($sql) === TRUE) {
        echo "Database created successfully with the name user_upload\n";
    } else {
        echo "Error creating database: {$databaseConnection->error}";
    }

    $databaseConnection->close();
    return;
}

echo "~~ Initialising user upload ~~ \n";
[$cmdValues, $csvRows] = readInputFile();
echo "CSV Rows: {$csvRows[0][0]}, {$csvRows[0][1]}, {$csvRows[0][2]}\n";

validateDatabaseCreds($cmdValues);

initialiseDatabase($cmdValues['u'], $cmdValues['p'], $cmdValues['h']);

// Iterate over file contents validating and prepping for insert

?>
