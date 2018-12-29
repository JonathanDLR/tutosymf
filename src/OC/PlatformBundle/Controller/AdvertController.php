<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Entity\Skill;
use OC\PlatformBundle\Form\AdvertType;

class AdvertController extends Controller
{
    public function indexAction($page)
    {
        if ($page < 1) {
            throw new NotFoundHttpException("Page " . $page . " inexistante!");
        }

        $nbPerPage = 3;

        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('OCPlatformBundle:Advert');

        $listAdverts = $repo->getAdverts($page, $nbPerPage);

        $nbPages = ceil(count($listAdverts) / $nbPerPage);

        if ($page > $nbPages) {
            throw new NotFoundHttpException("Page " . $page . " inexistante!");
        }

        return $this->render('@OCPlatform/Advert/index.html.twig', array(
            'listAdverts' => $listAdverts,
            'page' => $page,
            'nbPages' => $nbPages
        ));
    }

    public function menuAction($limit)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('OCPlatformBundle:Advert');

        $listAdverts = $repo->findAll();

        return $this->render('@OCPlatform/Advert/menu.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }

    public function viewAction($id)
    { 
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('OCPlatformBundle:Advert');

        $advert = $repo->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce " . $id . " n'existe pas");
        }

        $listApplication = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));

        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));

        return $this->render('@OCPlatform/Advert/view.html.twig', array(
            'advert' => $advert,
            'listApplication' => $listApplication,
            'listAdvertSkills' => $listAdvertSkills
        ));
    }

    public function addAction(Request $request)
    {
        $advert = new Advert();
        $advert->setDate(new \Datetime());

        $form = $this->createForm(AdvertType::class, $advert);

        $antispam = $this->container->get('oc_platform.antispam');


        // if ($antispam->isSpam($text)) {
        //     throw new \Exception('Votre message a été détecté comme spam!');
        // }
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($advert);
                $em->flush();

                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée');

                return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
            }
        }

        return $this->render('@OCPlatform/Advert/add.html.twig', array('form' => $form->createView()));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce " . $id . " n'existe pas!");
        }

        $listCategories = $em->getRepository('OCPlatformBundle:Category')->findAll();

        foreach ($listCategories as $category) {
            $advert->addCategory($category);
        }

        $em->flush();
      
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag->add('notice', 'Annonce bien modifiée');

            return $this->redirectToRoute('oc_platform_view', array('id' => $id));
        }

        return $this->render('@OCPlatform/Advert/edit.html.twig', array(
            'advert' => $advert
        ));
    }

    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);

        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce " . $id . " n'existe pas!");
        }
        

        foreach ($advert->getCategories() as $category) {
            $advert->removeCategory($category);
        }

        $em->flush();

        return $this->render('@OCPlatform/Advert/view.html.twig');
    }
}