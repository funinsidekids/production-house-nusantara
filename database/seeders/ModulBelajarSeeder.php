<?php

namespace Database\Seeders;

use App\Models\ModulChapter;
use App\Models\ModulMapel;
use App\Models\ModulMaterial;
use Illuminate\Database\Seeder;

class ModulBelajarSeeder extends Seeder
{
    public function run(): void
    {
        $mapels = [
            [
                'kode' => 'mtk',
                'nama' => 'Matematika (Wajib & Peminatan)',
                'deskripsi' => 'Limit fungsi, turunan, integral, matriks, vektor, statistika, dan peluang untuk kelas 12.',
                'ikon' => 'sigma',
                'warna' => '#6366f1',
                'urutan' => 1,
                'chapters' => [
                    ['judul' => 'Limit Fungsi Aljabar dan Trigonometri', 'ringkasan' => 'Konsep limit, sifat-sifat, limit tak hingga, dan limit trigonometri.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Pengertian dan Sifat Limit', 'isi' => "Materi limit fungsi kelas 12: definisi intuitif, operasi aljabar limit, teorema substitusi, limit kiri/kanan, serta bentuk tak tentu 0/0. Termasuk contoh penyelesaian dengan faktorisasi dan perkalian sekawan."],
                        ['tipe' => 'contoh', 'judul' => 'Contoh Soal Limit Bentuk Akar & Pecahan', 'isi' => "Kumpulan soal limit dengan akar dan pecahan linear lengkap dengan pembahasan langkah demi langkah, termasuk trik cepat L'Hospital sebagai alternatif."],
                        ['tipe' => 'latihan', 'judul' => 'Latihan Limit (20 Soal)', 'isi' => "20 soal latihan limit fungsi aljabar dan trigonometri bertingkat dari mudah ke HOTS, disertai kunci jawaban."],
                        ['tipe' => 'rangkuman', 'judul' => 'Rangkuman + Rumus Cepat Limit', 'isi' => "Ringkasan rumus limit dasar, limit trigonometri, dan limit tak hingga dalam satu halaman untuk belajar cepat sebelum ujian."],
                    ]],
                    ['judul' => 'Turunan Fungsi dan Penerapannya', 'ringkasan' => 'Aturan turunan, garis singgung, fungsi naik/turun, nilai stasioner, dan masalah optimisasi.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Aturan Turunan & Garis Singgung', 'isi' => "Definisi turunan sebagai limit, aturan hasil kali, hasil bagi, rantai, serta penerapan pada persamaan garis singgung kurva."],
                        ['tipe' => 'contoh', 'judul' => 'Contoh Soal Nilai Stasioner', 'isi' => "Pembahasan menentukan titik balik maksimum/minimum, uji turunan pertama dan kedua."],
                        ['tipe' => 'latihan', 'judul' => 'Latihan Optimisasi', 'isi' => "Soal cerita optimisasi (luas maksimum, volume minimum) khas UTBK/SNBT dengan kunci jawaban."],
                    ]],
                    ['judul' => 'Integral Tak Tentu dan Tertentu', 'ringkasan' => 'Anti-derivatif, teknik substitusi, luas daerah, dan volume benda putar.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Konsep Integral sebagai Anti-Turunan', 'isi' => "Notasi integral, sifat linier, integral fungsi aljabar dan trigonometri dasar, serta metode substitusi."],
                        ['tipe' => 'contoh', 'judul' => 'Contoh Luas Daerah & Benda Putar', 'isi' => "Pembahasan grafik irisan daerah, integral tertentu untuk luas, dan rumus cincin/cakram untuk volume."],
                        ['tipe' => 'latihan', 'judul' => 'Latihan Integral (15 Soal)', 'isi' => "Latihan integral tak tentu dan tertentu dengan tingkat kesulitan bertahap plus pembahasan singkat."],
                    ]],
                ],
            ],
            [
                'kode' => 'fis',
                'nama' => 'Fisika',
                'deskripsi' => 'Listrik statis & dinamis, magnetisme, induksi elektromagnetik, gelombang, optik, dan fisika kuantum.',
                'ikon' => 'atom',
                'warna' => '#0ea5e9',
                'urutan' => 2,
                'chapters' => [
                    ['judul' => 'Listrik Statis dan Dinamis', 'ringkasan' => 'Hukum Coulomb, medan listrik, potensial, kapasitor, arus, hukum Ohm, dan rangkaian.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Medan Listrik & Hukum Coulomb', 'isi' => "Gaya antar muatan, kuat medan listrik, potensial listrik, energi potensial, dan kapasitor keping sejajar."],
                        ['tipe' => 'contoh', 'judul' => 'Contoh Rangkaian Arus Searah', 'isi' => "Pembahasan hukum Kirchhoff I & II pada rangkaian majemuk dengan angka terurai."],
                        ['tipe' => 'latihan', 'judul' => 'Latihan Listrik Dinamis', 'isi' => "15 soal rangkaian, daya listrik, dan pengukuran alat ukur beserta kunci."],
                    ]],
                    ['judul' => 'Induksi Elektromagnetik', 'ringkasan' => 'Hukum Faraday, Lenz, GGL induksi, transformator, dan generator.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Flux Magnet & Hukum Faraday', 'isi' => "Perubahan flux magnet, GGL induksi diri dan mutual, serta aplikasi pada trafo step-up/step-down."],
                        ['tipe' => 'rangkuman', 'judul' => 'Peta Konsep Elektromagnetik', 'isi' => "Ringkasan satu halaman hubungan magnet-listrik-gaya untuk persiapan ujian."],
                    ]],
                    ['judul' => 'Gelombang, Optik, dan Fisika Kuantum', 'ringkasan' => 'Cahaya sebagai gelombang & partikel, interferensi, difraksi, efek fotolistrik, dan model atom Bohr.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Interferensi & Difraksi Cahaya', 'isi' => "Eksperimen Young, kisi difraksi, polarisasi, dispersi, serta dualisme gelombang-partikel."],
                        ['tipe' => 'video', 'judul' => 'Animasi Efek Fotolistrik', 'isi' => "Video simulasi percobaan efek fotolistrik Millikan dan interpretasi grafik energi kinetik vs frekuensi."],
                    ]],
                ],
            ],
            [
                'kode' => 'kim',
                'nama' => 'Kimia',
                'deskripsi' => 'Struktur atom & sistem periodik, laju reaksi, kesetimbangan, larutan penyangga, koloid, dan kimia organik.',
                'ikon' => 'flask',
                'warna' => '#10b981',
                'urutan' => 3,
                'chapters' => [
                    ['judul' => 'Struktur Atom & Sistem Periodik', 'ringkasan' => 'Model Bohr, mekanika kuantum, bilangan kuantum, konfigurasi elektron, dan sifat periodik.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Bilangan Kuantum & Konfigurasi Elektron', 'isi' => "Prinsip Aufbau, larangan Pauli, kaidah Hund, serta penentuan blok s/p/d/f dari konfigurasi."],
                        ['tipe' => 'latihan', 'judul' => 'Latihan SPME & Biloks', 'isi' => "Soal menentukan empat bilangan kuantum elektron terakhir suatu unsur."],
                    ]],
                    ['judul' => 'Laju Reaksi & Kesetimbangan', 'ringkasan' => 'Teori tumbukan, ordo reaksi, faktor pergeseran kesetimbangan, dan prinsip Le Chatelier.', 'materials' => [
                        ['tipe' => 'contoh', 'judul' => 'Contoh Perhitungan Ordo Reaksi', 'isi' => "Menentukan laju reaksi dan orde dari data eksperimen dengan pembahasan tabel."],
                        ['tipe' => 'teori', 'judul' => 'Hubungan Kc, Kp, dan Derajat Disosiasi', 'isi' => "Rumus kesetimbangan gas, tekanan parsial, dan faktor yang menggeser kesetimbangan."],
                    ]],
                    ['judul' => 'Kimia Organik', 'ringkasan' => 'Golongan fungsi alkana-alkuna, benzena, polimer, makromolekul (karbohidrat, protein, lemak).', 'materials' => [
                        ['tipe' => 'rangkuman', 'judul' => 'Tabel Gugus Fungsi & Reaksi Khas', 'isi' => "Rangkuman alkohol, eter, aldehid, keton, asam karboksilat, ester: rumus umum, nama, dan reaksinya."],
                    ]],
                ],
            ],
            [
                'kode' => 'bio',
                'nama' => 'Biologi',
                'deskripsi' => 'Pertumbuhan & perkembangan, metabolisme, pembagian sel, genetika, evolusi, dan ekologi.',
                'ikon' => 'leaf',
                'warna' => '#f59e0b',
                'urutan' => 4,
                'chapters' => [
                    ['judul' => 'Metabolisme Sel', 'ringkasan' => 'Enzim, katabolisme karbohidrat (glikolisis sampai transpor elektron), anabolisme fotosintesis.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Respirasi Aerob & Fosforilasi Oksidatif', 'isi' => "Tahapan glikolisis, dekarboksilasi oksidatif, siklus Krebs, rantai transpor elektron, dan total ATP."],
                        ['tipe' => 'contoh', 'judul' => 'Contoh Soal Perhitungan ATP', 'isi' => "Pembahasan yield ATP dari NADH/FADH2 dengan pendekatan modern."],
                    ]],
                    ['judul' => 'Pembagian Sel & Genetika', 'ringkasan' => 'Mitosis, meiosis, pola inheritance Mendel, linkage, pautan seks, dan mutasi.', 'materials' => [
                        ['tipe' => 'latihan', 'judul' => 'Latihan Persilangan Monohibrid–Polihibrid', 'isi' => "20 soal persilangan lengkap dengan diagram papan catur dan rasio F1/F2."],
                        ['tipe' => 'video', 'judul' => 'Animasi Meiosis & Crossover', 'isi' => "Video profase I sampai telofase II menjelaskan pembentukan gamet dan variasi genetik."],
                    ]],
                ],
            ],
            [
                'kode' => 'eng',
                'nama' => 'Bahasa Inggris',
                'deskripsi' => 'Analytical exposition, discussion text, narrative, dan keterampilan academic reading untuk SNBT.',
                'ikon' => 'globe',
                'warna' => '#ef4444',
                'urutan' => 5,
                'chapters' => [
                    ['judul' => 'Analytical Exposition Text', 'ringkasan' => 'Struktur thesis–argument–reiteration, language features, dan soal reading tipe HOTS.', 'materials' => [
                        ['tipe' => 'teori', 'judul' => 'Structure & Language Features', 'isi' => "Generic structure, simple present, causal conjunction, modality, dan contoh teks bertema lingkungan."],
                        ['tipe' => 'latihan', 'judul' => 'Reading Comprehension Set 1', 'isi' => "Dua teks eksposisi + 10 soal pilihan ganda model UTBK dengan pembahasan."],
                    ]],
                ],
            ],
        ];

        foreach ($mapels as $data) {
            $chapters = $data['chapters'];
            unset($data['chapters']);
            $mapel = ModulMapel::firstOrCreate(['kode' => $data['kode']], $data);

            foreach ($chapters as $i => $chapterData) {
                $materials = $chapterData['materials'];
                unset($chapterData['materials']);
                $chapter = ModulChapter::firstOrCreate(
                    ['mapel_id' => $mapel->id, 'nomor' => $i + 1],
                    $chapterData + ['terbit' => true]
                );

                foreach ($materials as $j => $materialData) {
                    ModulMaterial::firstOrCreate(
                        ['chapter_id' => $chapter->id, 'nomor' => $j + 1],
                        $materialData + ['terbit' => true, 'perkiraan_menit' => 15]
                    );
                }
            }
        }
    }
}
