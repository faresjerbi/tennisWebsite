<?php

namespace App\Controller;

use App\Entity\LocationTerrain;
use App\Entity\Partie;
use App\Entity\User;
use App\Form\PartieFormType;
use App\Repository\LocationTerrainRepository;
use App\Repository\PartieRepository;
use App\Repository\UserRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/partie')]
class PartieController extends AbstractController
{
    #[Route('/', name: 'app_index_parite')]
    public function index(EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();

        $query = $entityManager->getRepository(Partie::class)->createQueryBuilder('p')
            ->where('p.user != :user')
            ->setParameter('user', $user)
            ->getQuery();

        $parties = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            3
        );

        $pageCount = $parties->getPageCount();
        $currentPage = $parties->getCurrentPageNumber();
        $startPage = max(1, $currentPage - 2);
        $endPage = min($pageCount, $currentPage + 2);
        $pagesInRange = range($startPage, $endPage);
        $route = 'app_index_parite';
        $query = $request->query->all();
        $pageParameterName = 'page';

        $myParties = $entityManager->getRepository(Partie::class)->findBy(['user' => $user]);
        $locationTerrains = $entityManager->getRepository(LocationTerrain::class)->findAll();
        $mesLocationTerrains = $entityManager->getRepository(LocationTerrain::class)->createQueryBuilder('lt')
            ->leftJoin(User::class, 'u', 'WITH', 'lt.user = u.id')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        return $this->render('partie/index.html.twig', [
            'parties' => $parties,
            'pageCount' => $pageCount,
            'startPage' => $startPage,
            'endPage' => $endPage,
            'pagesInRange' => $pagesInRange,
            'current' => $currentPage,
            'route' => $route,
            'query' => $query,
            'pageParameterName' => $pageParameterName,
            'myParties' => $myParties,
            'locationTerrains' => $locationTerrains,
            'mesLocationTerrains' => $mesLocationTerrains,
        ]);
    }


    #[Route('/admin', name: 'app_index_parite_admin')]
    public function indexAdmin(EntityManagerInterface $entityManager): Response
    {
        $parties = $entityManager->getRepository(Partie::class)->findAll();
        $locationTerrains = $entityManager->getRepository(LocationTerrain::class)->findAll();

        $rdvs = [];
        foreach ($parties as $party) {
            $rdvs[] = [
                'id' => $party->getId(),
                'start' => $party->getDatePrevue()->format('Y-m-d H:i:s'),
                'title' => $party->getClub(),
                'description' => $party->getCreneauHoraire(),
                // Add other fields as needed
            ];
        }

        $dataCalendar = json_encode($rdvs);

        $acceptedCount = 0;
        $waitingCount = 0;

        foreach ($parties as $party) {
            if ($party->getEtat() === 1) {
                $acceptedCount++;
            } else {
                $waitingCount++;
            }
        }

        // Define data array with labels and counts
        $data = [
            ['Les Types', 'Count'], // Column headers
            ['Accepté', $acceptedCount], // Data for "Accepted" label
            ['En attente', $waitingCount] // Data for "Waiting" label
        ];

        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable($data);

        // Set chart options
        $pieChart->getOptions()->setTitle('Les états des parties');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        return $this->render('partie/indexAdmin.html.twig', [
            'parties' => $parties,
            'piechart' => $pieChart,
            'locationTerrains' => $locationTerrains,
            'data' => $dataCalendar
        ]);
    }


    #[Route('/partie/add', name: 'app_partie_add')]
    public function add(Request $request,userRepository $userRepository): Response
    {
        $user = $this->getUser();

        $partie = new Partie();
        $form = $this->createForm(PartieFormType::class, $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($partie->getCommentaire() === null) {
                $partie->setCommentaire('');
            }
            $partie->setEtat(0);
            $partie->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($partie);
            $entityManager->flush();

            return $this->redirectToRoute('app_index_parite');
        }

        return $this->render('partie/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit_partie', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partie $partie): Response
    {
        $form = $this->createForm(PartieFormType::class, $partie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($partie->getCommentaire() === null) {
                $partie->setCommentaire('');
            }
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('app_index_parite');
        }
        return $this->render('partie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/reserve/{id}', name: 'reserve_party')]
    public function reserve(Request $request, EntityManagerInterface $entityManager, Partie $partie,userRepository $userRepository): Response
    {
        $user = $this->getUser();

        $partie->setEtat(1);

        $locationTerrain = new LocationTerrain();
        $locationTerrain->setPartie($partie);
        $locationTerrain->setUser($user);

        $entityManager->persist($locationTerrain);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite');
    }

    #[Route('/partie/delete/{id}', name: 'delete_partie_with_location_front')]
    public function deletePartieWithLocationFront(int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $partieRepository = $entityManager->getRepository(Partie::class);
        $partie = $partieRepository->find($id);

        if (!$partie) {
            throw $this->createNotFoundException('Party not found');
        }

        $locationTerrainRepository = $entityManager->getRepository(LocationTerrain::class);
        $locationTerrains = $locationTerrainRepository->findBy(['partie' => $partie]);

        foreach ($locationTerrains as $locationTerrain) {
            $entityManager->remove($locationTerrain);
        }

        $entityManager->remove($partie);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite');
    }



    #[Route('/delete-location-terrain-and-update-partie-etat/{locationTerrainId}/{partyId}', name: 'delete_location_terrain_and_update_partie_etat')]
    public function deleteLocationTerrainAndUpdatePartieEtat(Request $request, int $locationTerrainId, int $partyId): Response
    {
            $entityManager = $this->getDoctrine()->getManager();
            $locationTerrain = $entityManager->getRepository(LocationTerrain::class)->find($locationTerrainId);
            $partie = $entityManager->getRepository(Partie::class)->find($partyId);

            if (!$locationTerrain || !$partie) {
                throw $this->createNotFoundException('LocationTerrain or Partie not found');
            }
            $entityManager->remove($locationTerrain);
            $partie->setEtat(0);
            $entityManager->flush();

            return $this->redirectToRoute('app_index_parite');
    }
    #[Route('/admin/delete/{id}', name: 'delete_partie_with_location')]
    public function deletePartieWithLocation(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $partieRepository = $entityManager->getRepository(Partie::class);
        $partie = $partieRepository->find($id);

        if (!$partie) {
            throw $this->createNotFoundException('Party not found');
        }

        $locationTerrainRepository = $entityManager->getRepository(LocationTerrain::class);
        $locationTerrain = $locationTerrainRepository->findOneBy(['partie' => $partie]);

        if ($locationTerrain) {
            $entityManager->remove($locationTerrain);
            $entityManager->flush();
        }
        $entityManager->remove($partie);
        $entityManager->flush();

        return $this->redirectToRoute('app_index_parite_admin');
    }

    #[Route('/r/search_partie', name: 'search_partie', methods: ['GET'])]
    public function searchPartie(
        Request $request,
        SerializerInterface $serializer,
        PartieRepository $partieRepository,
        LocationTerrainRepository $locationTerrainRepository
    ): Response {
        $searchValue = $request->query->get('searchValue');
        $orderId = $request->query->get('orderid');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();

        $qb->select('e')
            ->from(Partie::class, 'e')
            ->where($qb->expr()->like('e.datePrevue', ':value'))
            ->orWhere($qb->expr()->like('e.creneauHoraire', ':value'))
            ->setParameter('value', '%' . $searchValue . '%');

        if ($orderId === 'DESC') {
            $qb->orderBy('e.id', 'DESC');
        } else {
            $qb->orderBy('e.id', 'ASC');
        }

        $query = $qb->getQuery();
        $partys = $query->getResult();

        // Fetch locationTerrains for all parties
        $locationTerrains = $locationTerrainRepository->findBy(['partie' => $partys]);

        // Convert party objects to arrays and include reservations
        $partysArray = [];
        foreach ($partys as $party) {
            $partyArray = [
                'id' => $party->getId(),
                'datePrevue' => $party->getDatePrevue()->format('Y-m-d'),
                'creneauHoraire' => $party->getCreneauHoraire(),
                'club' => $party->getClub(),
                'commentaire' => $party->getCommentaire(),
                'etat' => $party->getEtat(),
                'reservéPar' => $party->getReservéPar(),
            ];

            $reservations = [];
            foreach ($locationTerrains as $locationTerrain) {
                if ($locationTerrain->getPartie() == $party) {
                    $reservations[] = $locationTerrain->getUser()->getNom();
                }
            }
            $partyArray['reservations'] = $reservations;

            $partysArray[] = $partyArray;
        }

        $jsonData = $serializer->serialize($partysArray, 'json', [
            'groups' => ['party:read']
        ]);
        return new JsonResponse($jsonData);
    }

}
