<?php
declare(strict_types=1);

namespace App\Controller\ControlPanel;

use App\Builder\ResumeBuilder;
use App\Constants\RouteRequirements;
use App\DataTransferObject\Form\EmploymentHistory\EmploymentRecordDto;
use App\DataTransferObject\ViewResponseDto;
use App\Entity\Resume;
use App\EntityTransformer\ResumeTransformer;
use App\Form\CommandCenter\Resume\EducationRecordFormType;
use App\Form\CommandCenter\Resume\ResumeFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    "/cp/r",
    name: "cp_resume",
    requirements: [
        'resume' => RouteRequirements::UUID->value,
    ]
)]
class ResumeController extends AbstractControlPanelController
{
    // create a new empty resume and redirect to edit
    #[Route(
        '/add',
        name: '_add',
        methods: ['GET']
    )]
    public function add(
        ResumeBuilder $resumeBuilder
    ): ViewResponseDto {
        $resume = $resumeBuilder->base($this->getUser());

        $this->entityManager->persist($resume);
        $this->entityManager->flush();

        return $this->response(
            [
                'resume' => $resume->getRawId(),
            ],
            'cp_resume_edit'
        );
    }

    #[Route(
        '/{resume}/edit',
        name: '_edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        Resume $resume,
        ResumeBuilder $resumeBuilder,
        ResumeTransformer $resumeTransformer,
        Request $request
    ): ViewResponseDto {
        dump($resume);

        $dto = $resumeTransformer->reverseTransform($resume);
        dump($dto);

        $editForm = $this->createForm(ResumeFormType::class, $dto);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // TODO handle form changes
            dump('saving...');
        }

        return $this->response(
            [
                'editForm' => $editForm,
            ],
            'control-panel/resume/edit.html.twig'
        );
    }
}
