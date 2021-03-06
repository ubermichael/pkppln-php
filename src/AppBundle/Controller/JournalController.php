<?php

/*
 * Copyright (C) 2015-2016 Michael Joyce <ubermichael@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Journal;
use AppBundle\Form\JournalType;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Journal controller. Journals can be deleted, and it's possible to update
 * the journal health status.
 *
 * @Route("/journal")
 */
class JournalController extends Controller
{
    /**
     * Lists all Journal entities.
     *
     * @Route("/", name="journal")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function indexAction(Request $request)
    {
        /*
         * @var EntityManager
         */
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Journal');
        $qb = $repo->createQueryBuilder('e');
        $status = $request->query->get('status');
        if ($status !== null) {
            $qb->where('e.status = :status');
            $qb->setParameter('status', $status);
        }
        $qb->orderBy('e.id');
        $query = $qb->getQuery();

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            25
        );
        $statuses = $repo->statusSummary();

        return array(
            'entities' => $entities,
            'statuses' => $statuses,
        );
    }

    /**
     * Search journals.
     *
     * In the JournalController, this action must appear before showAction().
     *
     * @Route("/search", name="journal_search")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     *
     * @return array
     */
    public function searchAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $q = $request->query->get('q', '');

        $repo = $em->getRepository('AppBundle:Journal');
        $paginator = $this->get('knp_paginator');

        $entities = array();
        $results = array();
        if ($q !== '') {
            $results = $repo->search($q);

            $entities = $paginator->paginate(
                $results,
                $request->query->getInt('page', 1),
                25
            );
        }

        return array(
            'q' => $q,
            'count' => count($results),
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Journal entity.
     *
     * @Route("/{id}", name="journal_show")
     * @Method("GET")
     * @Template()
     *
     * @param string $id
     *
     * @return array
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Journal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Journal entity.');
        }

        return array(
            'entity' => $entity,
        );
    }

    /**
     * Build and return a form to delete a journal.
     *
     * @param Journal $journal
     *
     * @return Form
     */
    private function createDeleteForm(Journal $journal)
    {
        $formBuilder = $this->createFormBuilder($journal);
        $formBuilder->setAction($this->generateUrl('journal_delete', array('id' => $journal->getId())));
        $formBuilder->setMethod('DELETE');
        $formBuilder->add('confirm', 'checkbox', array(
            'label' => 'Yes, delete this journal',
            'mapped' => false,
            'value' => 'yes',
            'required' => false,
        ));
        $formBuilder->add('delete', 'submit', array('label' => 'Delete'));
        $form = $formBuilder->getForm();

        return $form;
    }

    /**
     * Creates a form to edit a Journal entity.
     *
     * @param Document $entity The entity
     *
     * @return Form The form
     */
    private function createEditForm(Journal $entity)
    {
        $form = $this->createForm(new JournalType(), $entity, array(
            'action' => $this->generateUrl('journal_edit', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Displays a form to edit an existing Journal entity.
     *
     * @Route("/{id}/edit", name="journal_edit")
     * @Method({"GET", "PUT"})
     * @Template()
	 * @param Request $request
	 * @param Page $page
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Journal')->find($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->flush();
            $this->addFlash('success', 'The journal has been updated.');
            return $this->redirectToRoute('journal_show', array('id' => $id));
        }

        return array(
            'entity' => $entity,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Finds and displays a Journal entity.
     *
     * @Route("/{id}/delete", name="journal_delete")
     * @Method({"GET","DELETE"})
     * @Template()
     *
     * @param Request $request
     * @param string  $id
     *
     * @return array
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Journal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Journal entity.');
        }

        if ($entity->countDeposits() > 0) {
            $this->addFlash('warning', 'Journals which have made deposits cannot be deleted.');

            return $this->redirect($this->generateUrl('journal_show', array('id' => $entity->getId())));
        }

        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->get('confirm')->getData()) {
            //            Once JournalUrls are a thing, uncomment these lines.
//            foreach($entity->getUrls() as $url) {
//                $em->remove($url);
//            }

            $whitelist = $em->getRepository('AppBundle:Whitelist')->findOneBy(array('uuid' => $entity->getUuid()));
            if ($whitelist) {
                $em->remove($whitelist);
            }
            $blacklist = $em->getRepository('AppBundle:Whitelist')->findOneBy(array('uuid' => $entity->getUuid()));
            if ($blacklist) {
                $em->remove($blacklist);
            }
            $em->remove($entity);
            $em->flush();

            $this->addFlash('success', 'Journal deleted.');

            return $this->redirect($this->generateUrl('journal'));
        }

        return array(
            'entity' => $entity,
            'form' => $form->createView(),
        );
    }

    /**
     * Update a journal status.
     *
     * @Route("/{id}/status", name="journal_status")
     *
     * @param Request $request
     * @param string  $id
     */
    public function updateStatus(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:Journal')->find($id);
        $status = $request->query->get('status');
        if (!$status) {
            $this->addFlash('error', "The journal's status has not been changed.");
        } else {
            $entity->setStatus($status);
            if ($status === 'healthy') {
                $entity->setContacted(new DateTime());
            }
            $this->addFlash('success', "The journal's status has been updated.");
            $em->flush();
        }

        return $this->redirect($this->generateUrl('journal_show', array('id' => $entity->getId())));
    }

    /**
     * Ping a journal and display the result.
     *
     * @Route("/ping/{id}", name="journal_ping")
     * @Method("GET")
     * @Template()
     *
     * @param string $id
     *
     * @return array
     */
    public function pingAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Journal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Journal entity.');
        }

        try {
            $result = $this->container->get('ping')->ping($entity);
            if (!$result->hasXml() || $result->hasError() || ($result->getHttpStatus() !== 200)) {
                $this->addFlash('warning', "The ping did not complete. HTTP {$result->getHttpStatus()} {$result->getError()}");

                return $this->redirect($this->generateUrl('journal_show', array(
                    'id' => $id,
                )));
            }
            $entity->setContacted(new DateTime());
            $entity->setTitle($result->getJournalTitle());
            $entity->setStatus('healthy');
            $em->flush($entity);

            return array(
                'entity' => $entity,
                'ping' => $result,
            );
        } catch (Exception $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirect($this->generateUrl('journal_show', array(
                'id' => $id,
            )));
        }
    }

    /**
     * Show the deposits for a journal.
     *
     * @Route("/{id}/deposits", name="journal_deposits")
     * @Method("GET")
     * @Template()
     *
     * @param Request $request
     * @param string  $id
     *
     * @return array
     */
    public function showDepositsAction(Request $request, $id)
    {
        /** var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        $journal = $em->getRepository('AppBundle:Journal')->find($id);
        if (!$journal) {
            throw $this->createNotFoundException('Unable to find Journal entity.');
        }

        $qb = $em->getRepository('AppBundle:Deposit')->createQueryBuilder('d')
                ->where('d.journal = :journal')
                ->setParameter('journal', $journal);
        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            25
        );

        return array(
            'journal' => $journal,
            'entities' => $entities,
        );
    }
}
