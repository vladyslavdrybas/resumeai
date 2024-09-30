<?php
declare(strict_types=1);

namespace App\Controller\ControlPanel;

use App\Builder\JobBuilder;
use App\Constants\Job\JobStatus;
use App\DataTransferObject\Form\Job\JobDto;
use App\DataTransferObject\ViewResponseDto;
use App\Entity\Job;
use App\EntityTransformer\JobTransformer;
use App\Form\CommandCenter\Job\JobFormType;
use App\Repository\JobRepository;
use App\Security\Voter\VoterPermissions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\EnumRequirement;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    "/cp/job",
    name: "cp_job",
    requirements: [
        'job' => Requirement::UID_RFC4122,
        'status' => new EnumRequirement(JobStatus::class)
    ]
)]
class UserJobController extends AbstractControlPanelController
{
    #[Route(
        path: '/add',
        name: '_add',
        methods: ['GET']
    )]
    public function add(
        JobBuilder $builder
    ): ViewResponseDto {
        $job = $builder->base($this->getUser());

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return $this->response(
            [
                'job' => $job,
            ]
            ,'cp_job_edit',
        );
    }

    #[Route(
        path: '/{job}',
        name: '_show',
        methods: ['GET']
    )]
    #[IsGranted(
        VoterPermissions::VIEW->value,
        'job',
        'Access denied',
        Response::HTTP_UNAUTHORIZED
    )]
    public function show(
        Job $job,
        JobTransformer $transformer
    ): ViewResponseDto {

        $dto = $transformer->reverseTransform($job);
        dump($dto);

        // TODO remove fake skills
        if (!$dto->skills) {
            $dto->skills = ['PHP', 'MySQL', 'Javascript', 'TypeScript', 'Symfony', 'Spryker'];
        }

        $skills = array_map(function(string $skill) {
            return [
                'name' => $skill,
                'match' => rand(0,10) < 6,
            ];
        }, $dto->skills);

        $skillsMatched = array_reduce(
            $skills
            ,function(int $carry, array $skill) {
                return $carry + 1*$skill['match'];
            }
            ,0
        );

        // TODO remove faked documents. display attached documents.
        $documents = [
            [
                'type' => 'resume',
                'title' => 'Resume',
                'link' => '/document/resume'
            ],
            [
                'type' => 'cover letter',
                'title' => 'Cover Letter',
                'link' => '/document/cover-letter'
            ]
        ];

        return $this->response(
            [
                'job' => $dto,
                'jobSkills' => $skills,
                'jobSkillsMatched' => $skillsMatched,
                'jobBenefits' => [],
                'documents' => $documents,
                'navActions' => [
                    'edit' => [
                        'type' => 'link',
                        'title' => 'Edit',
                        'link' => $this->generateUrl('cp_job_edit', ['job' => $dto->id]),
                    ],
                    'pdf' => [
                        'type' => 'link',
                        'title' => 'PDF',
                        'link' => $this->generateUrl('cp_job_edit', ['job' => $dto->id]),
                    ],
                ],
            ]
            ,'control-panel/job/show.html.twig',
        );
    }

    #[Route(
        path: '/{job}/tailor/resume/{resume}',
        name: '_tailor_resume',
        methods: ['GET']
    )]
    #[IsGranted(
        VoterPermissions::VIEW->value,
        'job',
        'Access denied',
        Response::HTTP_UNAUTHORIZED
    )]
    public function tailorResume(
        Job $job
    ): ViewResponseDto {
        return $this->response(
            []
            ,'control-panel/job/show.html.twig',
        );
    }

    #[Route(
        path: '/{job}/edit',
        name: '_edit',
        methods: ['GET', 'POST']
    )]
    #[IsGranted(
        VoterPermissions::VIEW->value,
        'job',
        'Access denied',
        Response::HTTP_UNAUTHORIZED
    )]
    public function edit(
        Request $request,
        Job $job,
        JobTransformer $transformer
    ): ViewResponseDto {
        $dto = $transformer->reverseTransform($job);
        dump($dto);

        $editForm = $this->createForm(JobFormType::class, $dto);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            dump('form submitted');
            /** @var JobDto $dto */
            $dto = $editForm->getData();
            dump($editForm->getData());
            $actionBtn = $editForm->get('actionBtn')->getData();
            dump($actionBtn);

            $dto->isUserAdded = true;

            $entity = $transformer->transform($dto);

            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            if ('view' === $actionBtn) {
                return $this->response(
                    [
                        'job' => $job,
                    ],
                    'cp_job_show',
                );
            }
        }

        return $this->response(
            [
                'job' => $dto,
                'editForm' => $editForm,
                'editFormActions' => ['save', 'view'],
            ]
            ,'control-panel/job/edit.html.twig',
        );
    }

    #[Route(
        path: 's',
        name: '_list',
        methods: ['GET']
    )]
    public function list(
        JobRepository $jobRepository,
        JobTransformer $transformer
    ): ViewResponseDto {
        $entities = $jobRepository->findListForJobBoard($this->getUser());

        $dtos = array_map(function(Job $job) use ($transformer) {
            return $transformer->reverseTransform($job);
        }, $entities);

        $jobs = JobStatus::values();
        $jobs = array_flip($jobs);
        $jobs = array_map(fn() => [], $jobs);
        dump($dtos);

        $statuses = JobStatus::values();
        $statuses = array_filter(
            $statuses,
            fn(string $status) => JobStatus::ARCHIVED->value !== $status
        );

        dump($statuses);

        foreach($dtos as $jobDto) {
            $jobs[$jobDto->status->value][] = $jobDto;
        }

        return $this->response(
            [
                'jobs' => $jobs,
                'jobStatuses' => $statuses,
                'colWidth' => (int) ceil(12/count($statuses)),
            ]
            ,'control-panel/job/list-kanban.html.twig',
        );
    }

    #[Route(
        path: 's/filter/{status}',
        name: '_filter',
        methods: ['GET']
    )]
    public function filter(
        string $status
    ): ViewResponseDto {
        return $this->response(
            [
                'status' => $status,
            ]
            ,'control-panel/job/filter.html.twig',
        );
    }
}