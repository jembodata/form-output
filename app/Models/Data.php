<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class data extends Model
{
    use HasFactory;

    // Pastikan property $guarded atau $fillable Anda sudah diatur
    protected $guarded = ['id'];

    protected static function booted()
    {
        static::saving(function ($data) {
            // Helper function untuk menghitung persentase & mencegah error pembagian nol
            $calculatePercentage = function ($numerator, $denominator) {
                if (empty($denominator) || $denominator == 0) {
                    return 0;
                }
                return ($numerator / $denominator) * 100;
            };

            // Kalkulasi % Output Produksi vs Planning
            $data->pct_output_produksi_vs_planning_mtr = $calculatePercentage($data->output_produksi_open_mtr, $data->planning_mtr);
            $data->pct_output_produksi_vs_planning_ton_kabel = $calculatePercentage($data->output_produksi_open_kabel, $data->planning_ton_kabel);
            $data->pct_output_produksi_vs_planning_rp = $calculatePercentage($data->output_produksi_open, $data->planning);

            // Kalkulasi % Output QC vs Output Produksi
            $data->pct_output_qc_vs_output_produksi_mtr = $calculatePercentage($data->output_qc_transfer_mtr, $data->output_produksi_open_mtr);
            $data->pct_output_qc_vs_output_produksi_ton_kabel = $calculatePercentage($data->output_produksi_open_kabel, $data->output_qc_transfer_ton_kabel);
            $data->pct_output_qc_vs_output_produksi_rp = $calculatePercentage($data->output_qc_transfer, $data->output_produksi_open);
        });
    }
}
