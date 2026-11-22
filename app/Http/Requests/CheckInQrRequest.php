<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInQrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jadwal_id' => ['required', 'integer', 'exists:jadwal_posyandu,id'],
            'qr_code' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'jadwal_id.required' => 'Jadwal posyandu wajib dipilih.',
            'jadwal_id.exists' => 'Jadwal posyandu tidak valid.',
            'qr_code.required' => 'QR Code kartu balita wajib dipindai.',
        ];
    }
}
