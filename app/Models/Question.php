<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const TYPES = [
        'multiple_choice', 'true_false', 'short_answer', 'essay',
        'complex_multiple_choice', 'matching', 'numeric',
    ];

    public const DIFFICULTIES = ['easy', 'medium', 'hard', 'mixed'];

    public const SOURCES = ['official', 'curated', 'teacher_created', 'ai_generated', 'imported', 'sample'];

    public const STATUSES = ['pending', 'validated', 'published', 'rejected', 'archived', 'draft'];

    protected $fillable = [
        'subject_id', 'chapter_id', 'topic_id', 'curriculum_id', 'grade',
        'type', 'difficulty', 'cognitive_level', 'estimated_time',
        'question_text', 'explanation', 'answer_key', 'source', 'status',
        'content_hash', 'created_by', 'content_version',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    public function tagRelations(): HasMany
    {
        return $this->hasMany(QuestionTagRelation::class);
    }

    /** Normalize question text for duplicate detection. */
    public static function normalizeText(string $text): string
    {
        $t = mb_strtolower(trim($text));
        $t = preg_replace('/\s+/u', ' ', $t);
        $t = preg_replace('/[^\p{L}\p{N}\s]/u', '', $t);

        return trim($t);
    }

    public static function contentHash(string $text): string
    {
        return hash('sha256', self::normalizeText($text));
    }
}
