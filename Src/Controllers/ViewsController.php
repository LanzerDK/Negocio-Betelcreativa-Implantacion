<?php
namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\ViewsModel;

// ViewsController — Resuelve la vista solicitada desde la URL
// Hereda de ViewsModel para usar su whitelist de vistas permitidas
class ViewsController extends ViewsModel
{
    // Obtiene y valida la vista solicitada, devuelve "login" por defecto si está vacía
    public function getViewsController($views)
    {
        if ($views != "") {
            return $this->getViewsModel($views);
        } else {
            return $this->getViewsModel("login");
        }
    }
}