<?php
/**
 * Pruebas de Usuarios
 * CP-04 — Crear usuario
 * CP-05 — Editar usuario
 * CP-06 — Eliminar usuario
 */
use PHPUnit\Framework\TestCase;
 
class UsuarioTest extends TestCase
{
    private $db;
    private int $personaId;
 
    protected function setUp(): void
    {
        $this->db = getTestDB();
 
        // Limpia por si quedó algo de una prueba anterior
        pg_query($this->db, "DELETE FROM usuarios WHERE username LIKE 'test_vendedor%'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit = '99999999'");
 
        // Crea la persona base que necesitan los usuarios de prueba
        $result = pg_query(
            $this->db,
            "INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion)
             VALUES ('99999999', 'Usuario Test', '77777777', 'Calle Test 123')
             RETURNING id_persona"
        );
        $row = pg_fetch_assoc($result);
        $this->personaId = (int)$row['id_persona'];
    }
 
    protected function tearDown(): void
    {
        pg_query($this->db, "DELETE FROM usuarios WHERE username LIKE 'test_vendedor%'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit = '99999999'");
        pg_close($this->db);
    }
 
    /**
     * CP-04: Crear usuario
     * Resultado esperado: usuario guardado en BD
     */
    public function testCrearUsuario(): void
    {
        $hash   = password_hash('clave123', PASSWORD_BCRYPT);
        $result = pg_query(
            $this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ({$this->personaId}, 'test_vendedor', '$hash', 2, true)"
        );
 
        $this->assertNotFalse($result, 'La inserción del usuario no debería fallar');
 
        $check = pg_fetch_assoc(pg_query($this->db, "SELECT * FROM usuarios WHERE username = 'test_vendedor'"));
 
        $this->assertNotFalse($check,           'El usuario debería existir en la BD');
        $this->assertEquals('test_vendedor',    $check['username']);
        $this->assertEquals(2, (int)$check['id_rol'], 'Debería tener rol Vendedor (id=2)');
    }
 
    /**
     * CP-05: Editar usuario
     * Resultado esperado: datos actualizados en BD
     */
    public function testEditarUsuario(): void
    {
        pg_query(
            $this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ({$this->personaId}, 'test_vendedor', 'hash_prueba', 2, true)"
        );
 
        pg_query($this->db, "UPDATE usuarios SET username = 'test_vendedor_v2' WHERE username = 'test_vendedor'");
 
        $check = pg_fetch_assoc(pg_query($this->db, "SELECT username FROM usuarios WHERE username = 'test_vendedor_v2'"));
 
        $this->assertNotFalse($check,            'El usuario modificado debería existir');
        $this->assertEquals('test_vendedor_v2',  $check['username'], 'El username debería haberse actualizado');
 
        // Limpieza del nombre cambiado
        pg_query($this->db, "DELETE FROM usuarios WHERE username = 'test_vendedor_v2'");
    }
 
    /**
     * CP-06: Eliminar usuario
     * Resultado esperado: registro borrado de BD
     */
    public function testEliminarUsuario(): void
    {
        pg_query(
            $this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ({$this->personaId}, 'test_vendedor', 'hash_prueba', 2, true)"
        );
 
        pg_query($this->db, "DELETE FROM usuarios WHERE username = 'test_vendedor'");
 
        $result = pg_query($this->db, "SELECT * FROM usuarios WHERE username = 'test_vendedor'");
        $check  = pg_fetch_assoc($result);
 
        $this->assertFalse($check, 'El usuario debería haber sido eliminado de la BD');
    }
}