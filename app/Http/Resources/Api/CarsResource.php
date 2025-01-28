<?php

namespace App\Http\Resources\Api;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class CarsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $documents = Document::where('user_id', $this->user_id)->where('documentable_id', $this->id)->get();
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'attributes' => [
                'make' => $this->make,
                'model' => $this->model,
                'license_plate' => $this->license_plate,
                'seats' => $this->seats,
                'color' => $this->color,
                'year' => $this->year,
            ],
            'images' => DocumentsResource::collection($documents)
        ];
    }
}
