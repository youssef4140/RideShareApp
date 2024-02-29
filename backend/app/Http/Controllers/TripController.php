<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\TripAccepted;
use App\Events\TripBooked;
use App\Events\TripCanceledByDriver;
use App\Events\TripCanceledByUser;
use App\Events\TripCompleted;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Driver;

class TripController extends Controller
{
    public function book(Request $request)
    {
        $tripExists = Trip::where('user_id', $request->user()->id)
        ->where('status', '!=', 'completed')
        ->exists();
        if($tripExists) return response()->json(["you can't book multiple drives at once"]);
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
        $user = $request->user();
        $trip = Trip::create([
            'user_id'=>$user->id,
            'origin'=>$request->origin,
            'destination'=>$request->destination,
            'destination_name'=>$request->destination_name
        ]);
        $trip->load('user');

        TripBooked::dispatch();
        
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

            TripCanceledByUser::dispatch();
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
        $driver_id = $request->user()->id;
        if($trip->user_id == $driver_id) return response()->json(["message"=>"you can't accept your own ride!"]);
        $existingTrip = Trip::where('driver_id',$driver_id)->where('status','accepted')->first();
        if($existingTrip) return response()->json(["message"=>"you can't accept multiple rides"]);
        try {
            $request->validate([
                'driver_location' => ['required', 'array'],
                'driver_location.lat' => ['required', 'numeric'],
                'driver_location.lng' => ['required', 'numeric'],
            ]);

            $trip->update([
                'driver_id'=>$driver_id,
                'driver_location'=>$request->driver_location,
                'status'=>'accepted'
            ]);
            $driver = Driver::where('user_id',$driver_id)->first()->load('user');
            $trip->load('user');
            
            TripAccepted::dispatch($trip,$trip->user_id);


            return response()->json(["trip"=>$trip,
                                    "driver"=>$driver],200);
        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }

    public function driverCancel(Trip $trip)
    {
        $driver_id = Auth::id();

        if(!($trip->driver_id == $driver_id)) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "driver_location"=>null,
            "driver_id" => null,
            "status"=>'pending'
        ]);
        $driver = Driver::where('user_id',$driver_id)->first()->load('user');
        $trip->load('user');

        TripCanceledByDriver::dispatch($trip,$trip->user_id);
        return response()->json(["trip"=>$trip,
        "driver"=>$driver],200);
    }


    public function start(Trip $trip)
    {
        $driver_id=Auth::id();
        if(!($trip->driver_id == $driver_id)) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "status"=>'started'
        ]);
        $driver = Driver::where('user_id',$driver_id)->first()->load('user');
        $trip->load('user');

        TripStarted::dispatch($trip,$trip->user_id);
        return response()->json(["trip"=>$trip,
        "driver"=>$driver],200);
    }

    public function complete(Trip $trip)
    {
        $driver_id = Auth::id();
        if(!($trip->driver_id == $driver_id )) return response()->json(["message"=>"Unauthenticated"]);
        $trip->update([
            "status"=>'completed'
        ]);

        $driver = Driver::where('user_id',$driver_id)->first()->load('user');
        $trip->load('user');

        TripCompleted::dispatch($trip,$trip->user_id);
        return response()->json(["trip"=>$trip,
        "driver"=>$driver],200);
    }

    public function location(Request $request,Trip $trip)
    {
        $driver_id = Auth::id();
        if(!($trip->driver_id == $driver_id)) return response()->json(["message"=>"Unauthenticated"]);

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

            $driver = Driver::where('user_id',$driver_id)->first()->load('user');
            $trip->load('user');
    
            TripLocationUpdated::dispatch($trip,$trip->user_id);
            return response()->json(["trip"=>$trip,
            "driver"=>$driver],200);

        } catch (\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);

        }
    }




}
