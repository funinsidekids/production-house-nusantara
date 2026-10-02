<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\LearningTarget;
use App\Models\Note;
use App\Models\School;
use App\Models\Subject;
use App\Models\TryoutSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserDashboardController extends Controller
{
    public function dashboard(): View
    {
        $userId = auth()->id();

        $stats = $this->stats($userId);

        $recentAttempts = DB::table('answer_attempts')
            ->leftJoin('questions', 'questions.id', '=', 'answer_attempts.question_id')
            ->leftJoin('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->where('answer_attempts.user_id', $userId)
            ->orderByDesc('answer_attempts.answered_at')
            ->limit(8)
            ->get([
                'answer_attempts.id',
                'answer_attempts.is_correct',
                'answer_attempts.source',
                'answer_attempts.answered_at',
                'questions.question_text',
                'questions.difficulty',
                'subjects.name as subject_name',
            ]);

        $targets = LearningTarget::query()
            ->with(['tasks' => fn ($q) => $q->orderBy('due_date')])
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        return view('user-learn.dashboard', compact('stats', 'recentAttempts', 'targets'));
    }

    public function subjects(): View
    {
        $subjects = Subject::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $counts = [
            'materials' => DB::table('materials')->where('status', 'published')->groupBy('subject_id')->pluck(DB::raw('COUNT(*)'), 'subject_id'),
            'questions' => DB::table('questions')->where('status', 'published')->groupBy('subject_id')->pluck(DB::raw('COUNT(*)'), 'subject_id'),
        ];

        return view('user-learn.subjects', compact('subjects', 'counts'));
    }

    public function tryouts(): View
    {
        $tryouts = DB::table('tryouts')
            ->leftJoin('subjects', 'subjects.id', '=', 'tryouts.subject_id')
            ->whereIn('tryouts.status', ['open', 'scheduled'])
            ->orderByRaw("CASE WHEN tryouts.type = 'tka' THEN 0 ELSE 1 END")
            ->orderByDesc('tryouts.created_at')
            ->limit(100)
            ->get(['tryouts.id', 'tryouts.title', 'tryouts.type', 'tryouts.grade', 'tryouts.duration_minutes', 'tryouts.question_count', 'tryouts.status', 'subjects.name as subject_name']);

        $sessions = TryoutSession::query()
            ->with('tryout:id,title,type')
            ->where('user_id', auth()->id())
            ->latest('started_at')
            ->limit(20)
            ->get();

        return view('user-learn.tryouts', compact('tryouts', 'sessions'));
    }

    public function bookmarks(): View
    {
        $rows = DB::table('bookmarks')
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        $items = collect($rows->items())->map(function ($b) {
            $label = null;
            if ($b->bookmarkable_type === 'material') {
                $label = DB::table('materials')->where('id', $b->bookmarkable_id)->value('title');
            } elseif ($b->bookmarkable_type === 'question') {
                $label = DB::table('questions')->where('id', $b->bookmarkable_id)->value('question_text');
            } else {
                $label = DB::table('topics')->where('id', $b->bookmarkable_id)->value('name');
            }

            return (object) ['id' => $b->id, 'type' => $b->bookmarkable_type, 'label' => $label ?: '(item dihapus)', 'created_at' => $b->created_at];
        });

        $paginator = new LengthAwarePaginator(
            $items,
            $rows->total(),
            $rows->perPage(),
            $rows->currentPage(),
            ['path' => url()->current(), 'query' => request()->query()]
        );

        return view('user-learn.bookmarks', ['bookmarks' => $paginator]);
    }

    public function notes(): View
    {
        $notes = Note::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('user-learn.notes', compact('notes'));
    }

    public function storeNote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        Note::create([
            'user_id' => auth()->id(),
            'title' => $data['title'] ?: null,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Catatan tersimpan.');
    }

    public function destroyNote(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === auth()->id(), 403);
        $note->delete();

        return back()->with('status', 'Catatan dihapus.');
    }

    public function profile(): View
    {
        $user = auth()->user();
        $profile = $user->studentProfile;
        $schools = School::query()->orderBy('name')->get(['id', 'name']);

        return view('user-learn.profile', compact('user', 'profile', 'schools'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'grade' => ['nullable', 'string', 'max:10'],
            'class_name' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $user->update(['name' => $data['name'], 'phone' => $data['phone'] ?? null]);

        $user->studentProfile()->updateOrCreate([], [
            'school_id' => $data['school_id'] ?? null,
            'grade' => $data['grade'] ?? 'XII',
            'class_name' => $data['class_name'] ?? null,
            'phone' => $data['phone'] ?? null,
        ]);

        return back()->with('status', 'Profil diperbarui.');
    }

    private function stats(int $userId): array
    {
        $totals = DB::table('answer_attempts')
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) total, COALESCE(SUM(is_correct),0) correct, AVG(time_spent_seconds) avg_time')
            ->first();

        $total = (int) ($totals->total ?? 0);
        $correct = (int) ($totals->correct ?? 0);

        $bySubject = DB::table('answer_attempts')
            ->join('questions', 'questions.id', '=', 'answer_attempts.question_id')
            ->join('subjects', 'subjects.id', '=', 'questions.subject_id')
            ->where('answer_attempts.user_id', $userId)
            ->groupBy('subjects.name')
            ->selectRaw('subjects.name, COUNT(*) total, SUM(answer_attempts.is_correct) correct')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name,
                'total' => (int) $r->total,
                'accuracy' => $r->total > 0 ? round(($r->correct / $r->total) * 100) : 0,
            ]);

        $tryoutStats = DB::table('tryout_sessions')
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) done, AVG(score) avg_score, MAX(score) best_score')
            ->first();

        return [
            'answered' => $total,
            'correct' => $correct,
            'accuracy' => $total > 0 ? round(($correct / $total) * 100) : 0,
            'avg_time' => $totals->avg_time ? round((float) $totals->avg_time) : 0,
            'tryouts_done' => (int) ($tryoutStats->done ?? 0),
            'tryout_avg' => $tryoutStats->avg_score !== null ? round((float) $tryoutStats->avg_score, 1) : 0,
            'tryout_best' => $tryoutStats->best_score !== null ? round((float) $tryoutStats->best_score, 1) : 0,
            'by_subject' => $bySubject,
            'bookmarks' => Bookmark::where('user_id', $userId)->count(),
            'notes' => Note::where('user_id', $userId)->count(),
        ];
    }
}
