<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripController extends Controller
{
    public function book(Request $request)
    {
        $request->validate([
            'origin' => ['required', 'array'],
            'origin.lat' => ['required', 'numeric'],
            'origin.lng' => ['required', 'numeric'],
            'destination' => ['required', 'array'],
            'destination.lat' => ['required', 'numeric'],
            'destination.lng' => ['required', 'numeric'],
            'destination_name' => ['required', 'string'],
        ]);

        try {
        $trip = Trip::create([
            'user_id'=>$request->user()->id,
            'origin'=>$request->origin,
            'destination'=>$request->destination,
            'destination_name'=>$request->destination_name
        ]);
        $trip->load('user');

        return response()->json([
            "trip"=>$trip
        ]);
    }catch(\Exception $e){
        return response()->json(['error' => $e->getMessage()], 500);

    }


    }

    public function show(Trip $trip)
    {
        $trip->load('user')->load('driver');
        return $trip;
    }

    public function userCancel(Trip $trip)
    {
        try{
            if($trip->status != "pending") return response()->json(["You can't cancel an accepted trip"]);
            $deletion = $trip->forceDelete();

            return response()->json([
                "id"=>$trip->id,
                "deletion"=>$deletion
            ]);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }


    public function all()
    {
        return Trip::where('status','pending')->get();
    }

    public function accept(Request $request,Trip $trip)
    {
        try {
            $request->validate([
                'driver_location' => ['required', 'array'],
                'driver_location.lat' => ['required', 'numeric'],
                'driver_location.lng' => ['required', 'numeric'],
            ]);

            $trip->update([
                'driver_id'=>$request->user()->id,
                'driver_location'=>$request->driver_location,
                'status'=>'accepted'
            ]);
            $trip->load('user')->load('driver');

            return response()->json(["trip"=>$trip],200);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function driverCancel(Trip $trip)
    {

        if(!($trip->driver_id == Auth::id())) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "driver_location"=>null,
            "driver_id" => null,
            "status"=>'pending'
        ]);
        $trip->load('user')->load('driver');

        return response()->json([
            "trip"=>$trip
        ]);
    }


    public function start(Trip $trip)
    {
        if(!($trip->driver_id == Auth::id())) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "status"=>'started'
        ]);
        $trip->load('user')->load('driver');

        return response()->json([
            "trip"=>$trip
        ]);
    }

    public function complete(Trip $trip)
    {
        if(!($trip->driver_id == Auth::id())) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "status"=>'completed'
        ]);
        $trip->load('user')->load('driver');

        return response()->json([
            "trip"=>$trip
        ]);
    }

    public function location(Request $request,Trip $trip)
    {
        if(!($trip->driver_id == Auth::id())) return response()->json(["message"=>"Unauthenticated"]);

        try {
            $request->validate([
                'driver_location' => ['required', 'array'],
                'driver_location.lat' => ['required', 'numeric'],
                'driver_location.lng' => ['required', 'numeric'],
            ]);

            $trip->update([
                'driver_id'=>$request->user()->id,
                'driver_location'=>$request->driver_location,
                'status'=>'accepted'
            ]);
            $trip->load('user')->load('driver');


            return response()->json(["trip"=>$trip],200);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }




}
