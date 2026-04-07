<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TicketComment;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketCommentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_ticket::comment');
    }

    public function view(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('view_ticket::comment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_ticket::comment');
    }

    public function update(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('update_ticket::comment');
    }

    public function delete(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('delete_ticket::comment');
    }

    public function restore(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('restore_ticket::comment');
    }

    public function forceDelete(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('force_delete_ticket::comment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_ticket::comment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_ticket::comment');
    }

    public function replicate(AuthUser $authUser, TicketComment $ticketComment): bool
    {
        return $authUser->can('replicate_ticket::comment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_ticket::comment');
    }

}