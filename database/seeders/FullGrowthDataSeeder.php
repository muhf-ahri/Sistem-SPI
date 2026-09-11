<?php

namespace Database\Seeders;

use App\Models\ActionPlan;
use App\Models\AuditAssignment;
use App\Models\AuditLog;
use App\Models\AuditPlan;
use App\Models\FinalReport;
use App\Models\Finding;
use App\Models\FollowUpEvidence;
use App\Models\Inspection;
use App\Models\InspectionEvidence;
use App\Models\Division;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Seeder data pertumbuhan penuh (development/testing).
 * Membuat 20 rantai Audit lengkap: Audit, Pemeriksaan, Temuan, Tindak Lanjut,
 * Verifikasi, dan Laporan Hasil Audit (LHA), tersebar di berbagai divisi & tahun
 * (2021-2026) agar grafik pertumbuhan dan perbandingan temuan memiliki data.
 * TIDAK menambah data master (divisi, jenis audit, kategori, risiko, user).
 */
class FullGrowthDataSeeder extends Seeder
{
    public function run()
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $auditors = User::whereIn('email', ['auditor1@spi.com', 'auditor2@spi.com', 'auditor3@spi.com'])->get();
        if (!$superAdmin || $auditors->count() < 3) {
            $this->command?->warn('Seeder dibatalkan: seed master & users terlebih dahulu (DatabaseSeeder).');
            return;
        }

        // Bersihkan data [DEMO] lama agar aman dijalankan berulang
        $oldPlanIds = AuditPlan::where('title', 'like', '%[DEMO]%')->pluck('id');
        if ($oldPlanIds->isNotEmpty()) {
            $oldFindingIds = Finding::whereIn('audit_plan_id', $oldPlanIds)->pluck('id');
            $oldActionIds = ActionPlan::whereIn('finding_id', $oldFindingIds)->pluck('id');
            Verification::whereIn('action_plan_id', $oldActionIds)->delete();
            FollowUpEvidence::whereIn('action_plan_id', $oldActionIds)->delete();
            AuditLog::where('entity_type', '!=', 'auth')->whereIn('entity_id', $oldPlanIds)->delete();
            ActionPlan::whereIn('finding_id', $oldFindingIds)->delete();
            InspectionEvidence::whereIn('inspection_id',
                Inspection::whereIn('audit_plan_id', $oldPlanIds)->pluck('id'))->delete();
            Finding::whereIn('audit_plan_id', $oldPlanIds)->delete();
            Inspection::whereIn('audit_plan_id', $oldPlanIds)->delete();
            AuditAssignment::whereIn('audit_plan_id', $oldPlanIds)->delete();
            FinalReport::whereIn('audit_plan_id', $oldPlanIds)->delete();
            AuditPlan::whereIn('id', $oldPlanIds)->delete();
            Storage::disk('public')->deleteDirectory('reports');
        }

        $divisions   = Division::pluck('id', 'code')->toArray();
        $auditTypes  = \App\Models\AuditType::where('is_active', true)->orderBy('id')->get();
        $findingCats = \App\Models\FindingCategory::where('is_active', true)->orderBy('id')->get();
        $riskCats    = \App\Models\RiskCategory::pluck('id', 'level')->toArray();

        $auditor = $auditors->values();
        $typeAt  = fn ($i) => $auditTypes->get($i)->id ?? $auditTypes->first()->id;
        $catAt   = fn ($i) => $findingCats->get($i)->id ?? $findingCats->first()->id;

        $picOf = function ($divisionId) use ($superAdmin) {
            return User::where('division_id', $divisionId)
                ->where('role', 'kepala_divisi')
                ->where('is_active', true)
                ->first() ?? $superAdmin;
        };

