<?php

function size(array $arr, int $direction = 0) {
    $rows = sizeof($arr);
    $cols = sizeof($arr[0]);
    if ($direction != 0) {
        return ($direction == 1)? $rows : $cols;
    }
    return array($rows, $cols);
}
