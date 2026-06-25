<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesReportExport implements WithMultipleSheets
{
    public function __construct(
        protected float $totalPendapatan,
        protected int   $totalTransaksi,
        protected float $rataRata,
        protected string $dari,
        protected string $sampai,
        protected $perHari,
        protected $topProduk,
    ) {}

    public function sheets(): array
    {
        return [
            new Sheets\RingkasanSheet(
                $this->totalPendapatan,
                $this->totalTransaksi,
                $this->rataRata,
                $this->dari,
                $this->sampai,
            ),
            new Sheets\PerHariSheet($this->perHari),
            new Sheets\TopProdukSheet($this->topProduk),
        ];
    }
}
