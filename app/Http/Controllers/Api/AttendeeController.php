<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendeeResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttendeeController extends Controller
{
    use CanLoadRelationships;

    private $relations = ["user"];
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        Gate::authorize('viewAny', Attendee::class);
        $attendees = $this->loadRelationships(
            $event->attendees()->latest()
        );

        return AttendeeResource::collection(
            $attendees->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        Gate::authorize('create', Attendee::class);
        $attendee = $this->loadRelationships(
            $event->attendees()->create([
                "user_id" => $request->user()->id
            ])
        );
        return new AttendeeResource($attendee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Attendee $attendee)
    {
        Gate::authorize('viewAny', $attendee);
        return new AttendeeResource($this->loadRelationships($attendee));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        if (Gate::denies('delete-attendee', [$event, $attendee])) {
            abort(403, "Denied!");
        }

        $attendee->delete();

        return response()->json([
            'message' => 'Attendee deleted successfully'
        ]);
    }
}
