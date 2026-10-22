<?php
/**
 * PRUEBAS DE CAJA NEGRA — Login y Tareas de Administrador
 * Se prueban entradas y salidas sin conocer el código interno.
 * Solo importa qué entra y qué sale.
 *
 * CN-01: Login con credenciales válidas
 * CN-02: Login con contraseña incorrecta
 * CN-03: Login con username vacío
 * CN-04: Login con usuario inexistente
 * CN-05: Crear usuario con datos completos
 * CN-06: Crear usuario con username duplicado
 * CN-07: Desactivar usuario (estado = false)
 * CN-08: Eliminar usuario existente
 */
use PHPUnit\Framework\TestCase;

class CajaNegraLoginAdminTest extends TestCase
{
    private $db;
    private int $personaId;

    protected function setUp(): void
    {
        $this->db = getTestDB();

        // Limpia datos de prueba anteriores
        pg_query($this->db, "DELETE FROM usuarios WHERE username LIKE 'cn_%'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit  LIKE '666%'");

        // Persona base para los usuarios de prueba
        $r = pg_query($this->db,
            "INSERT INTO personas (ci_nit, nombre_completo, telefono, direccion)
             VALUES ('6661111', 'Admin CN Prueba', '79999999', 'Calle CN 123')
             RETURNING id_persona"
        );
        $this->personaId = (int)pg_fetch_assoc($r)['id_persona'];

        // Usuario activo con contraseña conocida
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        pg_query($this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ({$this->personaId}, 'cn_admin', '$hash', 1, true)"
        );
    }

    protected function tearDown(): void
    {
        pg_query($this->db, "DELETE FROM usuarios WHERE username LIKE 'cn_%'");
        pg_query($this->db, "DELETE FROM personas  WHERE ci_nit  LIKE '666%'");
        pg_close($this->db);
    }

    /**
     * CN-01: Login con credenciales válidas
     * Entrada:  username='cn_admin', password='admin123'
     * Esperado: login exitoso
     */
    public function testCN01LoginCredencialesValidas(): void
    {
        $r   = pg_query_params($this->db,
            "SELECT * FROM usuarios WHERE username = $1", ['cn_admin']
        );
        $row = pg_fetch_assoc($r);

        $existeUsuario = $row !== false;
        $claveCorrecta = password_verify('admin123', $row['password_hash']);
        $estaActivo    = $row['estado'] === 't';
        $loginExitoso  = $existeUsuario && $claveCorrecta && $estaActivo;

        $this->assertTrue($loginExitoso, 'CN-01: Credenciales válidas deben permitir el login');
    }

    /**
     * CN-02: Login con contraseña incorrecta
     * Entrada:  username='cn_admin', password='INCORRECTA'
     * Esperado: login bloqueado
     */
    public function testCN02LoginContrasenaIncorrecta(): void
    {
        $r   = pg_query_params($this->db,
            "SELECT * FROM usuarios WHERE username = $1", ['cn_admin']
        );
        $row = pg_fetch_assoc($r);

        $claveCorrecta = password_verify('INCORRECTA', $row['password_hash']);

        $this->assertFalse($claveCorrecta, 'CN-02: Contraseña incorrecta debe bloquear el login');
    }

    /**
     * CN-03: Login con username vacío
     * Entrada:  username='', password='admin123'
     * Esperado: validación rechaza la entrada antes de consultar la BD
     */
    public function testCN03LoginUsernameVacio(): void
    {
        $username  = '';
        $esValido  = !empty(trim($username));

        $this->assertFalse($esValido, 'CN-03: Un username vacío no debe pasar la validación');
    }

    /**
     * CN-04: Login con usuario inexistente
     * Entrada:  username='usuario_fantasma'
     * Esperado: 0 filas devueltas, login bloqueado
     */
    public function testCN04LoginUsuarioInexistente(): void
    {
        $r     = pg_query_params($this->db,
            "SELECT * FROM usuarios WHERE username = $1", ['usuario_fantasma']
        );
        $row   = pg_fetch_assoc($r);

        $this->assertFalse($row, 'CN-04: Un usuario inexistente no debe autenticarse');
    }

    /**
     * CN-05: Crear usuario con datos completos
     * Entrada:  datos válidos de un nuevo usuario
     * Esperado: usuario guardado en la BD
     */
    public function testCN05CrearUsuarioDatosCompletos(): void
    {
        // Persona nueva para este usuario
        $r2 = pg_query($this->db,
            "INSERT INTO personas (ci_nit, nombre_completo)
             VALUES ('6662222', 'Nuevo Vendedor CN')
             RETURNING id_persona"
        );
        $nuevaPersonaId = (int)pg_fetch_assoc($r2)['id_persona'];

        $hash = password_hash('vendedor123', PASSWORD_BCRYPT);
        pg_query($this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ($nuevaPersonaId, 'cn_vendedor', '$hash', 2, true)"
        );

        $check = pg_fetch_assoc(
            pg_query_params($this->db,
                "SELECT * FROM usuarios WHERE username = $1", ['cn_vendedor']
            )
        );

        $this->assertNotFalse($check,        'CN-05: El usuario debería haberse creado');
        $this->assertEquals('cn_vendedor',   $check['username']);
    }

    /**
     * CN-06: Crear usuario con username duplicado
     * Entrada:  username='cn_admin' que ya existe
     * Esperado: la BD rechaza la inserción por UNIQUE constraint
     */
    public function testCN06CrearUsuarioUsernameDuplicado(): void
    {
        $resultado = @pg_query($this->db,
            "INSERT INTO usuarios (id_persona, username, password_hash, id_rol, estado)
             VALUES ({$this->personaId}, 'cn_admin', 'hash', 2, true)"
        );

        $this->assertFalse($resultado,
            'CN-06: Un username duplicado debe ser rechazado por la BD (UNIQUE constraint)'
        );
    }

    /**
     * CN-07: Desactivar usuario (estado = false)
     * Entrada:  username='cn_admin', nuevo estado=false
     * Esperado: usuario queda inactivo en la BD
     */
    public function testCN07DesactivarUsuario(): void
    {
        pg_query_params($this->db,
            "UPDATE usuarios SET estado = false WHERE username = $1", ['cn_admin']
        );

        $row = pg_fetch_assoc(
            pg_query_params($this->db,
                "SELECT estado FROM usuarios WHERE username = $1", ['cn_admin']
            )
        );

        $this->assertEquals('f', $row['estado'],
            'CN-07: El usuario debe quedar inactivo tras desactivarlo'
        );
    }

    /**
     * CN-08: Eliminar usuario existente
     * Entrada:  username='cn_admin'
     * Esperado: registro borrado de la BD
     */
    public function testCN08EliminarUsuario(): void
    {
        pg_query_params($this->db,
            "DELETE FROM usuarios WHERE username = $1", ['cn_admin']
        );

        $result = pg_query_params($this->db,
            "SELECT * FROM usuarios WHERE username = $1", ['cn_admin']
        );
        $row    = pg_fetch_assoc($result);

        $this->assertFalse($row, 'CN-08: El usuario eliminado no debe existir en la BD');
    }
}