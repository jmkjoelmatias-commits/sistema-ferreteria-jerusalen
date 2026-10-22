<?php
/**
 * Modelo de Usuario en PHP - Consultas relacionales equivalentes (3FN)
 */

class UsuarioModel {
    
    public static function getByUsername($db, $username) {
        $query = "SELECT u.id_usuario, u.username, u.password_hash, r.nombre_rol, u.estado, u.id_persona
                  FROM usuarios u
                  JOIN roles r ON u.id_rol = r.id_rol
                  WHERE u.username = $1";
        $result = pg_query_params($db, $query, [$username]);
        return pg_fetch_assoc($result);
    }

    public static function toggleEstado($db, $id_usuario, $currentState) {
        $newState = $currentState ? 'false' : 'true';
        $query = "UPDATE usuarios SET estado = $1 WHERE id_usuario = $2";
        pg_query_params($db, $query, [$newState, $id_usuario]);
        return $newState === 'true';
    }

    public static function crearCliente($db, $ci_nit, $nombre_completo, $telefono, $direccion, $username, $passwordHash) {
        pg_query($db, "BEGIN");
        try {
            // 1. Insertar Persona
            $q1 = "INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion) VALUES ($1, $2, $3, $4) RETURNING id_persona";
            $res1 = pg_query_params($db, $q1, [$ci_nit, $nombre_completo, $telefono, $direccion]);
            $id_persona = pg_fetch_result($res1, 0, 0);

            // 2. Insertar Usuario (Rol 2 = Cliente)
            $q2 = "INSERT INTO usuarios (id_persona, username, password_hash, id_rol) VALUES ($1, $2, $3, 2)";
            pg_query_params($db, $q2, [$id_persona, $username, $passwordHash]);

            // 3. Insertar Cliente
            $q3 = "INSERT INTO clientes (id_persona) VALUES ($1)";
            pg_query_params($db, $q3, [$id_persona]);

            pg_query($db, "COMMIT");
            return true;
        } catch (Exception $e) {
            pg_query($db, "ROLLBACK");
            return false;
        }
    }
}
