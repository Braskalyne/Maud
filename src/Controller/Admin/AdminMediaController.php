<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/media')]
class AdminMediaController extends AbstractController
{
    #[Route('/', name: 'admin_media_index')]
    public function index(MediaRepository $mediaRepository): Response
    {
        $medias = $mediaRepository->findBy([], ['position' => 'ASC']);
        
        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
        ]);
    }

    #[Route('/new', name: 'admin_media_new')]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $media = new Media();
            $media->setType($request->request->get('type'));
            $media->setTitle($request->request->get('title'));
            $media->setDescription($request->request->get('description'));
            $media->setPosition((int) $request->request->get('position', 0));
            $media->setIsPublished($request->request->get('published') === '1');
            
            if ($media->getType() === 'image') {
                $uploadedFile = $request->files->get('image');
                if ($uploadedFile) {
                    $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $extension = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
                    
                    $uploadedFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/gallery',
                        $newFilename
                    );
                    
                    $media->setFilename($newFilename);
                }
            } elseif ($media->getType() === 'video') {
                $media->setYoutubeUrl($request->request->get('youtube_url'));
            }
            
            $em->persist($media);
            $em->flush();
            
            $this->addFlash('success', 'Média créé avec succès !');
            return $this->redirectToRoute('admin_media_index');
        }
        
        return $this->render('admin/media/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_media_edit')]
    public function edit(Media $media, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $media->setTitle($request->request->get('title'));
            $media->setDescription($request->request->get('description'));
            $media->setPosition((int) $request->request->get('position', 0));
            $media->setIsPublished($request->request->get('published') === '1');
            
            if ($media->getType() === 'video') {
                $media->setYoutubeUrl($request->request->get('youtube_url'));
            }
            
            $em->flush();
            
            $this->addFlash('success', 'Média mis à jour !');
            return $this->redirectToRoute('admin_media_index');
        }
        
        return $this->render('admin/media/edit.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_media_delete', methods: ['POST'])]
    public function delete(Media $media, EntityManagerInterface $em): Response
    {
        if ($media->getType() === 'image' && $media->getFilename()) {
            $filepath = $this->getParameter('kernel.project_dir') . '/public/uploads/gallery/' . $media->getFilename();
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
        
        $em->remove($media);
        $em->flush();
        
        $this->addFlash('success', 'Média supprimé !');
        return $this->redirectToRoute('admin_media_index');
    }
}
