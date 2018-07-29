<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only(['edit', 'update']);
        $this->middleware('admin')->except(['edit', 'update']);
    }

    public function index()
    {
        return view('users.index');
    }

    public function datatables()
    {
        return datatables()->of(User::query())->toJson();
    }

    protected function canUpdate(User $user)
    {
        return Auth::user()->isAdmin() or Auth::user()->id === $user->id;
    }

    public function edit(User $user)
    {
        abort_unless($this->canUpdate($user), 403);

        return view('users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($this->canUpdate($user), 403);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        if (null !== $password = $request->input('password')) {
            $user->password = bcrypt($password);
        }
        $saved = $user->save();

        $redirect = redirect()->back();

        if ($saved) {
            return $redirect->with('status:ok', 'The user successfully updated.');
        } else {
            return $redirect->with('status:error', 'Unable to update user.');
        }
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            $deleted = false;
        } else {
            $deleted = $user->delete();
        }

        $redirect = redirect()->back();

        if ($deleted) {
            return $redirect->with('status:ok', 'The user successfully deleted.');
        } else {
            return $redirect->with('status:error', 'Unable to delete user.');
        }
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create($request->input());

        if ($user) {
            return redirect()->route('users.edit', [$user]);
        } else {
            return redirect()->back()->with('status:error', 'Unable to create user.');
        }
    }
}
