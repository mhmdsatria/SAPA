<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;

class ComplaintPolicy
{
    public function view(?User $user, Complaint $complaint): bool
    {
        if ($complaint->status === Complaint::STATUS_APPROVED) {
            return true;
        }
        if ($user === null) {
            return false;
        }

        return $user->id === $complaint->user_id || $this->canModerateRegion($user, $complaint);
    }

    public function update(User $user, Complaint $complaint): bool
    {
        if ($this->canModerateRegion($user, $complaint)) {
            return true;
        }

        return $user->id === $complaint->user_id && $complaint->isEditableByReporter();
    }

    public function delete(User $user, Complaint $complaint): bool
    {
        return $user->id === $complaint->user_id && $complaint->isEditableByReporter();
    }

    public function moderate(User $user, Complaint $complaint): bool
    {
        return $this->canModerateRegion($user, $complaint);
    }

    private function canModerateRegion(User $user, Complaint $complaint): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (! $user->isAdminDaerah() || $complaint->region_id === null) {
            return false;
        }

        return $user->regionalAssignments()->where('region_id', $complaint->region_id)->exists();
    }
}
