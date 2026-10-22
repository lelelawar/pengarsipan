<?php

namespace App\Policies;

use App\Models\Arsip;
use App\Models\User;

class ArsipPolicy
{
    // Semua user login boleh lihat, cetak, dan upload
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Arsip $arsip): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    // Admin: semua, User: hanya milik sendiri
    public function update(User $user, Arsip $arsip): bool
    {
        return $user->isAdmin() || $arsip->created_by === $user->id;
    }

    // Hanya admin
    public function delete(User $user, Arsip $arsip): bool
    {
        return $user->isAdmin();
    }
}