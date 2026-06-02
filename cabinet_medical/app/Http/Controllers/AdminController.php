<?php

namespace App\Http\Controllers;

use App\Mail\UserApprovedMail;
use App\Models\Appointment;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(private ActivityLogService $activityLogs)
    {
        //
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'total_users' => User::count(),
                'total_doctors' => User::doctors()->count(),
                'total_patients' => User::patients()->count(),
                'total_services' => Service::count(),
                'total_appointments' => Appointment::count(),
                'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            ],
            'appointments' => Appointment::with(['patient', 'doctor', 'service'])
                ->whereDate('appointment_date', today())
                ->orderBy('appointment_time')
                ->get(),
            'activityLogs' => ActivityLog::with('user')
                ->latest('created_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function users(): View
    {
        $users = User::orderBy('is_approved')
            ->orderBy('name')
            ->get();

        return view('admin.users', [
            'roles' => User::roles(),
            'users' => $users,
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => ['required', 'in:'.implode(',', User::roles())],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_approved' => $request->boolean('is_approved'),
        ]);

        $this->activityLogs->userCreated($user, $request->user());

        return back()->with('success', __('messages.created_successfully'));
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $wasApproved = $user->is_approved;
        $oldRole = $user->role;

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'role' => ['required', 'in:'.implode(',', User::roles())],
            'is_approved' => ['nullable', 'boolean'],
        ]);

        $updates = [
            'name' => $data['name'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'role' => $data['role'],
            'is_approved' => $request->boolean('is_approved'),
        ];

        if (! empty($data['password'])) {
            $updates['password'] = Hash::make($data['password']);
        }

        $user->update($updates);

        if ($oldRole !== $user->role) {
            $this->activityLogs->roleChanged($user, $oldRole, $user->role, $request->user());
        }

        if (! $wasApproved && $user->is_approved) {
            Mail::to($user->email)->send(new UserApprovedMail($user));
        }

        return back()->with('success', __('messages.user_updated'));
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403);

        $user->delete();

        return back()->with('success', __('messages.deleted_successfully'));
    }

    public function services(): View
    {
        return view('admin.services', [
            'services' => Service::withCount('appointments')->orderBy('name')->get(),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        Service::create($this->validateService($request));

        return back()->with('success', __('messages.created_successfully'));
    }

    public function updateService(Request $request, Service $service): RedirectResponse
    {
        $service->update($this->validateService($request));

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroyService(Service $service): RedirectResponse
    {
        $service->delete();

        return back()->with('success', __('messages.deleted_successfully'));
    }

    public function appointments(): View
    {
        return view('admin.appointments', [
            'appointments' => Appointment::with(['patient', 'doctor', 'service'])
                ->latest()
                ->get(),
        ]);
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'duration' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
