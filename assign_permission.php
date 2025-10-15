<?php

require __DIR__."/vendor/autoload.php";

$app = require_once __DIR__."/bootstrap/app.php";

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\Permission;

$role = Role::where("name", "super admin")->first();

if ($role) {
    $permission = Permission::where("name", "user manage")->first();

    if (!$permission) {
        $permission = Permission::create(["name" => "user manage", "display_name" => "Gerenciar Usuários", "description" => "Permite gerenciar usuários", "module" => "Base"]);
    }

    if (!$role->hasPermission("user manage")) {
        $role->givePermission($permission);
        echo "Permissão 'user manage' atribuída ao papel 'super admin'.";
    } else {
        echo "O papel 'super admin' já possui a permissão 'user manage'.";
    }
} else {
    echo "Papel 'super admin' não encontrado.";
}

?>
