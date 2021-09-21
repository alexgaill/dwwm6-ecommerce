<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Form\PanierType;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/produit")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/", name="produit_index", methods={"GET"})
     *
     * @param ProduitRepository $produitRepository
     * @return Response
     */
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="produit_new", methods={"GET", "POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($produit->getImage() !== null) {
                $image = $produit->getImage();
                $nomImage = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('upload_produit'),
                    $nomImage
                );
                $produit->setImage($nomImage);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_show', ["id" => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="produit_show", methods={"GET", "POST"})
     * @param Produit $produit
     * @return Response
     */
    public function show(Produit $produit, Request $request): Response
    {
        $panier = new Panier;
        $form = $this->createForm(PanierType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $panier->setProduit($produit)
                ->setQuantite($form->get('quantite')->getViewData())
                ->setTotal($produit->getPrix() * $form->get('quantite')->getViewData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($panier);
            $em->flush();

            return $this->redirectToRoute('panier');
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Produit $produit
     * @return Response
     */
    public function edit(Request $request, Produit $produit): Response
    {
        $nomImageDefaut = null;
        if ($produit->getImage() !== null) {
            $nomImageDefaut = $produit->getImage();
            dump($nomImageDefaut);
            $produit->setImage(
                new File(
                    $this->getParameter('upload_produit') . '/' . $produit->getImage()
                )
            );
        }
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($produit->getImage() !== null) {
                $image = $produit->getImage();
                $nomImage = md5(uniqid()) . '.' . $image->guessExtension();
                $produit->setImage($nomImage);
                $image->move(
                    $this->getParameter('upload_produit'),
                    $nomImage
                );
            } elseif ($request->get('nomImage') !== null) {
                $produit->setImage($request->get('nomImage'));
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
            "image" => $nomImageDefaut
        ]);
    }

    /**
     * @Route("/{id}/delete", name="produit_delete", methods={"POST"})
     *
     * @param Request $request
     * @param Produit $produit
     * @return Response
     */
    public function delete(Request $request, Produit $produit): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
