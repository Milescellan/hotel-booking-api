<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->with('room')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->room_id, fn ($q, $roomId) => $q->where('room_id', $roomId))
            ->when($request->guest_email, fn ($q, $email) => $q->where('guest_email', $email))
            ->when($request->from, fn ($q, $from) => $q->where('check_in', '>=', $from))
            ->when($request->to, fn ($q, $to) => $q->where('check_out', '<=', $to))
            ->get();

        return BookingResource::collection($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();
        $room = Room::findOrFail($data['room_id']);

        $overlaps = Booking::where('room_id', $room->id)
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<', $data['check_out'])
            ->where('check_out', '>', $data['check_in'])
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'room_id' => 'This room is already booked for the selected dates.',
            ]);
        }

        $nights = \Carbon\Carbon::parse($data['check_in'])->diffInDays(\Carbon\Carbon::parse($data['check_out']));

        $booking = Booking::create([
            ...$data,
            'status' => 'pending',
            'total_price' => $nights * $room->price_per_night,
        ]);

        $booking->load('room');

        Mail::to($booking->guest_email)->queue(new BookingConfirmationMail($booking));

        return new BookingResource($booking);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        return new BookingResource($booking->load('room'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        $booking->update($request->validated());

        return new BookingResource($booking->load('room'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        $booking->update(['status' => 'cancelled']);

        return response()->json(null, 204);
    }
}
