<?php

namespace App\Controller;

use App\Repository\GalleryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function index(GalleryRepository $galleryRepository): Response
    {
        $urls = [];
        
        // Pages statiques
        $staticPages = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => '/presentation', 'priority' => '0.9', 'changefreq' => 'monthly'],
            ['loc' => '/realisations', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/contact', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => '/medias', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/mentions-legales', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => '/politique-de-confidentialite', 'priority' => '0.3', 'changefreq' => 'yearly'],
        ];
        
        foreach ($staticPages as $page) {
            $urls[] = array_merge($page, [
                'lastmod' => (new \DateTime())->format('Y-m-d'),
            ]);
        }
        
        // Pages dynamiques (galeries)
        $galleries = $galleryRepository->findBy(['isPublished' => true]);
        foreach ($galleries as $gallery) {
            $urls[] = [
                'loc' => '/realisations/' . $gallery->getId(),
                'lastmod' => $gallery->getUpdatedAt()?->format('Y-m-d') ?? (new \DateTime())->format('Y-m-d'),
                'priority' => '0.8',
                'changefreq' => 'monthly',
            ];
        }
        
        $response = $this->render('sitemap/index.xml.twig', [
            'urls' => $urls,
            'hostname' => 'https://www.4fam.fr',
        ]);
        
        $response->headers->set('Content-Type', 'application/xml');
        
        return $response;
    }
}
