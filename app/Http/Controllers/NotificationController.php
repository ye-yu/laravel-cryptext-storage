<?php

namespace App\Http\Controllers;

use App\Utils\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JetBrains\PhpStorm\ArrayShape;

class NotificationController extends Controller
{
    public function paginateNotifications(Request $request, int $pageSize = 10): LengthAwarePaginator
    {
        if ($pageSize < 1) abort(400, "Page size cannot be less than 1.");
        $user = Utils::user($request);
        return $user->notifications()->paginate($pageSize);
    }

    public function readAll(Request $request): Response
    {
        $user = Utils::user($request);
        $user->unreadNotifications->markAsRead();
        return response()->noContent();
    }

    #[ArrayShape(["total_read" => "int"])]
    public function markSomeAsRead(Request $request, int $pageSize): array
    {
        if ($pageSize < 1) abort(400, "Page size cannot be less than 1.");
        $user = Utils::user($request);
        $totalRead = $user->unreadNotifications()->take($pageSize)->update(['read_at' => now()]);
        return [
            "total_read" => $totalRead
        ];
    }
}
