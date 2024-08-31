<?php

namespace App\Services;

use App\Data\CriteriaDirection;
use App\Data\UserCriteria;
use App\Data\SortDirection;

class MagiqAras {
    protected $userCriteria;
    protected $places;

    public function __construct(UserCriteria $userCriteria, array $places)
    {
        $this->userCriteria = $userCriteria;
        $this->places = $places;
    }

    public function getBestPlace(
        int $l1_b_direction,
        int $l1_c_direction,
        int $limit = 5,
        int $sortDirection = SortDirection::DESCENDING
    ) {
        if (count($this->places) <= 0) {
            return [];
        }


        $level3 = $this->calculateLevel3();
        // dd($level3);
        $level2 = $this->calculateLevel2($level3);
        // dd($level2);
        $level1 = $this->calculateLevel1($level2, $l1_b_direction, $l1_c_direction);
        // dd($level1);
        $result = $this->sortAndMapResult($level1, $sortDirection);

        return array_slice($result, 0, $limit);
    }

    protected function sortAndMapResult(
        array $result,
        int $direction = SortDirection::DESCENDING
    ): array {
        usort($result, function($a, $b) use ($direction) {
            return $direction === SortDirection::DESCENDING
                ? $b['score'] <=> $a['score']
                : $a['score'] <=> $b['score'];
        });

        return array_map(function ($record, $index) {
            return [
                'id' => $record['place']['id'],
                'name' => $record['place']['name'],
                'city_name' => $record['place']['city_name'],
                'rank' => $index + 1,
                'score' => $record['score']
            ];
        }, $result, array_keys($result));
    }

    protected function calculateLevel1(
        array $graphs,
        int $l1_b_direction,
        int $l1_c_direction
    ): array {
        $output = [];
        $modifiedRows = $graphs;

        // Selects the minimum or maximum value based on the direction
        $maxMinSelector = function($input, $reducer, $direction) {
            return $reducer === null
                ? $input :
                (
                    $direction === CriteriaDirection::MIN
                        ? min($input, $reducer)
                        : max($input, $reducer)
                );
        };

        // Normalizes the value based on the direction
        $normalize = function($value, $direction) {
            return $direction === CriteriaDirection::MIN ? 1 / $value : $value;
        };

        $alternative0 = [
            'l1_a' => null,
            'l1_b' => null,
            'l1_c' => null
        ];

        $normalizationSum = [
            'l1_a' => 0,
            'l1_b' => 0,
            'l1_c' => 0
        ];

        foreach ($graphs as $graph) {
            // dd($graph);
            $alternative0['l1_a'] = $maxMinSelector($graph['metric']['l1_a'], $alternative0['l1_a'], CriteriaDirection::MAX);
            $alternative0['l1_b'] = $maxMinSelector($graph['place']['l1_b'], $alternative0['l1_b'], $l1_b_direction);
            $alternative0['l1_c'] = $maxMinSelector($graph['place']['l1_c'], $alternative0['l1_c'], $l1_c_direction);
        }

        // Normalized data
        $alternative0['l1_a'] = $normalize($alternative0['l1_a'], CriteriaDirection::MAX);
        $alternative0['l1_b'] = $normalize($alternative0['l1_b'], $l1_b_direction);
        $alternative0['l1_c'] = $normalize($alternative0['l1_c'], $l1_c_direction);

        // Normalize data
        foreach ($modifiedRows as &$graph) {
            $graph['place']['l1_b'] = $normalize($graph['place']['l1_b'], $l1_b_direction);
            $graph['place']['l1_c'] = $normalize($graph['place']['l1_c'], $l1_c_direction);
        }

        // dd($normalizationSum);

        foreach ($modifiedRows as $graph) {
            $normalizationSum['l1_a'] += $graph['metric']['l1_a'];
            $normalizationSum['l1_b'] += $graph['place']['l1_b'];
            $normalizationSum['l1_c'] += $graph['place']['l1_c'];
        }

        // Final normalization
        $alternative0['l1_a'] /= $normalizationSum['l1_a'];
        $alternative0['l1_b'] /= $normalizationSum['l1_b'];
        $alternative0['l1_c'] /= $normalizationSum['l1_c'];

        foreach ($modifiedRows as &$row) {
            $row['metric']['l1_a'] /= $normalizationSum['l1_a'];
            $row['place']['l1_b'] /= $normalizationSum['l1_b'];
            $row['place']['l1_c'] /= $normalizationSum['l1_c'];
        }

        // Weighted alternative data
        $alternative0['l1_a'] *= $this->userCriteria->inp_l1_a;
        $alternative0['l1_b'] *= $this->userCriteria->inp_l1_b;
        $alternative0['l1_c'] *= $this->userCriteria->inp_l1_c;

        foreach ($modifiedRows as &$row) {
            $row['metric']['l1_a'] *= $this->userCriteria->inp_l1_a;
            $row['place']['l1_b'] *= $this->userCriteria->inp_l1_b;
            $row['place']['l1_c'] *= $this->userCriteria->inp_l1_c;
        }

        // Determine the best data
        $alt = $alternative0['l1_a'] + $alternative0['l1_b'] + $alternative0['l1_c'];

        foreach ($modifiedRows as $index => $current) {
            $score = (
                $current['metric']['l1_a']
                + $current['place']['l1_b']
                + $current['place']['l1_c']
            ) / $alt;

            $output[] = [
                'place' => $this->places[$index],
                'score' => $score
            ];
        }

        return $output;
    }

