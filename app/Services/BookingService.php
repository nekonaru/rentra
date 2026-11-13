<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Exceptions\SlotBentrokException;
use App\Models\Booking;
use App\Models\Rental;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * Create a booking inside a transaction with a row lock on the unit, so
     * concurrent bookings for the same unit run sequentially.
     *
     * @param  array{mulai: string, selesai: string, nama_penyewa: string, wa: string}  $data
     */
    public function buat(Rental $rental, Unit $unit, array $data): Booking
    {
        return DB::transaction(function () use ($rental, $unit, $data) {
            // Lock the unit row. Any concurrent booking for this unit waits here.
            $locked = Unit::whereKey($unit->id)->lockForUpdate()->firstOrFail();

            $mulai = Carbon::parse($data['mulai']);
            $selesai = Carbon::parse($data['selesai']);

            if (! $this->availability->rentangTersedia($locked, $mulai, $selesai)) {
                throw new SlotBentrokException('Jadwal ini baru saja diambil, pilih waktu lain.');
            }

            $priceRule = $locked->priceRule;
            if ($priceRule === null) {
                throw new SlotBentrokException('Tarif unit belum diatur.');
            }

            $breakdown = $this->priceCalculator->hitung(
                (float) $priceRule->tarif_per_hari,
                (float) $priceRule->tarif_per_jam,
                $priceRule->minimal_sewa_jam,
                $priceRule->toleransi_menit,
                $mulai,
                $selesai,
                $rental->persen_dp,
            );

            return Booking::create([
                'kode' => $this->kodeUnik(),
                'unit_id' => $locked->id,
                'rental_id' => $rental->id,
                'nama_penyewa' => $data['nama_penyewa'],
                'wa' => $data['wa'],
                'mulai' => $mulai,
                'selesai' => $selesai,
                'total' => $breakdown['total'],
                'dp_nominal' => $breakdown['dp_nominal'],
                'status' => BookingStatus::Ditahan,
                'hold_expires_at' => now()->addMinutes($rental->durasi_hold_menit),
            ]);
        });
    }

    public function sweepExpired(): int
    {
        $expired = Booking::query()
            ->where('status', BookingStatus::Ditahan->value)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', now())
            ->update(['status' => BookingStatus::Kedaluwarsa->value]);

        $terlambat = Booking::query()
            ->where('status', BookingStatus::SedangDisewa->value)
            ->where('selesai', '<=', now())
            ->update(['status' => BookingStatus::Terlambat->value]);

        return $expired + $terlambat;
    }

    private function kodeUnik(): string
    {
        do {
            $kode = strtoupper(Str::random(10));
        } while (Booking::where('kode', $kode)->exists());

        return $kode;
    }
}
