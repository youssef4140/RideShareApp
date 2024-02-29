<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public function becomeDriver(Request $request)
    {

        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2024'],
            'model' => ['required', 'string'],
            'color' => ['required', 'string'],
            'license_plate' => ['required', 'string']
        ]);

        try {
            $user_id = $request->user()->id;

            DB::beginTransaction();

            Driver::create([
                "user_id" => $user_id,
                "year" => $request->year,
                "model" => $request->model,
                "color" => $request->color,
                "license_plate" => $request->license_plate
            ]);

            $user = User::where('id', $user_id)->with('driver')->first();

            $user->update([
                "isDriver" => 1
            ]);

            DB::commit();

            return response()->json([
                "user" => $user
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
