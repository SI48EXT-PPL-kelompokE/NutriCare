<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBalitaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'orang_tua_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('orang_tua', 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'birth_date' => [
                'bail',
                'required',
                'date',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'jenis_kelamin' => [
                'required',
                'string',
                Rule::in(['L', 'P']),
            ],
        ];
    }

    /**
     * Custom message for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'orang_tua_id.required' => 'Orang tua wajib dipilih.',
            'orang_tua_id.integer' => 'ID orang tua tidak valid.',
            'orang_tua_id.exists' => 'Data orang tua yang dipilih tidak terdaftar dalam sistem.',
            'name.required' => 'Nama lengkap balita wajib diisi.',
            'name.string' => 'Nama balita harus berupa teks.',
            'name.max' => 'Nama balita maksimal 255 karakter.',
            'birth_date.required' => 'Tanggal lahir balita wajib diisi.',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'birth_date.date_format' => 'Format tanggal lahir harus YYYY-MM-DD.',
            'birth_date.before_or_equal' => 'Tanggal lahir tidak boleh berada di masa depan.',
            'jenis_kelamin.required' => 'Jenis kelamin balita wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin hanya boleh bernilai "L" (Laki-laki) atau "P" (Perempuan).',
        ];
    }

    /**
     * Custom attribute names for validation.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'orang_tua_id' => 'Orang Tua',
            'name' => 'Nama Balita',
            'birth_date' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
        ];
    }
}

