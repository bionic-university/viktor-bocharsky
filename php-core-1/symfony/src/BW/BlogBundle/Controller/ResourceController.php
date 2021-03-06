<?php

namespace BW\BlogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use BW\BlogBundle\Entity\Resource;
use BW\BlogBundle\Form\ResourceType;

/**
 * Resource controller.
 *
 */
class ResourceController extends Controller
{

    /**
     * Lists all Resource entities.
     */
    public function indexAction()
    {
        if ( ! $this->getUser()) {
            return $this->redirect($this->generateUrl('user_sign_in'));
        }
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('BWBlogBundle:Resource')->findBy(array(
            'user' => $this->getUser(),
        ), array(
            'created' => 'DESC',
        ));
        $resources = new ArrayCollection($entities);

        return $this->render('BWBlogBundle:Resource:index.html.twig', array(
            'entities' => $entities,
            'resources' => $resources,
        ));
    }

    /**
     * Lists all tagged Resource entities.
     */
    public function taggedAction($id)
    {
        if ( ! $this->getUser()) {
            return $this->redirect($this->generateUrl('user_sign_in'));
        }
        $em = $this->getDoctrine()->getManager();

        $tag = $em->getRepository('BWBlogBundle:Tag')->find($id);
        if ( ! $tag) {
            throw $this->createNotFoundException("Tag with ID = '{$id}' not found!");
        }
        $tag_id = $tag->getId();

//        $entities = $em->getRepository('BWBlogBundle:Resource')
//            ->createQueryBuilder('r')
//            ->leftJoin('BWBlogBundle:Tag', 't')
//            ->where('r.user = :user')
//            ->andWhere('t.id = 1')
//            ->setParameter('user', $this->getUser())
//            ->getQuery()
//            ->getResult()
//        ;

        $entities = $em->getRepository('BWBlogBundle:Resource')->findBy(array(
            'user' => $this->getUser(),
        ), array(
            'created' => 'DESC',
        ));
        $resources = new ArrayCollection($entities);
        $resources = $resources->filter(function($resource) use ($tag_id) {
            return $resource->getTags()->filter(function($tag) use ($tag_id) {
                return $tag->getId() === $tag_id;
            })->count();
        });

        return $this->render('BWBlogBundle:Resource:index.html.twig', array(
            'entities' => $entities,
            'resources' => $resources,
        ));
    }

    /**
     * Creates a new Resource entity.
     */
    public function createAction(Request $request)
    {
        $entity = new Resource();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('resource'));
        }

        return $this->render('BWBlogBundle:Resource:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
    * Creates a form to create a Resource entity.
    *
    * @param Resource $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Resource $entity)
    {
        $form = $this->createForm(new ResourceType(), $entity, array(
            'em' => $this->getDoctrine()->getManager(),
            'action' => $this->generateUrl('resource_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array(
            'label' => 'Create',
            'attr' => array(
                'class' => 'btn btn-primary',
            ),
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Resource entity.
     *
     */
    public function newAction()
    {
        $entity = new Resource();
        $form   = $this->createCreateForm($entity);

        return $this->render('BWBlogBundle:Resource:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Resource entity.
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWBlogBundle:Resource')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Resource entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BWBlogBundle:Resource:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Resource entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWBlogBundle:Resource')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Resource entity.');
        }

        $form = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BWBlogBundle:Resource:edit.html.twig', array(
            'entity'      => $entity,
            'form'        => $form->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Resource entity.
    *
    * @param Resource $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Resource $entity)
    {
        $form = $this->createForm(new ResourceType(), $entity, array(
            'em' => $this->getDoctrine()->getManager(),
            'action' => $this->generateUrl('resource_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array(
            'label' => 'Update',
            'attr' => array(
                'class' => 'btn btn-primary',
            ),
        ));

        return $form;
    }
    /**
     * Edits an existing Resource entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('BWBlogBundle:Resource')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Resource entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('resource'));
        }

        return $this->render('BWBlogBundle:Resource:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Resource entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('BWBlogBundle:Resource')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Resource entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('resource'));
    }

    /**
     * Creates a form to delete a Resource entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('resource_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => 'Delete',
                'attr' => array(
                    'class' => 'btn btn-danger',
                    'onclick' => "return confirm('Are You sure You want to delete entity?')",
                ),
            ))
            ->getForm()
        ;
    }
}
