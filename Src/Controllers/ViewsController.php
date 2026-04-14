<?php
namespace BetelCreativa\Controllers;

use BetelCreativa\Domain\ViewsModel;

class ViewsController extends ViewsModel
{
    public function getViewsController($views)
    {
        if ($views != "") {
            return $this->getViewsModel($views);
        } else {
            return $this->getViewsModel("login");
        }
    }
}