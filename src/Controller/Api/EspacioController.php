<?php

namespace App\Controller\Api;

use App\Entity\Espacio;
use App\Repository\EspacioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class EspacioController extends AbstractController
{
    /**
     * @Route("/api/espacio", methods={"GET"})
     */
    public function index(Request $request, EspacioRepository $espacioRepository, PaginatorInterface $paginator): Response
    {
        $currentPage = $request->query->get('page', 1);
        $query = $espacioRepository->getQueryAll();
        $espacios = $paginator->paginate($query, $currentPage, 10);
        $resultado = [];
        foreach ($espacios as $espacio) {
            $resultado[] = [
                'id' => $espacio->getId(),
                'nombre' => $espacio->getNombre()
            ];
        }

        return $this->json([
            'result' => 'ok',
            'espacios' => $resultado
        ]);
    }
    /**
     * @Route("/api/espacio", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        //Nuevo espacio
        $data = $request->toArray();
        if (isset($data['nombre'])) {
            $espacio = new Espacio();
            $espacio->setNombre($data['nombre']);
            $em->persist($espacio);
            $em->flush();
            return $this->json([
                'id' => $espacio->getId(),
            ]);
        } else {
            return new Response('Campo <nombre> no encontrado', 400);
        }
    }
    /**
     * @Route("/api/espacio/{id}", methods={"GET"})
     */
    public function show($id, Request $request, EspacioRepository $espacioRepository, PaginatorInterface $paginator): Response
    {
        $espacio = $espacioRepository->find($id);
        //detalle de un espacio
        if ($espacio == null) {
            throw $this->createNotFoundException('this reference does not exist');
        }
        return $this->json([
            'id' => $espacio->getId(),
            'nombre' => $espacio->getNombre()
        ]);
    }
    /**
     * @Route("/api/espacio/{id}", methods={"PUT"})
     */
    public function edit($id, Request $request, EntityManagerInterface $em, EspacioRepository $espacioRepository): Response
    {
        //Edición de un espacio
        $data = $request->toArray();
        $espacio = $espacioRepository->find($id);
        if ($espacio == null) {
            throw $this->createNotFoundException('Recurso no encontrado');
        }
        if (isset($data['nombre'])) {
            try {
                $espacio->setNombre($data['nombre']);
                $em->flush();
                return new JsonResponse([
                    'result' => 'saved changes',
                    'id' => $espacio->getId()
                ]);
            } catch (\Exception $exception) {
                return $this->json([
                    'message' => $exception->getMessage()
                ], 400);
            };
        } else {
            return new Response('Error en la edición', 400);
        }
    }
    /**
     * @Route("/api/espacio/{id}", methods={"DELETE"})
     */
    public function delete($id, EntityManagerInterface $em, EspacioRepository $espacioRepository): Response
    {
        //Eliminación de un espacio
        $espacio = $espacioRepository->find($id);
        if ($espacio == null) {
            throw $this->createNotFoundException('Recurso no encontrado');
        }
        try {
            $espacioRepository->remove($espacio, true);
        } catch (\Exception $exception) {
            return $this->json([
                'message' => $exception->getMessage()
            ], 400);
        }
        return $this->json([
            'result' => 'Item has been erased'
        ]);
    }
}
