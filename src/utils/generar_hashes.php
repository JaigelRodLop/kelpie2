<?php
// Script temporal para generar hashes de contraseñas
$usuarios = [
    ["nombre" => "Bryan Romero", "correo" => "bromero@kelpie.com", "password" => "admin123", "rol_id" => 1],
    ["nombre" => "Jairo Rodriguez", "correo" => "jrodriguez@kelpie.com", "password" => "tec123", "rol_id" => 2],
    ["nombre" => "Angel Portal", "correo" => "aportal@kelpie.com", "password" => "user123", "rol_id" => 3],
];

foreach ($usuarios as $u) {
    $hash = password_hash($u["password"], PASSWORD_BCRYPT);
    echo "INSERT INTO usuarios (nombre, correo, contraseña, rol_id) VALUES ('{$u["nombre"]}', '{$u["correo"]}', '{$hash}', {$u["rol_id"]});\n";
}
?>