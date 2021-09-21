<?php

namespace App\Controller;

use App\Entity\Panier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier")
     *
     * @return Response
     */
    public function index(): Response
    {
        $paniers = $this->getDoctrine()->getRepository(Panier::class)->findAll();
        $totalTTC = 0;
        foreach ($paniers as $panier) {
            $totalTTC += $panier->getTotal();
        }
        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers,
            'totalTTC' => $totalTTC
        ]);
    }

    /**
     * @Route("/payer", name="payer")
     *
     * @return Response
     */
    public function payer (): Response
    {
        $paniers = $this->getDoctrine()->getRepository(Panier::class)->findAll();
        $em = $this->getDoctrine()->getManager();
        foreach ($paniers as $panier) {
            $em->remove($panier);
        }
        $em->flush();

        return $this->redirectToRoute('panier');
    }
}
