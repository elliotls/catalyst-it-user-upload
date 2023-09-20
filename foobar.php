#!/usr/bin/env php
<?php

echo "~~ Running FOOBAR number evaluation ~~\n";
$outputArray = [];
for ($i=1; $i<=100; $i++) {
    $outputArray[$i-1] = $i;
    if ($i % 3 == 0) $outputArray[$i-1] = 'foo';
    if ($i % 5 == 0) $outputArray[$i-1] = 'bar';
    if ($i % 3 == 0 && $i % 5 == 0) $outputArray[$i-1] = 'foobar';
}
echo implode(', ', $outputArray) . "\n";
?>