        // Katalog temuan per tingkat risiko (judul + rekomendasi), diputar sesuai urutan
        $catalog = [
            'critical' => [
                ['Pencatatan Hasil QC Tidak Dilengkapi', 'Wajibkan paraf supervisor pada setiap form dan audit sampling mingguan.'],
                ['Hak Akses Aplikasi Internal Tidak Terkelola', 'Terapkan hak akses berbasis peran dan audit trail sistem.'],
                ['Pengendalian Kas Tunai Tidak Memadai', 'Pisahkan fungsi pemegang kas, pembukuan, dan otorisasi.'],
                ['Sertifikasi Keamanan Produk Kedaluwarsa', 'Perbarui sertifikasi dan jadwalkan inspeksi eksternal.'],
            ],
            'high' => [
                ['Berkas Karyawan Tanpa Lampiran SK Terbaru', 'Lengkapi SK terbaru dan perbarui indeks berkas personalia.'],
                ['Evaluasi Vendor Tanpa Pengesahan Pejabat', 'Terapkan checklist kelengkapan tanda tangan sebelum arsip.'],
                ['Mutasi Barang Tanpa Berita Acara', 'Lengkapi berita acara serah terima untuk seluruh mutasi barang.'],
                ['Perawatan Mesin Melewati Jadwal', 'Susun jadwal perawatan dan penugasan teknisi yang jelas.'],
            ],
            'medium' => [
                ['Dokumen Pengadaan Belum Terarsip Lengkap', 'Digitalisasi dokumen pengadaan dengan indeks masa simpan.'],
                ['Data Kepegawaian Tidak Mutakhir', 'Sinkronkan data kepegawaian dengan sistem personalia pusat.'],
                ['Stock Opname Gudang Tidak Berkala', 'Tetapkan jadwal stock opname bulanan dan rekonsiliasi.'],
                ['Rekap Penjualan Tidak Direkonsiliasi', 'Lakukan rekonsiliasi penjualan dengan pembukuan berkala.'],
            ],
            'low' => [
                ['Label Gudang Kurang Konsisten', 'Seragamkan standar pelabelan dan penataan rak gudang.'],
                ['Notulen Rapat Tidak Terarsip', 'Arsipkan notulen dan distribusikan daftar tindak lanjut.'],
                ['Formulir Permintaan Kurang Lengkap', 'Lengkapi field wajib pada formulir permintaan internal.'],
                ['Jadwal Pemeliharaan Ruang Kerja Tidak Teratur', 'Buat jadwal pemeliharaan dan penanggung jawab area.'],
            ],
        ];

        $descOf = [
            'critical' => 'Kegagalan pengendalian utama berpotensi menimbulkan kerugian material dan reputasi signifikan.',
            'high'     => 'Pengendalian tidak memadai pada proses penting dan membutuhkan perbaikan segera.',
            'medium'   => 'Penyimpangan prosedur operasional standar pada aktivitas rutin.',
            'low'      => 'Ketidaksesuaian administratif ringan dan perbaikan tata kelola.',
        ];

        $riskOrder = ['low', 'medium', 'high', 'critical'];

