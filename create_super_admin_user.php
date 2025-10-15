<?php

require __DIR__."/vendor/autoload.php";

$app = require_once __DIR__."/bootstrap/app.php";

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Role;

$user = User::where("email", "joelson@jracing.dev.br")->first();

if (!$user) {
    $user = User::create([
        "name" => "Joelson Racing",
        "email" => "joelson@jracing.dev.br",
        "password" => bcrypt("120507"),
        "type" => "super admin",
        "email_verified_at" => now(),
    ]);
}

$role = Role::where("name", "super admin")->first();

if ($role) {
    $user->addRole($role);
    echo "Usuário joelson@jracing.dev.br criado e papel 'super admin' atribuído.";
} else {
    echo "Papel 'super admin' não encontrado.";
}

?>
