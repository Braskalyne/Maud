<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use App\Entity\GalleryImage;
use App\Repository\GalleryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/gallery')]
class AdminGalleryController extends AbstractController
{
    #[Route('/', name: 'admin_gallery_index')]
    public function index(GalleryRepository $galleryRepository): Response
    {
        $categories = [
            'trompe-loeil' => 'Trompe l\'œil',
            'projets-creatifs' => 'Projets créatifs',
            'univers-jeunesse' => 'Univers jeunesse',
            'evenement' => 'Événement'
        ];
        
        $galleriesByCategory = [];
        foreach (array_keys($categories) as $category) {
            $galleriesByCategory[$category] = $galleryRepository->findBy(
                ['category' => $category],
                ['position' => 'ASC']
            );
        }
        
        return $this->render('admin/gallery/index.html.twig', [
            'categories' => $categories,
            'galleriesByCategory' => $galleriesByCategory,
        ]);
    }

    #[Route('/new', name: 'admin_gallery_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $gallery = new Gallery();
            $gallery->setTitle($request->request->get('title'));
            $gallery->setCategory($request->request->get('category'));
            $gallery->setDescription($request->request->get('description'));
            $gallery->setPosition((int) $request->request->get('position', 0));
            $gallery->setIsPublished($request->request->get('published') === '1');
            
            $em->persist($gallery);
            $em->flush();
            
            $this->addFlash('success', 'Galerie créée avec succès !');
            return $this->redirectToRoute('admin_gallery_edit', ['id' => $gallery->getId()]);
        }
        
        return $this->render('admin/gallery/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_gallery_edit')]
    public function edit(Gallery $gallery, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            
            if ($action === 'update_info') {
                $gallery->setTitle($request->request->get('title'));
                $gallery->setCategory($request->request->get('category'));
                $gallery->setDescription($request->request->get('description'));
                $gallery->setPosition((int) $request->request->get('position', 0));
                $gallery->setIsPublished($request->request->get('published') === '1');
                
                $em->flush();
                $this->addFlash('success', 'Galerie mise à jour !');
            } 
            elseif ($action === 'add_images') {
                $uploadedFiles = $request->files->get('images', []);
                $startPosition = count($gallery->getImages());
                
                foreach ($uploadedFiles as $index => $uploadedFile) {
                    if ($uploadedFile) {
                        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalFilename);
                        $extension = $uploadedFile->getClientOriginalExtension() ?: 'jpg';
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;
                        
                        $uploadedFile->move(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/gallery',
                            $newFilename
                        );
                        
                        $image = new GalleryImage();
                        $image->setFilename($newFilename);
                        $image->setGallery($gallery);
                        $image->setPosition($startPosition + $index);
                        
                        $em->persist($image);
                    }
                }
                
                $em->flush();
                $this->addFlash('success', 'Images ajoutées !');
            }
            elseif ($action === 'update_images') {
                $positions = $request->request->all('positions');
                $captions = $request->request->all('captions');
                
                foreach ($gallery->getImages() as $image) {
                    $id = $image->getId();
                    if (isset($positions[$id])) {
                        $image->setPosition((int) $positions[$id]);
                    }
                    if (isset($captions[$id])) {
                        $image->setCaption($captions[$id] ?: null);
                    }
                }
                
                $em->flush();
                $this->addFlash('success', 'Images mises à jour !');
            }
            
            return $this->redirectToRoute('admin_gallery_edit', ['id' => $gallery->getId()]);
        }
        
        return $this->render('admin/gallery/edit.html.twig', [
            'gallery' => $gallery,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_gallery_delete', methods: ['POST'])]
    public function delete(Gallery $gallery, EntityManagerInterface $em): Response
    {
        $em->remove($gallery);
        $em->flush();
        
        $this->addFlash('success', 'Galerie supprimée !');
        return $this->redirectToRoute('admin_gallery_index');
    }

    #[Route('/image/{id}/delete', name: 'admin_gallery_image_delete', methods: ['POST'])]
    public function deleteImage(GalleryImage $image, EntityManagerInterface $em): Response
    {
        $galleryId = $image->getGallery()->getId();
        
        // Supprimer le fichier physique
        $filepath = $this->getParameter('kernel.project_dir') . '/public/uploads/gallery/' . $image->getFilename();
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        
        $em->remove($image);
        $em->flush();
        
        $this->addFlash('success', 'Image supprimée !');
        return $this->redirectToRoute('admin_gallery_edit', ['id' => $galleryId]);
    }
}