    protected function calculateLevel2(array $graphs): array
    {
        $output = [];
        $modifiedRows = $graphs;
        $alternative0 = [
            'l2_cg1_a' => 0,
            'l2_cg1_b' => 0,
            'l2_cg1_c' => 0
        ];

        $normalizationSum = [
            'l2_cg1_a' => 0,
            'l2_cg1_b' => 0,
            'l2_cg1_c' => 0
        ];

        foreach ($graphs as $graph) {
            // Finding the maximum value
            $alternative0['l2_cg1_a'] = max($alternative0['l2_cg1_a'], $graph['metric']['l2_cg1_a']);
            $alternative0['l2_cg1_b'] = max($alternative0['l2_cg1_b'], $graph['metric']['l2_cg1_b']);
            $alternative0['l2_cg1_c'] = max($alternative0['l2_cg1_c'], $graph['metric']['l2_cg1_c']);

            // Summing data
            $normalizationSum['l2_cg1_a'] += $graph['metric']['l2_cg1_a'];
            $normalizationSum['l2_cg1_b'] += $graph['metric']['l2_cg1_b'];
            $normalizationSum['l2_cg1_c'] += $graph['metric']['l2_cg1_c'];
        }

        // Normalized alternative data
        $alternative0['l2_cg1_a'] /= $normalizationSum['l2_cg1_a'];
        $alternative0['l2_cg1_b'] /= $normalizationSum['l2_cg1_b'];
        $alternative0['l2_cg1_c'] /= $normalizationSum['l2_cg1_c'];

        foreach ($graphs as $index => $graph) {
            $modifiedRows[$index]['metric']['l2_cg1_a'] /= $normalizationSum['l2_cg1_a'];
            $modifiedRows[$index]['metric']['l2_cg1_b'] /= $normalizationSum['l2_cg1_b'];
            $modifiedRows[$index]['metric']['l2_cg1_c'] /= $normalizationSum['l2_cg1_c'];
        }

        // Weighted alternative data
        $alternative0['l2_cg1_a'] *= $this->userCriteria->inp_l2_cg1_a;
        $alternative0['l2_cg1_b'] *= $this->userCriteria->inp_l2_cg1_b;
        $alternative0['l2_cg1_c'] *= $this->userCriteria->inp_l2_cg1_c;

        foreach ($modifiedRows as $index => $graph) {
            $modifiedRows[$index]['metric']['l2_cg1_a'] *= $this->userCriteria->inp_l2_cg1_a;
            $modifiedRows[$index]['metric']['l2_cg1_b'] *= $this->userCriteria->inp_l2_cg1_b;
            $modifiedRows[$index]['metric']['l2_cg1_c'] *= $this->userCriteria->inp_l2_cg1_c;
        }

        // Determine the best data
        $alt = $alternative0['l2_cg1_a'] + $alternative0['l2_cg1_b'] + $alternative0['l2_cg1_c'];

        foreach ($modifiedRows as $index => $current) {
            $output[] = [
                'place' => $this->places[$index],
                'metric' => [
                    'l1_a' => (
                        $current['metric']['l2_cg1_a']
                        + $current['metric']['l2_cg1_b']
                        + $current['metric']['l2_cg1_c']
                    ) / $alt
                ]
            ];
        }

        return $output;
    }

