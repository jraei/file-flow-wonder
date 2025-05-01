<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Inertia\Inertia;
use App\Models\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');

        // Validate the sort field to prevent SQL injection
        $allowedSortFields = ['id', 'username', 'email', 'phone', 'saldo', 'user_role_id', 'status'];
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }

        // Start query
        $query = User::query();

        // Apply search if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereRaw('CAST(saldo AS CHAR) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('role', function ($q) use ($search) {
                        $q->where('display_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply sorting
        $query->orderBy($sort, $direction);

        // Get paginated results
        $users = $query->paginate(5)->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'role' => UserRole::all(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = $request->validate([
            'username' => 'required|max:25|alpha_num|unique:users,username',
            'email' => 'required|email:dns|unique:users,email',
            'phone' => 'required|numeric',
            'saldo' => 'required|numeric',
            'user_role_id' => 'required',
            'password' => 'required|min:6|confirmed',
            'status' => 'required'
        ]);


        $validatedData = $request->validate($rules);

        $validatedData['password'] = Hash::make($request->password);

        User::create($validatedData);

        return to_route('users.index')->with('status', ['type' => 'success', 'action' => 'Success', 'text' => 'User has been updated!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            "message" => "Berhasil Show Data!",
            "type" => request('type'),
            "data" => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return response()->json([
            "message" => "Berhasil Show Data Edit!",
            "data" => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'username' => 'required|max:25|alpha_num|unique:users,username,' . $user->id,
            'email' => 'required|email:dns|unique:users,email,' . $user->id,
            'phone' => 'required|numeric',
            'saldo' => 'required|numeric',
            'user_role_id' => 'required',
            'status' => 'required'
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|min:6|confirmed';
        }

        $validatedData = $request->validate($rules);

        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        }

        $user->update($validatedData);

        return to_route('users.index')->with('status', [
            'type' => 'success',
            'action' => 'Success',
            'text' => 'User has been updated!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        User::destroy($user->id);
        return back()->with('success', 'User has been deleted!');
    }
}
