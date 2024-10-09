<?php

namespace App\Controller\Admin;

use App\Entity\Logo;
use App\Form\LogoFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;

class LogoAdminController extends AbstractController
{
    #[Route('/logo/edit', name: 'app_logo_admin_edit')]
    public function edit(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le logo existant ou créer un nouveau
        $logo = $entityManager->getRepository(Logo::class)->find(1); // Changez l'ID si nécessaire
        if (!$logo) {
            $logo = new Logo();
        }

        // Création du formulaire
        $form = $this->createForm(LogoFormType::class, $logo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du logo
            $logoFile = $form->get('logo')->getData();
            if ($logoFile) {
                // Supprimer l'ancien logo s'il existe
                if ($logo->getLogo() && file_exists($this->getParameter('kernel.project_dir') . '/public/uploads/' . $logo->getLogo())) {
                    unlink($this->getParameter('kernel.project_dir') . '/public/uploads/' . $logo->getLogo());
                }

                // Générer un nouveau nom de fichier avec le slugger
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newLogoFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                try {
                    $logoFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newLogoFilename
                    );

                    // Mettre à jour le nom du fichier dans l'entité
                    $logo->setLogo($newLogoFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload logo.');
                }
            }

            // Gestion du favicon
            $faviconFile = $form->get('favicon')->getData();
            if ($faviconFile) {
                // Supprimer l'ancien favicon s'il existe
                if ($logo->getFavicon() && file_exists($this->getParameter('kernel.project_dir') . '/public/uploads/' . $logo->getFavicon())) {
                    unlink($this->getParameter('kernel.project_dir') . '/public/uploads/' . $logo->getFavicon());
                }

                // Générer un nouveau nom de fichier pour le favicon
                $originalFaviconFilename = pathinfo($faviconFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFaviconFilename = $slugger->slug($originalFaviconFilename);
                $newFaviconFilename = $safeFaviconFilename . '-' . uniqid() . '.' . $faviconFile->guessExtension();

                try {
                    $faviconFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFaviconFilename
                    );

                    // Mettre à jour le nom du fichier dans l'entité
                    $logo->setFavicon($newFaviconFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload favicon.');
                }
            }

            // Persister les modifications en base de données
            $entityManager->persist($logo);
            $entityManager->flush();

            $this->addFlash('success', 'Images updated successfully!');
            return $this->redirectToRoute('app_logo_admin_edit');
        }

        return $this->render('admin/logo_admin/edit.html.twig', [
            'form' => $form->createView(),
            'newLogoFilename' => $logo->getLogo(),
            'newFaviconFilename' => $logo->getFavicon(), // Ajouter la variable pour le favicon
        ]);
    }
}
