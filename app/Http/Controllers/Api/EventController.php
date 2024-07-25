<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware("auth:sanctum")->except(['index','show']);
        return $this->middleware("throttle:api");
        $this->authorizeResource(Event::class,'event');
    }
    public function index()
    {
        return EventResource::collection(Event::with('user','attendees')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $valid = Validator::make($request->all(),[
            'name'=>'required|string',
            "description"=>'nullable|string',
            "start_time"=>'required|date',
            "end_time"=>'required|date|after:start_time'
        ]);

        if($valid->fails()){
            return response()->json(['msg'=>$valid->errors()],301);
        }

        $event = Event::create([
            ...$valid->getData(),
            "user_id"=>$request->user()->id
        ]);
        // dd($event);
        $event->load('user','attendees');
        return response()->json(['msg'=>"event created","event" =>new EventResource($event)],200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        $event->load('user','attendees');
        return new EventResource($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        if(Gate::denies("update-event",$event)){
            abort(403,"you arn't authorized to update this event");
        }
        $valid = Validator::make($request->all(),[
            'name'=>'sometimes|string',
            "description"=>'nullable|string',
            "start_time"=>'sometimes|date',
            "end_time"=>'sometimes|date|after:start_time'
        ]);

        if($valid->fails()){
            return response()->json(['msg'=>$valid->errors()],301);
        }

        // dd($valid->getData());
        $event->update($valid->getData());
        $event->load('user','attendees');
        return new EventResource($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event ->delete();
        return response()->json(['msg'=>"Event deleted succefully"]);
    }
}
