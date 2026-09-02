<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use App\Models\AuditPlan;
use App\Models\AuditAssignment;
use App\Models\Inspection;
use App\Models\Finding;
use App\Models\ActionPlan;
use App\Models\Verification;
use App\Models\FollowUpEvidence;
use App\Models\InspectionEvidence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // =========================================================
        // DATA DUMMY DEVELOPMENT/TESTING ONLY (sesuai MASTERP.md)
        // =========================================================

        // Bersihkan data demo lama agar seeder bisa dijalankan berulang
        $oldPlanIds = AuditPlan::where('title', 'like', '%[DEMO]%')->pluck('id');
        if ($oldPlanIds->isNotEmpty()) {
            $oldFindingIds = Finding::whereIn('audit_plan_id', $oldPlanIds)->pluck('id');
            $oldActionIds = ActionPlan::whereIn('finding_id', $oldFindingIds)->pluck('id');

            Verification::whereIn('action_plan_id', $oldActionIds)->delete();
            FollowUpEvidence::whereIn('action_plan_id', $oldActionIds)->delete();
            ActionPlan::whereIn('finding_id', $oldFindingIds)->delete();
            InspectionEvidence::whereIn('inspection_id',
                Inspection::whereIn('audit_plan_id', $oldPlanIds)->pluck('id')
            )->delete();
            Finding::whereIn('audit_plan_id', $oldPlanIds)->delete();
            Inspection::whereIn('audit_plan_id', $oldPlanIds)->delete();
            AuditAssignment::whereIn('audit_plan_id', $oldPlanIds)->delete();
            \App\Models\AuditLog::where(function ($q) {
                $q->where('entity_type', 'audit_plan')->orWhere('entity_type', 'inspection')
                  ->orWhere('entity_type', 'finding')->orWhere('entity_type', 'action_plan');
            })->delete();
            AuditPlan::whereIn('id', $oldPlanIds)->delete();

            Storage::disk('public')->deleteDirectory('evidences');
        }

        $superAdmin  = User::where('role', 'super_admin')->first();
        $auditor1    = User::where('email', 'auditor1@spi.com')->first();
        $auditor2    = User::where('email', 'auditor2@spi.com')->first();
        $auditor3    = User::where('email', 'auditor3@spi.com')->first();

        $divisions   = Division::pluck('id', 'code')->toArray();
        $auditTypes  = \App\Models\AuditType::where('is_active', true)->get();
        $findingCats = \App\Models\FindingCategory::where('is_active', true)->get();
        $riskCats    = \App\Models\RiskCategory::pluck('id', 'level')->toArray();

        $typeOf = fn($i) => $auditTypes->get($i)->id ?? $auditTypes->first()->id;
        $catOf  = fn($i) => $findingCats->get($i)->id ?? $findingCats->first()->id;

        $this->command?->info('Membuat data demo Audit...');

        // =========================================================
        // 1. AUDIT PLANS (semua status workflow)
        // =========================================================
        $planSelesai = AuditPlan::create([
            'division_id'   => $divisions['PRO'],
            'audit_type_id' => $typeOf(0),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_PRO_001_2026',
            'title'         => '[DEMO] Audit SOP Produksi Triwulan I',
            'start_date'    => now()->subDays(30),
            'end_date'      => now()->subDays(20),
            'status'        => 'completed',
            'description'   => 'Pemeriksaan kepatuhan SOP produksi dan dokumentasi lini produksi.',
        ]);

        $planBerjalan = AuditPlan::create([
            'division_id'   => $divisions['AKMR'],
            'audit_type_id' => $typeOf(1),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_AKMR_001_2026',
            'title'         => '[DEMO] Audit Pengelolaan Kas Kecil',
            'start_date'    => now()->subDays(5),
            'end_date'      => now()->addDays(7),
            'status'        => 'in_progress',
            'description'   => 'Pemeriksaan langsung proses kas kecil, dokumen pertanggungjawaban, dan rekonsiliasi.',
        ]);

        $planTerjadwal = AuditPlan::create([
            'division_id'   => $divisions['RKP'],
            'audit_type_id' => $typeOf(2),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_RKP_001_2026',
            'title'         => '[DEMO] Audit Keamanan Aplikasi Internal',
            'start_date'    => now()->addDays(10),
            'end_date'      => now()->addDays(15),
            'status'        => 'scheduled',
            'description'   => 'Review hak akses aplikasi internal dan backup data.',
        ]);

        $planDraft = AuditPlan::create([
            'division_id'   => $divisions['SDM'],
            'audit_type_id' => $typeOf(3),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_SDM_001_2026',
            'title'         => '[DEMO] Audit Absensi & Lembur',
            'start_date'    => now()->addDays(25),
            'end_date'      => now()->addDays(30),
            'status'        => 'draft',
            'description'   => 'Rencana awal pemeriksaan absensi manual vs sistem.',
        ]);

        // =========================================================
        // 2. PENUGASAN AUDITOR
        // =========================================================
        $assignments = [
            [$planSelesai->id, $auditor1->id, 'lead_auditor'],
            [$planSelesai->id, $auditor2->id, 'auditor'],
            [$planBerjalan->id, $auditor1->id, 'lead_auditor'],
            [$planBerjalan->id, $auditor3->id, 'auditor'],
            [$planTerjadwal->id, $auditor2->id, 'lead_auditor'],
        ];
        foreach ($assignments as [$planId, $userId, $role]) {
            AuditAssignment::create([
                'audit_plan_id' => $planId,
                'user_id'       => $userId,
                'role'          => $role,
                'assigned_at'   => now(),
            ]);
        }

        // =========================================================
        // 3. PEMERIKSAAN LANGSUNG
        // =========================================================
        $inspSelesai = Inspection::create([
            'audit_plan_id'    => $planSelesai->id,
            'auditor_id'       => $auditor1->id,
            'inspection_date'  => now()->subDays(28),
            'summary'          => 'SOP produksi umumnya dipatuhi. Ditemukan 1 ketidaksesuaian pada pencatatan hasil QC shift malam.',
            'notes'            => 'Perlu wawancara lanjutan dengan supervisor shift malam.',
            'result'           => 'needs_improvement',
        ]);

        $inspSelesai2 = Inspection::create([
            'audit_plan_id'    => $planSelesai->id,
            'auditor_id'       => $auditor2->id,
            'inspection_date'  => now()->subDays(22),
            'summary'          => 'Verifikasi ulang pencatatan QC sudah diperbaiki dengan form baru yang diinisialisasi supervisor.',
            'notes'            => null,
            'result'           => 'satisfactory',
        ]);

        $inspBerjalan = Inspection::create([
            'audit_plan_id'    => $planBerjalan->id,
            'auditor_id'       => $auditor1->id,
            'inspection_date'  => now()->subDay(),
            'summary'          => 'Ditemukan selisih kas kecil Rp 150.000 dan 3 bon pembelian tanpa tanda terima resmi.',
            'notes'            => 'Bukti fisik bon difoto dan dilampirkan sebagai evidence.',
            'result'           => 'non_conformity',
        ]);

        // Bukti pemeriksaan (file dummy asli agar link unduh berfungsi)
        Storage::disk('public')->put('evidences/inspections/demo-bon-kas-kecil.txt',
            "DATA DEMO/TESTING\n\nFoto scan bon pembelian tanpa tanda terima (3 lembar).\nPEN_AKMR_001_2026 / " . now());
        InspectionEvidence::create([
            'inspection_id' => $inspBerjalan->id,
            'uploaded_by'   => $auditor1->id,
            'file_name'     => 'scan-bon-pembelian.txt',
            'file_path'     => 'evidences/inspections/demo-bon-kas-kecil.txt',
            'file_type'     => 'txt',
            'file_size'     => strlen("demo"),
        ]);

        // =========================================================
        // 4. TEMUAN (semua status workflow)
        // =========================================================

        // Temuan CLOSED (alur penuh sampai verifikasi disetujui)
        $findingClosed = Finding::create([
            'audit_plan_id'     => $planSelesai->id,
            'inspection_id'     => $inspSelesai->id,
            'category_id'       => $catOf(0),
            'risk_category_id'  => $riskCats['critical'] ?? null,
            'created_by'        => $auditor1->id,
            'finding_number'    => 'FND_PRO_001_2026',
            'title'             => '[DEMO] Pencatatan Hasil QC Shift Malam Tidak Dilengkapi',
            'description'       => 'Hasil quality control shift malam tidak dicatat selama 5 hari berturut-turut sehingga traceability produk terganggu.',
            'recommendation'    => 'Wajibkan inisialisasi paraf supervisor di form QC setiap pergantian shift dan audit sampling mingguan.',
            'risk_description'  => 'Traceability produk terganggu berpotensi menyebar produk cacat ke pelanggan.',
            'criteria_explanation' => 'Kurang sesuai dengan SOP QC yang mewajibkan pencatatan hasil setiap shift.',
            'deadline'          => now()->addDays(20),
            'status'            => 'closed',
        ]);

        // Temuan WAITING_VERIFICATION
        $findingWaiting = Finding::create([
            'audit_plan_id'     => $planSelesai->id,
            'inspection_id'     => $inspSelesai->id,
            'category_id'       => $catOf(1),
            'risk_category_id'  => $riskCats['high'] ?? null,
            'created_by'        => $auditor2->id,
            'finding_number'    => 'FND_PRO_002_2026',
            'title'             => '[DEMO] APAR Kadaluarsa Belum Direplace',
            'description'       => '2 unit APAR di area gudang melewati tanggal expired pemeriksaan rutin.',
            'recommendation'    => 'Replace APAR kadaluarsa dan update jadwal refills.',
            'risk_description'  => 'APAR kadaluarsa berisiko gagal berfungsi saat kebakaran.',
            'criteria_explanation' => 'Menyimpang dari ketentuan jadwal inspeksi APAR berkala.',
            'deadline'          => now()->addDays(14),
            'status'            => 'waiting_verification',
        ]);

        // Temuan IN_PROGRESS
        $findingProgress = Finding::create([
            'audit_plan_id'     => $planBerjalan->id,
            'inspection_id'     => $inspBerjalan->id,
            'category_id'       => $catOf(2),
            'risk_category_id'  => $riskCats['medium'] ?? null,
            'created_by'        => $auditor1->id,
            'finding_number'    => 'FND_AKMR_001_2026',
            'title'             => '[DEMO] Bon Pembelian Tanpa Tanda Terima Resmi',
            'description'       => 'Terdapat 3 bon pembelian operasional harian tanpa tanda terima dan persetujuan atasan langsung.',
            'recommendation'    => 'Terapkan form pengajuan kas kecil dengan approval dua tingkat sebelum pembayaran.',
            'risk_description'  => 'Risiko penyalahgunaan dana kas kecil tanpa pengendalian.',
            'criteria_explanation' => 'Tidak sesuai prosedur pengajuan kas kecil yang berlaku.',
            'deadline'          => now()->addDays(10),
            'status'            => 'in_progress',
        ]);

        // Temuan OPEN (belum ada action plan)
        $findingOpen = Finding::create([
            'audit_plan_id'     => $planBerjalan->id,
            'inspection_id'     => $inspBerjalan->id,
            'category_id'       => $catOf(3),
            'risk_category_id'  => $riskCats['low'] ?? null,
            'created_by'        => $auditor3->id,
            'finding_number'    => 'FND_AKMR_002_2026',
            'title'             => '[DEMO] Selisih Saldo Kas Kecil Rp 150.000',
            'description'       => 'Selisih antara saldo fisik kas kecil dengan pembukuan sebesar Rp 150.000 yang belum dapat dijelaskan.',
'recommendation'    => 'Lakukan stock opname kas mendadak dan identifikasi penyebab selisih.',
            'risk_description'  => 'Selisih kas menandakan potensi kesalahan pencatatan atau kehilangan.',
            'criteria_explanation' => 'Tidak ditemukan rekonsiliasi kas kecil yang memadai.',
            'deadline'          => now()->subDays(2),
            'status'            => 'open',
        ]);

        // Temuan REJECTED (verifikasi ditolak, kembali ke perbaikan)
        $findingRejected = Finding::create([
            'audit_plan_id'     => $planSelesai->id,
            'inspection_id'     => $inspSelesai->id,
            'category_id'       => $catOf(4),
            'risk_category_id'  => $riskCats['high'] ?? null,
            'created_by'        => $auditor1->id,
            'finding_number'    => 'FND_PRO_003_2026',
            'title'             => '[DEMO] Dokumen Mutasi Barang Tidak Lengkap',
            'description'       => 'Form mutasi barang antar gudang tidak melampirkan berita acara serah terima.',
            'recommendation'    => 'Lengkapi berita acara untuk semua mutasi dan sosialisasikan ke kepala gudang.',
            'risk_description'  => 'Tanpa BA serah terima, jejak pertanggungjawaban barang hilang.',
            'criteria_explanation' => 'SOP mutasi barang mengharuskan berita acara serah terima.',
            'deadline'          => now()->addDays(18),
            'status'            => 'rejected',
        ]);

        // =========================================================
        // 5. ACTION PLANS (PIC dari divisi masing-masing)
        // =========================================================
        $picOf = function ($divisionId) use ($superAdmin) {
            return User::where('division_id', $divisionId)
                ->whereIn('role', ['kepala_divisi'])
                ->where('is_active', true)
                ->first() ?? $superAdmin;
        };

        // PIC Produksi
        $picProd = $picOf($divisions['PRO']);
        $picFin  = $picOf($divisions['AKMR']);

        // Action plan untuk temuan CLOSED -> verified/completed
        $apVerified = ActionPlan::create([
            'finding_id'   => $findingClosed->id,
            'pic_user_id'  => $picProd->id,
            'title'        => 'Penerapan Form QC dengan Paraf Supervisor',
            'action'       => 'Form QC baru dengan kolom paraf supervisor telah diterapkan sejak pekan lalu; seluruh supervisor shift telah diberi pengarahan.',
            'target_date'  => now()->addDays(15),
            'response'     => 'Telah selesai 100% dan didokumentasikan dalam memo internal No. MEMO-PRD-042.',
            'status'       => 'verified',
        ]);

        // Action plan untuk temuan WAITING_VERIFICATION -> submitted
        $apSubmitted = ActionPlan::create([
            'finding_id'   => $findingWaiting->id,
            'pic_user_id'  => $picProd->id,
            'title'        => 'Penggantian APAR Kadaluarsa',
            'action'       => 'Kedua unit APAR kadaluarsa telah direplace dengan unit baru dan jadwal refill diperbarui di checklist bulanan.',
            'target_date'  => now()->addDays(10),
            'response'     => 'Menunggu konfirmasi SPI atas foto APAR baru terlampir.',
            'status'       => 'submitted',
        ]);

        // Action plan untuk temuan IN_PROGRESS -> in_progress
        $apProgress = ActionPlan::create([
            'finding_id'   => $findingProgress->id,
            'pic_user_id'  => $picFin->id,
            'title'        => 'SOP Pengajuan Kas Kecil Dua Tingkat',
            'action'       => 'Menyusun draft SOP pengajuan kas kecil dua tingkat; draft sudah dikirim ke manajemen keuangan untuk review.',
            'target_date'  => now()->addDays(8),
            'response'     => null,
            'status'       => 'in_progress',
        ]);

        // Action plan untuk temuan REJECTED -> rejected (dipulihkan lagi)
        $apRejected = ActionPlan::create([
            'finding_id'   => $findingRejected->id,
            'pic_user_id'  => $picProd->id,
            'title'        => 'Kelengkapan BA Serah Terima Mutasi Barang',
            'action'       => 'Mengarsipkan ulang 12 mutasi barang lama dan menambahkan checklist kelengkapan BA serah terima.',
            'target_date'  => now()->addDays(12),
            'response'     => null,
            'status'       => 'rejected',
        ]);

        // =========================================================
        // 6. BUKTI TINDAK LANJUT (file dummy asli)
        // =========================================================
        Storage::disk('public')->put('evidences/follow_ups/demo-form-qc-baru.txt',
            "DATA DEMO/TESTING\n\nScan Form QC revisi + contoh pengisian shift pagi.\n" . now());
        FollowUpEvidence::create([
            'action_plan_id' => $apVerified->id,
            'uploaded_by'    => $picProd->id,
            'file_name'      => 'form-qc-revisi.txt',
            'file_path'      => 'evidences/follow_ups/demo-form-qc-baru.txt',
            'file_type'      => 'txt',
            'file_size'      => 1024,
        ]);

        Storage::disk('public')->put('evidences/follow_ups/demo-apar-baru.txt',
            "DATA DEMO/TESTING\n\nFoto kedua unit APAR baru + label tanggal inspeksi.\n" . now());
        FollowUpEvidence::create([
            'action_plan_id' => $apSubmitted->id,
            'uploaded_by'    => $picProd->id,
            'file_name'      => 'foto-apar-baru.txt',
            'file_path'      => 'evidences/follow_ups/demo-apar-baru.txt',
            'file_type'      => 'txt',
            'file_size'      => 2048,
        ]);

        // =========================================================
        // 7. VERIFIKASI
        // =========================================================
        Verification::create([
            'action_plan_id' => $apVerified->id,
            'verifier_id'    => $auditor1->id,
            'result'         => 'approved',
            'notes'          => 'Implementasi sudah berjalan konsisten selama 2 minggu observasi. Temuan ditutup.',
            'verified_at'    => now()->subDays(3),
        ]);

        Verification::create([
            'action_plan_id' => $apRejected->id,
            'verifier_id'    => $auditor2->id,
            'result'         => 'rejected',
            'notes'          => 'Arsip ulang belum menyertakan 3 mutasi periode Desember. Mohon dilengkapi kembali.',
            'verified_at'    => now()->subDay(),
        ]);

        // =========================================================
        // 8. AUDIT LOG CONTOH AKTIVITAS
        // =========================================================
        $logs = [
            [$superAdmin->id, 'create', 'audit_plan', $planSelesai->id],
            [$superAdmin->id, 'create', 'audit_plan', $planBerjalan->id],
            [$auditor1->id, 'start_inspection', 'audit_plan', $planBerjalan->id],
            [$auditor1->id, 'create', 'inspection', $inspBerjalan->id],
            [$auditor1->id, 'upload_evidence', 'inspection', $inspBerjalan->id],
            [$auditor1->id, 'create', 'finding', $findingOpen->id],
            [$picFin->id, 'create', 'action_plan', $apProgress->id],
            [$picProd->id, 'submit_verification', 'action_plan', $apSubmitted->id],
            [$auditor1->id, 'verify_action_plan', 'action_plan', $apVerified->id],
            [$auditor1->id, 'close_finding', 'finding', $findingClosed->id],
        ];
        foreach ($logs as $i => [$userId, $action, $entityType, $entityId]) {
            \App\Models\AuditLog::create([
                'user_id'     => $userId,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'old_values'  => null,
                'new_values'  => json_encode(['demo' => true, 'note' => 'data development/testing']),
                'created_at'  => now()->subHours(count($logs) - $i),
            ]);
        }

        $this->command?->info('Data demo selesai dibuat: 4 rencana Audit, 3 pemeriksaan, 5 temuan, 4 action plan, 3 bukti, 2 verifikasi.');

        // =========================================================
        // 9. DATA RANDOM TAMBAHAN (di luar master data)
        //    Setiap pemeriksaan dijadikan dasar SATU temuan (aturan
        //    "satu pemeriksaan hanya menghasilkan satu temuan").
        // =========================================================
        $this->command?->info('Menambahkan data random tambahan...');

        // Audit Selesai — RKP (kepatuhan prosedur pengadaan)
        $planRkp2 = AuditPlan::create([
            'division_id'   => $divisions['RKP'],
            'audit_type_id' => $typeOf(1),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_RKP_002_2026',
            'title'         => '[DEMO] Audit Prosedur Pengadaan Triwulan II',
            'start_date'    => now()->subDays(40),
            'end_date'      => now()->subDays(32),
            'status'        => 'completed',
            'description'   => 'Pemeriksaan kelengkapan dokumen lelang dan kepatuhan vendor.',
        ]);
        AuditAssignment::create(['audit_plan_id' => $planRkp2->id, 'user_id' => $auditor2->id, 'role' => 'lead_auditor', 'assigned_at' => now()]);
        $inspRkp2 = Inspection::create([
            'audit_plan_id'   => $planRkp2->id,
            'auditor_id'      => $auditor2->id,
            'inspection_date' => now()->subDays(36),
            'summary'         => 'Beberapa dokumen evaluasi vendor belum ditandatangani pejabat pengadaan.',
            'notes'           => 'Perbaikan cepat pada penandatanganan dokumen.',
            'result'          => 'needs_improvement',
        ]);
        $findingRkp2 = Finding::create([
            'audit_plan_id'    => $planRkp2->id,
            'inspection_id'    => $inspRkp2->id,
            'category_id'      => $catOf(1),
            'risk_category_id' => $riskCats['medium'] ?? null,
            'created_by'       => $auditor2->id,
            'finding_number'   => 'FND_RKP_001_2026',
            'title'            => '[DEMO] Dokumen Evaluasi Vendor Belum Ditandatangani',
            'description'      => '3 dari 10 lembar evaluasi vendor pada paket PBJ-220 belum ada paraf pejabat pengadaan.',
            'recommendation'   => 'Lengkapi tanda tangan dan terapkan checklist kelengkapan sebelum arsip.',
            'risk_description'  => 'Dokumen tanpa pengesahan berisiko menimbulkan sengketa vendor.',
            'criteria_explanation' => 'Kepatuhan tanda tangan pejabat pengadaan belum terpenuhi.',
            'deadline'         => now()->subDays(20),
            'status'           => 'closed',
        ]);
        $picRkp = $picOf($divisions['RKP']);
        $apRkp2 = ActionPlan::create([
            'finding_id'   => $findingRkp2->id,
            'pic_user_id'  => $picRkp->id,
            'title'        => 'Penandatanganan Dokumen Evaluasi Vendor',
            'action'       => 'Seluruh dokumen evaluasi vendor telah ditandatangani dan diaudit ulang.',
            'target_date'  => now()->subDays(25),
            'response'     => 'Selesai, paraf dilengkapi pada semua lembar.',
            'status'       => 'verified',
        ]);
        Verification::create([
            'action_plan_id' => $apRkp2->id,
            'verifier_id'    => $auditor2->id,
            'result'         => 'approved',
            'notes'          => 'Kelengkapan sudah diverifikasi, temuan ditutup.',
            'verified_at'    => now()->subDays(18),
        ]);

        // Audit Berjalan — SDM (data kepegawaian)
        $planSdm2 = AuditPlan::create([
            'division_id'   => $divisions['SDM'],
            'audit_type_id' => $typeOf(2),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_SDM_002_2026',
            'title'         => '[DEMO] Audit Kelengkapan Data Kepegawaian',
            'start_date'    => now()->subDays(4),
            'end_date'      => now()->addDays(10),
            'status'        => 'in_progress',
            'description'   => 'Pemeriksaan berkala kelengkapan berkas personalia.',
        ]);
        AuditAssignment::create(['audit_plan_id' => $planSdm2->id, 'user_id' => $auditor1->id, 'role' => 'lead_auditor', 'assigned_at' => now()]);
        $inspSdm2 = Inspection::create([
            'audit_plan_id'   => $planSdm2->id,
            'auditor_id'      => $auditor1->id,
            'inspection_date' => now()->subDay(),
            'summary'         => 'Sebagian berkas kontrak karyawan belum menyertakan lampiran SK terbaru.',
            'notes'           => 'Daftar nama yang belum lengkap dilampirkan.',
            'result'          => 'non_conformity',
        ]);
        $findingSdm2 = Finding::create([
            'audit_plan_id'    => $planSdm2->id,
            'inspection_id'    => $inspSdm2->id,
            'category_id'      => $catOf(0),
            'risk_category_id' => $riskCats['high'] ?? null,
            'created_by'       => $auditor1->id,
            'finding_number'   => 'FND_SDM_001_2026',
            'title'            => '[DEMO] Lampiran SK Terbaru Tidak Terarsip',
            'description'      => 'Berkas kontrak 7 karyawan tidak menyertakan SK terbaru pada folder personalia.',
            'recommendation'   => 'Lengkapi SK terbaru dan perbarui indeks berkas.',
            'risk_description'  => 'Berkas personalia tidak mutakhir menghambat administrasi kepegawaian.',
            'criteria_explanation' => 'Kelengkapan berkas personalia belum sesuai standar.',
            'deadline'         => now()->addDays(5),
            'status'           => 'waiting_verification',
        ]);
        $picSdm = $picOf($divisions['SDM']);
        $apSdm2 = ActionPlan::create([
            'finding_id'   => $findingSdm2->id,
            'pic_user_id'  => $picSdm->id,
            'title'        => 'Kelengkapan SK Terbaru Karyawan',
            'action'       => 'Mengumpulkan SK terbaru 7 karyawan dan memindai ke folder personalia digital.',
            'target_date'  => now()->addDays(4),
            'response'     => 'Berkas sudah dilengkapi, mohon diverifikasi.',
            'status'       => 'submitted',
        ]);
        Storage::disk('public')->put('evidences/follow_ups/demo-sk-karyawan.txt',
            "DATA DEMO/TESTING\n\nDaftar kelengkapan SK terbaru 7 karyawan.\n" . now());
        FollowUpEvidence::create([
            'action_plan_id' => $apSdm2->id,
            'uploaded_by'    => $picSdm->id,
            'file_name'      => 'daftar-sk-karyawan.txt',
            'file_path'      => 'evidences/follow_ups/demo-sk-karyawan.txt',
            'file_type'      => 'txt',
            'file_size'      => 1024,
        ]);

        // Audit Terjadwal — ADA (persediaan gudang)
        $planAda2 = AuditPlan::create([
            'division_id'   => $divisions['ADA'],
            'audit_type_id' => $typeOf(3),
            'created_by'    => $superAdmin->id,
            'audit_number'  => 'PEN_ADA_002_2026',
            'title'         => '[DEMO] Audit Persediaan & Stock Opname',
            'start_date'    => now()->addDays(6),
            'end_date'      => now()->addDays(12),
            'status'        => 'scheduled',
            'description'   => 'Rencana stock opname persediaan gudang dan pencocokan buku besar.',
        ]);
        AuditAssignment::create(['audit_plan_id' => $planAda2->id, 'user_id' => $auditor3->id, 'role' => 'lead_auditor', 'assigned_at' => now()]);

        $this->command?->info('Data random tambahan selesai: 3 Audit baru, 2 pemeriksaan, 2 temuan, 2 action plan, 2 verifikasi/evidence.');
    }
}
