<?php
ini_set('display_errors', 0);
ini_set('error_reporting', E_ALL & ~E_WARNING);
function calculateCombinations(array $hand, int $groupSize)
    {
        $result = [];
        $n = count($hand);
        if ($groupSize > $n) {
            return $result; // If i is greater than n, return an empty array
        }
        // Base case: If i is 0, return an array containing an empty array
        if ($groupSize == 0) {
            return [[]];
        }
        // Base case: If i is equal to n, return the array itself
        if ($groupSize == $n) {
            return [$hand];
        }
        // Recursive case
        // First, get combinations without the first element
        $subsets = calculateCombinations(array_slice($hand, 1), $groupSize);
        foreach ($subsets as $subset) {
            $result[] = $subset;
        }
        // Then, get combinations including the first element
        $subsets_with_first = calculateCombinations(array_slice($hand, 1), $groupSize - 1);
        foreach ($subsets_with_first as $subset) {
            array_unshift($subset, $hand[0]);
            $result[] = $subset;
        }
        return $result;
    }

function run($values, $group_sizes){
    $temp = [];
    $temp2 = [];
    $temp3 = [];

    $temp = calculateCombinations($values, $group_sizes[0]);
    if (count($group_sizes) > 2){
        foreach ($temp as $combination){
            $tmpArr = array_values(array_diff($values, $combination));
            $temp2 = calculateCombinations($tmpArr, $group_sizes[1]);
            foreach ($temp2 as $subset){
                $temp3[] = [$combination, $subset];
            }
            unset($temp2);
        }
        $temp = $temp3;
        unset($temp3);
    }

    if (count($group_sizes) > 3){
        foreach ($temp as $combination){
            $tmpArr = array_values(array_diff($values, $combination[0], $combination[1]));
            $temp2 = calculateCombinations($tmpArr, $group_sizes[2]);
            foreach ($temp2 as $subset){
                $temp3[] = [$combination[0], $combination[1], $subset];
            }
            unset($temp2);
        }
        $temp = $temp3;
        unset($temp3);
    }
    return count($temp);
}

// Test
$values = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
$group_sizes = [3, 3, 3, 4];
echo run($values, $group_sizes) . PHP_EOL;
echo "Max Mem Used: " . (memory_get_peak_usage(true)/1024)/1024 . PHP_EOL;
?>
