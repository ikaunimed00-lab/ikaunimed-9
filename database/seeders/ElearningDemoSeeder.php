<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;

class ElearningDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();

        if (! $admin) {
            $this->command?->warn('Tidak ada user di database, lewati ElearningDemoSeeder.');

            return;
        }

        $category = CourseCategory::firstOrCreate(
            ['slug' => 'pengembangan-karir'],
            [
                'name' => 'Pengembangan Karir',
                'description' => 'Kelas untuk meningkatkan keterampilan karir alumni.',
            ]
        );

        $course = Course::firstOrCreate(
            ['slug' => 'micro-learning-fundamental-karir'],
            [
                'title' => 'Micro Learning: Fundamental Karir untuk Alumni UNIMED',
                'description' => "Kelas pengantar singkat untuk membantu alumni memetakan ulang karir dan mengoptimalkan potensi diri.\nMateri dikemas dalam bentuk micro-learning yang bisa diselesaikan dalam waktu singkat.",
                'thumbnail' => null,
                'instructor_name' => $admin->name,
                'level' => 'Beginner',
                'status' => 'published',
                'duration_minutes' => 45,
                'category_id' => $category->id,
                'created_by' => $admin->id,
                'is_paid' => false,
                'price' => 0,
            ]
        );

        if ($course->wasRecentlyCreated) {
            $lessons = [
                [
                    'title' => 'Selamat Datang di Program Micro Learning',
                    'slug' => 'selamat-datang',
                    'order' => 1,
                    'type' => 'video',
                    'content' => 'Pengantar singkat mengenai tujuan program dan cara memaksimalkan manfaat kelas ini.',
                    'video_url' => null,
                    'duration_minutes' => 5,
                    'is_preview' => true,
                ],
                [
                    'title' => 'Mengenali Posisi Karir Saat Ini',
                    'slug' => 'mengenali-posisi-karir',
                    'order' => 2,
                    'type' => 'text',
                    'content' => 'Materi teks yang membantu peserta memetakan kondisi karir mereka saat ini.',
                    'video_url' => null,
                    'duration_minutes' => 15,
                    'is_preview' => false,
                ],
                [
                    'title' => 'Menyusun Rencana Pengembangan 30 Hari',
                    'slug' => 'rencana-30-hari',
                    'order' => 3,
                    'type' => 'text',
                    'content' => 'Panduan praktis menyusun rencana pengembangan karir untuk 30 hari ke depan.',
                    'video_url' => null,
                    'duration_minutes' => 25,
                    'is_preview' => false,
                ],
            ];

            foreach ($lessons as $data) {
                Lesson::create(array_merge($data, ['course_id' => $course->id]));
            }

            $this->command?->info('Demo course dan lesson e-learning berhasil dibuat.');
        } else {
            $this->command?->info('Demo course sudah ada, tidak membuat duplikasi.');
        }
    }
}