        // =========================================================
        // 20 AUDIT (divisi & tahun bervariasi)
        // [kodeDivisi, typeIndex, tahun, judul, deskripsi, [ [risiko, status], ... ]]
        // =========================================================
        $audits = [
            // 2021 — 1 audit
            ['DU',  0, 2021, 'Kepatuhan Pelaporan Keuangan', 'Pemeriksaan kelengkapan pelaporan keuangan tahunan.', [['low', 'closed']]],
            // 2022 — 2 audit
            ['DO',  1, 2022, 'Prosedur Operasional Logistik', 'Evaluasi kepatuhan prosedur operasional logistik.', [['medium', 'closed']]],
            ['SEVP', 2, 2022, 'Keamanan Akses Sistem', 'Review hak akses sistem informasi manajemen.', [['high', 'closed'], ['low', 'closed']]],
            // 2023 — 3 audit
            ['SP',  1, 2023, 'Arsip Dokumen Sekretariat', 'Pemeriksaan arsip dan notulen rapat direksi.', [['medium', 'closed'], ['low', 'closed']]],
            ['SDM', 2, 2023, 'Kelengkapan Berkas Personalia', 'Audit berkas kepegawaian dan kontrak kerja.', [['high', 'closed']]],
            ['ENJ', 0, 2023, 'Kalkulasi Biaya Proyek Enjiniring', 'Review perhitungan biaya dan dokumen pendukung proyek.', [['medium', 'closed'], ['high', 'closed']]],
            // 2024 — 4 audit
            ['AKMR', 1, 2024, 'Pengendalian Kas Kecil', 'Pemeriksaan kas kecil dan rekonsiliasi bank.', [['critical', 'closed'], ['medium', 'closed']]],
            ['PP',   2, 2024, 'Kepatuhan Penjualan & Piutang', 'Audit proses penjualan, retur, dan piutang usaha.', [['medium', 'closed'], ['low', 'closed']]],
            ['REND', 0, 2024, 'Pengendalian Persediaan Gudang', 'Evaluasi stock opname dan pencatatan barang.', [['high', 'closed'], ['medium', 'closed'], ['low', 'closed']]],
            ['MUTU', 1, 2024, 'Sistem Manajemen Mutu & K3LH', 'Audit penerapan standar mutu dan keselamatan kerja.', [['critical', 'closed']]],
            // 2025 — 5 audit
            ['ADA',  2, 2025, 'Prosedur Pengadaan Barang', 'Pemeriksaan dokumen pengadaan dan evaluasi vendor.', [['high', 'closed'], ['medium', 'closed']]],
            ['RKP',  0, 2025, 'Keamanan Aplikasi Internal', 'Audit pengamanan aplikasi dan backup data.', [['critical', 'closed'], ['high', 'closed'], ['low', 'closed']]],
            ['PTR',  1, 2025, 'Produksi Turen & Quality Control', 'Pemeriksaan proses produksi unit Turen.', [['medium', 'closed'], ['high', 'closed']]],
            ['BP',   2, 2025, 'Bisnis Pariwisata & Pengelolaan Venue', 'Audit pendapatan dan pengelolaan fasilitas pariwisata.', [['low', 'closed']]],
            ['SPI',  0, 2025, 'Pengawasan Program Internal SPI', 'Review pelaksanaan program pengawasan internal.', [['medium', 'closed'], ['low', 'closed']]],
            // 2026 — 5 audit
            ['PRO',  1, 2026, 'SOP Produksi & Pencatatan QC', 'Pemeriksaan kepatuhan SOP lini produksi dan QC.', [['critical', 'closed'], ['high', 'closed'], ['medium', 'closed']]],
            ['RKP',  2, 2026, 'Backup Data & Disaster Recovery', 'Audit kebijakan backup dan uji pemulihan data.', [['high', 'closed'], ['medium', 'closed']]],
            ['SDM',  0, 2026, 'Absensi & Pengelolaan Lembur', 'Pemeriksaan rekonsiliasi absensi dan lembur.', [['medium', 'closed'], ['low', 'closed']]],
            ['AKMR', 1, 2026, 'Anggaran & Realisasi Belanja', 'Audit kesesuaian anggaran dengan realisasi belanja.', [['critical', 'closed'], ['medium', 'closed'], ['low', 'closed']]],
            ['ENJ',  2, 2026, 'Laporan Kemajuan Proyek', 'Review laporan kemajuan dan kelayakan proyek enjiniring.', [['high', 'closed'], ['medium', 'closed']]],
        ];

        // =========================================================
        // BANGUN RANTAI DATA
        // =========================================================
        $seqAudit  = [];
        $seqReport = [];
        $seqFinding = [];
        $ci = 0; // counter katalog temuan

