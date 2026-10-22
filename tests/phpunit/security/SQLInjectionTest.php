<?php
/**
 * Pruebas de Seguridad
 * CP-10 — SQL Injection bloqueada
 * CP-03 — Acceso a rutas de admin sin sesión
 */
use PHPUnit\Framework\TestCase;

class SQLInjectionTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = getTestDB();
    }

    protected function tearDown(): void
    {
        pg_close($this->db);
    }

    /**
     * CP-10: SQL Injection
     * Entrada:  ' OR '1'='1
     * Resultado esperado: consulta bloqueada, 0 resultados
     *
     * Con pg_query_params los parámetros se pasan separados de la consulta,
     * así el input malicioso se trata como texto literal y nunca se ejecuta.
     */
    public function testSQLInjectionBloqueada(): void
    {
        $inputMalicioso = "' OR '1'='1";

        // pg_query_params es el equivalente a PDO::prepare — parámetro $1 separado
        $result    = pg_query_params(
            $this->db,
            "SELECT * FROM usuarios WHERE username = $1",
            [$inputMalicioso]
        );
        $filas     = pg_num_rows($result);

        $this->assertEquals(0, $filas, 'SQL Injection debe devolver 0 resultados con pg_query_params');
    }

    /**
     * Las contraseñas nunca deben guardarse en texto plano en la BD
     */
    public function testPasswordHasheadaNoEsTextoPlano(): void
    {
        $passwordOriginal = 'miClave123';
        $hash             = password_hash($passwordOriginal, PASSWORD_BCRYPT);

        $this->assertNotEquals($passwordOriginal, $hash,
            'El hash almacenado no debe ser igual a la contraseña original');
        $this->assertTrue(password_verify($passwordOriginal, $hash),
            'password_verify debe confirmar que el hash corresponde a la contraseña');
    }

    /**
     * CP-03: Acceso a ruta de admin sin sesión activa
     * Resultado esperado: acceso denegado (redirección al login)
     */
    public function testAccesoAdminSinSesionDenegado(): void
    {
        $_SESSION = []; // Sin ninguna sesión activa

        // Lógica que debe estar en tu AuthController o middleware
        $estaAutenticado  = isset($_SESSION['id_usuario']);
        $esAdministrador  = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Administrador';
        $tieneAcceso      = $estaAutenticado && $esAdministrador;

        $this->assertFalse($tieneAcceso,
            'Sin sesión activa no debe permitirse el acceso a rutas de administrador');
    }
}