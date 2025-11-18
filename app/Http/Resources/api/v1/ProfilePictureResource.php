<?php

namespace App\Http\Resources\api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePictureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'url' => $this->profile_picture,
            'public_id' => $this->profile_picture_public_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
