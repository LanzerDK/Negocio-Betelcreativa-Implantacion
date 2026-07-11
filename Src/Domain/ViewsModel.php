<?php
namespace BetelCreativa\Domain;

// ViewsModel — Enrutador de vistas del sistema
// Determina qué archivo PHP cargar según el nombre de vista solicitado, usando listas blancas
class ViewsModel
{
    // Recibe el nombre de la vista (desde la URL) y retorna la ruta al archivo o '404'
    protected function getViewsModel($views)
    {
        // La carpeta Views está dos niveles arriba: Src/Domain/ → Views/
        $baseViewsDir = __DIR__ . '/../../Views/';

        // Lista blanca de vistas que requieren autenticación
        $whiteList = [
            "dashboard",
            "category",
            "admin-settings",
            "customers",
            "materials",
            "quotes",
            "reports",
            "storage",
            "storage-inventario",
            "facturas",
            "factura-recibo",
            "suppliers",
        ];
        // Vistas públicas (no requieren inicio de sesión)
        $publicViews = ["login", "register", "recoverPassword"];

        // Si la vista solicitada está en alguna de las listas y el archivo existe, lo retorna
        if (in_array($views, $whiteList) || in_array($views, $publicViews)) {
            $ruta = $baseViewsDir . $views . '.php';
            if (is_file($ruta)) {
                return $ruta;
            }
        }

        // Si no está en ninguna lista blanca o el archivo no existe, muestra 404
        return '404';
    }
}