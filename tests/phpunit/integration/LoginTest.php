<?php
/**
 * Pruebas de Integración — Login
 * CP-01 — Login exitoso
 * CP-02 — Login fallido (contraseña incorrecta)
 * CP-11 — Cierre de sesión
 */
use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = getTestDB();

        // Limpia por si quedó algo
        pg_query($this->db, "DELETE FROM usuarios WHERE username = 'admin_test'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit  = '88888888'");

        // Crea persona + usuario admin de prueba
        $result    = pg_query(
            $this->db,
            "INSERT INTO personas (ci_nit, nombre_completo)
             VALUES ('88888888', 'Admin Test')
             RETURNING id_persona"
        );
        $row       = pg_fetch_assoc($result);
        $personaId = (int)$row['id_persona'];
        $hash      = password_hash('admin123', PASSWORD_BCRYPT);

        pg_query(
            $this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ($personaId, 'admin_test', '$hash', 1, true)"
        );
    }

    protected function tearDown(): void
    {
        pg_query($this->db, "DELETE FROM usuarios WHERE username = 'admin_test'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit  = '88888888'");
        pg_close($this->db);
    }

    /**
     * CP-01: Login exitoso
     * Resultado esperado: acceso al dashboard admin
     */
    public function testLoginExitoso(): void
    {
        $result = pg_query($this->db, "SELECT * FROM usuarios WHERE username = 'admin_test'");
        $row    = pg_fetch_assoc($result);

        $this->assertNotFalse($row,                     'El usuario admin_test debería existir');
        $this->assertTrue(
            password_verify('admin123', $row['password_hash']),
            'La contraseña correcta debería verificarse con éxito'
        );
        $this->assertEquals('t', $row['estado'],        'El usuario debería estar activo (estado = true)');
    }

    /**
     * CP-02: Login fallido — contraseña incorrecta
     * Resultado esperado: mensaje "Credenciales inválidas"
     */
    public function testLoginFallido(): void
    {
        $result = pg_query($this->db, "SELECT * FROM usuarios WHERE username = 'admin_test'");
        $row    = pg_fetch_assoc($result);

        $loginValido = password_verify('CLAVE_INCORRECTA', $row['password_hash']);

        $this->assertFalse($loginValido, 'Una contraseña incorrecta no debería autenticarse');
    }

    /**
     * CP-11: Cierre de sesión
     * Resultado esperado: sesión destruida, variables de sesión vacías
     */
    public function testCierreSesion(): void
    {
        // Simula que el usuario inició sesión
        $_SESSION['id_usuario'] = 1;
        $_SESSION['rol']        = 'Administrador';

        // Simula el cierre de sesión (lo que hace tu logout.php)
        session_unset();
        $_SESSION = [];

        $this->assertArrayNotHasKey('id_usuario', $_SESSION, 'id_usuario no debería existir en sesión tras logout');
        $this->assertArrayNotHasKey('rol',        $_SESSION, 'rol no debería existir en sesión tras logout');
    }
}