<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table): void {
            $table->dropUnique('organization_invitations_unique_pending');
            $table->boolean('pending_marker')->nullable()->after('accepted_at');
        });

        DB::table('organization_invitations')->update([
            'pending_marker' => null,
        ]);

        $pendingInvitationGroups = DB::table('organization_invitations')
            ->select('organization_id', 'email')
            ->whereNull('accepted_at')
            ->groupBy('organization_id', 'email')
            ->get();

        foreach ($pendingInvitationGroups as $pendingInvitationGroup) {
            $activeInvitationId = DB::table('organization_invitations')
                ->where('organization_id', $pendingInvitationGroup->organization_id)
                ->where('email', $pendingInvitationGroup->email)
                ->whereNull('accepted_at')
                ->orderByDesc('expires_at')
                ->orderByDesc('id')
                ->value('id');

            if ($activeInvitationId !== null) {
                DB::table('organization_invitations')
                    ->where('id', $activeInvitationId)
                    ->update(['pending_marker' => true]);
            }
        }

        Schema::table('organization_invitations', function (Blueprint $table): void {
            $table->unique(
                ['organization_id', 'email', 'pending_marker'],
                'organization_invitations_unique_pending'
            );
        });
    }

    public function down(): void
    {
        Schema::table('organization_invitations', function (Blueprint $table): void {
            $table->dropUnique('organization_invitations_unique_pending');
            $table->dropColumn('pending_marker');
        });

        Schema::table('organization_invitations', function (Blueprint $table): void {
            $table->unique(
                ['organization_id', 'email', 'accepted_at'],
                'organization_invitations_unique_pending'
            );
        });
    }
};
