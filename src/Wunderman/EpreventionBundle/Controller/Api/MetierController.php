<?php

namespace Wunderman\EpreventionBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations
use JMS\Serializer\Annotation as Serializer;

use Wunderman\EpreventionBundle\Entity\Metier;
use Wunderman\EpreventionBundle\Form\MetierType;



class MetierController extends FOSRestController
{
    /**
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/api/metiers")
     */
    public function newAction(Request $request)
    {
        $metier = new Metier();
        $form = $this->createForm(MetierType::class, $metier);

        $form->submit($request->request->all()); // Validation des données

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($metier);
            $em->flush();
            return $metier;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\Get("/api/metiers/{id}")
     * @Rest\Post("/api/metiers")
     */
    public function showAction($id)
    {
        $metier = $this->getDoctrine()
            ->getRepository('EpreventionBundle:Metier')
            ->findOneById($id);

        if (!$metier) {
            throw $this->createNotFoundException(sprintf(
                'Metier inconuue : "%s"',
                $metier
            ));
        }

        return $metier;
    }

    /**
     *
     * @Rest\Get("/api/metiers")
     * @Rest\View(serializerGroups={"Default", "list"})
     */
    public function listAction(Request $request)
    {
         $filter = $request->query->get('filter');

        $qb = $this->getDoctrine()
            ->getRepository('EpreventionBundle:Metier')
            ->findAllQueryBuilder($filter);

        $metiers = $this->get('pagination_factory')
            ->createCollection($qb, $request, 'wunderman_eprevention_api_metier_list');
        /* @var $metiers Metier[] */

        return $metiers;
    }


}
