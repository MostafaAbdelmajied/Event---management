<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendeeController extends Controller
{

    public function __construct()
    {
        return $this->middleware('auth:sanctum')->except(['index','show']);
        return $this->middleware("throttle:api");
        return $this->authorizeResource(Attendee::class,'attendee');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        $attendees = $event->attendees();
        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request ,Event $event)
    {
        $attendee = $event->attendees()->create([
            "user_id"=>$request->user()->id,
        ]);
        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event,Attendee $attendee)
    {
        return new AttendeeResource($attendee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        $this->authorize("delete-attendee",[$attendee,$event]);
        $attendee->delete();
        return response(status:204);
    }
}
