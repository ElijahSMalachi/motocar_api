<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class DocumentsResource extends JsonResource
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
            'user_id' => $this->user_id,
            'documentable_id' => $this->documentable_id,
            'attributes' => [
                'make' => $this->make,
                'file_path' => $this->pathGenerator($this->file_path),
                'documentable_type' => $this->documentable_type,
            ]
        ];
    }

    private function pathGenerator($path)
    {
        if (App::environment('local')) {
            $port = request()->getPort(); // Dynamically get the port
            $path = 'http://' . request()->getHost() . ':' . $port . '/' . $path;
        } else {
            $path = 'https://' . request()->getHost() . '/' . $path;
        }
        return $path;
    }
}
