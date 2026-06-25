<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class RingkasanSheet implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected float  $totalPendapatan,
        protected int    $totalTransaksi,
        protected float  $rataRata,
        protected string $dari,
        protected string $sampai,
    ) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function collection(): Collection
    {
        return collect([
            ['Periode Dari',       $this->dari],
            ['Periode Sampai',     $this->sampai],
            ['Total Pendapatan',   $this->totalPendapatan],
            ['Jumlah Transaksi',   $this->totalTransaksi],
            ['Rata-rata / Transaksi', $this->rataRata],
        ]);
    }
}
