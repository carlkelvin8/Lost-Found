<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Creates the core application tables that were missing from the repository.
     * Timestamped before add_ai_analysis migration so it runs first on fresh DBs.
     */
    public function up(): void
    {
        // users table already exists (framework migration) - add missing columns
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
        });

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->primary(['user_id', 'role_id']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_profiles')) {
            Schema::create('user_profiles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->primary();
                $table->string('full_name');
                $table->string('school_id_number')->nullable();
                $table->string('user_type', 20)->default('student');
                $table->string('department_id', 60)->nullable();
                $table->string('contact_no', 40)->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('locations')) {
            Schema::create('locations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('details')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('item_reports')) {
            Schema::create('item_reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_type', 10);
                $table->unsignedBigInteger('reporter_user_id');
                $table->unsignedBigInteger('owner_user_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('item_name', 190)->nullable();
                $table->text('item_description');
                $table->string('brand_model', 190)->nullable();
                $table->string('color', 60)->nullable();
                $table->date('incident_date')->nullable();
                $table->time('incident_time')->nullable();
                $table->unsignedBigInteger('location_id')->nullable();
                $table->text('circumstances')->nullable();
                $table->string('contact_override', 255)->nullable();
                $table->string('status', 20)->default('pending');
                $table->unsignedBigInteger('matched_report_id')->nullable();
                $table->decimal('matched_score', 5, 2)->nullable();
                $table->timestamps();

                $table->index('report_type');
                $table->index('status');
                $table->foreign('reporter_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('owner_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
                $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
                $table->foreign('matched_report_id')->references('id')->on('item_reports')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('report_photos')) {
            Schema::create('report_photos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->string('photo_url');
                $table->string('caption', 255)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->foreign('report_id')->references('id')->on('item_reports')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('report_status_history')) {
            Schema::create('report_status_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->string('old_status', 20)->nullable();
                $table->string('new_status', 20);
                $table->unsignedBigInteger('changed_by_user_id')->nullable();
                $table->string('note', 255)->nullable();
                $table->timestamp('changed_at')->nullable();

                $table->index('report_id');
                $table->foreign('report_id')->references('id')->on('item_reports')->onDelete('cascade');
                $table->foreign('changed_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('claims')) {
            Schema::create('claims', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('report_id');
                $table->unsignedBigInteger('claimant_user_id');
                $table->text('proof_text');
                $table->string('status', 20)->default('pending');
                $table->unsignedBigInteger('reviewed_by_user_id')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->foreign('report_id')->references('id')->on('item_reports')->onDelete('cascade');
                $table->foreign('claimant_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('reviewed_by_user_id')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('claim_documents')) {
            Schema::create('claim_documents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('claim_id');
                $table->string('file_url');
                $table->string('file_type')->nullable();
                $table->string('file_hash_sha256', 64)->nullable();
                $table->timestamp('created_at')->nullable();

                $table->foreign('claim_id')->references('id')->on('claims')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('report_matches')) {
            Schema::create('report_matches', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lost_report_id');
                $table->unsignedBigInteger('found_report_id');
                $table->decimal('score', 5, 2)->default(0);
                $table->string('method', 20)->nullable();
                $table->string('status', 20)->default('suggested');
                $table->timestamps();

                $table->unique(['lost_report_id', 'found_report_id']);
                $table->foreign('lost_report_id')->references('id')->on('item_reports')->onDelete('cascade');
                $table->foreign('found_report_id')->references('id')->on('item_reports')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 100);
                $table->string('entity_type', 100)->nullable();
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->text('meta_json')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('user_id');
                $table->index('action');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('report_matches');
        Schema::dropIfExists('claim_documents');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('report_status_history');
        Schema::dropIfExists('report_photos');
        Schema::dropIfExists('item_reports');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
