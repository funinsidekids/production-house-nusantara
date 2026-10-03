#!/usr/bin/env python3
"""Generate Learning OS Eloquent models."""
import os

BASE = "/workspace/app/Models"

M = {}

M["CurriculumSubject.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class CurriculumSubject extends Model
{
    protected $fillable = ['curriculum_id', 'subject_id', 'grade', 'semester'];

    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }
}
"""

M["Chapter.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Chapter extends Model
{
    protected $fillable = ['curriculum_subject_id', 'number', 'title', 'summary', 'status'];

    public function curriculumSubject(): BelongsTo
    {
        return $this->belongsTo(CurriculumSubject::class);
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class)->orderBy('position');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
"""

M["Topic.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Topic extends Model
{
    protected $fillable = ['chapter_id', 'title', 'description', 'position', 'status'];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(TopicProgress::class);
    }
}
"""

M["Material.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Material extends Model
{
    protected $fillable = [
        'subject_id', 'chapter_id', 'topic_id', 'grade', 'title',
        'type', 'status', 'content_version',
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

    public function sections(): HasMany
    {
        return $this->hasMany(MaterialSection::class)->orderBy('position');
    }
}
"""

M["MaterialSection.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class MaterialSection extends Model
{
    protected $fillable = ['material_id', 'heading', 'body', 'kind', 'position'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
"""

M["Question.php"] = r"""<?php

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
"""

M["QuestionOption.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class QuestionOption extends Model
{
    protected $fillable = ['question_id', 'option_key', 'option_text', 'is_correct', 'position'];

    protected $casts = ['is_correct' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
"""

M["QuestionTag.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class QuestionTag extends Model
{
    protected $fillable = ['name', 'slug'];

    public function relations(): HasMany
    {
        return $this->hasMany(QuestionTagRelation::class, 'tag_id');
    }
}
"""

M["QuestionTagRelation.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class QuestionTagRelation extends Model
{
    protected $fillable = ['question_id', 'tag_id'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(QuestionTag::class, 'tag_id');
    }
}
"""

M["QuestionSet.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class QuestionSet extends Model
{
    protected $fillable = [
        'title', 'description', 'subject_id', 'grade', 'purpose',
        'status', 'created_by', 'duration_minutes',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuestionSetQuestion::class)->orderBy('position');
    }

    public function tryouts(): HasMany
    {
        return $this->hasMany(Tryout::class);
    }
}
"""

M["QuestionSetQuestion.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class QuestionSetQuestion extends Model
{
    protected $fillable = ['question_set_id', 'question_id', 'position'];

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
"""

M["Tryout.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Tryout extends Model
{
    protected $fillable = [
        'title', 'description', 'subject_id', 'question_set_id', 'type', 'grade',
        'duration_minutes', 'question_count', 'question_order', 'status',
        'starts_at', 'ends_at', 'auto_publish_result', 'created_by',
    ];

    protected $casts = [
        'question_order' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_publish_result' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function questionSet(): BelongsTo
    {
        return $this->belongsTo(QuestionSet::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TryoutQuestion::class)->orderBy('position');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TryoutSession::class);
    }

    public function isTka(): bool
    {
        return $this->type === 'tka';
    }
}
"""

M["TryoutQuestion.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class TryoutQuestion extends Model
{
    protected $fillable = ['tryout_id', 'question_id', 'position', 'points', 'is_compulsory'];

    protected $casts = ['is_compulsory' => 'boolean'];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
"""

M["TryoutSession.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class TryoutSession extends Model
{
    protected $fillable = [
        'tryout_id', 'user_id', 'uuid', 'status', 'question_order', 'flagged_questions',
        'answered_count', 'started_at', 'expires_at', 'submitted_at', 'duration_seconds',
        'score', 'accuracy', 'statistics', 'submit_idempotency_key',
    ];

    protected $casts = [
        'question_order' => 'array',
        'flagged_questions' => 'array',
        'statistics' => 'array',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'decimal:2',
        'accuracy' => 'decimal:2',
    ];

    public function tryout(): BelongsTo
    {
        return $this->belongsTo(Tryout::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TryoutAnswer::class);
    }

    public function isExpired(): bool
    {
        return $this->status === 'active'
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }
}
"""

M["TryoutAnswer.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class TryoutAnswer extends Model
{
    protected $fillable = [
        'tryout_session_id', 'question_id', 'selected_option_key', 'answer_text',
        'is_correct', 'time_spent_seconds', 'flagged', 'attempt_number', 'client_request_id',
    ];

    protected $casts = ['is_correct' => 'boolean', 'flagged' => 'boolean'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(TryoutSession::class, 'tryout_session_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
"""

M["LearningTarget.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class LearningTarget extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'topic_id', 'title', 'description', 'status', 'target_date',
    ];

    protected $casts = ['target_date' => 'date'];

    public function tasks(): HasMany
    {
        return $this->hasMany(LearningTask::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
"""

M["LearningTask.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class LearningTask extends Model
{
    protected $fillable = [
        'learning_target_id', 'user_id', 'title', 'kind', 'status', 'due_date', 'completed_at',
    ];

    protected $casts = ['due_date' => 'date', 'completed_at' => 'datetime'];

    public function target(): BelongsTo
    {
        return $this->belongsTo(LearningTarget::class, 'learning_target_id');
    }
}
"""

M["StudySession.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class StudySession extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'topic_id', 'duration_seconds',
        'questions_answered', 'questions_correct', 'day',
    ];

    protected $casts = ['day' => 'date'];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
"""

M["AnswerAttempt.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class AnswerAttempt extends Model
{
    public const SOURCES = ['practice', 'tryout', 'tka', 'remedial', 'ai_practice'];

    protected $fillable = [
        'user_id', 'tryout_session_id', 'question_id', 'topic_id', 'selected_option_key',
        'answer_text', 'is_correct', 'time_spent_seconds', 'source', 'client_request_id', 'answered_at',
    ];

    protected $casts = ['is_correct' => 'boolean', 'answered_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["ProgressSnapshot.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class ProgressSnapshot extends Model
{
    protected $fillable = ['user_id', 'day', 'payload'];

    protected $casts = ['day' => 'date', 'payload' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["TopicProgress.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class TopicProgress extends Model
{
    protected $fillable = [
        'user_id', 'topic_id', 'total_attempts', 'correct_attempts',
        'accuracy', 'avg_time_seconds', 'mastery_status', 'last_activity_at',
    ];

    protected $casts = [
        'accuracy' => 'decimal:2',
        'last_activity_at' => 'datetime',
    ];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["Bookmark.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = ['user_id', 'bookmarkable_type', 'bookmarkable_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["Note.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\MorphTo;

class Note extends Model
{
    protected $fillable = [
        'user_id', 'noteable_type', 'noteable_id', 'title', 'body', 'deleted_locally_at',
    ];

    protected $casts = ['deleted_locally_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }
}
"""

M["Achievement.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class Achievement extends Model
{
    protected $fillable = ['code', 'title', 'description', 'icon', 'criteria', 'points'];

    protected $casts = ['criteria' => 'array'];

    public function userAchievements(): HasMany
    {
        return $this->hasMany(UserAchievement::class);
    }
}
"""

M["UserAchievement.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class UserAchievement extends Model
{
    protected $fillable = ['user_id', 'achievement_id', 'unlocked_at'];

    protected $casts = ['unlocked_at' => 'datetime'];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
"""

M["AiConversation.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

class AiConversation extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'subject_id', 'topic_id', 'title', 'status',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["AiMessage.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = ['ai_conversation_id', 'role', 'content', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }
}
"""

M["AiGeneratedQuestion.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class AiGeneratedQuestion extends Model
{
    protected $fillable = [
        'question_id', 'user_id', 'ai_request_id', 'raw_payload', 'review_status',
        'validation_error', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = ['raw_payload' => 'array', 'reviewed_at' => 'datetime'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
"""

M["AiRequest.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class AiRequest extends Model
{
    protected $fillable = [
        'user_id', 'request_type', 'model', 'status', 'duration_ms', 'token_usage', 'error_message',
    ];

    protected $casts = ['token_usage' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["SyncChange.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class SyncChange extends Model
{
    protected $fillable = [
        'user_id', 'entity', 'entity_id', 'local_id', 'operation',
        'content_hash', 'payload', 'changed_at',
    ];

    protected $casts = ['payload' => 'array', 'changed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["SyncLog.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class SyncLog extends Model
{
    protected $fillable = [
        'user_id', 'device_id', 'direction', 'changes_in', 'changes_out',
        'conflicts', 'report', 'idempotency_key',
    ];

    protected $casts = ['report' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["DeviceSession.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class DeviceSession extends Model
{
    protected $fillable = [
        'user_id', 'device_id', 'platform', 'app_version', 'token_identifier', 'last_seen_at',
    ];

    protected $casts = ['last_seen_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["Backup.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class Backup extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'device_id', 'name', 'size_bytes', 'storage_path',
        'checksum_sha256', 'status', 'record_count', 'idempotency_key',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
"""

M["Setting.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'is_public'];

    protected $casts = ['is_public' => 'boolean'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::where('key', $key)->first();

        if (! $row) {
            return $default;
        }

        $decoded = json_decode((string) $row->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_string($value) ? $value : json_encode($value), 'group' => $group],
        );
    }
}
"""

M["ApiAccessToken.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class ApiAccessToken extends Model
{
    protected $fillable = [
        'user_id', 'name', 'token_hash', 'device_id', 'abilities', 'last_used_at', 'expires_at',
    ];

    protected $casts = ['abilities' => 'array', 'last_used_at' => 'datetime', 'expires_at' => 'datetime'];

    protected $hidden = ['token_hash'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
"""

M["StudentProfile.php"] = """<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\BelongsTo;

class StudentProfile extends Model
{
    protected $fillable = ['user_id', 'school_id', 'grade', 'class_name', 'phone'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
"""

os.makedirs(BASE, exist_ok=True)
for name, content in M.items():
    with open(os.path.join(BASE, name), "w") as f:
        f.write(content)
print("wrote", len(M), "models")
