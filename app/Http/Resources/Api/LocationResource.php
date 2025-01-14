<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'attributes' => [
                'name' => $this->name,
                'country' => $this->country,
                'state' => $this->state,
                'street' => $this->street,
                'county' => $this->county,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
        ];
    }
}
