$role = App\Models\Role::firstOrCreate(['name' => 'admin'], ['description' => 'Administrator']);
$user = App\Models\User::firstOrCreate(['email' => 'admin@admin.com'], ['password' => Illuminate\Support\Facades\Hash::make('password123'), 'is_active' => 1, 'email_verified_at' => now()]);
$user->password = Illuminate\Support\Facades\Hash::make('password123');
$user->save();
App\Models\UserProfile::updateOrCreate(['user_id' => $user->id], ['full_name' => 'System Admin', 'user_type' => 'staff', 'school_id_number' => 'ADMIN001', 'contact_no' => '0000000000']);
if (!$user->roles->contains($role->id)) { $user->roles()->attach($role); }
echo "Admin user created successfully.";
