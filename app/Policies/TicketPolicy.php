<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Ticket;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_ticket');
    }

    public function view(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('view_ticket');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_ticket');
    }

    public function update(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('update_ticket');
    }

    public function delete(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('delete_ticket');
    }

    public function restore(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('restore_ticket');
    }

    public function forceDelete(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('force_delete_ticket');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_ticket');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_ticket');
    }

    public function replicate(AuthUser $authUser, Ticket $ticket): bool
    {
        return $authUser->can('replicate_ticket');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_ticket');
    }

}