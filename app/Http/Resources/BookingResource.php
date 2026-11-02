<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'room' => new RoomResource($this->whenLoaded('room')),
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'check_in' => $this->check_in?->toDateString(),
            'check_out' => $this->check_out?->toDateString(),
            'nights' => $this->check_in?->diffInDays($this->check_out),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at->timezone('Asia/Manila')->format('Y-m-d h:i A'),
            'updated_at' => $this->updated_at->timezone('Asia/Manila')->format('Y-m-d h:i A'),
        ];
    }
}
