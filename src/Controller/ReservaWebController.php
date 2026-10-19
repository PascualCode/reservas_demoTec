<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\ReservaFiltros;
use App\Service\ReservaService;
use Twig\Environment;

final class ReservaWebController
{
    public function __construct(
        private ReservaService $service,
        private Environment $twig,
    ) {
    }

    public function listado(): void
    {
        /* Pide todas las reservas al servicio 
           y renderiza 'reservas/listado.html.twig' con ellas */
        $reservas = $this->service->getAll(new ReservaFiltros());
        echo $this->twig->render('reservas/listado.html.twig',[
            'titulo' => 'Reservas',
            'reservas' => $reservas
        ]);
    }
}
