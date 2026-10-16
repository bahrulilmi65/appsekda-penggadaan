<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'superadmin@sekda.go.id')->first();
echo "User: " . $user->name . "\n";
echo "Roles: " . $user->getRoleNames()->join(', ') . "\n";
echo "Has role superadmin: " . ($user->hasRole('superadmin') ? 'YES' : 'NO') . "\n";
echo "Has role admin: " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
echo "Can view dashboard: " . ($user->can('view dashboard') ? 'YES' : 'NO') . "\n";
echo "\nAll roles in DB:\n";
foreach (\Spatie\Permission\Models\Role::all() as $r) {
    echo "  - {$r->name} (guard: {$r->guard_name})\n";
}
