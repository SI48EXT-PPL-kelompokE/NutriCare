<?php

namespace App\Http\Requests;

use App\Models\OrangTua;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrangTuaRequest extends FormRequest
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
        $orangTuaRoute = $this->route('orang_tua');
        $orangTuaId = $orangTuaRoute instanceof OrangTua ? $orangTuaRoute->id : $orangTuaRoute;

        $userId = null;
        if ($orangTuaRoute instanceof OrangTua) {
            $userId = $orangTuaRoute->user_id;
        } elseif ($orangTuaId) {
            $userId = OrangTua::where('id', $orangTuaId)->value('user_id');
        }

        $rules = [
            // Data Utama Orang Tua
            'nik' => [
                'required',
                'numeric',
                'digits:16',
                Rule::unique('orang_tua', 'nik')->ignore($orangTuaId),
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
            'create_account' => [
                'nullable',
                'boolean',
            ],
        ];

        // Validasi field akun User sesuai kondisi keterhubungan akun saat ini
        if ($userId) {
            // Sudah terhubung ke akun: field bersifat opsional untuk diperbarui
            $rules['username'] = [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ];
            $rules['email'] = [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ];
            $rules['password'] = [
                'nullable',
                'string',
                'min:8',
            ];
        } else {
            // Belum punya akun: validasi jika request memilih membuat akun (create_account = true)
            $rules['username'] = [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'min:3',
                'max:50',
                'alpha_dash',
                'unique:users,username',
            ];
            $rules['email'] = [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ];
            $rules['password'] = [
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'nullable',
                'string',
                'min:8',
            ];
        }

        return $rules;
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
            'nik.unique' => 'NIK sudah digunakan oleh orang tua lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
            'kontak.required' => 'Nomor kontak/HP wajib diisi.',
            'kontak.regex' => 'Format nomor kontak tidak valid.',
            'pregnancy_status.required' => 'Status kehamilan wajib dipilih.',
            'pregnancy_status.in' => 'Status kehamilan hanya boleh "hamil" atau "tidak_hamil".',
            'username.required_if' => 'Username wajib diisi jika memilih membuat akun.',
            'username.unique' => 'Username ini sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).',
            'email.required_if' => 'Email wajib diisi jika memilih membuat akun.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required_if' => 'Password wajib diisi jika memilih membuat akun.',
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

