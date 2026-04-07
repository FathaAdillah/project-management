<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TicketPriority;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPriorityPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_ticket::priority');
    }

    public function view(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('view_ticket::priority');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_ticket::priority');
    }

    public function update(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('update_ticket::priority');
    }

    public function delete(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('delete_ticket::priority');
    }

    public function restore(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('restore_ticket::priority');
    }

    public function forceDelete(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('force_delete_ticket::priority');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_ticket::priority');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_ticket::priority');
    }

    public function replicate(AuthUser $authUser, TicketPriority $ticketPriority): bool
    {
        return $authUser->can('replicate_ticket::priority');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_ticket::priority');
    }

}