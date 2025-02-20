<?php

namespace App\Controller;

use App\Repository\NewsPostVectorStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShowStatsController extends AbstractController
{
    #[Route('/show/stats', name: 'app_show_stats')]
    public function index(NewsPostVectorStore $store): Response
    {
        $stats = $store->getBasicStats();

        return $this->render('show_stats/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
