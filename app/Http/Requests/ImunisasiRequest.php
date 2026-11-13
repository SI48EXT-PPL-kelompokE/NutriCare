<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Balita;

class ImunisasiRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'balita_id' => [
                'required',
                'integer',
                Rule::exists('balita', 'id'),
            ],
            'jenis_vaksin' => [
                'required',
                'string',
                Rule::in(config('imunisasi.jenis_vaksin')),
            ],
            'tanggal' => [
                'required',
                'date',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    $balita = Balita::find($this->input('balita_id'));
                    if ($balita && $value < $balita->birth_date->format('Y-m-d')) {
                        $fail('Tanggal pemberian imunisasi tidak boleh sebelum tanggal lahir balita.');
                    }
                },
            ],
            'no_batch' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'balita_id.required' => 'Balita harus dipilih.',
            'balita_id.exists' => 'Balita yang dipilih tidak valid.',
            'jenis_vaksin.required' => 'Jenis vaksin harus dipilih.',
            'jenis_vaksin.in' => 'Jenis vaksin yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal pemberian harus diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal pemberian tidak boleh di masa depan.',
            'no_batch.max' => 'Nomor batch maksimal 50 karakter.',
        ];
    }
}
