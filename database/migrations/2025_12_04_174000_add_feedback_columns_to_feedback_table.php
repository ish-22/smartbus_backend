<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Add type column if it doesn't exist
            if (!Schema::hasColumn('feedback', 'type')) {
                $table->enum('type', ['complaint', 'suggestion', 'praise', 'general'])->default('general');
            }
            
            // Add route_id if it doesn't exist
            if (!Schema::hasColumn('feedback', 'route_id')) {
                $table->foreignId('route_id')->nullable()->constrained()->onDelete('set null');
            }
            
            // Add admin_response if it doesn't exist
            if (!Schema::hasColumn('feedback', 'admin_response')) {
                $table->text('admin_response')->nullable();
            }
            
            // Add responded_by if it doesn't exist
            if (!Schema::hasColumn('feedback', 'responded_by')) {
                $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            }
            
            // Add responded_at if it doesn't exist
            if (!Schema::hasColumn('feedback', 'responded_at')) {
                $table->timestamp('responded_at')->nullable();
            }
        });

        // Update existing records to have a default type if type column was just added
        if (Schema::hasColumn('feedback', 'type')) {
            DB::table('feedback')->whereNull('type')->update(['type' => 'general']);
        }
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            // Drop columns in reverse order
            if (Schema::hasColumn('feedback', 'responded_at')) {
                $table->dropColumn('responded_at');
            }
            
            if (Schema::hasColumn('feedback', 'responded_by')) {
                $table->dropForeign(['responded_by']);
                $table->dropColumn('responded_by');
            }
            
            if (Schema::hasColumn('feedback', 'admin_response')) {
                $table->dropColumn('admin_response');
            }
            
            if (Schema::hasColumn('feedback', 'route_id')) {
                $table->dropForeign(['route_id']);
                $table->dropColumn('route_id');
            }
            
            if (Schema::hasColumn('feedback', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