    protected function calculateLevel3(): array
    {
        $output = [];
        $modifiedRows = $this->places;
        $alternative0 = [
            'l3_cg1_a' => 0,
            'l3_cg1_b' => 0,
            'l3_cg2_a' => 0,
            'l3_cg2_b' => 0,
            'l3_cg2_c' => 0,
            'l3_cg3_a' => 0,
            'l3_cg3_b' => 0,
            'l3_cg3_c' => 0
        ];

        $normalizationSum = [
            'l3_cg1_a' => 0,
            'l3_cg1_b' => 0,
            'l3_cg2_a' => 0,
            'l3_cg2_b' => 0,
            'l3_cg2_c' => 0,
            'l3_cg3_a' => 0,
            'l3_cg3_b' => 0,
            'l3_cg3_c' => 0
        ];

        foreach ($this->places as $place) {
            // Finding the maximum value
            $alternative0['l3_cg1_a'] = max($alternative0['l3_cg1_a'], $place['l3_cg1_a']);
            $alternative0['l3_cg1_b'] = max($alternative0['l3_cg1_b'], $place['l3_cg1_b']);
            $alternative0['l3_cg2_a'] = max($alternative0['l3_cg2_a'], $place['l3_cg2_a']);
            $alternative0['l3_cg2_b'] = max($alternative0['l3_cg2_b'], $place['l3_cg2_b']);
            $alternative0['l3_cg2_c'] = max($alternative0['l3_cg2_c'], $place['l3_cg2_c']);
            $alternative0['l3_cg3_a'] = max($alternative0['l3_cg3_a'], $place['l3_cg3_a']);
            $alternative0['l3_cg3_b'] = max($alternative0['l3_cg3_b'], $place['l3_cg3_b']);
            $alternative0['l3_cg3_c'] = max($alternative0['l3_cg3_c'], $place['l3_cg3_c']);

            // Summing data
            $normalizationSum['l3_cg1_a'] += $place['l3_cg1_a'];
            $normalizationSum['l3_cg1_b'] += $place['l3_cg1_b'];
            $normalizationSum['l3_cg2_a'] += $place['l3_cg2_a'];
            $normalizationSum['l3_cg2_b'] += $place['l3_cg2_b'];
            $normalizationSum['l3_cg2_c'] += $place['l3_cg2_c'];
            $normalizationSum['l3_cg3_a'] += $place['l3_cg3_a'];
            $normalizationSum['l3_cg3_b'] += $place['l3_cg3_b'];
            $normalizationSum['l3_cg3_c'] += $place['l3_cg3_c'];
        }

        // Normalized alternative data
        $alternative0['l3_cg1_a'] /= $normalizationSum['l3_cg1_a'];
        $alternative0['l3_cg1_b'] /= $normalizationSum['l3_cg1_b'];
        $alternative0['l3_cg2_a'] /= $normalizationSum['l3_cg2_a'];
        $alternative0['l3_cg2_b'] /= $normalizationSum['l3_cg2_b'];
        $alternative0['l3_cg2_c'] /= $normalizationSum['l3_cg2_c'];
        $alternative0['l3_cg3_a'] /= $normalizationSum['l3_cg3_a'];
        $alternative0['l3_cg3_b'] /= $normalizationSum['l3_cg3_b'];
        $alternative0['l3_cg3_c'] /= $normalizationSum['l3_cg3_c'];

        foreach ($this->places as $index => $place) {
            $modifiedRows[$index]['l3_cg1_a'] /= $normalizationSum['l3_cg1_a'];
            $modifiedRows[$index]['l3_cg1_b'] /= $normalizationSum['l3_cg1_b'];
            $modifiedRows[$index]['l3_cg2_a'] /= $normalizationSum['l3_cg2_a'];
            $modifiedRows[$index]['l3_cg2_b'] /= $normalizationSum['l3_cg2_b'];
            $modifiedRows[$index]['l3_cg2_c'] /= $normalizationSum['l3_cg2_c'];
            $modifiedRows[$index]['l3_cg3_a'] /= $normalizationSum['l3_cg3_a'];
            $modifiedRows[$index]['l3_cg3_b'] /= $normalizationSum['l3_cg3_b'];
            $modifiedRows[$index]['l3_cg3_c'] /= $normalizationSum['l3_cg3_c'];
        }

        // Weighted alternative data
        $alternative0['l3_cg1_a'] *= $this->userCriteria->inp_l3_cg1_a;
        $alternative0['l3_cg1_b'] *= $this->userCriteria->inp_l3_cg1_b;
        $alternative0['l3_cg2_a'] *= $this->userCriteria->inp_l3_cg2_a;
        $alternative0['l3_cg2_b'] *= $this->userCriteria->inp_l3_cg2_b;
        $alternative0['l3_cg2_c'] *= $this->userCriteria->inp_l3_cg2_c;
        $alternative0['l3_cg3_a'] *= $this->userCriteria->inp_l3_cg3_a;
        $alternative0['l3_cg3_b'] *= $this->userCriteria->inp_l3_cg3_b;
        $alternative0['l3_cg3_c'] *= $this->userCriteria->inp_l3_cg3_c;

        foreach ($modifiedRows as $index => $place) {
            $modifiedRows[$index]['l3_cg1_a'] *= $this->userCriteria->inp_l3_cg1_a;
            $modifiedRows[$index]['l3_cg1_b'] *= $this->userCriteria->inp_l3_cg1_b;
            $modifiedRows[$index]['l3_cg2_a'] *= $this->userCriteria->inp_l3_cg2_a;
            $modifiedRows[$index]['l3_cg2_b'] *= $this->userCriteria->inp_l3_cg2_b;
            $modifiedRows[$index]['l3_cg2_c'] *= $this->userCriteria->inp_l3_cg2_c;
            $modifiedRows[$index]['l3_cg3_a'] *= $this->userCriteria->inp_l3_cg3_a;
            $modifiedRows[$index]['l3_cg3_b'] *= $this->userCriteria->inp_l3_cg3_b;
            $modifiedRows[$index]['l3_cg3_c'] *= $this->userCriteria->inp_l3_cg3_c;
        }


        // Determine the best data
        $alt1 = $alternative0['l3_cg1_a'] + $alternative0['l3_cg1_b'];
        $alt2 = $alternative0['l3_cg2_a'] + $alternative0['l3_cg2_b'] + $alternative0['l3_cg2_c'];
        $alt3 = $alternative0['l3_cg3_a'] + $alternative0['l3_cg3_b'] + $alternative0['l3_cg3_c'];

        foreach ($modifiedRows as $place) {
            $output[] = [
                'place' => $place,
                'metric' => [
                    'l2_cg1_a' => ($place['l3_cg1_a'] + $place['l3_cg1_b']) / $alt1,
                    'l2_cg1_b' => ($place['l3_cg2_a'] + $place['l3_cg2_b'] + $place['l3_cg2_c']) / $alt2,
                    'l2_cg1_c' => ($place['l3_cg3_a'] + $place['l3_cg3_b'] + $place['l3_cg3_c']) / $alt3
                ]
            ];
        }

        return $output;
    }
}
