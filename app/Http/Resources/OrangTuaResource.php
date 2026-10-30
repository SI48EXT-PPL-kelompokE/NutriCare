<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrangTuaResource extends JsonResource
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
            'nik' => $this->nik,
            'name' => $this->name,
            'alamat' => $this->alamat,
            'kontak' => $this->kontak,
            'pregnancy_status' => $this->pregnancy_status,
            'has_account' => !is_null($this->user_id),
            'user' => $this->whenLoaded('user', function () {
                if (!$this->user) {
                    return null;
                }

                return [
                    'id' => $this->user->id,
                    'role_id' => $this->user->role_id,
                    'role_name' => $this->user->role?->name_role,
                    'name' => $this->user->name,
                    'username' => $this->user->username,
                    'email' => $this->user->email,
                    'active_status' => (bool) $this->user->active_status,
                    'last_login' => $this->user->last_login?->toISOString(),
                ];
            }),
            'balita_count' => $this->whenCounted('balita', $this->balita_count),
            'balita' => $this->whenLoaded('balita'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

