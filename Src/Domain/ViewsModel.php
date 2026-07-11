<?php
namespace BetelCreativa\Domain;

class ViewsModel
{
    protected function getViewsModel($views)
    {
        // Ruta absoluta a la carpeta Views (dos niveles arriba de Src/Domain)
        $baseViewsDir = __DIR__ . '/../../Views/';
        
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
        $publicViews = ["login", "register", "recoverPassword"];

        if (in_array($views, $whiteList) || in_array($views, $publicViews)) {
            $ruta = $baseViewsDir . $views . '.php';
            if (is_file($ruta)) {
                return $ruta;
            }
        }
        
        // Si no está en listas o el archivo no existe, retorna '404'
        return '404';
    }
}