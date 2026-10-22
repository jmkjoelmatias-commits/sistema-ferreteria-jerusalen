<?php
/**
 * Controlador de Cotizaciones en PHP
 */
require_once __DIR__ . '/../models/VentaModel.php';

class CotizacionController {
    public function guardar($db) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_cliente = $_SESSION['id_cliente'];
            $cart_data = $_POST['cart_data'] ?? '[]';
            $cart = json_decode($cart_data, true);

            $total = 0.0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            $id_cotiz = VentaModel::guardarCotizacion($db, $id_cliente, 'Tradicional', $total, $cart_data);
            if ($id_cotiz) {
                $_SESSION['success'] = "Cotización guardada exitosamente.";
                header("Location: /cotizacion/ver?id=" . $id_cotiz);
                exit;
            }
        }
    }
}
?>
