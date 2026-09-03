<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $rooms = Room::query()
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->min_price, fn ($q, $p) => $q->where('price_per_night', '>=', $p))
            ->when($request->max_price, fn ($q, $p) => $q->where('price_per_night', '<=', $p))
            ->when($request->capacity, fn ($q, $c) => $q->where('capacity', '>=', $c))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(
                fn ($q2) => $q2->where('room_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            ))
            ->get();

        return RoomResource::collection($rooms);
    }

    /**
     * Display rooms with no overlapping booking for the given date range.
     */
    public function available(Request $request)
    {
        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
        ]);

        $rooms = Room::query()
            ->where('status', 'available')
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->capacity, fn ($q, $c) => $q->where('capacity', '>=', $c))
            ->whereDoesntHave('bookings', function ($q) use ($request) {
                $q->where('status', '!=', 'cancelled')
                    ->where('check_in', '<', $request->check_out)
                    ->where('check_out', '>', $request->check_in);
            })
            ->get();

        return RoomResource::collection($rooms);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request)
    {
        $room = Room::create($request->validated());

        return new RoomResource($room);
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        return new RoomResource($room);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $room->update($request->validated());

        return new RoomResource($room);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();

        return response()->json(null, 204);
    }
}
