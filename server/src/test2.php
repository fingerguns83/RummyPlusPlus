<?php
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL & ~E_WARNING);

function calculateCombinations(array $hand, int $groupSize)
{
    $n = count($hand);
    if ($groupSize > $n) {
        return;
    }
    if ($groupSize == 0) {
        yield [];
        return;
    }
    if ($groupSize == $n) {
        yield $hand;
        return;
    }
    foreach (calculateCombinations(array_slice($hand, 1), $groupSize) as $subset) {
        yield $subset;
    }
    foreach (calculateCombinations(array_slice($hand, 1), $groupSize - 1) as $subset) {
        array_unshift($subset, $hand[0]);
        yield $subset;
    }
}

function checkHand($values, $group_sizes){
    foreach (calculateCombinations($values, $group_sizes[0]) as $combination1){
        if(count($group_sizes) < 2) yield [$combination1];
        $tmpArr1 = array_values(array_diff($values, $combination1));
        foreach (isset($group_sizes[1]) ? calculateCombinations($tmpArr1, $group_sizes[1]) : [] as $combination2){
            if(count($group_sizes) < 3) yield [$combination1, $combination2];
            $tmpArr2 = array_values(array_diff($tmpArr1, $combination2));
            foreach (isset($group_sizes[2]) ? calculateCombinations($tmpArr2, $group_sizes[2]) : [] as $combination3) {
                yield [$combination1, $combination2, $combination3];
            }
        }
    }
}

$values = array(1, 2, 3, 4, 5);
$groupings = [
    [5]
];

$output = [];
foreach ($groupings as $grouping) {
    foreach(checkHand($values, $grouping) as $combination) {
        $output[] = $combination;
    }
}
echo count($output) . PHP_EOL;
echo "Max Mem Used: " . (memory_get_peak_usage(true)/1024)/1024 . PHP_EOL;