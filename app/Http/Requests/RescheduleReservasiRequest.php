<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RescheduleReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'new_jadwal_id' => ['required', 'integer', 'exists:jadwal_posyandu,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'new_jadwal_id.required' => 'Jadwal baru wajib dipilih.',
            'new_jadwal_id.exists' => 'Jadwal baru tidak valid.',
        ];
    }
}
