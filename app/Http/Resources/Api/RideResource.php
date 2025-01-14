<?php

namespace App\Http\Resources\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RideResource extends JsonResource
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
            'driver_id' => $this->driver_id,
            'pickup_location_id' => $this->pickup_location_id,
            'destination_location_id' => $this->destination_location_id,
            'attributes' => [
                'seats' => $this->seats,
                'available_seats' => $this->available_seats,
                'price_per_seat' => $this->price_per_seat,
                'start_off_date' => $this->start_off_date,
                'return_date' => $this->return_date,
                'pickup_location' => new LocationResource($this->pickupLocation),
                'destination_location' => new LocationResource($this->destinationLocation),
                'driver' => new UsersResource($this->driver)
            ],
        ];
    }
}
