<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Services\MagiqAras;
use App\Data\UserCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DSSCalculationController extends Controller
{
    public function calculate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'inp_l3_cg1_a' => 'required|numeric',
            'inp_l3_cg1_b' => 'required|numeric',
            'inp_l3_cg2_a' => 'required|numeric',
            'inp_l3_cg2_b' => 'required|numeric',
            'inp_l3_cg2_c' => 'required|numeric',
            'inp_l3_cg3_a' => 'required|numeric',
            'inp_l3_cg3_b' => 'required|numeric',
            'inp_l3_cg3_c' => 'required|numeric',
            'inp_l2_cg1_a' => 'required|numeric',
            'inp_l2_cg1_b' => 'required|numeric',
            'inp_l2_cg1_c' => 'required|numeric',
            'inp_l1_a' => 'required|numeric',
            'inp_l1_b' => 'required|numeric',
            'inp_l1_c' => 'required|numeric',
            'l1_b_direction' => 'required|numeric',
            'l1_c_direction' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();
        $places = Place::all()->toArray();

        $criteria = new UserCriteria();
        $criteria->inp_l3_cg1_a = floatval($validatedData['inp_l3_cg1_a']);
        $criteria->inp_l3_cg1_b = floatval($validatedData['inp_l3_cg1_b']);
        $criteria->inp_l3_cg2_a = floatval($validatedData['inp_l3_cg2_a']);
        $criteria->inp_l3_cg2_b = floatval($validatedData['inp_l3_cg2_b']);
        $criteria->inp_l3_cg2_c = floatval($validatedData['inp_l3_cg2_c']);
        $criteria->inp_l3_cg3_a = floatval($validatedData['inp_l3_cg3_a']);
        $criteria->inp_l3_cg3_b = floatval($validatedData['inp_l3_cg3_b']);
        $criteria->inp_l3_cg3_c = floatval($validatedData['inp_l3_cg3_c']);
        $criteria->inp_l2_cg1_a = floatval($validatedData['inp_l2_cg1_a']);
        $criteria->inp_l2_cg1_b = floatval($validatedData['inp_l2_cg1_b']);
        $criteria->inp_l2_cg1_c = floatval($validatedData['inp_l2_cg1_c']);
        $criteria->inp_l1_a = floatval($validatedData['inp_l1_a']);
        $criteria->inp_l1_b = floatval($validatedData['inp_l1_b']);
        $criteria->inp_l1_c = floatval($validatedData['inp_l1_c']);

        $magiqAras = new MagiqAras($criteria, $places);
        $result = $magiqAras->getBestPlace(
            intval($validatedData['l1_b_direction']),
            intval($validatedData['l1_c_direction'])
        );

        return response()->json($result);
    }
}
