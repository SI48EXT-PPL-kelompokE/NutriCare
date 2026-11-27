<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_id' => ['required', 'integer', 'exists:jadwal_posyandu,id'],
            'balita_id' => ['required', 'integer', 'exists:balita,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_id.required' => 'Jadwal posyandu wajib dipilih.',
            'jadwal_id.exists' => 'Jadwal posyandu tidak valid.',
            'balita_id.required' => 'Balita wajib dipilih.',
            'balita_id.exists' => 'Data balita tidak ditemukan.',
        ];
    }
}
