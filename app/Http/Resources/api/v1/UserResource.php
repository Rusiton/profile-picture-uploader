<?php

namespace App\Http\Resources\api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'verified' => $this->hasVerifiedEmail(),
            'profilePicture' => new ProfilePictureResource($this->whenLoaded('picture')),
        ];
    }
}