        foreach ($audits as $def) {
            [$code, $typeIdx, $year, $title, $desc, $findings] = $def;

            $divisionId    = $divisions[$code] ?? null;
            if (!$divisionId) {
                continue;
            }
            $auditNumber = 'PEN_' . $code . '_' . str_pad(($seqAudit[$code . '-' . $year] ?? 0) + 1, 3, '0', STR_PAD_LEFT) . '_' . $year;
            $seqAudit[$code . '-' . $year] = ($seqAudit[$code . '-' . $year] ?? 0) + 1;

            $monthMax = $year >= (int) now()->format('Y') ? max(2, (int) now()->format('n') - 1) : 9;
            $startDate = Carbon::create($year, random_int(2, $monthMax), random_int(3, 14));
            $endDate   = $startDate->copy()->addDays(9);

            $plan = AuditPlan::create([
                'division_id'   => $divisionId,
                'audit_type_id' => $typeAt($typeIdx),
                'created_by'    => $superAdmin->id,
                'audit_number'  => $auditNumber,
                'title'         => '[DEMO] Audit ' . $title . ' ' . $year,
                'description'   => $desc,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'status'        => 'completed',
                'working_days'  => $startDate->diffInWeekdays($endDate),
                'created_at'    => $startDate->copy()->subDays(7),
                'updated_at'    => $endDate,
            ]);

            AuditAssignment::create([
                'audit_plan_id' => $plan->id,
                'user_id'       => $auditor[$ci % 3]->id,
                'role'          => $ci % 2 === 0 ? 'lead_auditor' : 'auditor',
                'assigned_at'   => $startDate->copy()->subDays(5),
                'created_at'    => $startDate->copy()->subDays(5),
                'updated_at'    => $startDate->copy()->subDays(5),
            ]);

            $pic = $picOf($divisionId);
            $auditorId = $auditor[$ci % 3]->id;

            foreach ($findings as $f) {
                [$risk, $status] = $f;
                $item = $catalog[$risk][$ci % count($catalog[$risk])];
                $ci++;

                $inspDate = $startDate->copy()->addDays(2);
                $inspection = Inspection::create([
                    'audit_plan_id'   => $plan->id,
                    'auditor_id'      => $auditorId,
                    'inspection_date' => $inspDate,
                    'summary'         => 'Pemeriksaan lapangan ' . $title . ' ditemukan ketidaksesuaian pada ' . strtolower($item[0]) . '.',
                    'notes'           => 'Perlu tindak lanjut dari pemilik proses divisi.',
                    'result'          => $risk === 'critical' || $risk === 'high' ? 'non_conformity' : ($risk === 'medium' ? 'needs_improvement' : 'satisfactory'),
                    'created_at'      => $inspDate,
                    'updated_at'      => $inspDate,
                ]);

                $findingNumber = 'FND_' . $code . '_' . str_pad(($seqFinding[$code . '-' . $year] ?? 0) + 1, 3, '0', STR_PAD_LEFT) . '_' . $year;
                $seqFinding[$code . '-' . $year] = ($seqFinding[$code . '-' . $year] ?? 0) + 1;

                $deadline = $inspDate->copy()->addDays(21);
                $finding = Finding::create([
                    'audit_plan_id'       => $plan->id,
                    'inspection_id'       => $inspection->id,
                    'category_id'         => $catAt($ci),
                    'risk_category_id'    => $riskCats[$risk] ?? null,
                    'created_by'          => $auditorId,
                    'finding_number'      => $findingNumber,
                    'title'               => '[DEMO] ' . $item[0],
                    'description'         => 'Hasil pemeriksaan menemukan: ' . $descOf[$risk] . ' pada area ' . strtolower($item[0]) . '.',
                    'recommendation'      => $item[1],
                    'risk_description'    => $descOf[$risk],
                    'criteria_explanation'=> 'Dibandingkan kriteria/prosedur yang berlaku pada divisi ' . $code . '.',
                    'deadline'            => $deadline,
                    'status'              => $status,
                    'created_at'          => $inspDate->copy()->addHours(4),
                    'updated_at'          => $status === 'closed' ? $deadline->copy()->subDays(3) : $inspDate,
                ]);

                if ($status === 'closed') {
                    // Action plan tervalidasi penuh
                    $ap = ActionPlan::create([
                        'finding_id'   => $finding->id,
                        'pic_user_id'  => $pic->id,
                        'title'        => 'Perbaikan: ' . $item[0],
                        'action'       => 'Rencana perbaikan telah disusun dan dilaksanakan oleh pemilik proses, termasuk pengendalian lanjutan.',
                        'target_date'  => $deadline,
                        'response'     => 'Perbaikan selesai dan didokumentasikan (berita acara internal).',
                        'status'       => 'verified',
                        'verification_round' => 1,
                        'created_at'   => $inspDate->copy()->addDays(2),
                        'updated_at'   => $deadline->copy()->subDays(2),
                    ]);
                    Verification::create([
                        'action_plan_id' => $ap->id,
                        'verifier_id'    => $auditor[($ci + 1) % 3]->id,
                        'result'         => 'approved',
                        'notes'          => 'Implementasi sudah terbukti di lapangan; temuan ditutup.',
                        'verified_at'    => $deadline->copy()->subDays(1),
                        'created_at'     => $deadline->copy()->subDays(1),
                        'updated_at'     => $deadline->copy()->subDays(1),
                    ]);
                } else {
                    // Temuan belum selesai: action plan masih berjalan / menunggu
                    $apStatus = $status === 'open' ? 'in_progress' : ($status === 'in_progress' ? 'submitted' : 'pending');
                    $ap = ActionPlan::create([
                        'finding_id'   => $finding->id,
                        'pic_user_id'  => $pic->id,
                        'title'        => 'Perbaikan: ' . $item[0],
                        'action'       => 'Rencana perbaikan sedang disusun oleh pemilik proses divisi.',
                        'target_date'  => $deadline,
                        'response'     => $status === 'waiting_verification' ? 'Perbaikan selesai, menunggu verifikasi SPI.' : null,
                        'status'       => $apStatus,
                        'verification_round' => 1,
                        'created_at'   => $inspDate->copy()->addDays(2),
                        'updated_at'   => $inspDate->copy()->addDays(2),
                    ]);
                }
            }

            // Laporan Hasil Audit untuk audit selesai
            $reportNumber = 'LHA_' . $code . '_' . str_pad(($seqReport[$code . '-' . $year] ?? 0) + 1, 3, '0', STR_PAD_LEFT) . '_' . $year;
            $seqReport[$code . '-' . $year] = ($seqReport[$code . '-' . $year] ?? 0) + 1;

            $reportPath = 'reports/' . $plan->id . '/lha-' . $year . '.txt';
            Storage::disk('public')->put($reportPath, "LAPORAN HASIL AUDIT (DATA DEMO/TESTING)\n\n" . $auditNumber . "\n" . $year . "\n" . $title);

            FinalReport::create([
                'audit_plan_id' => $plan->id,
                'report_number' => $reportNumber,
                'title'         => $reportNumber,
                'file_path'     => $reportPath,
                'file_name'     => 'laporan-hasil-audit.txt',
                'file_type'     => 'txt',
                'file_size'     => 1024,
                'description'   => 'Laporan hasil audit ' . $title . ' tahun ' . $year . ' (data demo).',
                'created_by'    => $superAdmin->id,
                'created_at'    => $endDate->copy()->addDays(2),
                'updated_at'    => $endDate->copy()->addDays(2),
            ]);

            AuditLog::create([
                'user_id'     => $superAdmin->id,
                'action'      => 'create',
                'entity_type' => 'audit_plan',
                'entity_id'   => $plan->id,
                'old_values'  => null,
                'new_values'  => json_encode(['demo' => true]),
                'created_at'  => $plan->created_at,
            ]);
        }

        $this->command?->info('FullGrowthDataSeeder selesai: 20 audit lengkap (pemeriksaan, temuan, tindak lanjut, laporan LHA) lintas divisi & tahun 2021-2026.');
    }
}