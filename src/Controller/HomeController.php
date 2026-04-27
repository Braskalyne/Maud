<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Form\ContactType;
use App\Repository\GalleryRepository;
use App\Repository\MediaRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/presentation', name: 'app_presentation')]
    public function presentation(): Response
    {
        return $this->render('home/presentation.html.twig');
    }

    #[Route('/realisations', name: 'app_realisations')]
    public function realisations(GalleryRepository $galleryRepository): Response
    {
        $categories = [
            'trompe-loeil' => 'Trompe l\'œil',
            'projets-creatifs' => 'Projets créatifs',
            'univers-jeunesse' => 'Univers jeunesse',
            'evenement' => 'Événement'
        ];

        $galleries = [];
        foreach (array_keys($categories) as $category) {
            $galleries[$category] = $galleryRepository->findPublishedByCategory($category);
        }

        return $this->render('home/realisations.html.twig', [
            'categories' => $categories,
            'galleries' => $galleries,
        ]);
    }

    #[Route('/realisations/{id}', name: 'app_gallery_detail', requirements: ['id' => '\d+'])]
    public function galleryDetail(Gallery $gallery): Response
    {
        if (!$gallery->isPublished()) {
            throw $this->createNotFoundException('Galerie introuvable.');
        }

        return $this->render('home/gallery_detail.html.twig', [
            'gallery' => $gallery,
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Créer l'email
            $email = (new TemplatedEmail())
                ->from(new Address($data['email'], $data['name']))
                ->to(new Address('augustin.gantelmi@gmail.com'))
                ->replyTo($data['email'])
                ->subject('Contact depuis le site - ' . $data['subject'])
                ->htmlTemplate('emails/contact.html.twig')
                ->context([
                    'name' => $data['name'],
                    'user_email' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                ]);

            try {
                $mailer->send($email);
                $this->addFlash('success', 'Votre message a été envoyé avec succès !');
                
                // Redirection pour éviter la resoumission du formulaire
                return $this->redirectToRoute('app_contact');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.');
            }
        }

        return $this->render('home/contact.html.twig', [
            'contactForm' => $form,
        ]);
    }

    #[Route('/medias', name: 'app_medias')]
    public function medias(MediaRepository $mediaRepository): Response
    {
        $medias = $mediaRepository->findAllPublished();

        return $this->render('home/medias.html.twig', [
            'medias' => $medias,
        ]);
    }

    #[Route('/mentions-legales', name: 'app_legal')]
    public function legal(): Response
    {
        return $this->render('home/legal.html.twig');
    }

    #[Route('/politique-de-confidentialite', name: 'app_privacy')]
    public function privacy(): Response
    {
        return $this->render('home/privacy.html.twig');
    }
}
