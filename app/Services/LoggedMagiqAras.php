<?php

namespace App\Services;

use App\Data\UserCriteria;
use App\Data\SortDirection;
use App\Models\CalculationLog;
use App\Models\LogLevel1;
use App\Models\LogLevel2;
use App\Models\LogLevel3;

class LoggedMagiqAras extends MagiqAras
{
    public function __construct(UserCriteria $userCriteria, array $places)
    {
        parent::__construct($userCriteria, $places);
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

        $calculationLogId = CalculationLog::insertGetId([
            'inp_l3_cg1_a' => $this->userCriteria->inp_l3_cg1_a,
            'inp_l3_cg1_b' => $this->userCriteria->inp_l3_cg1_b,
            'inp_l3_cg2_a' => $this->userCriteria->inp_l3_cg2_a,
            'inp_l3_cg2_b' => $this->userCriteria->inp_l3_cg2_b,
            'inp_l3_cg2_c' => $this->userCriteria->inp_l3_cg2_c,
            'inp_l3_cg3_a' => $this->userCriteria->inp_l3_cg3_a,
            'inp_l3_cg3_b' => $this->userCriteria->inp_l3_cg3_b,
            'inp_l3_cg3_c' => $this->userCriteria->inp_l3_cg3_c,
            'inp_l2_cg1_a' => $this->userCriteria->inp_l2_cg1_a,
            'inp_l2_cg1_b' => $this->userCriteria->inp_l2_cg1_b,
            'inp_l2_cg1_c' => $this->userCriteria->inp_l2_cg1_c,
            'inp_l1_a' => $this->userCriteria->inp_l1_a,
            'inp_l1_b' => $this->userCriteria->inp_l1_b,
            'inp_l1_c' => $this->userCriteria->inp_l1_c,
            'l1_b_direction' => $l1_b_direction,
            'l1_c_direction' => $l1_c_direction,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        $level3 = $this->calculateLevel3();

        foreach ($level3 as $row)
        {
            $placeId = $row['place']['id'];
            $output_l2_cg1_a = $row['metric']['l2_cg1_a'];
            $output_l2_cg1_b = $row['metric']['l2_cg1_b'];
            $output_l2_cg1_c = $row['metric']['l2_cg1_c'];

            LogLevel3::insert([
                'l2_cg1_a' => $output_l2_cg1_a,
                'l2_cg1_b' => $output_l2_cg1_b,
                'l2_cg1_c' => $output_l2_cg1_c,
                'parent_id' => $calculationLogId,
                'place_id' => $placeId
            ]);
        }

        $level2 = $this->calculateLevel2($level3);

        foreach ($level2 as $row)
        {
            $placeId = $row['place']['id'];
            $output_l1_a = $row['metric']['l1_a'];

            LogLevel2::insert([
                'parent_id' => $calculationLogId,
                'place_id' => $placeId,
                'l1_a' => $output_l1_a
            ]);
        }

        $level1 = $this->calculateLevel1($level2, $l1_b_direction, $l1_c_direction);

        foreach ($level1 as $row)
        {
            $placeId = $row['place']['id'];
            $score = $row['score'];

            LogLevel1::insert([
                'parent_id' => $calculationLogId,
                'place_id' => $placeId,
                'score' => $score
            ]);
        }

        $result = array_slice(
            $this->sortAndMapResult($level1, $sortDirection),
            0,
            $limit
        );

        return $result;
    }
}
