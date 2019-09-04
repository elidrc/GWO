<?php

// Grey Wolf Optimizer (GWO)
// Programmer : Elid Rubio
// Main Paper: S. Mirjalili, S. M. Mirjalili, A. Lewis, Grey Wolf Optimizer, Advances in Engineering


require('functions.php');


function createWolfpack(int $agents, Array $search_space)
{
    $dimensions = size($search_space, 2);
    $wolfpack = [];
    for ($i=0; $i < $agents; $i++) {
        $positions = [];
        for ($j=0; $j < $dimensions; $j++) {
            $positions[] = $search_space[0][$j] + lcg_value() * $search_space[1][$j] - $search_space[0][$j];
        }
        $wolfpack[] = $positions;
    }
    return $wolfpack;
}

function checkWolfpack(Array $wolfpack, $search_space) {
    list($agents, $dimensions) = size($wolfpack);

    // Return back the search agents that go beyond the bonduries of the search space
    for ($i=0; $i < $agents; $i++) {
        for ($j=0; $j < $dimensions; $j++) {
            if ($wolfpack[$i][$j] < $search_space[0][$j] || $wolfpack[$i][$j] > $search_space[1][$j]) {
                $wolfpack[$i][$j] = $search_space[0][$j] + lcg_value() * abs($search_space[1][$j] - $search_space[0][$j]);
            }
        }
    }
    return $wolfpack;
}

function updateWolfpack(Array $leaders, Array $wolfpack, float $a)
{
    list($agents, $dimensions) = size($wolfpack);

    for ($i=0; $i < $agents; $i++) {
        for ($j=0; $j < $dimensions; $j++) {
            //Update with respect to leader (alpha, beta and delta)
            $x = [];
            foreach ($leaders as $leader) {
                $a1 = 2 * $a * lcg_value() - $a;
                $c1 = 2 * lcg_value();
                $dist = abs($c1 * $leader[$j] - $wolfpack[$i][$j]);
                $x[] = ($leader[$j] - ($a1 * $dist));
            }
            $wolfpack[$i][$j] = array_sum($x) / 3;
        }
    }

    return $wolfpack;
}

function gwo(int $agents, int $iterations, Array $search_space)
{
    $alpha_position = $beta_position =  $delta_position = null;
    $alpha_score = $beta_score = $delta_score = PHP_INT_MAX;

    $dimensions = size($search_space, 2);

    $wolfpack = createWolfpack($agents, $search_space);

    $i = 0;
    while ($i < $iterations) {
        $fitness = [];
        // Return back the search agents that go beyond the bonduries of the search space
        $wolfpack = checkWolfpack($wolfpack, $search_space);

        // Calculate objective function for each search agent
        for ($j = 0; $j < $agents; $j++) {
            // Sphere function
            $fitness[] = abs(0 - array_reduce($wolfpack[$j], function($carry, $item) {
                $carry += $item * $item;
                return $carry;
            }));

            // Update Alpha, Beta and Delta
            if ($fitness[$j] < $alpha_score) {
                $alpha_score = $fitness[$j];
                $alpha_position = $wolfpack[$j];
            }

            if ($fitness[$j] > $alpha_score && $fitness[$j] < $beta_score) {
                $beta_score = $fitness[$j];
                $beta_position = $wolfpack[$j];
            }

            if ($fitness[$j] > $alpha_score && $fitness[$j] > $beta_score && $fitness[$j] < $delta_score) {
                $delta_score = $fitness[$j];
                $delta_position = $wolfpack[$j];
            }
        }

        $a = 2 - $i * (2.0 / $iterations); // a decreases linearly fron 2 to 0

        // Update the positions of search agents with respect to leader
        $leaders = [$alpha_position, $beta_position, $delta_position];
        $wolfpack = updateWolfpack($leaders, $wolfpack, $a);

        echo "Iteration {$i}: Best Fitness {$alpha_score}\n";

        $i++;
    }
}



gwo(100, 100, [array_fill(0, 30, -5.12), array_fill(0, 30, 5.12)]);
