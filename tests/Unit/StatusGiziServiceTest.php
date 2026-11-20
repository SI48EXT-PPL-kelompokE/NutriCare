<?php

namespace Tests\Unit;

use App\Enums\StatusGizi;
use App\Services\StatusGiziService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StatusGiziServiceTest extends TestCase
{
    #[DataProvider('bbuProvider')]
    public function test_batas_bbu(float $z, string $expected): void
    {
        $this->assertSame($expected, StatusGiziService::kategoriBbU($z));
    }

    public static function bbuProvider(): array
    {
        return [
            '-3.01' => [-3.01, 'Berat Badan Sangat Kurang'],
            '-3.00' => [-3.00, 'Berat Badan Kurang'],
            '-2.99' => [-2.99, 'Berat Badan Kurang'],
            '-2.01' => [-2.01, 'Berat Badan Kurang'],
            '-2.00' => [-2.00, 'Berat Badan Normal'],
            '-1.99' => [-1.99, 'Berat Badan Normal'],
            '+0.99' => [0.99, 'Berat Badan Normal'],
            '+1.00' => [1.00, 'Berat Badan Normal'],
            '+1.01' => [1.01, 'Risiko Berat Badan Lebih'],
            '+1.99' => [1.99, 'Risiko Berat Badan Lebih'],
            '+2.00' => [2.00, 'Risiko Berat Badan Lebih'],
            '+2.01' => [2.01, 'Risiko Berat Badan Lebih'],
            '+2.99' => [2.99, 'Risiko Berat Badan Lebih'],
            '+3.00' => [3.00, 'Risiko Berat Badan Lebih'],
            '+3.01' => [3.01, 'Risiko Berat Badan Lebih'],
        ];
    }

    #[DataProvider('tbuProvider')]
    public function test_batas_tbu(float $z, string $expected): void
    {
        $this->assertSame($expected, StatusGiziService::kategoriTbU($z));
    }

    public static function tbuProvider(): array
    {
        return [
            '-3.01' => [-3.01, 'Sangat Pendek'], '-3.00' => [-3.00, 'Pendek'],
            '-2.99' => [-2.99, 'Pendek'], '-2.01' => [-2.01, 'Pendek'],
            '-2.00' => [-2.00, 'Normal'], '-1.99' => [-1.99, 'Normal'],
            '+0.99' => [0.99, 'Normal'], '+1.00' => [1.00, 'Normal'],
            '+1.01' => [1.01, 'Normal'], '+1.99' => [1.99, 'Normal'],
            '+2.00' => [2.00, 'Normal'], '+2.01' => [2.01, 'Normal'],
            '+2.99' => [2.99, 'Normal'], '+3.00' => [3.00, 'Normal'],
            '+3.01' => [3.01, 'Tinggi'],
        ];
    }

    #[DataProvider('bbtbProvider')]
    public function test_batas_bbtb(float $z, string $expected): void
    {
        $this->assertSame($expected, StatusGiziService::kategoriBbTb($z));
    }

    public static function bbtbProvider(): array
    {
        return [
            '-3.01' => [-3.01, 'Gizi Buruk'], '-3.00' => [-3.00, 'Gizi Kurang'],
            '-2.99' => [-2.99, 'Gizi Kurang'], '-2.01' => [-2.01, 'Gizi Kurang'],
            '-2.00' => [-2.00, 'Gizi Baik'], '-1.99' => [-1.99, 'Gizi Baik'],
            '+0.99' => [0.99, 'Gizi Baik'], '+1.00' => [1.00, 'Gizi Baik'],
            '+1.01' => [1.01, 'Berisiko Gizi Lebih'], '+1.99' => [1.99, 'Berisiko Gizi Lebih'],
            '+2.00' => [2.00, 'Berisiko Gizi Lebih'], '+2.01' => [2.01, 'Gizi Lebih'],
            '+2.99' => [2.99, 'Gizi Lebih'], '+3.00' => [3.00, 'Gizi Lebih'],
            '+3.01' => [3.01, 'Obesitas'],
        ];
    }

    public function test_prioritas_dan_penanda(): void
    {
        $hasil = StatusGiziService::tentukan(-2.5, -2.5, -2.5);
        $this->assertSame(StatusGizi::GIZI_KURANG, $hasil['status']);
        $this->assertTrue($hasil['stunting']);
        $this->assertTrue($hasil['wasting']);
        $this->assertTrue($hasil['underweight']);

        $this->assertSame(StatusGizi::STUNTING, StatusGiziService::tentukan(-2.5, -2.5, 0.0)['status']);
        $this->assertSame(StatusGizi::NORMAL, StatusGiziService::tentukan(0.0, 0.0, 0.0)['status']);
    }

    public function test_semua_null_dan_penilaian_sebagian(): void
    {
        $semuaNull = StatusGiziService::tentukan(null, null, null);
        $this->assertNull($semuaNull['status']);
        $this->assertFalse($semuaNull['lengkap']);

        $sebagian = StatusGiziService::tentukan(0.0, null, null);
        $this->assertSame(StatusGizi::NORMAL, $sebagian['status']);
        $this->assertFalse($sebagian['lengkap']);
    }

    public function test_perlu_dicek(): void
    {
        $this->assertTrue(StatusGiziService::tentukan(-6.01, null, null)['perlu_dicek']);
        $this->assertTrue(StatusGiziService::tentukan(null, 6.01, null)['perlu_dicek']);
        $this->assertTrue(StatusGiziService::tentukan(null, null, -5.01)['perlu_dicek']);
        $this->assertFalse(StatusGiziService::tentukan(-6.0, 6.0, 5.0)['perlu_dicek']);
    }
}
