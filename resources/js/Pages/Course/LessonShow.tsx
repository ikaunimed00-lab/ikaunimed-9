import React, { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import MainLayout from '@/components/MainLayout';
import { route } from 'ziggy-js';
import { ArrowLeft, Clock, GraduationCap } from 'lucide-react';

interface QuizQuestion {
  id: number;
  question: string;
  options?: Record<string, string> | null;
  correct_option_key?: string | null;
  weight: number;
}

interface Quiz {
  id: number;
  title: string;
  description?: string | null;
  passing_score: number;
  questions: QuizQuestion[];
}

interface Lesson {
  id: number;
  title: string;
  type: 'video' | 'text' | 'quiz';
  content?: string | null;
  video_url?: string | null;
  duration_minutes: number;
}

interface CourseSummary {
  id: number;
  title: string;
  slug: string;
}

interface QuizStats {
  attempts_count: number;
  best_score: number | null;
  attempts: {
    id: number;
    score: number;
    is_passed: boolean;
    created_at: string;
  }[];
}

interface Props {
  course: CourseSummary;
  lesson: Lesson;
  quiz: Quiz | null;
  lessonCompleted: boolean;
  quizStats: QuizStats | null;
  nextLessonId: number | null;
}

export default function LessonShow({
  course,
  lesson,
  quiz,
  lessonCompleted,
  quizStats,
  nextLessonId,
}: Props) {
  const { auth }: any = usePage().props;

  const [answers, setAnswers] = useState<Record<number, string>>({});
  const [result, setResult] = useState<{ score: number; passed: boolean } | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [completed, setCompleted] = useState<boolean>(lessonCompleted);
  const [stats, setStats] = useState<QuizStats | null>(quizStats);

  const handleMarkCompleted = () => {
    router.post(
      route('courses.lessons.complete', {
        course: course.slug,
        lesson: lesson.id,
      }),
      {},
      {
        preserveScroll: true,
        onSuccess: () => {
          setCompleted(true);
        },
      }
    );
  };

  const handleAnswerChange = (questionId: number, optionKey: string) => {
    setAnswers((prev) => ({
      ...prev,
      [questionId]: optionKey,
    }));
  };

  const handleSubmitQuiz = async (event: React.FormEvent) => {
    event.preventDefault();

    if (!quiz || !quiz.questions || quiz.questions.length === 0) {
      return;
    }

    for (const question of quiz.questions) {
      if (!answers[question.id]) {
        setError('Masih ada soal yang belum dijawab.');
        return;
      }
    }

    setError(null);

    try {
      const csrfToken =
        (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';

      const response = await fetch(
        route('courses.lessons.quiz.attempt', {
          course: course.slug,
          lesson: lesson.id,
        }),
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ answers }),
        }
      );

      if (!response.ok) {
        setError('Terjadi kesalahan saat mengirim jawaban.');
        return;
      }

      const data = await response.json();

      setResult({ score: data.score, passed: data.passed });

      setStats({
        attempts_count: data.attempts_count,
        best_score: data.best_score,
      });

      if (data.passed && !completed) {
        handleMarkCompleted();
      }
    } catch {
      setError('Terjadi kesalahan jaringan saat mengirim jawaban.');
    }
  };

  const lessonTypeLabel =
    lesson.type === 'video' ? 'Video' : lesson.type === 'text' ? 'Materi Teks' : 'Quiz';

  return (
    <MainLayout variant="full">
      <Head title={`${lesson.title} - ${course.title}`} />

      <div className="bg-gray-50 min-h-screen pb-12">
        <div className="bg-white border-b border-gray-200 sticky top-16 md:top-20 z-10">
          <div className="container mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <div className="flex items-center gap-3">
              <Link
                href={route('courses.show', course.slug)}
                className="inline-flex items-center text-gray-600 hover:text-emerald-600 transition-colors text-sm"
              >
                <ArrowLeft className="w-4 h-4 mr-2" />
                Kembali ke Kursus
              </Link>
              <span className="hidden sm:inline-block text-xs text-gray-400">
                {course.title}
              </span>
            </div>

            {completed && (
              <span className="inline-flex items-center px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-semibold">
                Materi selesai
              </span>
            )}
          </div>
        </div>

        <div className="container mx-auto px-4 py-8 max-w-4xl">
          <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
            <div className="flex items-center justify-between gap-4 mb-4">
              <div className="flex items-center gap-2">
                <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                  {lessonTypeLabel}
                </span>
                {lesson.duration_minutes > 0 && (
                  <span className="inline-flex items-center gap-1 text-xs text-gray-500">
                    <Clock className="w-3 h-3" />
                    {lesson.duration_minutes} menit
                  </span>
                )}
              </div>

              {auth?.user && lesson.type !== 'quiz' && (
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={handleMarkCompleted}
                    className={`inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors ${
                      completed
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-emerald-600 text-white hover:bg-emerald-700'
                    }`}
                  >
                    <GraduationCap className="w-4 h-4" />
                    {completed ? 'Sudah ditandai selesai' : 'Tandai materi selesai'}
                  </button>

                  {completed && nextLessonId && (
                    <button
                      type="button"
                      onClick={() =>
                        router.visit(
                          route('courses.lessons.show', {
                            course: course.slug,
                            lesson: nextLessonId,
                          })
                        )
                      }
                      className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors"
                    >
                      Lanjut ke materi berikutnya
                    </button>
                  )}
                </div>
              )}
            </div>

            <h1 className="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
              {lesson.title}
            </h1>

            {lesson.type === 'video' && lesson.video_url && (
              <div className="mb-6 aspect-video bg-black rounded-lg overflow-hidden">
                <iframe
                  src={lesson.video_url}
                  className="w-full h-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                  title={lesson.title}
                />
              </div>
            )}

            {lesson.type === 'text' && lesson.content && (
              <div className="prose prose-emerald max-w-none text-gray-700">
                <div className="whitespace-pre-line">{lesson.content}</div>
              </div>
            )}

            {lesson.type === 'quiz' && quiz && (
              <div className="space-y-6">
                <div>
                  <h2 className="text-lg font-semibold text-gray-900 mb-1">{quiz.title}</h2>
                  {quiz.description && (
                    <p className="text-sm text-gray-600">{quiz.description}</p>
                  )}
                  <p className="mt-2 text-xs text-gray-500">
                    Minimal skor lulus {quiz.passing_score}%.
                  </p>
                  {stats && stats.attempts_count > 0 && (
                    <p className="mt-1 text-xs text-gray-500">
                      Percobaan: {stats.attempts_count}, skor terbaik: {stats.best_score}%
                    </p>
                  )}
                </div>

                <form onSubmit={handleSubmitQuiz} className="space-y-6">
                  {quiz.questions.map((question, index) => (
                    <div
                      key={question.id}
                      className="border border-gray-100 rounded-lg p-4 bg-gray-50"
                    >
                      <p className="text-sm font-medium text-gray-900 mb-3">
                        {index + 1}. {question.question}
                      </p>
                      <div className="space-y-2">
                        {question.options &&
                          Object.entries(question.options).map(([key, label]) => (
                            <label
                              key={key}
                              className="flex items-center gap-2 text-sm text-gray-700 cursor-pointer"
                            >
                              <input
                                type="radio"
                                name={`question-${question.id}`}
                                value={key}
                                checked={answers[question.id] === key}
                                onChange={() => handleAnswerChange(question.id, key)}
                                className="h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500"
                              />
                              <span className="font-semibold">{key}.</span>
                              <span>{label}</span>
                            </label>
                          ))}
                      </div>
                    </div>
                  ))}

                  {error && (
                    <p className="text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                      {error}
                    </p>
                  )}

                  <button
                    type="submit"
                    className="inline-flex items-center justify-center px-4 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors"
                  >
                    Kirim Jawaban
                  </button>
                </form>

                {result && (
                  <div
                    className={`mt-4 rounded-lg border px-4 py-3 text-sm ${
                      result.passed
                        ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                        : 'border-red-200 bg-red-50 text-red-800'
                    }`}
                  >
                    <p className="font-semibold">
                      Skor kamu: {result.score}% ({result.passed ? 'Lulus' : 'Belum lulus'})
                    </p>
                    {result.passed ? (
                      <>
                        <p className="mt-1">
                          Selamat, kamu lulus quiz ini. Materi akan ditandai selesai.
                        </p>
                        {nextLessonId && (
                          <div className="mt-3">
                            <button
                              type="button"
                              onClick={() =>
                                router.visit(
                                  route('courses.lessons.show', {
                                    course: course.slug,
                                    lesson: nextLessonId,
                                  })
                                )
                              }
                              className="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors"
                            >
                              Lanjut ke materi berikutnya
                            </button>
                          </div>
                        )}
                      </>
                    ) : (
                      <p className="mt-1">
                        Coba lagi sampai mencapai skor minimal untuk lulus.
                      </p>
                    )}
                  </div>
                )}

                {stats && stats.attempts && stats.attempts.length > 0 && (
                  <div className="mt-4 border border-gray-100 rounded-lg p-4 bg-gray-50">
                    <p className="text-xs font-semibold text-gray-700 mb-2">
                      Riwayat percobaan terakhir
                    </p>
                    <div className="space-y-1">
                      {stats.attempts.map((attempt) => (
                        <div
                          key={attempt.id}
                          className="flex items-center justify-between text-xs text-gray-700"
                        >
                          <span>
                            Skor {attempt.score}% (
                            {attempt.is_passed ? 'Lulus' : 'Belum lulus'})
                          </span>
                          <span className="text-[10px] text-gray-400">
                            {new Date(attempt.created_at).toLocaleString('id-ID')}
                          </span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
