<?php

namespace App\Controller;

use App\Entity\ModelEnum;
use App\Repository\NewsPostVectorStore;
use LLPhant\Embeddings\EmbeddingGenerator\Ollama\OllamaEmbeddingGenerator;
use LLPhant\OllamaConfig;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search')]
    public function index(
        Request $request,
        string $ollamaUrl,
        NewsPostVectorStore $store,
    ): Response {
        $documents = [];
        if ($request->get('query')) {
            $model = ModelEnum::from($request->get('model'));
            $config = new OllamaConfig();
            $config->url = $ollamaUrl;
            $config->model = $model->modelName();
            $embeddingGenerator = new OllamaEmbeddingGenerator($config);
            $queryEmbedding = $embeddingGenerator->embedText($request->get('query'));

            $documents = $store->similaritySearch($model, $queryEmbedding);
        }

        return $this->render('search/index.html.twig', [
            'documents' => $documents,
            'query' => $request->get('query'),
            'model' => $request->get('model'),
        ]);
    }
}
