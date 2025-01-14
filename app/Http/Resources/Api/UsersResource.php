<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "user_id" => $this->id,
            "attributes" => [
                "first_name" => $this->first_name,
                "last_name" => $this->last_name,
                "profile_picture" => $this->profile_picture,
                "email" => $this->email,
                "dob" => $this->dob,
                "gender" => $this->gender,
            ]
        ];
    }
}
