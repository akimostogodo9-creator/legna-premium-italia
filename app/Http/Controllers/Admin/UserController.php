<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// F-AUSR-01/02 : comptes administrateurs
class UserController extends Controller
{
    public function __construct(private readonly AdminUserService $users) {}

    public function index(Request $request): Response
    {
        return Inertia::render('admin/users/index', [
            'users' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'is_active', 'last_login_at'])
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->is_active,
                    'last_login_at' => $user->last_login_at?->format('d/m/Y H:i'),
                    'is_current' => $user->is($request->user()),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/users/create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->users->create($request->validated('name'), $request->validated('email'));

        return to_route('admin.users.index')->with('success', __('admin.users.created'));
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->users->toggleActive($user, $request->user());

        return back()->with('success', __('admin.messages.saved'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->users->delete($user, $request->user());

        return back()->with('success', __('admin.messages.deleted'));
    }
}
