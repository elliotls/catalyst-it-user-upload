#!/usr/bin/env php
<?php

function readInputFile() {
    $cmdOptions = ["file:","dry_run:"];
    $cmdValues = getopt('file:u:p:h:', $cmdOptions);

    // Validate file format
    if (!isset($cmdValues['file']) || !str_ends_with($cmdValues['file'], ".csv")) {
        echo "Please provide a csv file with the --file option\n";
        exit;
    } else {
        echo "Filename: {$cmdValues['file']}\n";
    }

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
            CREATE TABLE user_upload.users (
                name VARCHAR(100),
                surname VARCHAR(100),
                email VARCHAR(100) UNIQUE
            );";

    if ($databaseConnection->multi_query($sql) === TRUE) {
        echo "Database created successfully with the name user_upload\n";
    } else {
        echo "Error creating database: {$databaseConnection->error}";
    }

    $databaseConnection->close();
    return;
}

function insertUsers($csvRows, $host, $user, $password) {
    echo "Beginning user insert\n";
    $queryArray = [];
    $queuedEmailAddresses = [];
    $databaseConnection = new mysqli($host, $user, $password, 'user_upload');
    foreach ($csvRows as $userData) {
        $userData[0] = str_replace('\'', '\\\'', $userData[0]);
        $userData[1] = str_replace('\'', '\\\'', $userData[1]);
        $emailAddress = trim(str_replace('\'', '\'\'', $userData[2]));
        // Validate and queue email address
        if (!validateEmailAddress($emailAddress)) {
            echo "Email address '$emailAddress' is considered invalid and will be ignored.\n";
        } elseif (in_array($emailAddress, $queuedEmailAddresses)) {
            echo "Email address '$emailAddress' is not unique and will be ignored.\n";
        } else {
            $queryArray[] = "('$userData[0]', '$userData[1]', '$emailAddress')";
            $queuedEmailAddresses[] = $emailAddress;
        }
    }
    
    // echo "INSERT INTO users (name, surname, email) VALUES ".implode(",",$queryArray)."\n";
    $result = $databaseConnection->query("INSERT INTO users (name, surname, email) VALUES ".implode(",",$queryArray));
    if (!$result) {
        die('Invalid query');
    }
    echo "Database populated. Closing connection\n";
    $databaseConnection->close();
}

function validateEmailAddress($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

echo "~~ Initialising user upload ~~ \n";
[$cmdValues, $csvRows] = readInputFile();
echo "CSV Rows: {$csvRows[0][0]}, {$csvRows[0][1]}, {$csvRows[0][2]}\n";

validateDatabaseCreds($cmdValues);

initialiseDatabase($cmdValues['u'], $cmdValues['p'], $cmdValues['h']);

insertUsers($csvRows, $cmdValues['h'], $cmdValues['u'], $cmdValues['p']);

?>
