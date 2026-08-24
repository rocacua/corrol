<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Services\AdminService;
use App\Services\MailingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    protected AdminService $adminService;
    protected MailingService $mailingService;

    public function __construct(AdminService $adminService, MailingService $mailingService)
    {
        $this->adminService = $adminService;
        $this->mailingService = $mailingService;
    }

    // Ruta secreta de reclamo con SETUP_SECRET_KEY
    public function claimAdmin(string $secretKey): RedirectResponse
    {
        if ($secretKey !== config('app.setup_secret_key')) {
            abort(404);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['is_admin' => true]);

        return redirect()->route('admin.dashboard')->with('success', '¡Has activado tus privilegios de Administrador!');
    }

    public function dashboard(): View
    {
        $stats = $this->adminService->getDashboardStats();
        return view('admin.dashboard', compact('stats'));
    }

    public function editResource(int $id): View
    {
        $resource = Resource::findOrFail($id);
        $users = $this->adminService->getAllUsers();

        return view('admin.resources.edit', compact('resource', 'users'));
    }

    public function updateResource(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'privacy' => ['required', 'in:public,private'],
            'game' => ['nullable', 'string', 'max:100'],
            'campaign' => ['nullable', 'string', 'max:100'],
            'author' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $this->adminService->reassignResourceOwner($id, $validated['user_id'] ?? null, $validated);

        return redirect()->route('resources.show', $id)
            ->with('success', '¡Recurso actualizado con éxito por el Administrador!');
    }

    public function mailingForm(Request $request): View
    {
        $filters = $request->only(['game', 'tag', 'has_resource_type']);
        $users = $this->adminService->getUsersForMailing($filters);

        return view('admin.mailing', compact('users', 'filters'));
    }

    public function sendMailing(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'game' => ['nullable', 'string'],
            'tag' => ['nullable', 'string'],
            'has_resource_type' => ['nullable', 'string'],
        ]);

        $users = $this->adminService->getUsersForMailing($request->only(['game', 'tag', 'has_resource_type']));
        $count = $this->mailingService->sendBulkMail($users, $validated['subject'], $validated['message']);

        return redirect()->back()->with('success', "¡Correo enviado a {$count} usuarios correctamente!");
    }
}