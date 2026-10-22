<?php
/**
 * Pruebas de Productos
 * CP-07 — Agregar producto
 * CP-08 — Editar stock
 * CP-12 — Validar stock insuficiente
 */
use PHPUnit\Framework\TestCase;
 
class ProductoTest extends TestCase
{
    private $db;
 
    protected function setUp(): void
    {
        $this->db = getTestDB();
        // Limpia el producto de prueba antes de cada test
        pg_query($this->db, "DELETE FROM productos WHERE codigo_barras = 'TEST-001'");
    }
 
    protected function tearDown(): void
    {
        // Limpia después también por si algo falló a mitad
        pg_query($this->db, "DELETE FROM productos WHERE codigo_barras = 'TEST-001'");
        pg_close($this->db);
    }
 
    /**
     * CP-07: Agregar producto
     * Resultado esperado: producto visible en inventario
     */
    public function testAgregarProducto(): void
    {
        $result = pg_query(
            $this->db,
            "INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria)
             VALUES ('TEST-001', 'Cemento Portland 50kg', 55.00, 100, 10, 1)"
        );
 
        $this->assertNotFalse($result, 'La inserción del producto no debería fallar');
 
        $check = pg_fetch_assoc(pg_query($this->db, "SELECT * FROM productos WHERE codigo_barras = 'TEST-001'"));
 
        $this->assertNotFalse($check,                              'El producto debería existir en la BD');
        $this->assertEquals('Cemento Portland 50kg',               $check['nombre_producto']);
        $this->assertEquals(55.00, (float)$check['precio_venta'],  'El precio debería ser 55.00');
        $this->assertEquals(100,   (int)$check['stock_actual'],    'El stock inicial debería ser 100');
    }
 
    /**
     * CP-08: Editar stock
     * Resultado esperado: stock actualizado en BD
     */
    public function testEditarStock(): void
    {
        // Inserta primero
        pg_query(
            $this->db,
            "INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria)
             VALUES ('TEST-001', 'Cemento', 55.00, 100, 10, 1)"
        );
 
        // Actualiza el stock
        pg_query($this->db, "UPDATE productos SET stock_actual = 75 WHERE codigo_barras = 'TEST-001'");
 
        $check = pg_fetch_assoc(pg_query($this->db, "SELECT stock_actual FROM productos WHERE codigo_barras = 'TEST-001'"));
 
        $this->assertEquals(75, (int)$check['stock_actual'], 'El stock debería haberse actualizado a 75');
    }
 
    /**
     * CP-12: Validar stock insuficiente
     * Resultado esperado: el sistema detecta que no hay stock suficiente
     */
    public function testValidarStockInsuficiente(): void
    {
        pg_query(
            $this->db,
            "INSERT INTO productos (codigo_barras, nombre_producto, precio_venta, stock_actual, stock_minimo, id_categoria)
             VALUES ('TEST-001', 'Cemento', 55.00, 5, 2, 1)"
        );
 
        $row                = pg_fetch_assoc(pg_query($this->db, "SELECT stock_actual FROM productos WHERE codigo_barras = 'TEST-001'"));
        $stockDisponible    = (int)$row['stock_actual'];
        $cantidadSolicitada = 20;
 
        $hayStockSuficiente = $stockDisponible >= $cantidadSolicitada;
 
        $this->assertFalse($hayStockSuficiente, 'Con stock=5 y pedido=20 debería detectarse stock insuficiente');
    }
}