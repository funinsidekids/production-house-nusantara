<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------- Organization: schools, classes ----------
        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('npsn', 20)->nullable();
            $t->string('city', 100)->nullable();
            $t->string('province', 100)->nullable();
            $t->enum('type', ['SMA', 'MA', 'SMK', 'MAN', 'other'])->default('SMA');
            $t->timestamps();
            $t->unique(['name', 'npsn']);
        });

        Schema::create('school_classes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('school_id')->constrained()->cascadeOnDelete();
            $t->string('grade', 8)->default('XII');
            $t->string('name', 50); // e.g. XII IPA 1, XII IPS 2
            $t->string('department', 30)->nullable(); // IPA/IPS/Keagamaan
            $t->timestamps();
            $t->unique(['school_id', 'grade', 'name']);
        });

        // ---------- Curriculum & content hierarchy ----------
        Schema::create('curriculums', function (Blueprint $t) {
            $t->id();
            $t->string('name'); // Kurikulum Merdeka, K-13
            $t->string('code', 30)->unique();
            $t->unsignedInteger('version')->default(1);
            $t->boolean('is_active')->default(false);
            $t->text('description')->nullable();
            $t->timestamps();
        });

        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code', 20)->unique();
            $t->string('icon', 32)->default('book');
            $t->string('color', 16)->default('#6366f1');
            $t->enum('group', ['umum', 'peminatan', 'keagamaan', 'muatan_lokal'])->default('umum');
            $t->boolean('active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });

        Schema::create('curriculum_subjects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('curriculum_id')->constrained('curriculums')->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->string('grade', 8)->default('XII');
            $t->unsignedInteger('semester')->nullable();
            $t->timestamps();
            $t->unique(['curriculum_id', 'subject_id', 'grade', 'semester'], 'curr_subj_grade_sem_uq');
        });

        Schema::create('chapters', function (Blueprint $t) {
            $t->id();
            $t->foreignId('curriculum_subject_id')->constrained('curriculum_subjects')->cascadeOnDelete();
            $t->unsignedInteger('number')->default(1);
            $t->string('title');
            $t->text('summary')->nullable();
            $t->enum('status', ['draft', 'published', 'archived'])->default('published');
            $t->timestamps();
            $t->unique(['curriculum_subject_id', 'number'], 'chapter_cs_num_uq');
        });

        Schema::create('topics', function (Blueprint $t) {
            $t->id();
            $t->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->unsignedInteger('position')->default(0);
            $t->enum('status', ['draft', 'published', 'archived'])->default('published');
            $t->timestamps();
        });

        Schema::create('materials', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->foreignId('chapter_id')->constrained()->cascadeOnDelete();
            $t->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $t->string('grade', 8)->default('XII');
            $t->string('title');
            $t->enum('type', ['teori', 'contoh', 'latihan', 'rangkuman', 'video', 'modul'])->default('teori');
            $t->enum('status', ['draft', 'published', 'archived'])->default('published');
            $t->unsignedBigInteger('content_version')->default(1);
            $t->timestamps();
            $t->index(['subject_id', 'grade', 'status']);
        });

        Schema::create('material_sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('material_id')->constrained()->cascadeOnDelete();
            $t->string('heading')->nullable();
            $t->longText('body')->nullable();
            $t->enum('kind', ['text', 'image', 'video', 'formula', 'example', 'exercise'])->default('text');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });

        // ---------- Question bank ----------
        Schema::create('questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->foreignId('chapter_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('curriculum_id')->nullable()->constrained('curriculums')->nullOnDelete();
            $t->string('grade', 8)->default('XII');
            $t->enum('type', [
                'multiple_choice', 'true_false', 'short_answer', 'essay',
                'complex_multiple_choice', 'matching', 'numeric',
            ])->default('multiple_choice');
            $t->enum('difficulty', ['easy', 'medium', 'hard', 'mixed'])->default('medium');
            $t->enum('cognitive_level', ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'])->nullable();
            $t->unsignedInteger('estimated_time')->nullable(); // seconds
            $t->longText('question_text');
            $t->longText('explanation')->nullable();
            $t->string('answer_key', 500)->nullable(); // for short_answer/numeric/true_false
            $t->enum('source', ['official', 'curated', 'teacher_created', 'ai_generated', 'imported', 'sample'])->default('curated');
            $t->enum('status', ['pending', 'validated', 'published', 'rejected', 'archived', 'draft'])->default('published');
            $t->char('content_hash', 64)->nullable(); // duplicate detection
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedBigInteger('content_version')->default(1);
            $t->timestamps();
            $t->index(['subject_id', 'grade', 'difficulty', 'status'], 'q_filter_idx');
            $t->index(['topic_id', 'status']);
            $t->unique('content_hash');
        });

        Schema::create('question_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->string('option_key', 5);
            $t->text('option_text');
            $t->boolean('is_correct')->default(false);
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->unique(['question_id', 'option_key']);
        });

        Schema::create('question_tags', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80)->unique();
            $t->string('slug', 100)->unique();
            $t->timestamps();
        });

        Schema::create('question_tag_relations', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tag_id')->constrained('question_tags')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['question_id', 'tag_id']);
        });

        Schema::create('question_sets', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->string('grade', 8)->default('XII');
            $t->enum('purpose', ['practice', 'tryout', 'tka', 'simulation', 'remedial'])->default('practice');
            $t->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unsignedInteger('duration_minutes')->nullable();
            $t->timestamps();
        });

        Schema::create('question_set_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_set_id')->constrained()->cascadeOnDelete();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
            $t->unique(['question_set_id', 'question_id'], 'qs_question_uq');
        });

        // ---------- Tryout / TKA ----------
        Schema::create('tryouts', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('question_set_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('type', ['umum', 'mata_pelajaran', 'tka', 'simulasi', 'remedial'])->default('umum');
            $t->string('grade', 8)->default('XII');
            $t->unsignedInteger('duration_minutes')->default(90);
            $t->unsignedInteger('question_count')->default(0);
            $t->json('question_order')->nullable(); // fixed randomized order per tryout
            $t->enum('status', ['draft', 'scheduled', 'open', 'closed', 'archived'])->default('draft');
            $t->dateTime('starts_at')->nullable();
            $t->dateTime('ends_at')->nullable();
            $t->boolean('auto_publish_result')->default(true);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('tryout_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('position')->default(0);
            $t->unsignedInteger('points')->default(1);
            $t->boolean('is_compulsory')->default(true);
            $t->timestamps();
            $t->unique(['tryout_id', 'question_id']);
        });

        Schema::create('tryout_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tryout_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->uuid('uuid');
            $t->enum('status', ['active', 'completed', 'expired', 'abandoned'])->default('active');
            $t->json('question_order')->nullable();
            $t->json('flagged_questions')->nullable();
            $t->unsignedInteger('answered_count')->default(0);
            $t->dateTime('started_at');
            $t->dateTime('expires_at')->nullable();
            $t->dateTime('submitted_at')->nullable();
            $t->unsignedInteger('duration_seconds')->nullable();
            $t->decimal('score', 7, 2)->nullable();
            $t->decimal('accuracy', 5, 2)->nullable();
            $t->json('statistics')->nullable();
            $t->string('submit_idempotency_key', 100)->nullable();
            $t->timestamps();
            $t->unique('uuid');
            $t->unique(['user_id', 'tryout_id', 'submit_idempotency_key'], 'session_submit_idem_uq');
            $t->index(['user_id', 'status']);
        });

        Schema::create('tryout_answers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tryout_session_id')->constrained()->cascadeOnDelete();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->string('selected_option_key', 10)->nullable();
            $t->text('answer_text')->nullable();
            $t->boolean('is_correct')->nullable();
            $t->unsignedInteger('time_spent_seconds')->default(0);
            $t->boolean('flagged')->default(false);
            $t->unsignedInteger('attempt_number')->default(1);
            $t->string('client_request_id', 100)->nullable(); // idempotency
            $t->timestamps();
            $t->unique(['tryout_session_id', 'question_id'], 'ta_session_question_uq');
            $t->unique('client_request_id');
        });

        // ---------- Learning plan ----------
        Schema::create('learning_targets', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->text('description')->nullable();
            $t->enum('status', ['active', 'achieved', 'abandoned'])->default('active');
            $t->date('target_date')->nullable();
            $t->timestamps();
        });

        Schema::create('learning_tasks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('learning_target_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->enum('kind', ['study_material', 'practice', 'review_mistakes', 'remedial', 'mini_test', 'tryout'])->default('practice');
            $t->enum('status', ['pending', 'in_progress', 'completed', 'overdue'])->default('pending');
            $t->date('due_date')->nullable();
            $t->dateTime('completed_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'status']);
        });

        Schema::create('study_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedInteger('duration_seconds')->default(0);
            $t->unsignedInteger('questions_answered')->default(0);
            $t->unsignedInteger('questions_correct')->default(0);
            $t->date('day');
            $t->timestamps();
            $t->index(['user_id', 'day']);
        });

        // ---------- Attempts & progress ----------
        Schema::create('answer_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tryout_session_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('question_id')->constrained()->cascadeOnDelete();
            $t->foreignId('topic_id')->nullable();
            $t->string('selected_option_key', 10)->nullable();
            $t->text('answer_text')->nullable();
            $t->boolean('is_correct')->nullable();
            $t->unsignedInteger('time_spent_seconds')->default(0);
            $t->enum('source', ['practice', 'tryout', 'tka', 'remedial', 'ai_practice'])->default('practice');
            $t->string('client_request_id', 100)->nullable(); // sync idempotency
            $t->dateTime('answered_at');
            $t->timestamps();
            $t->unique('client_request_id');
            $t->index(['user_id', 'topic_id', 'answered_at']);
            $t->index(['user_id', 'source']);
        });

        Schema::create('progress_snapshots', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->date('day');
            $t->json('payload'); // aggregated stats of the day
            $t->timestamps();
            $t->unique(['user_id', 'day']);
        });

        Schema::create('topic_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('total_attempts')->default(0);
            $t->unsignedInteger('correct_attempts')->default(0);
            $t->decimal('accuracy', 5, 2)->default(0);
            $t->unsignedInteger('avg_time_seconds')->default(0);
            $t->enum('mastery_status', ['not_started', 'learning', 'proficient', 'mastered', 'needs_review'])->default('not_started');
            $t->dateTime('last_activity_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'topic_id']);
        });

        // ---------- User-generated content ----------
        Schema::create('bookmarks', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('bookmarkable_type'); // material | question | topic
            $t->unsignedBigInteger('bookmarkable_id');
            $t->timestamps();
            $t->unique(['user_id', 'bookmarkable_type', 'bookmarkable_id'], 'bookmark_uq');
        });

        Schema::create('notes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->nullableMorphs('noteable'); // material | question | topic
            $t->string('title')->nullable();
            $t->longText('body')->nullable();
            $t->dateTime('deleted_locally_at')->nullable(); // soft-delete sync marker
            $t->timestamps();
        });

        // ---------- Achievements ----------
        Schema::create('achievements', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('icon', 32)->default('trophy');
            $t->json('criteria')->nullable();
            $t->unsignedInteger('points')->default(0);
            $t->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $t->dateTime('unlocked_at');
            $t->timestamps();
            $t->unique(['user_id', 'achievement_id']);
        });

        // ---------- AI ----------
        Schema::create('ai_conversations', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid');
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title')->nullable();
            $t->enum('status', ['active', 'archived'])->default('active');
            $t->timestamps();
            $t->unique('uuid');
        });

        Schema::create('ai_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $t->enum('role', ['user', 'assistant', 'system_note'])->default('user');
            $t->longText('content');
            $t->json('metadata')->nullable();
            $t->timestamps();
        });

        Schema::create('ai_generated_questions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('question_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('ai_request_id')->nullable();
            $t->json('raw_payload')->nullable();
            $t->enum('review_status', ['pending', 'validated', 'published', 'rejected', 'archived'])->default('pending');
            $t->text('validation_error')->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->dateTime('reviewed_at')->nullable();
            $t->timestamps();
        });

        Schema::create('ai_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('request_type', ['chat', 'question_generation', 'explanation', 'remedial_plan'])->default('chat');
            $t->string('model', 80)->nullable();
            $t->enum('status', ['success', 'failed', 'rate_limited', 'invalid_output'])->default('success');
            $t->unsignedInteger('duration_ms')->default(0);
            $t->json('token_usage')->nullable();
            $t->text('error_message')->nullable();
            $t->timestamps();
        });

        // ---------- Sync ----------
        Schema::create('sync_changes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('entity', 60);
            $t->unsignedBigInteger('entity_id');
            $t->string('local_id', 100)->nullable();
            $t->enum('operation', ['create', 'update', 'delete'])->default('create');
            $t->string('content_hash', 64)->nullable();
            $t->json('payload')->nullable();
            $t->dateTime('changed_at');
            $t->timestamps();
            $t->unique(['user_id', 'entity', 'entity_id', 'changed_at'], 'sync_change_uq');
            $t->index(['user_id', 'changed_at']);
        });

        Schema::create('sync_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('device_id', 120)->nullable();
            $t->enum('direction', ['push', 'pull'])->default('push');
            $t->unsignedInteger('changes_in')->default(0);
            $t->unsignedInteger('changes_out')->default(0);
            $t->unsignedInteger('conflicts')->default(0);
            $t->json('report')->nullable();
            $t->string('idempotency_key', 120)->nullable()->unique();
            $t->timestamps();
        });

        Schema::create('device_sessions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('device_id', 120);
            $t->string('platform', 30)->default('android');
            $t->string('app_version', 20)->nullable();
            $t->string('token_identifier', 100)->nullable();
            $t->dateTime('last_seen_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'device_id']);
        });

        // ---------- Backup ----------
        Schema::create('backups', function (Blueprint $t) {
            $t->id();
            $t->uuid('uuid');
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('device_id', 120)->nullable();
            $t->string('name')->nullable();
            $t->unsignedBigInteger('size_bytes')->default(0);
            $t->string('storage_path', 500);
            $t->char('checksum_sha256', 64)->nullable();
            $t->enum('status', ['uploading', 'ready', 'corrupted', 'deleted'])->default('uploading');
            $t->unsignedInteger('record_count')->default(0);
            $t->string('idempotency_key', 120)->nullable();
            $t->timestamps();
            $t->unique('uuid');
            $t->unique(['user_id', 'idempotency_key'], 'backup_idem_uq');
        });

        // ---------- Audit & settings ----------
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $t->string('action', 80);
                $t->string('auditable_type', 100)->nullable();
                $t->unsignedBigInteger('auditable_id')->nullable();
                $t->string('ip_address', 45)->nullable();
                $t->json('meta')->nullable();
                $t->timestamps();
                $t->index(['action', 'created_at']);
            });
        }

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key', 120)->unique();
            $t->text('value')->nullable();
            $t->string('group', 50)->default('general');
            $t->boolean('is_public')->default(false); // visible via app config endpoint
            $t->timestamps();
        });

        // ---------- API tokens (self-contained token auth) ----------
        Schema::create('api_access_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('name', 80)->default('android');
            $t->string('token_hash', 64)->unique(); // sha256 of plain token
            $t->string('device_id', 120)->nullable();
            $t->json('abilities')->nullable();
            $t->dateTime('last_used_at')->nullable();
            $t->dateTime('expires_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        $tables = [
            'api_access_tokens', 'settings', 'audit_logs', 'backups', 'device_sessions',
            'sync_logs', 'sync_changes', 'ai_requests', 'ai_generated_questions', 'ai_messages',
            'ai_conversations', 'user_achievements', 'achievements', 'notes', 'bookmarks',
            'topic_progress', 'progress_snapshots', 'answer_attempts', 'study_sessions',
            'learning_tasks', 'learning_targets', 'tryout_answers', 'tryout_sessions',
            'tryout_questions', 'tryouts', 'question_set_questions', 'question_sets',
            'question_tag_relations', 'question_tags', 'question_options', 'questions',
            'material_sections', 'materials', 'topics', 'chapters', 'curriculum_subjects',
            'subjects', 'curriculums', 'school_classes', 'schools',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
