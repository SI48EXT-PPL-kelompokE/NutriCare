<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PengukuranKmsRequest extends FormRequest
{
    /**
     * Tentukan apakah user terautentikasi boleh membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Sudah dijaga oleh middleware/policy
    }

    /**
     * Normalisasi input sebelum validasi
     */
    protected function prepareForValidation(): void
    {
        // Ubah koma desimal menjadi titik dan trim spasi
        $fields = ['berat_badan', 'tinggi_badan', 'lingkar_lengan_atas'];
        
        foreach ($fields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $this->merge([
                    $field => str_replace(',', '.', trim($this->input($field)))
                ]);
            }
        }
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        $balitaId = $this->input('balita_id');
        
        return [
            'balita_id' => [
                'required',
                'exists:balita,id',
            ],
            'date' => [
                'required',
                'date',
                'before_or_equal:today',
                // Validasi tanggal tidak boleh sebelum tanggal lahir balita
                function ($attribute, $value, $fail) use ($balitaId) {
                    if (!$balitaId) return;
                    
                    $balita = \App\Models\Balita::find($balitaId);
                    if (!$balita) return;
                    
                    $dateUkur = Carbon::parse($value);
                    if ($dateUkur->isBefore($balita->birth_date)) {
                        $fail('Tanggal ukur tidak boleh sebelum tanggal lahir balita.');
                    }
                },
                // Validasi usia tidak boleh lebih dari 60 bulan
                function ($attribute, $value, $fail) use ($balitaId) {
                    if (!$balitaId) return;
                    
                    $balita = \App\Models\Balita::find($balitaId);
                    if (!$balita) return;
                    
                    $dateUkur = Carbon::parse($value);
                    $usiaBulan = (int) floor(max(0, $balita->birth_date->floatDiffInMonths($dateUkur)));
                    
                    if ($usiaBulan > 60) {
                        $fail('Usia balita saat diukur melebihi 60 bulan.');
                    }
                },
                // Validasi tidak boleh ada pengukuran lain pada tanggal yang sama
                Rule::unique('pengukuran_kms', 'date')
                    ->where(fn ($query) => $query->where('balita_id', $balitaId))
                    ->ignore($this->route('kms')), // Abaikan record yang sedang diedit
            ],
            'berat_badan' => [
                'required',
                'numeric',
                'min:0.5',
                'max:30',
            ],
            'tinggi_badan' => [
                'required',
                'numeric',
                'min:30',
                'max:130',
            ],
            'lingkar_lengan_atas' => [
                'nullable',
                'numeric',
                'min:5',
                'max:30',
            ],
        ];
    }

    /**
     * Label atribut untuk pesan error.
     */
    public function attributes(): array
    {
        return [
            'balita_id' => 'Balita',
            'date' => 'Tanggal ukur',
            'berat_badan' => 'Berat badan',
            'tinggi_badan' => 'Tinggi/panjang badan',
            'lingkar_lengan_atas' => 'Lingkar lengan atas (LILA)',
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'balita_id.required' => 'Balita harus dipilih.',
            'balita_id.exists' => 'Balita yang dipilih tidak valid.',
            'date.required' => 'Tanggal ukur harus diisi.',
            'date.date' => 'Format tanggal ukur tidak valid.',
            'date.before_or_equal' => 'Tanggal ukur tidak boleh di masa depan.',
            'date.unique' => 'Balita ini sudah memiliki pengukuran pada tanggal tersebut.',
            'berat_badan.required' => 'Berat badan harus diisi.',
            'berat_badan.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan.min' => 'Berat badan minimal 0,5 kg.',
            'berat_badan.max' => 'Berat badan maksimal 30 kg.',
            'tinggi_badan.required' => 'Tinggi/panjang badan harus diisi.',
            'tinggi_badan.numeric' => 'Tinggi/panjang badan harus berupa angka.',
            'tinggi_badan.min' => 'Tinggi/panjang badan minimal 30 cm.',
            'tinggi_badan.max' => 'Tinggi/panjang badan maksimal 130 cm.',
            'lingkar_lengan_atas.numeric' => 'Lingkar lengan atas (LILA) harus berupa angka.',
            'lingkar_lengan_atas.min' => 'Lingkar lengan atas (LILA) minimal 5 cm.',
            'lingkar_lengan_atas.max' => 'Lingkar lengan atas (LILA) maksimal 30 cm.',
        ];
    }
}
