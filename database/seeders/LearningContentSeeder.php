<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Material;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\School;
use App\Models\Setting;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Tryout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearningContentSeeder extends Seeder
{
    public function run(): void
    {
        $curriculum = Curriculum::query()->firstOrCreate(
            ['code' => 'KM-2025'],
            ['name' => 'Kurikulum Merdeka', 'version' => 1, 'is_active' => true, 'description' => 'Kurikulum aktif untuk kelas XII SMA/MA.']
        );

        $subjects = [
            ['MAT', 'Matematika', '#6366f1', 'umum'],
            ['MTL', 'Matematika Tingkat Lanjut', '#8b5cf6', 'peminatan'],
            ['BIND', 'Bahasa Indonesia', '#0ea5e9', 'umum'],
            ['BING', 'Bahasa Inggris', '#14b8a6', 'umum'],
            ['FIS', 'Fisika', '#f97316', 'peminatan'],
            ['KIM', 'Kimia', '#ef4444', 'peminatan'],
            ['BIO', 'Biologi', '#22c55e', 'peminatan'],
            ['EKO', 'Ekonomi', '#eab308', 'peminatan'],
            ['GEO', 'Geografi', '#a16207', 'peminatan'],
            ['SOC', 'Sosiologi', '#64748b', 'peminatan'],
            ['SEJ', 'Sejarah', '#b45309', 'peminatan'],
            ['FIQ', 'Fikih', '#0d9488', 'keagamaan'],
            ['SKI', 'Sejarah Kebudayaan Islam', '#7c3aed', 'keagamaan'],
            ['AQA', 'Akidah Akhlak', '#059669', 'keagamaan'],
            ['QRH', 'Al-Qur\'an Hadis', '#2563eb', 'keagamaan'],
            ['BAR', 'Bahasa Arab', '#db2777', 'keagamaan'],
        ];

        foreach ($subjects as [$code, $name, $color, $group]) {
            $subject = Subject::firstOrCreate(['code' => $code], [
                'name' => $name, 'icon' => 'book', 'color' => $color, 'group' => $group, 'active' => true,
            ]);

            $cs = CurriculumSubject::firstOrCreate(['curriculum_id' => $curriculum->id, 'subject_id' => $subject->id, 'grade' => 'XII']);

            if (Chapter::where('curriculum_subject_id', $cs->id)->exists()) {
                continue;
            }

            $chapterDefs = match ($code) {
                'MAT', 'MTL' => [['Integral', ['Integral Tak Tentu', 'Integral Tertentu', 'Aplikasi Integral']], ['Turunan', ['Aturan Turunan', 'Aplikasi Turunan']]],
                'BIND' => [['Teks Editorial', ['Struktur Editorial', 'Kebahasaan']], ['Puisi', ['Unsur Puisi', 'Apresiasi Puisi']]],
                'BING' => [['Analytical Exposition', ['Generic Structure', 'Language Features']], ['Narrative Text', ['Elements', 'Comprehension']]],
                'FIS' => [['Listrik Arus Kuat', ['Hukum Ohm', 'Energi & Daya Listrik']], ['Induksi Elektromagnetik', ['Fluks', 'Hukum Faraday']]],
                'KIM' => [['Laju Reaksi', ['Orde Reaksi', 'Teori Tumbukan']], ['Kesetimbangan Kimia', ['Geseran Kesetimbangan', 'Hukum Henry']]],
                'BIO' => [['Pertumbuhan & Perkembangan', ['Faktor Internal', 'Faktor Eksternal']], ['Metabolisme', ['Enzim', 'Katabolisme Karbohidrat']]],
                'EKO' => [['Pembangunan Ekonomi', ['Teori Pembangunan', 'Permasalahan']], ['Anggaran & Perpajakan', ['APBN', 'Policy Fiscal']]],
                'GEO' => [['Pembangunan Berkelanjutan', ['Konsep', 'Indikator']], ['Negara Maju & Berkembang', ['Klasifikasi', 'Indikator']]],
                'SOC' => [['Perubahan Sosial', ['Teori', 'Faktor Pendorong']], ['Globalisasi', ['Bentuk', 'Dampak']]],
                'SEJ' => [['Reformasi 1998', ['Latar Belakang', 'Dinamika']], ['Indonesia Masa Orde Baru', ['Pembangunan', 'Kebijakan']]],
                default => [['Konsep Dasar', ['Pengertian', 'Ruang Lingkup']], ['Penerapan', ['Studi Kasus', 'Evaluasi']]],
            };

            foreach ($chapterDefs as $ci => [$ctitle, $topics]) {
                $chapter = Chapter::create([
                    'curriculum_subject_id' => $cs->id,
                    'number' => $ci + 1,
                    'title' => $ctitle,
                    'summary' => "Bab {$ctitle} — {$name} kelas XII.",
                    'status' => 'published',
                ]);

                foreach ($topics as $ti => $ttitle) {
                    $topic = Topic::create([
                        'chapter_id' => $chapter->id,
                        'title' => $ttitle,
                        'position' => $ti + 1,
                        'status' => 'published',
                    ]);

                    Material::create([
                        'subject_id' => $subject->id,
                        'chapter_id' => $chapter->id,
                        'topic_id' => $topic->id,
                        'grade' => 'XII',
                        'title' => 'Materi: '.$ttitle,
                        'type' => 'teori',
                        'status' => 'published',
                    ])->sections()->create([
                        'heading' => 'Pendahuluan',
                        'kind' => 'text',
                        'body' => "Contoh materi pembelajaran (sample) tentang {$ttitle} pada mata pelajaran {$name}. Materi lengkap dapat ditambahkan melalui dashboard admin.",
                        'position' => 1,
                    ]);

                    // Sample questions per topic (labelled sample, never official).
                    foreach ([['easy', 'C1'], ['medium', 'C3'], ['hard', 'C4']] as $qi => [$diff, $cog]) {
                        $question = Question::create([
                            'subject_id' => $subject->id,
                            'chapter_id' => $chapter->id,
                            'topic_id' => $topic->id,
                            'curriculum_id' => $curriculum->id,
                            'grade' => 'XII',
                            'type' => 'multiple_choice',
                            'difficulty' => $diff,
                            'cognitive_level' => $cog,
                            'estimated_time' => 90,
                            'question_text' => "[SAMPLE] Pertanyaan {$diff} tentang {$ttitle} ({$name}): manakah pernyataan yang paling tepat?",
                            'explanation' => "Pembahasan contoh: opsi A adalah kunci yang benar untuk soal sample ini.",
                            'source' => 'sample',
                            'status' => 'published',
                            'content_hash' => hash('sha256', strtolower(preg_replace('/\\s+/', ' ', "{$code}-{$ctitle}-{$ttitle}-{$diff}-{$qi}"))),
                        ]);

                        foreach (['A' => true, 'B' => false, 'C' => false, 'D' => false, 'E' => false] as $key => $correct) {
                            QuestionOption::create([
                                'question_id' => $question->id,
                                'option_key' => $key,
                                'option_text' => "Pilihan {$key} untuk soal {$ttitle}",
                                'is_correct' => $correct,
                                'position' => ord($key) - 64,
                            ]);
                        }
                    }
                }
            }
        }

        School::firstOrCreate(['name' => 'MA Nurul Huda'], ['type' => 'MA', 'city' => 'Jakarta', 'province' => 'DKI Jakarta']);
        School::firstOrCreate(['name' => 'SMA Negeri 1 Bandung'], ['type' => 'SMA', 'city' => 'Bandung', 'province' => 'Jawa Barat']);

        // Settings used by remedial engine & app config.
        foreach ([
            ['remedial.accuracy_threshold', '60', 'learning'],
            ['remedial.min_attempts', '5', 'learning'],
            ['app.api_version', 'v1', 'app'],
            ['app.content_version', '1', 'app'],
            ['app.min_version', '1.0.0', 'app'],
        ] as [$key, $value, $group]) {
            Setting::updateOrCreate(['key' => $key], ['value' => json_encode($value), 'group' => $group, 'is_public' => true]);
        }

        $this->command?->info('Learning content seeded: subjects, chapters, topics, materials, sample questions.');
    }
}
