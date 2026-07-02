<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Re-uploading the same relative path should overwrite in place rather than
     * create duplicates, so (project_id, path) must be unique. Dedupe any
     * existing collisions (keeping the latest per group) before adding the
     * index. Done via the query builder (separate SELECT + DELETE) so it is
     * portable across MySQL and SQLite.
     */
    public function up(): void
    {
        $keepIds = DB::table('project_files')
            ->selectRaw('MAX(id) AS id')
            ->groupBy('project_id', 'path')
            ->pluck('id')
            ->all();

        if (! empty($keepIds)) {
            DB::table('project_files')->whereNotIn('id', $keepIds)->delete();
        }

        Schema::table('project_files', function (Blueprint $table) {
            $table->unique(['project_id', 'path'], 'project_files_project_path_unique');
        });
    }

    public function down(): void
    {
        Schema::table('project_files', function (Blueprint $table) {
            $table->dropUnique('project_files_project_path_unique');
        });
    }
};
