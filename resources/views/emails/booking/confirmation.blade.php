<x-mail::message>
# Booking Confirmed

Hi {{ $booking->guest_name }},

Your booking has been received. Here are the details:

<x-mail::table>
| Field | Details |
| ------------- | ------------- |
| Booking # | {{ $booking->id }} |
| Room | {{ $booking->room->room_number }} ({{ $booking->room->type }}) |
| Check-in | {{ $booking->check_in->toFormattedDateString() }} |
| Check-out | {{ $booking->check_out->toFormattedDateString() }} |
| Total price | ${{ number_format($booking->total_price, 2) }} |
| Status | {{ ucfirst($booking->status) }} |
</x-mail::table>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
