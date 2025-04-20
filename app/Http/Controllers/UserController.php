<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $users = User::where('tenant_id', $tenantId)->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $stores = Auth::user()->tenant->stores()->where('is_active', true)->get();
        return view('users.create', compact('stores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $data = $request->validated();
            $data['tenant_id'] = Auth::user()->tenant_id;

            $user = $this->userService->create($data);

            return redirect()->route('users.show', $user->id)
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $stores = Auth::user()->tenant->stores()->where('is_active', true)->get();
        $assignedStores = $user->stores->pluck('id')->toArray();

        return view('users.edit', compact('user', 'stores', 'assignedStores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        try {
            $this->userService->update($user, $request->validated());

            return redirect()->route('users.show', $user->id)
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $this->userService->delete($user);
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        try {
            $this->userService->toggleActiveStatus($user);
            return back()->with('success', 'User status updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating user status: ' . $e->getMessage());
        }
    }

    /**
     * Display user profile.
     */
    public function profile()
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password|password',
            'password' => 'nullable|min:8|confirmed',
        ]);

        try {
            $data = $request->only(['name', 'email', 'phone', 'address']);

            if ($request->filled('password')) {
                $data['password'] = $request->password;
            }

            if ($request->hasFile('avatar')) {
                $data['avatar'] = $request->file('avatar');
            }

            $this->userService->update($user, $data);

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }
}
