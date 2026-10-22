<?php
/**
 * Controlador de Productos e Inventarios en PHP
 */
require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    public function listar($db) {
        return ProductoModel::getAll($db);
    }

    public function actualizarStock($db) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id_producto'];
            $stock = $_POST['stock'];
            if (ProductoModel::updateStock($db, $id, $stock)) {
                $_SESSION['success'] = "Stock actualizado.";
            } else {
                $_SESSION['error'] = "Error al actualizar stock.";
            }
            header("Location: /admin/dashboard");
            exit;
        }
    }
}
?>
