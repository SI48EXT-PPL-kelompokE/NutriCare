<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrangTuaRequest extends FormRequest
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
            // Data Utama Orang Tua
            'nik' => [
                'required',
                'numeric',
                'digits:16',
                'unique:orang_tua,nik',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'alamat' => [
                'required',
                'string',
                'max:1000',
            ],
            'kontak' => [
                'required',
                'string',
                'max:20',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
            ],
            'pregnancy_status' => [
                'required',
                'string',
                Rule::in(['hamil', 'tidak_hamil']),
            ],

            // Flag pembuatan akun User
            'create_account' => [
                'nullable',
                'boolean',
            ],

            // Field User (Wajib jika create_account = true)
            'username' => [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                'unique:users,username',
            ],
            'email' => [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'min:8',
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
            'nik.required' => 'NIK wajib diisi.',
            'nik.numeric' => 'NIK harus berupa deretan angka.',
            'nik.digits' => 'NIK harus tepat berjumlah 16 digit.',
            'nik.unique' => 'NIK sudah terdaftar dalam sistem.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'kontak.required' => 'Nomor kontak/HP wajib diisi.',
            'kontak.regex' => 'Format nomor kontak tidak valid.',
            'pregnancy_status.required' => 'Status kehamilan wajib dipilih.',
            'pregnancy_status.in' => 'Status kehamilan hanya boleh "hamil" atau "tidak_hamil".',
            'username.required_if' => 'Username wajib diisi apabila ingin membuat akun pengguna.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.required_if' => 'Email wajib diisi apabila ingin membuat akun pengguna.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar dalam sistem.',
            'password.required_if' => 'Password wajib diisi apabila ingin membuat akun pengguna.',
            'password.min' => 'Password minimal harus terdiri dari 8 karakter.',
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
            'nik' => 'NIK',
            'name' => 'Nama Lengkap',
            'alamat' => 'Alamat',
            'kontak' => 'Nomor Kontak',
            'pregnancy_status' => 'Status Kehamilan',
            'create_account' => 'Buat Akun',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
        ];
    }
}

